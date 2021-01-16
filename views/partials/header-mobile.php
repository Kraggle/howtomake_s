<?php
// HTML partial/header-mobile
?>

<header class="banner for-mobile" data-nonce="<?php echo $nonce ?>">
	<div class="lower">
		<div class="hamburger">
			<span></span>
			<span></span>
			<span></span>
		</div>

		<div class="menu-spacer"></div>

		<a class="brand" href="<?php echo esc_url(home_url('/')); ?>">
			<?php get_template_part('views/partials/logo') ?>
		</a>

		<div class="menu-spacer"></div>

		<div class="top-search for-mobile">
			<?php get_template_part('views/widgets/search-form') ?>
		</div>
	</div>

	<nav class="primary-nav for-mobile">
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

</header>

<?php
// END
