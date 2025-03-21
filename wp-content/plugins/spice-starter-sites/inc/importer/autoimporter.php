<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/** Display verbose errors */
define( 'IMPORT_DEBUG', false );

// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( ! class_exists( 'WP_Importer' ) ) {
  $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_wp_importer ) )
		require $class_wp_importer;
}

$required = array(
	'post_exists'                     => ABSPATH . 'wp-admin/includes/post.php',
	'wp_generate_attachment_metadata' => ABSPATH . 'wp-admin/includes/image.php',
	'comment_exists'                  => ABSPATH . 'wp-admin/includes/comment.php'
);

foreach ( $required as $func => $req_file ) {
	if ( ! function_exists( $func ) )
		require_once $req_file;
}


// include WXR file parsers
require SPICE_STARTER_SITES_PLUGIN_PATH . '/inc/importer/parsers.php';

if ( ! class_exists( 'WP_Importer' ) )
	die( 'WP_Importer not found' );

/**
 * WordPress Importer class for managing the import process of a WXR file
 *
 * @package WordPress
 * @subpackage Importer
 */
if ( ! class_exists( 'Spice_Starter_Sites_Importer_Auto' ) ) {
	class Spice_Starter_Sites_Importer_Auto extends WP_Importer
	{

		var $max_wxr_version = 1.2; // max. supported WXR version

		var $id; // WXR attachment ID

		// information to import from WXR file
		var $version;
		var $authors = array();
		var $posts = array();
		var $terms = array();
		var $categories = array();
		var $tags = array();
		var $base_url = '';

		// mappings from old information to new
		var $processed_authors = array();
		var $author_mapping = array();
		var $processed_terms = array();
		var $processed_posts = array();
		var $post_orphans = array();
		var $processed_menu_items = array();
		var $menu_item_orphans = array();
		var $missing_menu_items = array();

		var $fetch_attachments = false;
		var $url_remap = array();
		var $featured_images = array();

		var $xmlfile = '';
		var $map_user_id = 0;

		function spice_starter_sites_importer_auto_callback( $args ) {

			if ( file_exists( $args['file'] ) ) {

				// for windows systems
				$file = str_replace( '\\', '/', $args['file'] );

				$this->xmlfile = $file;
			}

			if ( isset( $args['map_user_id'] ) )
				$this->map_user_id = $args['map_user_id'];

		}

		/**
		 * Registered callback function for the WordPress Importer
		 *
		 * Manages the three separate stages of the WXR import process
		 */
		function do_import() {

			$this->handle_upload();

			$this->fetch_attachments = $this->allow_fetch_attachments();
			// $this->id = ... the ID was already set in $this->handle_upload();
			$file = get_attached_file( $this->id );

			// get_attachment_file() add the uploaddir to the filename, we have to remove it
			$uploads = wp_upload_dir();
			$file = str_replace( $uploads['basedir'] . '/', '', $file );

			set_time_limit(1200);
			$this->import( $file );

		}

		/**
		 * The main controller for the actual import stage.
		 *
		 * @param string $file Path to the WXR file for importing
		 */
		function import( $file ) {
			add_filter( 'import_post_meta_key', array( $this, 'is_valid_meta_key' ) );
			add_filter( 'http_request_timeout', array( &$this, 'bump_request_timeout' ) );

			$this->import_start( $file );

			$this->get_author_mapping( $this->map_user_id );

			wp_suspend_cache_invalidation( true );
			$this->process_categories();
			$this->process_tags();
			$this->process_terms();
			$this->process_posts();
			wp_suspend_cache_invalidation( false );

			// update incorrect/missing information in the DB
			$this->backfill_parents();
			$this->backfill_attachment_urls();
			$this->remap_featured_images();

			$this->import_end();
		}

		/**
		 * Parses the WXR file and prepares us for the task of processing parsed data
		 *
		 * @param string $file Path to the WXR file for importing
		 */
		function import_start( $file ) {

			if ( ! is_file($file) ) {
				echo '<p><strong>' . esc_html__( 'Sorry, there has been an error.', 'spice-starter-sites' ) . '</strong><br />';
				echo esc_html__( 'The file does not exist, please try again.', 'spice-starter-sites' ) . '</p>';
				die();
			}

			$import_data = $this->parse( $file );

			if ( is_wp_error( $import_data ) ) {
				echo '<p><strong>' . esc_html__( 'Sorry, there has been an error.', 'spice-starter-sites' ) . '</strong><br />';
				echo esc_html( $import_data->get_error_message() ) . '</p>';
				die();
			}

			$this->version = $import_data['version'];
			$this->get_authors_from_import( $import_data );
			$this->posts = $import_data['posts'];
			$this->terms = $import_data['terms'];
			$this->categories = $import_data['categories'];
			$this->tags = $import_data['tags'];
			$this->base_url = esc_url( $import_data['base_url'] );

			wp_defer_term_counting( true );
			wp_defer_comment_counting( true );

			do_action( 'import_start' );
		}

		/**
		 * Performs post-import cleanup of files and the cache
		 */
		function import_end() {
			wp_import_cleanup( $this->id );

			wp_cache_flush();
			foreach ( get_taxonomies() as $tax ) {
				delete_option( "{$tax}_children" );
				_get_term_hierarchy( $tax );
			}

			wp_defer_term_counting( false );
			wp_defer_comment_counting( false );

			do_action( 'import_end' );
		}

		function import_handle_upload() {

			$url = get_template_directory_uri() . str_replace( SPICE_STARTER_SITES_PLUGIN_PATH, '', $this->xmlfile );
			$type = 'application/xml'; // we know the mime type of our file
			$file = $this->xmlfile;
			$filename = basename( $this->xmlfile );

			// Construct the object array
			$object = array( 'post_title' => $filename,
					'post_content' => $url,
					'post_mime_type' => $type,
					'guid' => $url,
					'context' => 'import',
					'post_status' => 'private'
			);

			// Save the data
			$id = wp_insert_attachment( $object, $file );

			// schedule a cleanup for one day from now in case of failed import or missing wp_import_cleanup() call
			wp_schedule_single_event( time() + DAY_IN_SECONDS, 'importer_scheduled_cleanup', array( $id ) );

			return array( 'file' => $file, 'id' => $id );
		}

		/**
		 * Handles the WXR upload and initial parsing of the file to prepare for
		 * displaying author import options
		 *
		 * @return bool False if error uploading or invalid file, true otherwise
		 */
		function handle_upload() {
			$file = $this->import_handle_upload();

			if ( isset( $file['error'] ) ) {
				echo '<p><strong>' . esc_html__( 'Sorry, there has been an error.', 'spice-starter-sites' ) . '</strong><br />';
				echo esc_html( $file['error'] ) . '</p>';
				return false;
			} else if ( ! file_exists( $file['file'] ) ) {
				echo '<p><strong>' . esc_html__( 'Sorry, there has been an error.', 'spice-starter-sites' ) . '</strong><br />';
				// Translators: %s is the path to the export file.
				printf( esc_html__( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'spice-starter-sites' ), esc_html( $file['file'] ) );
				echo '</p>';
				return false;
			}

			$this->id = (int) $file['id'];
			$import_data = $this->parse( $file['file'] );
			if ( is_wp_error( $import_data ) ) {
				echo '<p><strong>' . esc_html__( 'Sorry, there has been an error.', 'spice-starter-sites' ) . '</strong><br />';
				echo esc_html( $import_data->get_error_message() ) . '</p>';
				return false;
			}

			$this->version = $import_data['version'];
			if ( $this->version > $this->max_wxr_version ) {
				echo '<div class="error"><p><strong>';
				// translators: %s is the version number of the WXR file.
				printf( esc_html__( 'This WXR file (version %s) may not be supported by this version of the importer. Please consider updating.', 'spice-starter-sites' ), esc_html($import_data['version']) );
				echo '</strong></p></div>';
			}

			$this->get_authors_from_import( $import_data );

			return true;
		}

		/**
		 * Retrieve authors from parsed WXR data
		 *
		 * Uses the provided author information from WXR 1.1 files
		 * or extracts info from each post for WXR 1.0 files
		 *
		 * @param array $import_data Data returned by a WXR parser
		 */
		function get_authors_from_import( $import_data ) {
			if ( ! empty( $import_data['authors'] ) ) {
				$this->authors = $import_data['authors'];
			// no author information, grab it from the posts
			} else {
				foreach ( $import_data['posts'] as $post ) {
					$login = sanitize_user( $post['post_author'], true );
					if ( empty( $login ) ) {
						// Translators: %s is the name of the author that failed to import.
						printf( esc_html__( 'Failed to import author %s. Their posts will be attributed to the current user.', 'spice-starter-sites' ), esc_html( $post['post_author'] ) );
						echo '<br />';
						continue;
					}

					if ( ! isset($this->authors[$login]) )
						$this->authors[$login] = array(
							'author_login' => $login,
							'author_display_name' => $post['post_author']
						);
				}
			}
		}

		/**
		 * Map old author logins to local user IDs based on decisions made
		 * in import options form. Can map to an existing user, create a new user
		 * or falls back to the current user in case of error with either of the previous
		 */
		function get_author_mapping( $map_users_id ) {
			if ( empty( $this->authors ) )
				return;

			$create_users = $this->allow_create_users();

			foreach ( (array) $this->authors as $i => $data ) {

				$old_login = $data['author_login'];

				// Multisite adds strtolower to sanitize_user. Need to sanitize here to stop breakage in process_posts.
				$santized_old_login = sanitize_user( $old_login, true );
				$old_id = isset( $this->authors[$old_login]['author_id'] ) ? intval($this->authors[$old_login]['author_id']) : false;

				if ( ! $create_users ) {
					$user = get_userdata( intval($map_users_id) );
					if ( isset( $user->ID ) ) {
						if ( $old_id )
							$this->processed_authors[$old_id] = $user->ID;
						$this->author_mapping[$santized_old_login] = $user->ID;
					}
				} else if ( $create_users ) {
					if ( ! empty($this->authors[$i]) ) {
						$user_id = wp_create_user( $this->authors[$i]['author_login'], wp_generate_password() );
					} else if ( $this->version != '1.0' ) {
						$user_data = array(
							'user_login' => $old_login,
							'user_pass' => wp_generate_password(),
							'user_email' => isset( $this->authors[$old_login]['author_email'] ) ? $this->authors[$old_login]['author_email'] : '',
							'display_name' => $this->authors[$old_login]['author_display_name'],
							'first_name' => isset( $this->authors[$old_login]['author_first_name'] ) ? $this->authors[$old_login]['author_first_name'] : '',
							'last_name' => isset( $this->authors[$old_login]['author_last_name'] ) ? $this->authors[$old_login]['author_last_name'] : '',
						);
						$user_id = wp_insert_user( $user_data );
					}

					if ( ! is_wp_error( $user_id ) ) {
						if ( $old_id )
							$this->processed_authors[$old_id] = $user_id;
						$this->author_mapping[$santized_old_login] = $user_id;
					} else {
						// translators: %s: the author's display name
						printf( esc_html__( 'Failed to create new user for %s. Their posts will be attributed to the current user.', 'spice-starter-sites' ), esc_html($this->authors[$old_login]['author_display_name']) );
						if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
							echo ' ' . esc_html($user_id->get_error_message());
						echo '<br />';
					}
				}

				// failsafe: if the user_id was invalid, default to the current user
				if ( ! isset( $this->author_mapping[$santized_old_login] ) ) {
					if ( $old_id )
						$this->processed_authors[$old_id] = (int) get_current_user_id();
					$this->author_mapping[$santized_old_login] = (int) get_current_user_id();
				}
			}
		}

		/**
		 * Create new categories based on import information
		 *
		 * Doesn't create a new category if its slug already exists
		 */
		function process_categories() {
			$this->categories = apply_filters( 'wp_import_categories', $this->categories );

			if ( empty( $this->categories ) )
				return;

			foreach ( $this->categories as $cat ) {
				// if the category already exists leave it alone
				$term_id = term_exists( $cat['category_nicename'], 'category' );
				if ( $term_id ) {
					if ( is_array($term_id) ) $term_id = $term_id['term_id'];
					if ( isset($cat['term_id']) )
						$this->processed_terms[intval($cat['term_id'])] = (int) $term_id;
					continue;
				}

				$category_parent = empty( $cat['category_parent'] ) ? 0 : category_exists( $cat['category_parent'] );
				$category_description = isset( $cat['category_description'] ) ? $cat['category_description'] : '';
				$catarr = array(
					'category_nicename' => $cat['category_nicename'],
					'category_parent' => $category_parent,
					'cat_name' => $cat['cat_name'],
					'category_description' => $category_description
				);

				$id = wp_insert_category( $catarr );
				if ( ! is_wp_error( $id ) ) {
					if ( isset($cat['term_id']) )
						$this->processed_terms[intval($cat['term_id'])] = $id;
				} else {
					// translators: %s: category nicename
					printf( esc_html__( 'Failed to import category %s', 'spice-starter-sites' ), esc_html($cat['category_nicename']) );
					if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
						echo ': ' . esc_html($id->get_error_message());
					echo '<br />';
					continue;
				}
			}

			unset( $this->categories );
		}

		/**
		 * Create new post tags based on import information
		 *
		 * Doesn't create a tag if its slug already exists
		 */
		function process_tags() {
			$this->tags = apply_filters( 'wp_import_tags', $this->tags );

			if ( empty( $this->tags ) )
				return;

			foreach ( $this->tags as $tag ) {
				// if the tag already exists leave it alone
				$term_id = term_exists( $tag['tag_slug'], 'post_tag' );
				if ( $term_id ) {
					if ( is_array($term_id) ) $term_id = $term_id['term_id'];
					if ( isset($tag['term_id']) )
						$this->processed_terms[intval($tag['term_id'])] = (int) $term_id;
					continue;
				}

				$tag_desc = isset( $tag['tag_description'] ) ? $tag['tag_description'] : '';
				$tagarr = array( 'slug' => $tag['tag_slug'], 'description' => $tag_desc );

				$id = wp_insert_term( $tag['tag_name'], 'post_tag', $tagarr );
				if ( ! is_wp_error( $id ) ) {
					if ( isset($tag['term_id']) )
						$this->processed_terms[intval($tag['term_id'])] = $id['term_id'];
				} else {
					// Translators: %s is the tag name that failed to be imported.
					printf( esc_html__( 'Failed to import post tag %s', 'spice-starter-sites' ), esc_html($tag['tag_name']) );
					if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
						echo ': ' . esc_html($id->get_error_message());
					echo '<br />';
					continue;
				}
			}

			unset( $this->tags );
		}

		/**
		 * Create new terms based on import information
		 *
		 * Doesn't create a term its slug already exists
		 */
		function process_terms() {
			$this->terms = apply_filters( 'wp_import_terms', $this->terms );

			if ( empty( $this->terms ) )
				return;

			foreach ( $this->terms as $term ) {
				// if the term already exists in the correct taxonomy leave it alone
				$term_id = term_exists( $term['slug'], $term['term_taxonomy'] );
				if ( $term_id ) {
					if ( is_array($term_id) ) $term_id = $term_id['term_id'];
					if ( isset($term['term_id']) )
						$this->processed_terms[intval($term['term_id'])] = (int) $term_id;
					continue;
				}

				if ( empty( $term['term_parent'] ) ) {
					$parent = 0;
				} else {
					$parent = term_exists( $term['term_parent'], $term['term_taxonomy'] );
					if ( is_array( $parent ) ) $parent = $parent['term_id'];
				}
				$description = isset( $term['term_description'] ) ? $term['term_description'] : '';
				$termarr = array( 'slug' => $term['slug'], 'description' => $description, 'parent' => intval($parent) );

				$id = wp_insert_term( $term['term_name'], $term['term_taxonomy'], $termarr );
				if ( ! is_wp_error( $id ) ) {
					if ( isset($term['term_id']) )
						$this->processed_terms[intval($term['term_id'])] = $id['term_id'];
				} else {
					// Translators: %1$s is the term taxonomy, %2$s is the term name.
					printf( esc_html__( 'Failed to import %1$s %2$s', 'spice-starter-sites' ), esc_html($term['term_taxonomy']), esc_html($term['term_name']) );
					if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
						echo ': ' . esc_html($id->get_error_message());
					echo '<br />';
					continue;
				}
			}

			unset( $this->terms );
		}

		/**
		 * Create new posts based on import information
		 *
		 * Posts marked as having a parent which doesn't exist will become top level items.
		 * Doesn't create a new post if: the post type doesn't exist, the given post ID
		 * is already noted as imported or a post with the same title and date already exists.
		 * Note that new/updated terms, comments and meta are imported for the last of the above.
		 */
		function process_posts() {
			$this->posts = apply_filters( 'wp_import_posts', $this->posts );

			foreach ( $this->posts as $post ) {
				$post = apply_filters( 'wp_import_post_data_raw', $post );

				if ( ! post_type_exists( $post['post_type'] ) ) {
					// Translators: %1$s is the post title, %2$s is the post type.
					printf( esc_html__( 'Failed to import &#8220;%1$s&#8221;: Invalid post type %2$s', 'spice-starter-sites' ), esc_html($post['post_title']), esc_html($post['post_type']) );
					echo '<br />';
					do_action( 'wp_import_post_exists', $post );
					continue;
				}

				if ( isset( $this->processed_posts[$post['post_id']] ) && ! empty( $post['post_id'] ) )
					continue;

				if ( $post['status'] == 'auto-draft' )
					continue;

				if ( 'nav_menu_item' == $post['post_type'] ) {
					$this->process_menu_item( $post );
					continue;
				}

				$post_type_object = get_post_type_object( $post['post_type'] );

				$post_exists = post_exists( $post['post_title'], '', $post['post_date'] );
				if ( $post_exists && get_post_type( $post_exists ) == $post['post_type'] ) {
					// Translators: %1$s is the post type (e.g., "Post"), %2$s is the post title.
					printf( esc_html__( '%1$s &#8220;%2$s&#8221; already exists.', 'spice-starter-sites' ), esc_html($post_type_object->labels->singular_name), esc_html($post['post_title']) );
					echo '<br />';
					$comment_post_ID = $post_id = $post_exists;
				} else {
					$post_parent = (int) $post['post_parent'];
					if ( $post_parent ) {
						// if we already know the parent, map it to the new local ID
						if ( isset( $this->processed_posts[$post_parent] ) ) {
							$post_parent = $this->processed_posts[$post_parent];
						// otherwise record the parent for later
						} else {
							$this->post_orphans[intval($post['post_id'])] = $post_parent;
							$post_parent = 0;
						}
					}

					// map the post author
					$author = sanitize_user( $post['post_author'], true );
					if ( isset( $this->author_mapping[$author] ) )
						$author = $this->author_mapping[$author];
					else
						$author = (int) get_current_user_id();

					$postdata = array(
						'import_id' => $post['post_id'], 'post_author' => $author, 'post_date' => $post['post_date'],
						'post_date_gmt' => $post['post_date_gmt'], 'post_content' => $post['post_content'],
						'post_excerpt' => $post['post_excerpt'], 'post_title' => $post['post_title'],
						'post_status' => $post['status'], 'post_name' => $post['post_name'],
						'comment_status' => $post['comment_status'], 'ping_status' => $post['ping_status'],
						'guid' => $post['guid'], 'post_parent' => $post_parent, 'menu_order' => $post['menu_order'],
						'post_type' => $post['post_type'], 'post_password' => $post['post_password']
					);

					$original_post_ID = $post['post_id'];
					$postdata = apply_filters( 'wp_import_post_data_processed', $postdata, $post );

					if ( 'attachment' == $postdata['post_type'] ) {
						$remote_url = ! empty($post['attachment_url']) ? $post['attachment_url'] : $post['guid'];

						// try to use _wp_attached file for upload folder placement to ensure the same location as the export site
						// e.g. location is 2003/05/image.jpg but the attachment post_date is 2010/09, see media_handle_upload()
						$postdata['upload_date'] = $post['post_date'];
						if ( isset( $post['postmeta'] ) ) {
							foreach( $post['postmeta'] as $meta ) {
								if ( $meta['key'] == '_wp_attached_file' ) {
									if ( preg_match( '%^[0-9]{4}/[0-9]{2}%', $meta['value'], $matches ) )
										$postdata['upload_date'] = $matches[0];
									break;
								}
							}
						}

						$comment_post_ID = $post_id = $this->process_attachment( $postdata, $remote_url );
					} else {
						$comment_post_ID = $post_id = wp_insert_post( $postdata, true );
						do_action( 'wp_import_insert_post', $post_id, $original_post_ID, $postdata, $post );
					}

					if ( is_wp_error( $post_id ) ) {
						// Translators: %1$s is the post type, %2$s is the post title.
						printf( esc_html__( 'Failed to import %1$s &#8220;%2$s&#8221;', 'spice-starter-sites' ), esc_html($post_type_object->labels->singular_name), esc_html($post['post_title']) );
						if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
							echo ': ' . esc_html($post_id->get_error_message());
						echo '<br />';
						continue;
					}

					if ( $post['is_sticky'] == 1 )
						stick_post( $post_id );
				}

				// map pre-import ID to local ID
				$this->processed_posts[intval($post['post_id'])] = (int) $post_id;

				if ( ! isset( $post['terms'] ) )
					$post['terms'] = array();

				$post['terms'] = apply_filters( 'wp_import_post_terms', $post['terms'], $post_id, $post );

				// add categories, tags and other terms
				if ( ! empty( $post['terms'] ) ) {
					$terms_to_set = array();
					foreach ( $post['terms'] as $term ) {
						// back compat with WXR 1.0 map 'tag' to 'post_tag'
						$taxonomy = ( 'tag' == $term['domain'] ) ? 'post_tag' : $term['domain'];
						$term_exists = term_exists( $term['slug'], $taxonomy );
						$term_id = is_array( $term_exists ) ? $term_exists['term_id'] : $term_exists;
						if ( ! $term_id ) {
							$t = wp_insert_term( $term['name'], $taxonomy, array( 'slug' => $term['slug'] ) );
							if ( ! is_wp_error( $t ) ) {
								$term_id = $t['term_id'];
								do_action( 'wp_import_insert_term', $t, $term, $post_id, $post );
							} else {
								// Translators: %1$s is the taxonomy name, %2$s is the term name.
								printf( esc_html__( 'Failed to import %1$s %2$s', 'spice-starter-sites' ), esc_html($taxonomy), esc_html($term['name']) );
								if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
									echo ': ' . esc_html($t->get_error_message());
								echo '<br />';
								do_action( 'wp_import_insert_term_failed', $t, $term, $post_id, $post );
								continue;
							}
						}
						$terms_to_set[$taxonomy][] = intval( $term_id );
					}

					foreach ( $terms_to_set as $tax => $ids ) {
						$tt_ids = wp_set_post_terms( $post_id, $ids, $tax );
						do_action( 'wp_import_set_post_terms', $tt_ids, $ids, $tax, $post_id, $post );
					}
					unset( $post['terms'], $terms_to_set );
				}

				if ( ! isset( $post['comments'] ) )
					$post['comments'] = array();

				$post['comments'] = apply_filters( 'wp_import_post_comments', $post['comments'], $post_id, $post );

				// add/update comments
				if ( ! empty( $post['comments'] ) ) {
					$num_comments = 0;
					$inserted_comments = array();
					foreach ( $post['comments'] as $comment ) {
						$comment_id	= $comment['comment_id'];
						$newcomments[$comment_id]['comment_post_ID']      = $comment_post_ID;
						$newcomments[$comment_id]['comment_author']       = $comment['comment_author'];
						$newcomments[$comment_id]['comment_author_email'] = $comment['comment_author_email'];
						$newcomments[$comment_id]['comment_author_IP']    = $comment['comment_author_IP'];
						$newcomments[$comment_id]['comment_author_url']   = $comment['comment_author_url'];
						$newcomments[$comment_id]['comment_date']         = $comment['comment_date'];
						$newcomments[$comment_id]['comment_date_gmt']     = $comment['comment_date_gmt'];
						$newcomments[$comment_id]['comment_content']      = $comment['comment_content'];
						$newcomments[$comment_id]['comment_approved']     = $comment['comment_approved'];
						$newcomments[$comment_id]['comment_type']         = $comment['comment_type'];
						$newcomments[$comment_id]['comment_parent'] 	  = $comment['comment_parent'];
						$newcomments[$comment_id]['commentmeta']          = isset( $comment['commentmeta'] ) ? $comment['commentmeta'] : array();
						if ( isset( $this->processed_authors[$comment['comment_user_id']] ) )
							$newcomments[$comment_id]['user_id'] = $this->processed_authors[$comment['comment_user_id']];
					}
					ksort( $newcomments );

					foreach ( $newcomments as $key => $comment ) {
						// if this is a new post we can skip the comment_exists() check
						if ( ! $post_exists || ! comment_exists( $comment['comment_author'], $comment['comment_date'] ) ) {
							if ( isset( $inserted_comments[$comment['comment_parent']] ) )
								$comment['comment_parent'] = $inserted_comments[$comment['comment_parent']];
							$comment = wp_filter_comment( $comment );
							$inserted_comments[$key] = wp_insert_comment( $comment );
							do_action( 'wp_import_insert_comment', $inserted_comments[$key], $comment, $comment_post_ID, $post );

							foreach( $comment['commentmeta'] as $meta ) {
								$value = maybe_unserialize( $meta['value'] );
								add_comment_meta( $inserted_comments[$key], $meta['key'], $value );
							}

							$num_comments++;
						}
					}
					unset( $newcomments, $inserted_comments, $post['comments'] );
				}

				if ( ! isset( $post['postmeta'] ) )
					$post['postmeta'] = array();

				$post['postmeta'] = apply_filters( 'wp_import_post_meta', $post['postmeta'], $post_id, $post );

				// add/update post meta
				if ( ! empty( $post['postmeta'] ) ) {
					foreach ( $post['postmeta'] as $meta ) {
						$key = apply_filters( 'import_post_meta_key', $meta['key'], $post_id, $post );
						$value = false;

						if ( '_edit_last' == $key ) {
							if ( isset( $this->processed_authors[intval($meta['value'])] ) )
								$value = $this->processed_authors[intval($meta['value'])];
							else
								$key = false;
						}

						if ( $key ) {
							// export gets meta straight from the DB so could have a serialized string
							if ( ! $value )
								$value = maybe_unserialize( $meta['value'] );

							add_post_meta( $post_id, $key, $value );
							do_action( 'import_post_meta', $post_id, $key, $value );

							// if the post has a featured image, take note of this in case of remap
							if ( '_thumbnail_id' == $key )
								$this->featured_images[$post_id] = (int) $value;
						}
					}
				}
			}

			unset( $this->posts );
		}

		/**
		 * Attempt to create a new menu item from import data
		 *
		 * Fails for draft, orphaned menu items and those without an associated nav_menu
		 * or an invalid nav_menu term. If the post type or term object which the menu item
		 * represents doesn't exist then the menu item will not be imported (waits until the
		 * end of the import to retry again before discarding).
		 *
		 * @param array $item Menu item details from WXR file
		 */
		function process_menu_item( $item ) {
			// skip draft, orphaned menu items
			if ( 'draft' == $item['status'] )
				return;

			$menu_slug = false;
			if ( isset($item['terms']) ) {
				// loop through terms, assume first nav_menu term is correct menu
				foreach ( $item['terms'] as $term ) {
					if ( 'nav_menu' == $term['domain'] ) {
						$menu_slug = $term['slug'];
						break;
					}
				}
			}

			// no nav_menu term associated with this menu item
			if ( ! $menu_slug ) {
				esc_html_e( 'Menu item skipped due to missing menu slug', 'spice-starter-sites' );
				echo '<br />';
				return;
			}

			$menu_id = term_exists( $menu_slug, 'nav_menu' );
			if ( ! $menu_id ) {
				// Translators: %s is the menu slug that caused the skip.
				printf( esc_html__( 'Menu item skipped due to invalid menu slug: %s', 'spice-starter-sites' ), esc_html( $menu_slug ) );
				echo '<br />';
				return;
			} else {
				$menu_id = is_array( $menu_id ) ? $menu_id['term_id'] : $menu_id;
			}

			 foreach ( $item['postmeta'] as $meta ) {
	                ${$meta['key']} = $meta['value'];
	            }

	            if ( 'taxonomy' == $_menu_item_type && isset( $this->processed_terms[intval($_menu_item_object_id)] ) ) {
	                $_menu_item_object_id = $this->processed_terms[intval($_menu_item_object_id)];
	            } else if ( 'post_type' == $_menu_item_type && isset( $this->processed_posts[intval($_menu_item_object_id)] ) ) {
	                $_menu_item_object_id = $this->processed_posts[intval($_menu_item_object_id)];
	            } else if ( 'custom' != $_menu_item_type ) {
	                // associated object is missing or not imported yet, we'll retry later
	                $this->missing_menu_items[] = $item;
	                return;
	            }

	            if ( isset( $this->processed_menu_items[intval($_menu_item_menu_item_parent)] ) ) {
	                $_menu_item_menu_item_parent = $this->processed_menu_items[intval($_menu_item_menu_item_parent)];
	            } else if ( $_menu_item_menu_item_parent ) {
	                $this->menu_item_orphans[intval($item['post_id'])] = (int) $_menu_item_menu_item_parent;
	                $_menu_item_menu_item_parent = 0;
	            }

	            // wp_update_nav_menu_item expects CSS classes as a space separated string
	            $_menu_item_classes = maybe_unserialize( $_menu_item_classes );
	            if ( is_array( $_menu_item_classes ) )
	                $_menu_item_classes = implode( ' ', $_menu_item_classes );

	            $args = array(
	                'menu-item-object-id' => $_menu_item_object_id,
	                'menu-item-object' => $_menu_item_object,
	                'menu-item-parent-id' => $_menu_item_menu_item_parent,
	                'menu-item-position' => intval( $item['menu_order'] ),
	                'menu-item-type' => $_menu_item_type,
	                'menu-item-title' => $item['post_title'],
	                'menu-item-url' => $_menu_item_url,
	                'menu-item-description' => $item['post_content'],
	                'menu-item-attr-title' => $item['post_excerpt'],
	                'menu-item-target' => $_menu_item_target,
	                'menu-item-classes' => $_menu_item_classes,
	                'menu-item-xfn' => $_menu_item_xfn,
	                'menu-item-status' => $item['status']
	            );

	            $id = wp_update_nav_menu_item( $menu_id, 0, $args );
	            if ( $id && ! is_wp_error( $id ) )
	                $this->processed_menu_items[intval($item['post_id'])] = (int) $id;
	        }

		/**
		 * If fetching attachments is enabled then attempt to create a new attachment
		 *
		 * @param array $post Attachment post details from WXR
		 * @param string $url URL to fetch attachment from
		 * @return int|WP_Error Post ID on success, WP_Error otherwise
		 */
		function process_attachment( $post, $url ) {
			if ( ! $this->fetch_attachments )
				return new WP_Error( 'attachment_processing_error',
					__( 'Fetching attachments is not enabled', 'spice-starter-sites' ) );

			// if the URL is absolute, but does not contain address, then upload it assuming base_site_url
			if ( preg_match( '|^/[\w\W]+$|', $url ) )
				$url = rtrim( $this->base_url, '/' ) . $url;

			$upload = $this->fetch_remote_file( $url, $post );
			if ( is_wp_error( $upload ) )
				return $upload;

			if ( $info = wp_check_filetype( $upload['file'] ) )
				$post['post_mime_type'] = $info['type'];
			else
				return new WP_Error( 'attachment_processing_error', __('Invalid file type', 'spice-starter-sites') );

			$post['guid'] = $upload['url'];

			// as per wp-admin/includes/upload.php
			$post_id = wp_insert_attachment( $post, $upload['file'] );
			wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );

			// remap resized image URLs, works by stripping the extension and remapping the URL stub.
			if ( preg_match( '!^image/!', $info['type'] ) ) {
				$parts = pathinfo( $url );
				$name = basename( $parts['basename'], ".{$parts['extension']}" ); // PATHINFO_FILENAME in PHP 5.2

				$parts_new = pathinfo( $upload['url'] );
				$name_new = basename( $parts_new['basename'], ".{$parts_new['extension']}" );

				$this->url_remap[$parts['dirname'] . '/' . $name] = $parts_new['dirname'] . '/' . $name_new;
			}

			return $post_id;
		}

		/**
		 * Attempt to download a remote file attachment
		 *
		 * @param string $url URL of item to fetch
		 * @param array $post Attachment details
		 * @return array|WP_Error Local file location details on success, WP_Error otherwise
		 */
		function fetch_remote_file( $url, $post ) {
			// extract the file name and extension from the url
			$file_name = basename( $url );

			// get placeholder file in the upload dir with a unique, sanitized filename
			$upload = wp_upload_bits( $file_name, null, $post['upload_date'] );
			if ( $upload['error'] )
				return new WP_Error( 'upload_dir_error', $upload['error'] );

			// Use WP_Http class to fetch the remote URL and write it to the placeholder file
	    $http = new WP_Http();
	    $response = $http->request( $url, array( 'method' => 'GET', 'timeout' => 10, 'stream' => true, 'filename' => $upload['file'] ) );

			// request failed
	    if ( is_wp_error( $response ) ) {
	        wp_delete_file( $upload['file'] );
	        return new WP_Error( 'import_file_error', __('Remote server did not respond', 'spice-starter-sites') );
	    }

			// make sure the fetch was successful
	    if ( $response['response']['code'] != 200 ) {
	        wp_delete_file( $upload['file'] );
	        // Translators: %1$d is the response code, %2$s is the response message.
	        return new WP_Error( 'import_file_error', sprintf( __('Remote server returned error response %1$d %2$s', 'spice-starter-sites'), esc_html($response['response']['code']), get_status_header_desc($response['response']['code']) ) );
	    }

	    // Check the filesize
			$filesize = filesize( $upload['file'] );

			if ( isset( $response['headers']['content-length'] ) && $filesize != $response['headers']['content-length'] ) {
	        wp_delete_file( $upload['file'] );
	        return new WP_Error( 'import_file_error', __('Remote file is incorrect size', 'spice-starter-sites') );
	    }

	    if ( 0 == $filesize ) {
		      wp_delete_file( $upload['file'] );
		      return new WP_Error( 'import_file_error', __('Zero size file downloaded', 'spice-starter-sites') );
	    }

			$max_size = (int) $this->max_attachment_size();
	    if ( ! empty( $max_size ) && $filesize > $max_size ) {
	      wp_delete_file( $upload['file'] );
	      // Translators: %s is the maximum allowed file size limit.
	      return new WP_Error( 'import_file_error', sprintf(__('Remote file is too large, limit is %s', 'spice-starter-sites'), size_format($max_size) ) );
	    }

			// keep track of the old and new urls so we can substitute them later
			$this->url_remap[$url] = $upload['url'];
			$this->url_remap[$post['guid']] = $upload['url']; // r13735, really needed?

			// Keep track of the destination if the remote URL is redirected somewhere else
	    if ( isset( $response['headers']['x-final-location'] ) && $response['headers']['x-final-location'] != $url ) {
	        $this->url_remap[$response['headers']['x-final-location']] = $upload['url'];
	    }

			return $upload;
		}

		/**
		 * Attempt to associate posts and menu items with previously missing parents
		 *
		 * An imported post's parent may not have been imported when it was first created
		 * so try again. Similarly for child menu items and menu items which were missing
		 * the object (e.g. post) they represent in the menu
		 */
		function backfill_parents() {
			global $wpdb;

			// find parents for post orphans
			foreach ( $this->post_orphans as $child_id => $parent_id ) {
				$local_child_id = $local_parent_id = false;
				if ( isset( $this->processed_posts[$child_id] ) )
					$local_child_id = $this->processed_posts[$child_id];
				if ( isset( $this->processed_posts[$parent_id] ) )
					$local_parent_id = $this->processed_posts[$parent_id];

				// Assuming $local_child_id and $local_parent_id are already defined
				if ( $local_child_id && $local_parent_id ) {
				    // Define a cache key
				    $cache_key = 'post_parent_' . $local_child_id;

				    // Check if the cached value exists
				    $cached_value = wp_cache_get( $cache_key, 'post_parent_cache' );

				    // If there's no cached value, proceed to update and cache the result.
				    if ( false === $cached_value ) {
				        // Prepare the post array for wp_update_post
				        $post_data = array(
				            'ID' => $local_child_id,
				            'post_parent' => $local_parent_id,
				        );

				        // Update the post using wp_update_post
				        $updated = wp_update_post( $post_data, true );

				        // If the update was successful, cache the new value.
				        if ( ! is_wp_error( $updated ) ) {
				            // Cache the new parent ID.
				            wp_cache_set( $cache_key, $local_parent_id, 'post_parent_cache', HOUR_IN_SECONDS );
				        }
				    }
				}

			}

			// all other posts/terms are imported, retry menu items with missing associated object
			$missing_menu_items = $this->missing_menu_items;
			foreach ( $missing_menu_items as $item )
				$this->process_menu_item( $item );

			// find parents for menu item orphans
			foreach ( $this->menu_item_orphans as $child_id => $parent_id ) {
				$local_child_id = $local_parent_id = 0;
				if ( isset( $this->processed_menu_items[$child_id] ) )
					$local_child_id = $this->processed_menu_items[$child_id];
				if ( isset( $this->processed_menu_items[$parent_id] ) )
					$local_parent_id = $this->processed_menu_items[$parent_id];

				if ( $local_child_id && $local_parent_id )
					update_post_meta( $local_child_id, '_menu_item_menu_item_parent', (int) $local_parent_id );
			}
		}

		/**
		 * Use stored mapping information to update old attachment URLs
		 */
		function backfill_attachment_urls() {
			global $wpdb;
			// make sure we do the longest urls first, in case one is a substring of another
			uksort( $this->url_remap, array(&$this, 'cmpr_strlen') );

			foreach ( $this->url_remap as $from_url => $to_url ) {
				// remap urls in post_content
				$wpdb->query( $wpdb->prepare("UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s)", $from_url, $to_url) );
				// remap enclosure urls
				$result = $wpdb->query( $wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key='enclosure'", $from_url, $to_url) );
			}
		}

		/**
		 * Update _thumbnail_id meta to new, imported attachment IDs
		 */
		function remap_featured_images() {
			// cycle through posts that have a featured image
			foreach ( $this->featured_images as $post_id => $value ) {
				if ( isset( $this->processed_posts[$value] ) ) {
					$new_id = $this->processed_posts[$value];
					// only update if there's a difference
					if ( $new_id != $value )
						update_post_meta( $post_id, '_thumbnail_id', $new_id );
				}
			}
		}

		/**
		 * Parse a WXR file
		 *
		 * @param string $file Path to WXR file for parsing
		 * @return array Information gathered from the WXR file
		 */
		function parse( $file ) {
			$parser = new Spice_Starter_Sites_WXR_Parser();
			return $parser->parse( $file );
		}

		/**
		 * Decide if the given meta key maps to information we will want to import
		 *
		 * @param string $key The meta key to check
		 * @return string|bool The key if we do want to import, false if not
		 */
		function is_valid_meta_key( $key ) {
			// skip attachment metadata since we'll regenerate it from scratch
			// skip _edit_lock as not relevant for import
			if ( in_array( $key, array( '_wp_attached_file', '_wp_attachment_metadata', '_edit_lock' ) ) )
				return false;
			return $key;
		}

		/**
		 * Decide whether or not the importer is allowed to create users.
		 * Default is true, can be filtered via import_allow_create_users
		 *
		 * @return bool True if creating users is allowed
		 */
		function allow_create_users() {
			return apply_filters( 'import_allow_create_users', true );
		}

		/**
		 * Decide whether or not the importer should attempt to download attachment files.
		 * Default is true, can be filtered via import_allow_fetch_attachments. The choice
		 * made at the import options screen must also be true, false here hides that checkbox.
		 *
		 * @return bool True if downloading attachments is allowed
		 */
		function allow_fetch_attachments() {
			return apply_filters( 'import_allow_fetch_attachments', true );
		}

		/**
		 * Decide what the maximum file size for downloaded attachments is.
		 * Default is 0 (unlimited), can be filtered via import_attachment_size_limit
		 *
		 * @return int Maximum attachment file size to import
		 */
		function max_attachment_size() {
			return apply_filters( 'import_attachment_size_limit', 0 );
		}

		/**
		 * Added to http_request_timeout filter to force timeout at 1200 seconds during import
		 * @return int 1200
		 */
		function bump_request_timeout( $val ) {
	        return 1200;
	    }

		// return the difference in length between two strings
		function cmpr_strlen( $a, $b ) {
			return strlen($b) - strlen($a);
		}
	}
} // end class_exists

function spice_starter_sites_auto_import( $args ) {
	
	$defaults = array( 'file' => '', 'map_user_id' => 0);
	$args = wp_parse_args( $args, $defaults );

	$autoimport = new Spice_Starter_Sites_Importer_Auto( $args );
	$autoimport->do_import();

}