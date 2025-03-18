(function(){
    jQuery(document).ready(function() {
      // Fullscreen Serach Box    
      jQuery(function() {      
        jQuery('.sss-popup').on("click", function(event) {   
          event.preventDefault();
          jQuery("#sss_template_1").addClass("open");
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
  });
})(jQuery);