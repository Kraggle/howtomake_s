<?php


function mailtrap($phpmailer) {
  $phpmailer->isSMTP();
  $phpmailer->Host = 'smtp.mailtrap.io';
  $phpmailer->SMTPAuth = true;
  $phpmailer->Port = 2525;
  $phpmailer->Username = '52f0414896ef81';
  $phpmailer->Password = 'e15279224f161c';
}

add_action('phpmailer_init', 'mailtrap');