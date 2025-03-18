<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $spice_starter_sites_importer_filepath,$spice_starter_sites_importer_pro_filepath,$spice_starter_sites_importer_new_filepath;
?>
<section id="spice-starter-sites-importer-dashboard">
    <div class="sss-library-body-wrapper" id="sss-demo-section-inner"> 
        <div class="sss-library-content">
            <div class="sss-library-heading">
                <h2><?php esc_html_e('Free And Premium Demo','spice-starter-sites');?></h2>
            </div>
            <div class="sss-library-content-wrapper sss-business-starter-demo">
                <?php 
                foreach($spice_starter_sites_importer_filepath as $spice_starter_sites_importer_target){?>
                    <div class="sss-content-section sss-starter-pack">
                        <div class="sss-card" >
                            <div class="sss-starter-pack-inner-img" style="background-image:url(<?php echo esc_url($spice_starter_sites_importer_target['image']);?>)"></div>
                            <div class="stater-badge-new">
                                <img decoding="async" width="50" height="24" src="<?php echo esc_url('https://spicethemes.com/wp-content/uploads/2023/06/bedge_6.png'); ?>" class="attachment-large size-large wp-image-7046" alt="" loading="lazy">
                            </div>
                            <div class="sss-card-details">
                                <div class="sss-heading"><h4><?php echo esc_html($spice_starter_sites_importer_target['title']); ?></h4></div>
                                <div class="sss-card-btn">
                                    <a href="<?php echo esc_url($spice_starter_sites_importer_target['demo_link']);?>" class="sss-preview" target="_blank"><?php esc_html_e('Preview','spice-starter-sites'); ?></a>
                                    <a href="#" class="sss-popup" data-theme="<?php echo esc_attr($spice_starter_sites_importer_target['slug']); ?>" data-plugin="<?php echo esc_attr($spice_starter_sites_importer_target['plugin']); ?>" data-title="<?php echo esc_attr($spice_starter_sites_importer_target['title']); ?>"><?php esc_html_e('Install','spice-starter-sites'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                foreach($spice_starter_sites_importer_pro_filepath as $spice_starter_sites_importer_pro_target){?>
                    <div class="sss-content-section sss-starter-pack pro">
                        <div class="sss-card" >
                            <div class="sss-starter-pack-inner-img" style="background-image:url(<?php echo esc_url($spice_starter_sites_importer_pro_target['image']);?>)"></div>
                            <div class="stater-badge-new">
                                <img decoding="async" width="50" height="24" src="<?php echo esc_url('https://spicethemes.com/wp-content/uploads/2023/06/bedge-9.png'); ?>" class="attachment-large size-large wp-image-7046" alt="" loading="lazy">
                            </div>
                            <div class="sss-card-details">
                                <div class="sss-heading"><h4><?php echo esc_html($spice_starter_sites_importer_pro_target['title']);?></h4></div>
                                <div class="sss-card-btn">
                                    <a href="<?php echo esc_url($spice_starter_sites_importer_pro_target['demo_link']);?>" class="sss-preview" target="_blank"><?php esc_html_e('Preview','spice-starter-sites'); ?></a>
                                    <?php if (!class_exists('Newscrunch_Plus')){?>
                                        <a  href="<?php echo esc_url('https://spicethemes.com/newscrunch-plus/');?>" class="sss-buy-now" target="_blank" ><?php esc_html_e('Buy Now','spice-starter-sites'); ?></a>
                                    <?php }else{ ?>
                                        <a  href="#" class="sss-popup" data-theme="<?php echo esc_attr($spice_starter_sites_importer_pro_target['slug']);?>" data-plugin="<?php echo esc_attr($spice_starter_sites_importer_pro_target['plugin']);?>" data-title="<?php echo esc_attr($spice_starter_sites_importer_pro_target['title']);?>"><?php esc_html_e('Install','spice-starter-sites'); ?></a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                if (!empty($spice_starter_sites_importer_new_filepath) && is_array($spice_starter_sites_importer_new_filepath)) {
                    foreach($spice_starter_sites_importer_new_filepath as $spice_starter_sites_importer_new_target){?>
                    <div class="sss-content-section sss-starter-pack new">
                        <div class="sss-card" >
                            <div class="sss-starter-pack-inner-img" style="background-image:url(<?php echo esc_url($spice_starter_sites_importer_new_target['image']);?>)"></div>
                            <div class="sss-card-details">
                                <div class="sss-heading"><h4><?php echo esc_html($spice_starter_sites_importer_new_target['title']);?></h4></div>
                                <div class="stater-badge">
                                <img decoding="async" width="111" height="24" src="<?php echo esc_url('https://spicethemes.com/wp-content/uploads/2023/06/bedge-8.png'); ?>" class="attachment-large size-large wp-image-7046" alt="" loading="lazy">
                            </div>
                            </div>
                        </div>
                    </div>
                    <?php }
                }
                ?>
            </div>
        </div>
    </div>

    <div class="sss_template" id="sss_template_1">
       <button type="button" class="sss_block_close"><?php echo esc_html('Close','spice-starter-sites');?></button>
        <div align="center">
             <?php
            $theme=wp_get_theme();
            if($theme->name==='Newscrunch' || 'Newscrunch Child' == $theme->name || 'Newscrunch child' == $theme->name  || 'NewsBlogger' == $theme->name){?>
                <h3 class="spice-starter-sites-importer-heading"><?php esc_html_e('Demo Import Instructions','spice-starter-sites');?></h3>
                <div align="justify" class="block-container">
                    <div class="importer-header">
                    
                    <p><?php esc_html_e('Spice Starter Sites Importer is a plugin that provides a demo import feature with one click. Follow instructions for better results.','spice-starter-sites');?></p>
                    </div>
                    <div class="importer-body">
                    <ol>
                        <li><?php esc_html_e('Start with a fresh or reset WordPress installation.', 'spice-starter-sites');?></li>
                        <li><?php esc_html_e('Install & activate all recommended plugins.', 'spice-starter-sites');?></li>
                        <li><?php esc_html_e('Click "Import Demo Data," and wait for the success message.', 'spice-starter-sites');?></li>
                        <li><?php esc_html_e('Do not re-import to avoid issues; reset WordPress if re-importing is necessary.', 'spice-starter-sites');?></li>
                        <li><?php esc_html_e('Enjoy your new demo site!', 'spice-starter-sites')?></li>                
                    </ol>
                    <a href="#" class="spice-starter-sites-importer-button next-btn button-primary"><?php esc_html_e('Next', 'spice-starter-sites');?> </a>
                    </div>
                </div>
            <?php 
            } else {?>
                <h3 class="spice-starter-sites-importer-heading"><?php esc_html_e('Spice Starter Sites Importer','spice-starter-sites');?></h3>
                <div align="center" class="spice-starter-sites-importer-sorry-msg">
                    <img src="<?php echo esc_url(SPICE_STARTER_SITES_PLUGIN_URL.'assets/images/not-support.gif');?>"/>
                    <p align="center" class="spice-starter-sites-importer-warning"><span><?php esc_html_e('Sorry!','spice-starter-sites');?></span><?php esc_html_e(' This Theme is not compatible for this plugins','spice-starter-sites');?></p>
                </div>
             <?php 
            }?>   
         </div>
       </div>
    </div>

</section>
<script>
     /* ---------------------------------------------- /*
 * Preloader
 /* ---------------------------------------------- */
(function(){

    jQuery(document).ready(function() {
        jQuery('body').addClass('sss-main');
    // Fullscreen Serach Box    

    jQuery(function() {      
      jQuery('.sss-popup').on("click", function(event) {   
        var theme_data=jQuery(this).data('theme');
        var theme_plugin=jQuery(this).data('plugin');
        var theme_title=jQuery(this).data('title');
        event.preventDefault();
       jQuery("#sss_template_1").addClass("open");
       jQuery(".next-btn").attr("data-theme",theme_data);
       jQuery(".next-btn").attr("data-plugin",theme_plugin);
       jQuery(".next-btn").attr("data-title",theme_title);
        jQuery('#sss_template_1 > form > input[type="search"]').focus();
      });

      jQuery("#sss_template_1,.sss_template button.sss_block_close").on("click keyup", function(event) {
        if (
          event.target == this ||
          event.target.className == "sss_block_close" ||
          event.keyCode == 27
        ) {
         jQuery(this).removeClass("open");
        }
      });

     jQuery("iframe").submit(function(event) {
        event.preventDefault();
        return false;
      });
    });
jQuery('.next-btn').on("click", function(event) {     
        event.preventDefault();
        var theme_data=jQuery(this).data('theme');
        var theme_plugin=jQuery(this).data('plugin');
        var theme_title=jQuery(this).data('title');
        var url='admin.php?page=spice-settings-importer&theme='+theme_data+'&plugin='+theme_plugin+'&title='+theme_title;
        document.location = url;
      });
    });
})(jQuery);
 </script>