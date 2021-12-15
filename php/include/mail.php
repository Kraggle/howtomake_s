<?php



function mailtrap($phpmailer) {

  $debugOptions = get_field('debug', 'option');


  $phpmailer->isSMTP();
  $phpmailer->Host = 'smtp.mailtrap.io';
  $phpmailer->SMTPAuth = true;
  $phpmailer->Port = 2525;
  $phpmailer->Username = $debugOptions['mailtrap_user']; //'52f0414896ef81';
  $phpmailer->Password = $debugOptions['mailtrap_password']; //'e15279224f161c';
}
// $debugOptions = get_field( 'debug', 'option' );
// if($debugOptions['enable_mailtrap'][0] == '1'){
// add_action('phpmailer_init', 'mailtrap');

// }