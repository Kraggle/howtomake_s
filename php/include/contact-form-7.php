<?php

add_action( 'wpcf7_init', 'htmmfh_add_form_tag_current_url' );
 
function htmmfh_add_form_tag_current_url() {
    
  wpcf7_add_form_tag( 'currenturl', 'htmmfh_current_url_form_tag_handler', ['name-attr' => false] ); // "current-url" is the type of the form-tag
}
 
function htmmfh_current_url_form_tag_handler( $tag ) {
    global $wp;

    $url = base64_encode( home_url( $wp->request ) );
    return '<input type="hidden" name="current-url" value="'.$url.'" />';

}


/* URL added to Email to continue signup */
add_filter('wpcf7_special_mail_tags', 'howtomake_wpcf7_mail_tags', 10, 3);
function howtomake_wpcf7_mail_tags($output, $name, $html)
{
	global $wp;

    $name = preg_replace('/^wpcf7\./', '_', $name); // for back-compat

    $submission = WPCF7_Submission::get_instance();

    if (! $submission) {
        return $output;
    }

    switch($name) {
        case 'continue-register-url':

			$fullName = ucwords($submission->get_posted_data("your-name"));
			$email = $submission->get_posted_data("your-email");
			$currentUrl = $submission->get_posted_data("current-url");

			if(strpos($fullName, ' ') !== false){
				$lastSpace = strrpos($fullName, ' ');
				$firstName = substr($fullName, 0, $lastSpace);
				$lastName = substr($fullName, $lastSpace);
			}else{
				$firstName = $fullName;
				$lastName = '';
			}

			$postData = (object)[
				'user_first_name' => $firstName,
				'user_last_name' => $lastName,
				'user_email' => $email
			];


			$url = "https://dashboard.howtomakemoneyfromhomeuk.com/register/basic/?postData=" . urlencode(base64_encode(json_encode($postData))) . 
			"&return-url=" . urlencode($currentUrl);
			return $url;
        break;

    }

    return $output;
}