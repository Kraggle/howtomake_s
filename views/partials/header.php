<?php
// HTML partial/header

$nonce = wp_create_nonce('main_menu_nonce');

// CREATE header-mobile.html
ob_start();

include_once 'header-mobile.php';

$page = ob_get_contents();
ob_end_clean();
$file = __DIR__ . '/header-mobile.html';
@chmod($file, 0755);
$fw = fopen($file, "w");
fputs($fw, $page, strlen($page));
fclose($fw);

global $wp;

?>

<div class="menu-back"></div>

<header class="banner for-desktop" data-nonce="<?= $nonce ?>">
	<div class="upper">
		<div class="trending-box">
			<span class="trending-label">TRENDING:</span>
			<div class="trending-wrap"></div>
		</div>

		<div class="menu-spacer"></div>

		<?php
		if (has_nav_menu('social-menu')) {
			wp_nav_menu([
				'theme_location' => 'social-menu',
				'container' => '',
				'menu_class' => 'social-icons icons',
				'menu_id' => '',
				'echo' => true,
			]);
		}
		?>

		<!-- .et-top-search -->
		<div class="top-search">
			<?php get_template_part('views/widgets/search-form') ?>
		</div>

		<div class="top-account icons">
			<?php if(is_user_logged_in()): ?>
				<div class="account-menu logged-in">
					<div class="fa-icon el-a type-solid svg-user">
						<a href="https://dashboard.howtomakemoneyfromhomeuk.com/" >&nbsp;</a>
						</div>
					<div class="submenu">
						<div class="submenu-inner">
							<ul>
							<li><a href="https://dashboard.howtomakemoneyfromhomeuk.com/">Dashboard</a></li>
							<li><a href="https://dashboard.howtomakemoneyfromhomeuk.com/account/">Account</a></li>
								<li><a href="https://dashboard.howtomakemoneyfromhomeuk.com/account/?action=courses">Courses</a></li>
								<li><a href="/wp-login.php?action=logout">Log Out</a></li>
							</ul>
						</div>
					</div>
				</div>
			<?php else: ?>
				<div class="auth-links logged-out">
					<a href="https://dashboard.howtomakemoneyfromhomeuk.com/login/?return-url=<?php echo urlencode(add_query_arg( $wp->query_vars, home_url( $wp->request ) )); ?>" class="login-link">Sign In</a> / <a href="https://dashboard.howtomakemoneyfromhomeuk.com/register/basic/?return-url=<?php echo urlencode(add_query_arg( $wp->query_vars, home_url( $wp->request ) )); ?>" class="register-link">Register</a>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="lower">
		<a class="brand" href="<?= esc_url(home_url('/')); ?>">
			<?php get_template_part('views/partials/logo') ?>
		</a>

		<div class="menu-spacer"></div>

		<nav class="primary-nav for-desktop">
			<?php
			if (has_nav_menu('primary-menu')) {
				wp_nav_menu([
					'theme_location' => 'primary-menu',
					'container' => '',
					'menu_class' => 'nav',
					'menu_id' => '',
					'echo' => true,
				]);
			}
			?>
		</nav>
	</div>
</header>

<?php
// END
