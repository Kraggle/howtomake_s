<?
// HTML partial/header-mobile
?>

<header class="banner for-mobile">
	<div class="lower">
		<div class="hamburger">
			<span></span>
			<span></span>
			<span></span>
		</div>

		<div class="menu-spacer"></div>

		<a class="brand" href="<? echo esc_url(home_url('/')); ?>">
			<? get_template_part('views/partials/logo') ?>
		</a>

		<div class="menu-spacer"></div>

		<div class="top-search for-mobile">
			<? get_template_part('views/widgets/search-form') ?>
		</div>
	</div>

	<nav class="primary-nav for-mobile">
		<?
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

<?
// END
