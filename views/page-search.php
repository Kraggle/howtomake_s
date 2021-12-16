<?php

/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package howtomake_S
 */

wp_reset_query();

$orderby = [
	(object) [
		'name' => 'Relevance',
		'value' => 'relevance',
		'enabled' => true
	],
	(object) [
		'name' => 'Date Published',
		'value' => 'date',
		'enabled' => true
	],
	(object) [
		'name' => 'Date Modified',
		'value' => 'modified',
		'enabled' => true
	],
	(object) [
		'name' => 'Title',
		'value' => 'title',
		'enabled' => true
	],
	(object) [
		'name' => 'Duration',
		'value' => 'duration',
		'enabled' => true
	],
	(object) [
		'name' => 'Author',
		'value' => 'author',
		'enabled' => false
	],
	(object) [
		'name' => 'Type',
		'value' => 'type',
		'enabled' => false
	],
	(object) [
		'name' => 'Name',
		'value' => 'name',
		'enabled' => false
	],
	(object) [
		'name' => 'Random',
		'value' => 'rand',
		'enabled' => true
	],
];

$taxes = (object) [
	'post' => 'category',
	'video' => 'video-category'
];

$uri = explode('/', $_SERVER['REQUEST_URI']);
if ($uri[1] !== 'search') {
	$_REQUEST['type'] = ($uri[1] == 'cat' ? 'post' : 'video') . '~' . $uri[2];
}

// INFO:: Default search terms
$query = (object) [
	'order' => 'desc',
	'orderby' => isset($_REQUEST['term']) ? 'relevance' : 'date',
	'type' => [
		'video',
		'post'
	]
];

// INFO:: Parse the terms from the uri
foreach ($_REQUEST as $key => $val) {

	if ($key === 'type') {
		$types = explode(',', $val);
		$aTypes = [];

		foreach ($types as $value) {

			if (preg_match('/~/', $value)) {
				$cats = explode('~', $value);
				$type = $cats[0];
				$aTypes[] = $type;
				$tax = $taxes->$type;
				$len = count($cats);

				for ($i = 1; $i < $len; $i++) {
					if ($i === 1) $query->$tax = [];

					$query->$tax[] = $cats[$i];
				}
			} else {
				$aTypes[] = $value;
			}
		}
		$query->$key = $aTypes;
	} else {
		$query->$key = $val;
	}
}

$query->meta_query = [
	'relation' => 'AND',
	'0' => [
		'key' => 'duration_seconds',
		'value' => 120,
		'compare' => '>='
	],
	'1' => [
		'key' => 'duration_seconds',
		'value' => 180,
		'compare' => '<='
	]
];

// logger($query);

$is_post = in_array('post', $query->type);
$is_video = in_array('video', $query->type);

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
			<div class="content">
				<main class="main">

					<div class="mobile-button"><i class="fa-icon type-regular svg-search"></i></div>

					<div class="mobile-wrap">
						<div class="query-box" data-nonce="<?= wp_create_nonce('custom_search_nonce') ?>">

							<div class="search-box">
								<input id="search" type="text" name="s" class="search" placeholder="Search" value="<?= $query->term ?? '' ?>">
								<button class="svg clear"><i class="fa-icon type-light svg-times"></i></button>
								<div class="divider"></div>
								<button class="svg submit"><i class="fa-icon type-regular svg-search"></i></button>
							</div>

							<div id="filters-btn" class="button-wrap filter-btn">
								<label class="name">Filters</label>
								<span class="icon filters"></span>
							</div>

							<div class="results none">
								<label class="name">Results</label>
								<span class="got">20</span>
								<span>/</span>
								<span class="total">164</span>
								<span class="no">None</span>
							</div>

						</div>

						<div class="filter-box closed">

							<div class="dropdown orderby select-wrap">
								<label class="name" for="orderby">Sort by</label>
								<select name="orderby" id="orderby">
									<?php foreach ($orderby as $o) {
										if ($o->enabled) { ?>
											<option value="<?= $o->value ?>" <?= ($o->value === $query->orderby ? ' selected' : '') ?>>
												<?= $o->name ?>
											</option>
									<?php }
									} ?>
								</select>
							</div>

							<div class="toggle toggle-wrap">
								<label class="name">Order</label>
								<label for="asc">ASC</label>
								<input type="radio" name="order" id="asc" value="asc" <?= ($query->order === 'asc' ? 'checked' : '') ?>>
								<label for="desc">DESC</label>
								<input type="radio" name="order" id="desc" value="desc" <?= ($query->order === 'desc' ? 'checked' : '') ?>>
							</div>

							<div class="slider-wrap double duration">
								<label class="name">Duration</label>
								<input id="time_from" type="text" data-index="0" value="0" />
								<input id="time_to" type="text" data-index="1" value="99" />
								<div id="time_slider" max="99"></div>
								<span class="display"><span class="infinity"></span> mins</span>
							</div>

						</div>

						<div class="terms-box closed">

							<div class="checks flex type-box" data-at-least=1>
								<label class="name">Sections</label>
								<div class="check" data-disable="posts">
									<label for="post">Articles</label>
									<input type="checkbox" name="post" id="post" taxonomy="category" <?= ($is_post ? 'checked' : '') ?> enabled="true">
								</div>
								<div class="check" data-disable="videos">
									<label for="video">Videos</label>
									<input type="checkbox" name="video" id="video" taxonomy="video-category" <?= ($is_video ? 'checked' : '') ?> enabled="true">
								</div>
							</div>

							<?php $taxes = get_search_categories(); ?>

							<div class="checks cat-wrap" data-at-least=1>
								<label class="name">Categories</label>
								<button class="any">Select All</button>

								<?php foreach ($taxes as $slug => $tax) {
									$name = isset($tax->post) ? $tax->post->name : $tax->video->name;
									$enabled = 'false';
									$checked = '';
									$disable = '';

									if (isset($tax->post)) {
										unset($tax->post->description);
										if (!isset($tax->video)) $disable = 'posts';
									}

									if (isset($tax->video)) {
										unset($tax->video->description);
										if (!isset($tax->post)) $disable = 'videos';
									}

									if (isset($tax->post) && isset($tax->video))
										$disable = 'posts,videos';

									if (isset($tax->post) && $is_post) {
										$enabled = 'true';
										$checked = 'checked';
										if (isset($query->category) && is_array($query->category) && !in_array($slug, $query->category))
											$checked = '';
									}

									if (isset($tax->video) && $is_video) {
										$enabled = 'true';
										$checked = 'checked';
										if (isset($query->{'video-category'}) && is_array($query->{'video-category'}) && !in_array($slug, $query->{'video-category'}))
											$checked = '';
									}
								?>

									<div class="check">
										<label for="_<?= $slug ?>"><?= $name ?></label>
										<input id="_<?= $slug ?>" type="checkbox" enabled="<?= $enabled ?>" <?= $checked ?> data-object="<?= htmlspecialchars(json_encode($tax)) ?>" disable="<?= $disable ?>">

										<div class="cat-menu" slug="_<?= $slug ?>" data-object=" <?= htmlspecialchars(json_encode($tax)) ?>"></div>
									</div>
								<?php } ?>
							</div>
						</div>
					</div>

					<div class="list closed"></div>

				</main>
			</div>
		</div>

		<?php do_action('get_footer') ?>
		<?php get_template_part('views/partials/footer') ?>
		<?php wp_footer() ?>
	</div>
	<?php get_template_part('views/partials/body-bottom') ?>
</body>

</html>

<?php
// END
