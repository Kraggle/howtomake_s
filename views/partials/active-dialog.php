<?php 

$popupOptions = get_field( 'promo_popup', 'option' );
if(dialogShouldShow())get_template_part('views/partials/dialogs/' . $popupOptions['view_template']);



?>