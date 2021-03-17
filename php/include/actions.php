<?php

global $stop_words_default;
$stop_words_default = ["a", "able", "about", "above", "abroad", "accordance", "according", "accordingly", "across", "actually", "added", "after", "afterwards", "again", "against", "ago", "ah", "ahead", "ain't", "almost", "along", "alongside", "already", "also", "although", "always", "am", "amid", "amidst", "among", "amongst", "an", "and", "another", "any", "anybody", "anyhow", "anymore", "anyone", "anything", "anyway", "anyways", "anywhere", "apart", "apparently", "approximately", "are", "aren't", "around", "as", "aside", "at", "awfully", "be", "became", "because", "become", "becomes", "becoming", "been", "before", "beforehand", "began", "begin", "begins", "behind", "being", "beings", "besides", "between", "beyond", "both", "briefly", "but", "by", "came", "can", "cannot", "can't", "certainly", "clearly", "come", "comes", "concerning", "consequently", "corresponding", "could", "couldn't", "currently", "definitely", "despite", "did", "didn't", "directly", "do", "does", "doesn't", "doing", "done", "don't", "due", "during", "each", "either", "else", "elsewhere", "ends", "enough", "entirely", "especially", "etc.", "even", "evenly", "ever", "evermore", "every", "everybody", "everyone", "everything", "everywhere", "exactly", "except", "fairly", "far", "farther", "fewer", "finds", "for", "forever", "from", "fully", "further", "furthered", "furthering", "furthermore", "furthers", "generally", "get", "gets", "getting", "goes", "going", "gone", "got", "gotten", "greetings", "had", "hadn't", "happens", "hardly", "has", "hasn't", "have", "haven't", "having", "he", "he'd", "he'll", "hello", "hence", "her", "here", "hereafter", "hereby", "herein", "here's", "hereupon", "hers", "herself", "he's", "hi", "him", "himself", "his", "hither", "hopefully", "however", "how's", "i", "i'd", "if", "i'll", "i'm", "immediately", "importance", "in", "inasmuch", "inc.", "indeed", "insofar", "instead", "into", "inward", "is", "isn't", "it", "it'll", "its", "it's", "itself", "i've", "just", "keep", "keeps", "kept", "known", "largely", "lately", "later", "less", "lest", "let", "let's", "likely", "likewise", "looking", "looks", "mainly", "many", "may", "maybe", "me", "mean", "means", "meantime", "meanwhile", "merely", "might", "mine", "minus", "more", "moreover", "mostly", "much", "must", "mustn't", "my", "myself", "namely", "nay", "nearly", "necessarily", "neither", "nevertheless", "nobody", "nonetheless", "noone", "no-one", "nor", "normally", "not", "nothing", "notwithstanding", "nowhere", "obviously", "of", "often", "oh", "ok", "okay", "on", "one's", "only", "onto", "or", "other", "others", "otherwise", "ought", "our", "ours", "ourselves", "over", "overall", "particularly", "per", "perhaps", "please", "plus", "poorly", "possibly", "potentially", "predominantly", "presumably", "previously", "primarily", "probably", "promptly", "quite", "rather", "readily", "really", "reasonably", "regarding", "regardless", "regards", "relatively", "respectively", "secondly", "seem", "seemed", "seeming", "seems", "self", "selves", "seriously", "shall", "she", "she'd", "she'll", "she's", "should", "shouldn't", "sides", "significantly", "similarly", "since", "slightly", "so", "some", "somebody", "someday", "somehow", "someone", "something", "sometime", "sometimes", "somewhat", "somewhere", "specifically", "still", "stop", "strongly", "substantially", "successfully", "such", "sufficiently", "suggest", "sure", "tends", "than", "thank", "thanks", "that", "that'll", "that's", "the", "their", "theirs", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "there'll", "thereof", "there's", "thereto", "thereupon", "these", "they", "they'd", "they'll", "they're", "they've", "this", "thoroughly", "those", "though", "through", "throughout", "thus", "to", "too", "truly", "under", "underneath", "undoing", "unfortunately", "unless", "unlike", "unlikely", "until", "unto", "upon", "us", "usefully", "usefulness", "uses", "using", "usually", "various", "versus", "very", "via", "viz", "vs.", "was", "wasn't", "way", "ways", "we", "we'd", "welcome", "we'll", "went", "were", "we're", "weren't", "we've", "whatever", "what'll", "what's", "whence", "whenever", "whereafter", "whereas", "whereby", "wherein", "where's", "whereupon", "wherever", "whether", "whichever", "while", "whilst", "whither", "who'd", "whoever", "who'll", "whom", "whomever", "who's", "whose", "why", "widely", "willing", "with", "within", "without", "wonder", "won't", "would", "wouldn't", "yes", "yet", "you", "you'd", "you'll", "your", "you're", "yours", "yourself", "yourselves", "you've"];

