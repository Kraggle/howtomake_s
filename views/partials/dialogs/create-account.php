<?php
    $popupOptions = get_field( 'promo_popup', 'option' );
?><div id="dialog-wrapper" class="dialog-inner" title="" style="display:none;">

    <div class="container">
        <div class="region image-region">
           <div class="inner">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/register-iso.png" /> 
           </div>
        </div>
        <div class="region form-region">
        <?php echo do_shortcode('[contact-form-7 id="' . $popupOptions['contact_form'] . '" title=""]'); ?>
        </div>
    </div>
</div>