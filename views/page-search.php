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
	'orderby' => $_REQUEST['term'] ? 'relevance' : 'date',
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

// error_log(json_encode($query));

?>
<!doctype html>
<html <?php echo get_language_attributes() ?>>
<?php get_template_part('views/partials/head') ?>

<body <?php body_class() ?>>
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
						<div class="query-vars" data-nonce="<?php echo wp_create_nonce('custom_search_nonce') ?>">

							<div class="search-box">
								<input id="search" type="text" name="s" class="search" placeholder="Search" value="<?php echo $query->term ?>">
								<button class="svg clear"><i class="fa-icon type-light svg-times"></i></button>
								<div class="divider"></div>
								<button class="svg submit"><i class="fa-icon type-regular svg-search"></i></button>
							</div>

							<div class="dropdown orderby select-wrap">
								<label class="name" for="orderby">Sort by</label>
								<select name="orderby" id="orderby">
									<?php foreach ($orderby as $o) {
										if ($o->enabled) { ?>
											<option value="<?php echo $o->value ?>" <?php echo ($o->value === $query->orderby ? ' selected' : '') ?>>
												<?php echo $o->name ?>
											</option>
									<?php }
									} ?>
								</select>
							</div>

							<div class="toggle toggle-wrap">
								<label class="name">Order</label>
								<label for="asc">ASC</label>
								<input type="radio" name="order" id="asc" value="asc" <?php echo ($query->order === 'asc' ? 'checked' : '') ?>>
								<label for="desc">DESC</label>
								<input type="radio" name="order" id="desc" value="desc" <?php echo ($query->order === 'desc' ? 'checked' : '') ?>>
							</div>

							<div class="results none">
								<label class="name">Results</label>
								<span class="got">20</span>
								<span>/</span>
								<span class="total">164</span>
								<span class="no">None</span>
							</div>

						</div>

						<div class="which-box">

							<div class="checks flex type-box" data-at-least=1>
								<label class="name">Type</label>
								<div class="check" data-disable="posts">
									<label for="post">Articles</label>
									<input type="checkbox" name="post" id="post" <?php echo (in_array('post', $query->type) ? 'checked' : '') ?> enabled="true">
								</div>
								<div class="check" data-disable="videos">
									<label for="video">Videos</label>
									<input type="checkbox" name="video" id="video" <?php echo (in_array('video', $query->type) ? 'checked' : '') ?> enabled="true">
								</div>
								<!-- <div class="check">
									<label for="page">Pages</label>
									<input type="checkbox" name="page" id="page">
								</div> -->
							</div>

							<?php $args = array(
								'taxonomy' => 'category',
								'orderby' => 'name',
								'order' => 'ASC'
							);
							$taxes = get_terms($args);
							$enabled = in_array('post', $query->type); ?>

							<div class="checks cat-wrap" tax="category" data-at-least=1 data-include="videos" data-name="posts" enabled="<?php echo json_encode($enabled) ?>">
								<label class="name">Article Categories</label>
								<button class="any">Select All</button>

								<?php foreach ($taxes as $tax) {
									$checked = 'checked';
									if (is_array($query->category)) {
										if (!in_array($tax->slug, $query->category))
											$checked = '';
									} elseif (!$enabled) {
										$checked = '';
									} ?>

									<div class="check">
										<label for="_<?php echo $tax->term_id ?>"><?php echo $tax->name ?></label>
										<input type="checkbox" name="<?php echo $tax->slug ?>" id="_<?php echo $tax->term_id ?>" tax="<?php echo $tax->taxonomy ?>" enabled="<?php echo json_encode($enabled) ?>" <?php echo $checked ?>>
									</div>
								<?php } ?>
							</div>

							<?php $args = array(
								'taxonomy' => 'video-category',
								'orderby' => 'name',
								'order' => 'ASC'
							);
							$taxes = get_terms($args);
							$enabled = in_array('video', $query->type); ?>

							<div class="checks cat-wrap" tax="video-category" data-at-least=1 data-include="posts" data-name="videos" enabled="<?php echo json_encode($enabled) ?>">
								<label class="name">Video Categories</label>
								<button class="any">Select All</button>

								<?php foreach ($taxes as $tax) {
									$checked = 'checked';
									$cat = 'video-category';
									if (is_array($query->$cat)) {
										if (!in_array($tax->slug, $query->$cat))
											$checked = '';
									} elseif (!$enabled) {
										$checked = '';
									} ?>

									<div class="check">
										<label for="_<?php echo $tax->term_id ?>"><?php echo $tax->name ?></label>
										<input type="checkbox" name="<?php echo $tax->slug ?>" id="_<?php echo $tax->term_id ?>" tax="<?php echo $tax->taxonomy ?>" enabled="<?php echo json_encode($enabled) ?>" <?php echo $checked ?>>
									</div>
								<?php } ?>
							</div>
						</div>
					</div>

					<div class="list"></div>

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
