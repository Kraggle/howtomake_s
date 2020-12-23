<?

/**
 * This function creates a custom login form adding:
 * 1. Placeholder text for the inputs
 * 2. An link for forgotten passwords
 * 3. Changed wrapping elements to div from p
 */
function ks_login_form($args = array()) {
	$defaults = array(
		'echo'           => true,
		'redirect'       => (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
		'form_id'        => 'loginform',
		'label_username' => '',
		'label_password' => '',
		'label_remember' => __('Remember Me'),
		'placeholder_username' => __('Username'),
		'placeholder_password' => __('Password'),
		'label_log_in'   => __('Log In'),
		'id_username'    => 'user_login',
		'id_password'    => 'user_pass',
		'id_remember'    => 'rememberme',
		'id_submit'      => 'wp-submit',
		'remember'       => true,
		'value_username' => '',
		'value_remember' => false,
		'forgot'         => true,
		'label_forgot'   => __('Forgot your password?'),
	);

	$args = wp_parse_args($args, apply_filters('login_form_defaults', $defaults));

	$login_form_top = apply_filters('login_form_top', '', $args);

	$login_form_middle = apply_filters('login_form_middle', '', $args);

	$login_form_bottom = apply_filters('login_form_bottom', '', $args);

	$form = '
        <form name="' . $args['form_id'] . '" id="' . $args['form_id'] . '" action="' . esc_url(site_url('wp-login.php', 'login_post')) . '" method="post">
            ' . $login_form_top . '
            <div class="login-username">
                <label for="' . esc_attr($args['id_username']) . '">' . esc_html($args['label_username']) . '</label>
                <input type="text" name="log" id="' . esc_attr($args['id_username']) . '" class="input" value="' . esc_attr($args['value_username']) . '" size="20" placeholder="' . esc_attr($args['placeholder_username']) . '" />
            </div>
            <div class="login-password">
                <label for="' . esc_attr($args['id_password']) . '">' . esc_html($args['label_password']) . '</label>
                <input type="password" name="pwd" id="' . esc_attr($args['id_password']) . '" class="input" value="" size="20" placeholder="' . esc_attr($args['placeholder_password']) . '" />
			</div>
			' . ($args['forgot'] ? '<a href="' . wp_lostpassword_url() . '" class="forgot">' . esc_html($args['label_forgot']) . '</a>' : '') .
		$login_form_middle . '
            ' . ($args['remember'] ? '<div class="login-remember"><label><input name="rememberme" type="checkbox" id="' . esc_attr($args['id_remember']) . '" value="forever"' . ($args['value_remember'] ? ' checked="checked"' : '') . ' /> ' . esc_html($args['label_remember']) . '</label></div>' : '') . '
            <div class="login-submit">
                <input type="submit" name="wp-submit" id="' . esc_attr($args['id_submit']) . '" class="button button-primary" value="' . esc_attr($args['label_log_in']) . '" />
                <input type="hidden" name="redirect_to" value="' . esc_url($args['redirect']) . '" />
            </div>
            ' . $login_form_bottom . '
        </form>';

	if ($args['echo']) {
		echo $form;
	} else {
		return $form;
	}
}
