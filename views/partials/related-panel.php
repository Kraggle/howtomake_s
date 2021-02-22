<?php // This is used on the single video page for related channels

empty_error_log();

$channel_id = get_the_terms($post, 'video-channel')[0]->term_id;
$terms = get_results(
	"SELECT
	GROUP_CONCAT(DISTINCT(t1.term_id)) AS categories,
	t2.term_id AS channel
	FROM wp_term_relationships tr1
	INNER JOIN wp_posts p
		ON tr1.object_id = p.ID
	INNER JOIN wp_term_taxonomy tt1
		ON tr1.term_taxonomy_id = tt1.term_taxonomy_id
	INNER JOIN wp_term_relationships tr2
		ON tr2.object_id = p.ID
	INNER JOIN wp_terms t2
		ON t2.term_id = tr2.term_taxonomy_id
	INNER JOIN wp_terms t1
		ON t1.term_id = tr1.term_taxonomy_id
	INNER JOIN wp_term_taxonomy tt2
		ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
	WHERE tt1.taxonomy = 'video-category'
	AND tt2.taxonomy = 'video-channel'
	GROUP BY t2.term_id"
);

$total = count(get_terms([
	'video-category'
]));

$my_categories;
$scores = [];
foreach ($terms as $term) {
	$ids = explode(',', $term->categories);
	if ($term->channel == $channel_id) {
		$my_categories = $ids;
		continue;
	}

	$scores[] = (object) [
		'channel'    => $term->channel,
		'categories' => $ids,
		'score'      => 0
	];
}

foreach ($scores as $t) {
	$int = array_intersect($my_categories, $t->categories);
	$t->score = ($total / count($t->categories)) * count($int);
}

usort($scores, function ($a, $b) {
	return $a->score <=> $b->score;
});
$scores = array_slice(array_reverse($scores), 0, 6);

?>

<div class="related-channels">
	<div class="curves top"></div>
	<div class="content">

		<div class="wrap">
			<h4 class="related-title">You may also like...</h4>
			<?php foreach ($scores as $obj) { ?>

				<a href="<?= get_channel($obj->channel, 'link') ?>" class="channel-wrap">
					<?= get_channel($obj->channel, 'logo') ?>
					<h3 class="title"><?= get_channel($obj->channel, 'name') ?></h3>
					<p class="meta">
						<span class="subscribers"><?= custom_number_format(get_channel_meta($obj->channel, 'statistics.subscriberCount')) ?> subscribers</span> |
						<span class="videos"><?php get_channel($obj->channel, 'count') ?> videos</span>
					</p>
				</a>

			<?php } ?>
		</div>

	</div>
	<div class="curves bottom"></div>
</div>

<?php 
// END
