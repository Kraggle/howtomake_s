<?php

/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package howtomake_S
 */

$fields = to_object(get_fields(get_queried_object()));
$chevron_down = get_font_awesome_icon('chevron-down', 'regular');

?>
<!doctype html>
<html <?= get_language_attributes() ?>>
<?php get_template_part('views/partials/head') ?>

<body <?php body_class() ?>>
	<?php get_template_part('views/partials/body-top') ?>
	<?php get_template_part('views/partials/loader') ?>
	<div class="body-wrap">
		<?php do_action('get_header') ?>
		<?php get_template_part('views/partials/header') ?>
		<div class="wrap main-container" role="document">
			<div class="body-decor"></div>
			<div class="body-curves"></div>
			<div class="content" data-nonce="<?= wp_create_nonce('custom_search_nonce') ?>">
				<main class="main">

					<div class="top-grid">
						<div class="channel-title">
							<?php get_channel(null, 'logo'); ?>
							<h1 class="title"><?= single_cat_title() ?></h1>
							<p class="meta">
								<span class="subscribers"><?= custom_number_format(get_channel_meta(get_queried_object()->term_id, 'statistics.subscriberCount')) ?> subscribers</span> |
								<span class="videos"><?php get_channel(null, 'count') ?> videos</span>
							</p>
							<div class="social-links">
								<?php get_channel(null, 'social') ?>
							</div>
						</div>

						<div class="right-wrap">

							<div class="detail-wrap">

								<span class="nav prev"><?= get_font_awesome_icon('chevron-left', 'solid') ?></span>
								<span class="nav next"><?= get_font_awesome_icon('chevron-right', 'solid') ?></span>
								<div class="category-box">
									<div class="scroller">
										<?php get_channel(null, 'categories'); ?>
									</div>
								</div>

								<div class="more-info">
									<span>Channel Information</span>
									<span class="icon"><?= $chevron_down ?></span>
								</div>

								<div class="collapse">

									<div class="detail-box">
										<h5 class="detail-head">Description</h5>
										<span class="icon"><?= $chevron_down ?></span>
										<div class="collapse">
											<p><?php get_channel(null, 'description') ?></p>
										</div>
									</div>

									<?php if ((is_array($fields->channel_people) && count($fields->channel_people)) || $fields->people_description) { ?>
										<div class="detail-box">

											<h5 class="detail-head"><?= $fields->people_header ?></h5>
											<span class="icon"><?= $chevron_down ?></span>

											<div class="collapse">
												<?php if ($fields->people_description)
													echo $fields->people_description; ?>

												<?php if (is_array($fields->channel_people) && count($fields->channel_people)) { ?>

													<div class="people">
														<?php foreach ($fields->channel_people as $person) {
															if ($person->name) { ?>
																<a href="<?= $person->url ?: '' ?>" class="person">
																	<img src="<?= wp_get_attachment_image_url($person->image->ID, 'channel') ?>" class="person-image" />
																	<div class="overline"></div>
																	<p class="name"><?= $person->name ?></p>
																</a>
															<?php } ?>
														<?php } ?>
													</div>
												<?php } ?>
											</div>

										</div>
									<?php }

									if (is_array($fields->quick_facts) && count($fields->quick_facts)) { ?>
										<div class="detail-box">

											<h5 class="detail-head">Quick Facts</h5>
											<span class="icon"><?= $chevron_down ?></span>

											<div class="collapse">
												<ul class="facts">
													<?php foreach ($fields->quick_facts as $fact) {
														if ($fact->quick_fact) { ?>
															<li class="fact"><?= $fact->quick_fact ?></li>
														<?php } ?>
													<?php } ?>
												</ul>
											</div>

										</div>
									<?php }

									if (is_array($fields->faqs) && count($fields->faqs)) { ?>
										<div class="detail-box">

											<h5 class="detail-head">FAQs</h5>
											<span class="icon"><?= $chevron_down ?></span>

											<div class="collapse">
												<div class="faqs">
													<?php foreach ($fields->faqs as $faq) {
														if ($faq->faq_question && $faq->faq_answer) { ?>
															<h2 class="question"><?= $faq->faq_question ?></h2>
															<?= $faq->faq_answer ?>
														<?php } ?>
													<?php } ?>
												</div>
											</div>

										</div>
									<?php } ?>
								</div>
							</div>
						</div>

						<div class="featured-video">
							<!-- <h5>Featured Channel Video</h5> -->

							<?php if (have_posts()) {
								the_post(); ?>

								<div id="v_<?= $id ?>" class="video-wrap">
									<iframe width="1080" height="608" src="https://www.youtube.com/embed/<?php the_field('youtube_video_id'); ?>?autoplay=0 " frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
								</div>
							<?php } ?>

						</div>

					</div>

					<div class="list" term="<?= get_channel(null, 'term_id') ?>">
						<div class="results none">
							<label class="name">Results</label>
							<span class="got">20</span>
							<span>/</span>
							<span class="total">164</span>
							<span class="no">None</span>
						</div>
					</div>
				</main>
			</div>
		</div>
		<?php do_action('get_footer') ?>
		<?php get_template_part('views/partials/footer') ?>
		<?php wp_footer() ?>
	</div>
</body>

</html>

<?php
// END
