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
				'menu_class' => 'social-icons',
				'menu_id' => '',
				'echo' => true,
			]);
		}
		?>

		<!-- .et-top-search -->
		<div class="top-search">
			<?php get_template_part('views/widgets/search-form') ?>
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
