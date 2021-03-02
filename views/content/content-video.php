<?php

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package htm_s
 */

//echo content_schema_meta(); 

$channel_id = get_the_terms($post, 'video-channel')[0]->term_id;

ob_start();
the_terms($id, 'video-category', '', '~');
$categories = concat_strings(explode('~', ob_get_contents()));
ob_end_clean();

// This is used to split the content and the related posts apart 
ob_start();
the_content();
echo '<div class="remove">';
$content = ob_get_contents();
echo '</div>';
ob_end_clean();

$doc = phpQuery::newDocument($content);
$content = pq('> p');
$related = pq('.yarpp-related');

ob_start();
print $content->htmlOuter();
$content = ob_get_contents();
ob_end_clean();

ob_start();
print $related->htmlOuter();
$related = ob_get_contents();
ob_end_clean();

?>

<div class="wrapper youtube">
	<div class="content-wrap">
		<div id="v_<?= $id ?>" class="video-wrap">
			<iframe width="1080" height="608" src="https://www.youtube.com/embed/<?php the_field('youtube_video_id'); ?>?autoplay=1" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		</div>
		<div class="display-wrap">
			<?php the_title('<h1 class="title">', '</h1>'); ?>
			<p class="meta">
				<?= custom_number_format(get_video_meta($id, 'statistics.viewCount')) ?> views |
				<span class="date"><?= get_the_date('M jS, Y') ?></span> |
				<?= $categories ?>
			</p>
			<?php 
			$user = wp_get_current_user();
			$userPostInteractions = get_user_post_interactions($user->ID, $post->ID); 
			//var_dump($userPostInteractions);

			
			?>
			<div class="button-wrap" data-nonce="<?= wp_create_nonce('post_interaction_nonce') ?>">
				<div class="video like <?php if($userPostInteractions['like'])echo 'selected'; ?>" data-post-id="<?= $id ?>" data-action="like"><?= get_font_awesome_icon('thumbs-up', 'solid') ?><?php get_template_part('views/widgets/ispinner-white') ?></div>
				<div class="video dislike <?php if($userPostInteractions['dislike'])echo 'selected'; ?>" data-post-id="<?= $id ?>" data-action="dislike"><?= get_font_awesome_icon('thumbs-down', 'solid') ?><?php get_template_part('views/widgets/ispinner-white') ?></div>
				<div class="video fave <?php if($userPostInteractions['fave'])echo 'selected'; ?>" data-post-id="<?= $id ?>" data-action="fave"><?= get_font_awesome_icon('heart', 'solid') ?><?php get_template_part('views/widgets/ispinner-white') ?></div>
			</div>
		</div>

		<!-- <h3>Description</h3> -->
		<div class="desc">
			<?= $content ?>
		</div>
		<div class="show-more"><?= get_font_awesome_icon('chevron-down', 'solid') ?></div>
	</div>
	<div class="side-content">
		<a href="<?= get_channel($channel_id, 'link') ?>" class="channel-title">
			<?php get_channel($channel_id, 'logo'); ?>
			<h2 class="title"><?= get_channel($channel_id) ?></h2>
			<p class="meta">
				<span class="subscribers"><?= custom_number_format(get_channel_meta($channel_id, 'statistics.subscriberCount')) ?> subscribers</span> |
				<span class="videos"><?php get_channel($channel_id, 'count') ?> videos</span>
			</p>
		</a>
		<?= $related ?>
	</div>
</div>

<?php 
// END
