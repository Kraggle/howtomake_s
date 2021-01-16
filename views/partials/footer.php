<?php
// HTML partial/footer
?>

<footer class="foot">
	<div class="foot-clouds"></div>
	<div class="foot-wrap">
		<div class="foot-content">

			<?php // INFO:: Search 
			?>
			<div class="foot-search-label">Search to see if you can make money out of your hobby.</div>
			<?php get_template_part('views/widgets/search-bar') ?>

			<?php // INFO:: Logo 
			?>
			<div class="foot-logo">
				<?php get_template_part('views/partials/logo') ?>
			</div>

			<?php // INFO:: Navigation top 
			?>
			<div class="foot-nav-wrap one">
				<?php
				if (has_nav_menu('footer-nav-1')) {
					wp_nav_menu([
						'theme_location' => 'footer-nav-1',
						'container' => '',
						'menu_class' => 'foot-nav one',
						'menu_id' => '',
						'echo' => true,
					]);
				}
				?>
			</div>

			<?php // INFO:: Navigation bottom 
			?>
			<div class="foot-nav-wrap two">
				<?php
				if (has_nav_menu('footer-nav-2')) {
					wp_nav_menu([
						'theme_location' => 'footer-nav-2',
						'container' => '',
						'menu_class' => 'foot-nav two',
						'menu_id' => '',
						'echo' => true,
					]);
				}
				?>
			</div>

			<?php // INFO:: Icons 
			?>
			<div class="foot-icon-wrap">
				<img src="<?php echo wp_get_attachment_image_url(6490, '') ?>" alt="Other Logos">
			</div>

			<?php // INFO:: Login 
			?>
			<div class="foot-login-wrap">

				<?php if (is_user_logged_in()) { ?>
					<span class="user-hi">Welcome to HTMMFH</span>

					<?php $user = wp_get_current_user() ?>
					<span class="in-as">Logged in as: </span>
					<span class="user-name"><?php echo $user->display_name ?></span>
					<a href="<?php echo wp_logout_url(get_permalink()) ?>" class="log-out">Log Out</a>

					<?php // TODO:: Add members page buttons 
					?>

				<?php } else { ?>

					<span class="user-hi">Log in to HTMMFH</span>

					<?php ks_login_form([
						'echo' => true,
						'redirect' => get_permalink(),
						'label_username' => '',
						'label_password' => '',
					]) ?>

				<?php } ?>

			</div>

			<?php // INFO:: Copywrite 
			?>
			<div class="foot-copy">Copyright © 2020 How To Make Money From Home UK.</div>

			<?php // INFO:: Licence 
			?>
			<div class="foot-claim">All content published on HTMMFH is owned and published from HTMMFH Ltd.</div>
		</div>
	</div>
</footer>


<?php
// END