/**
 * This is used to add a metabox to the edit pages for post and page.
 * This particular one is for Links.
 */
add_action('add_meta_boxes', function () {
	$screens = ['post', 'page'];
	foreach ($screens as $screen) {
		add_meta_box(
			'htm-keyword-info',
			'Keyword Density',
			'htm_keyword_metabox_html',
			$screen,
			'side'
		);
	}
});

function htm_keyword_metabox_html() {
	global $stop_words_default;
	$checked = get_option('keywords_ignore_enabled', 'true') == 'true' ? 'checked="checked"' : ''; ?>

	<input type="hidden" id="keyword_nonce" value="<?= wp_create_nonce('density_nonce') ?>">
	<p>We're going to replace this with some cool keyword density stats and stuff!</p>
	<div class="htm-tab-wrap">

		<div class="htm-tabs">
			<div class="tab-group">
				<label>Word Count</label>
				<div class="tab" tab="1">1</div>
				<div class="tab" tab="2">2</div>
				<div class="tab" tab="3">3</div>
			</div>
			<div class="tab active" tab="settings"><?= get_font_awesome_icon('cogs', 'solid') ?></div>
		</div>

		<div class="htm-boxes">
			<div class="box active" box="settings">
				<h3 class="title">Settings</h3>
				<div class="save button-primary">Save</div>

				<div class="section-wrap" key="min_words">
					<label class="head">Minimum Word Count</label>
					<p>The lowest amount of keywords to show in the tabs. Absolute minimum is 1. Obviously!</p>
					<input type="number" class="input" min=1 max=9 value="<?= get_option('keywords_min_words', 1) ?>">
				</div>

				<div class="section-wrap" key="max_words">
					<label class="head">Maximum Word Count</label>
					<p>The highest amount of keywords to show in the tabs. Absolute maximum is 9.</p>
					<input type="number" class="input" min=1 max=9 value="<?= get_option('keywords_max_words', 6) ?>">
				</div>

				<div class="section-wrap" key="min_count">
					<label class="head">Minimum Frequency</label>
					<p>The minimum amount of times you want a keyword to appear before it shows in the list.</p>
					<input type="number" class="input" min=1 max=9 value="<?= get_option('keywords_min_count', 2) ?>">
				</div>

				<div class="section-wrap" key="refresh">
					<label class="head">Refresh Rate (seconds)</label>
					<p>How long after you stop typing into the content should the Keyword Density lists update? This delay is added so it would not refresh if you continued to change the content. So if you set it to 2 seconds and stop typing for this time, it will then update.</p>
					<input type="number" class="input" min=2 max=60 value="<?= get_option('keywords_refresh', 2) ?>">
				</div>

				<div class="section-wrap" key="ignore_enabled">
					<label class="head">Enable Stop Words</label>
					<p>Turing this off would stop the use of the Stop Words bellow.</p>
					<label>
						<input type="checkbox" class="switch-input" <?= $checked ?>>
						<div class="switch-wrap">
							<span class="switch on">Yes</span>
							<span class="switch off">No</span>
							<div class="switch-slider"></div>
						</div>
					</label>
				</div>

				<div class="section-wrap" key="ignore_list">
					<label class="head">Stop Words</label>
					<p>Stop words are words that carry no keyword relevance value, meaning search engines generally ignore them.</p>
					<?php $list = get_option('keywords_ignore_list', $stop_words_default);
					if (!count($list)) ignore_item();
					foreach ($list as $word) {
						ignore_item($word);
					} ?>
					<div class="actions">
						<div class="button-primary" add="item" title="Add Item">Add Item</div>
					</div>
				</div>

			</div>
		</div>
	</div>
<?php }

