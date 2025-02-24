<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'prueba' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '-k,kxFW}:WN_U1shF5DT+S`v37}a&Un+sL}}JmM}=vVV#BTgCXwx.?EG-0elpPj8' );
define( 'SECURE_AUTH_KEY',  '5,wIzKbppko:jm|nGW*|_#o{OoCb+NbI=HL4l5HpGbH00kpzIrAUcr%9*+eZv.@k' );
define( 'LOGGED_IN_KEY',    '.8jOeDxOcDNm[xpe/G%[)@5O9dM`t,L@YUa;X(i)YmOMCj}Q9h)[~+FpQ6$G&>!:' );
define( 'NONCE_KEY',        'x;*IQG-<cKJ,zjp3U+x+dvkc4jR@.6pk6U,XPtst{(BSWPdOQVPZP?&THQW|LTzS' );
define( 'AUTH_SALT',        'd`^UW{@:?V<MvEc+g8Xe:4DltdYWb,vhYDco8RrPz=F13q%sc=LYjt+`6LO+T);z' );
define( 'SECURE_AUTH_SALT', '<7GK-) wN l*B6_EDEv!~10svAa#K5ii^:F:m-fQWXE@k]LXM{WMOg| (*&;Z8/z' );
define( 'LOGGED_IN_SALT',   'uk?7Z2Fon8+5,!CX^OH~g+qNFaV aR>tTasD<sXB+cRzBjW[b1qK0=+ntrH*L&om' );
define( 'NONCE_SALT',       '9w%abA,u 3=S=Isa|kXS$dR5=Q;7[1?pK(i(0_AKfaXww@[hH]y:}IbL7Dg}mRR)' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