function ignore_item($word = '') {
?>
	<div class="input-wrap" is="item">
		<input type="text" class="input" value="<?= $word ?>">
		<div class="button delete" delete="item" title="Delete Item"><?= get_font_awesome_icon('times', 'solid') ?></div>
	</div>
<?php
}

add_action('admin_menu', function () {
	$min = IS_LIVE ? '-min' : '';
	wp_enqueue_script(
		'module-htm-keywords-js',
		get_template_directory_uri() . "/scripts/keywords{$min}.js",
		[],
		filemtime(get_template_directory() . "/scripts/keywords{$min}.js"),
		true
	);
	wp_enqueue_style(
		'htm-keywords-css',
		get_template_directory_uri() . '/styles/keywords.css',
		[],
		filemtime(get_template_directory() . '/styles/keywords.css')
	);
});

add_shortcode('htm_keyword_density_tool', function () {
	$min = IS_LIVE ? '-min' : '';
	$is_in = is_user_logged_in();
	wp_enqueue_script(
		'module-htm-tool-keyword-js',
		get_template_directory_uri() . "/scripts/tool-keyword{$min}.js",
		[],
		filemtime(get_template_directory() . "/scripts/tool-keyword{$min}.js"),
		true
	);
	wp_enqueue_style(
		'htm-tool-keyword-css',
		get_template_directory_uri() . '/styles/tool-keyword.css',
		[],
		filemtime(get_template_directory() . '/styles/tool-keyword.css')
	);
	wp_enqueue_style(
		'htm-codemirror-css',
		get_template_directory_uri() . '/styles/src/codemirror.css',
		[],
		filemtime(get_template_directory() . '/styles/src/codemirror.css')
	);

	global $stop_words_default; ?>

	<input type="hidden" id="keyword_nonce" value="<?= wp_create_nonce('density_nonce') ?>">

	<div class="tool-wrap tab-box" which="Tool">
		<div class="tab-wrap">
			<div class="tab active" tab="url">URL Input</div>
			<div class="tab" tab="html">HTML Input</div>
			<div class="tab" tab="text">Text Input</div>
		</div>
		<div class="box-wrap container">
			<div class="box active" box="url">
				<div class="input-wrap">
					<div class="icon"><?= get_font_awesome_icon('link', 'regular') ?></div>
					<input type="url" id="tool-url" class="input in-wrap" placeholder="https://www.example-domain.com">
					<div class="button" btn="url">
						<span>Check</span>
						<?php get_spinner() ?>
					</div>
				</div>
				<span class="error url">Please provide a valid URL!</span>
			</div>
			<div class="box" box="html">
				<div class="textarea-wrap">
					<div class="icon"><?= get_font_awesome_icon('code', 'regular') ?></div>
					<div class="textarea code">
						<textarea id="tool-html" class="in-wrap code" placeholder="Paste your html content here!"></textarea>
						<div class="handle"></div>
					</div>
					<div class="button" btn="html">
						<span>Check</span>
						<?php get_spinner() ?>
					</div>
				</div>
			</div>
			<div class="box" box="text">
				<div class="textarea-wrap">
					<div class="icon"><?= get_font_awesome_icon('text', 'regular') ?></div>
					<div class="textarea">
						<textarea id="tool-text" class="in-wrap" placeholder="Paste your text content here!"></textarea>
						<div class="handle"></div>
					</div>
					<div class="button" btn="text">
						<span>Check</span>
						<?php get_spinner() ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="setting-wrap tab-box hidden<?= $is_in ? ' signed-in' : '' ?>" which="Setting">

		<?php if ($is_in) { ?>

			<div class="update button">
				<span>Update</span>
				<?php get_spinner() ?>
			</div>

		<?php } ?>

		<div class="tab-wrap">
			<div class="tab active" tab="all">Settings</div>

			<?php if ($is_in) { ?>
				<div class="tab" tab="more">Advanced</div>
			<?php } ?>
		</div>

		<div class="container box-wrap">

			<div class="box active" box="all">
				<label for="min_words" class="input-wrap" key="min_words" title="The lowest amount of keywords to show in the tabs. Absolute minimum is 1, obviously!">
					<span class="head">Min Keywords</span>
					<input id="min_words" type="number" class="input" min=1 max=9 value="<?= get_option('keywords_min_words', 1) ?>" other="max_words" sum="<=" resolve="min">
				</label>

				<label for="max_words" class="input-wrap" key="max_words" title="The highest amount of keywords to show in the tabs. Absolute maximum is 9.">
					<span class="head">Max Keywords</span>
					<input id="max_words" type="number" class="input" min=1 max=9 value="<?= get_option('keywords_max_words', 6) ?>" other="min_words" sum=">=" resolve="max">
				</label>

				<label for="min_freq" class="input-wrap" key="min_count" title="The minimum amount of times you want a keyword to appear before it shows in the list.">
					<span class="head">Min Frequency</span>
					<input id="min_freq" type="number" class="input" min=2 max=9 value="<?= get_option('keywords_min_count', 2) ?>" resolve="min">
				</label>

				<?php if (!$is_in) { ?>

					<span class="log-in">For more settings please <a href="#">create an account</a> and <a href="#">sign in</a>!</span>

				<?php } ?>

			</div>

			<?php if ($is_in) { ?>

				<div class="box" box="more">

					<label for="use_meta" class="input-wrap" key="meta_tags" title="Include the meta data in the keyword results.">
						<span class="head">Include Meta Tags</span>
						<input id="use_meta" type="checkbox" class="switch-input">
						<div class="switch-wrap">
							<span class="switch on">Yes</span>
							<span class="switch off">No</span>
							<div class="switch-slider"></div>
						</div>
					</label>

					<label for="use_alts" class="input-wrap" key="image_alts" title="Include the image Alt tags in the keyword results.">
						<span class="head">Include Image Alts</span>
						<input id="use_alts" type="checkbox" class="switch-input">
						<div class="switch-wrap">
							<span class="switch on">Yes</span>
							<span class="switch off">No</span>
							<div class="switch-slider"></div>
						</div>
					</label>

					<label for="on_stop" class="input-wrap" key="ignore_enabled" title="Turing this off would stop the use of the Stop Words.">
						<span class="head">Use Stop Words</span>
						<input id="on_stop" type="checkbox" class="switch-input" <?= get_option('keywords_ignore_enabled', 'true') == 'true' ? 'checked="checked"' : ''; ?>>
						<div class="switch-wrap">
							<span class="switch on">Yes</span>
							<span class="switch off">No</span>
							<div class="switch-slider"></div>
						</div>
					</label>

					<div class="collapse-wrap span-all" key="ignore_list">
						<div class="title-bar" title="Stop words are words that carry no keyword relevance value, meaning search engines generally ignore them.">
							<span class="head">Stop Words</span>
							<div class="button collapse"><?= get_font_awesome_icon('chevron-down', 'solid') ?></div>
						</div>
						<?php $list = get_option('keywords_ignore_list', $stop_words_default);
						if (!count($list)) ignore_item();
						foreach ($list as $word) {
							ignore_item($word);
						} ?>
						<div class="button add" add="item" title="Add Item">Add Item</div>
					</div>

				</div>
			<?php } ?>

		</div>
	</div>

	<div class="results-wrap tab-box hidden" which="Result">
		<div class="tab-wrap"></div>
		<div class="box-wrap container"></div>
	</div>
<?php
});

// TODO: filter exact phrase, OR, AND
// TODO: 
// TODO: save selected settings to local (not logged in) / user metadata (logged in)
// TODO: add some sort of history and compare for logged in users
