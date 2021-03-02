<?php


/**
 * Enqueue scripts and styles.
 */
add_action('wp_enqueue_scripts', function () {
	// global $template;
	// logger('Using template: ' . basename($template));

	wp_enqueue_style('page_loader', get_template_directory_uri() . "/styles/loader.css");
	// wp_enqueue_script('greensock', "//cdnjs.cloudflare.com/ajax/libs/gsap/3.5.1/gsap.min.js", array(), null, true);

	if (is_single() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}, 100);


function htm_add_head_stuff() {
	global $template;
	$base = basename($template);

	$path = get_template_directory();
	$uri = get_template_directory_uri();

	$links = (object) [
		"everyPage" => (object) [
			"query" => true,
			"scripts" => [
				(object) [
					"name" => "htm_s-main",
					"path" => "$path/scripts/main.js",
					"src" => "$uri/scripts/main.js",
					"module" => true,
					"params" => [ // These appear as global js object called 'params'
						'ajax' => admin_url('admin-ajax.php'),
						'theme_path' => get_stylesheet_directory_uri(),
						'is_user_logged_in' => is_user_logged_in(),
						'promo_popup_options' => get_field('promo_popup', 'option'),
						'show_dialog' => dialogShouldShow() ? '1' : '',
						// 'dialog_createaccount_closed' => $_COOKIE['dialog_createaccount_closed'] ? $_COOKIE['dialog_createaccount_closed'] : null
					]
				]
			],
			"styles" => [
				(object) [
					"name" => "htm_s-main",
					"path" => "$path/styles/main.css",
					"src" => "$uri/styles/main.css"
				]
			]
		],
		// "home" => (object) [
		// 	"query" => is_front_page(),
		// 	"styles" => [
		// 		(object) [
		// 			"name" => "htm_s-home",
		// 			"path" => "$path/styles/home.css",
		// 			"src" => "$uri/styles/home.css"
		// 		]
		// 	]
		// ],
		"single" => (object) [
			"query" => in_array($base, ['single.php', 'index.php']),
			"scripts" => [
				(object) [
					"name" => "htm_s-single",
					"path" => "$path/scripts/single.js",
					"src" => "$uri/scripts/single.js",
					"module" => true,
					"params" => [
						'ajax' => admin_url('admin-ajax.php'),
					]
				]
			],
			"styles" => [
				(object) [
					"name" => "htm_s-single",
					"path" => "$path/styles/single.css",
					"src" => "$uri/styles/single.css"
				]
			]
		],
		"category" => (object) [
			"query" => $base === 'category.php',
			"scripts" => [
				(object) [
					"name" => "htm_s-category",
					"path" => "$path/scripts/category.js",
					"src" => "$uri/scripts/category.js",
					"module" => true
				]
			],
			"styles" => [
				(object) [
					"name" => "htm_s-category",
					"path" => "$path/styles/category.css",
					"src" => "$uri/styles/category.css"
				]
			]
		],
		"search" => (object) [
			"query" => $base === 'page-search.php',
			"scripts" => [
				(object) [
					"name" => "htm_s-search",
					"path" => "$path/scripts/search.js",
					"src" => "$uri/scripts/search.js",
					"module" => true
				]
			],
			"styles" => [
				(object) [
					"name" => "htm_s-search",
					"path" => "$path/styles/search.css",
					"src" => "$uri/styles/search.css"
				]
			]
		],
		"channel" => (object) [
			"query" => $base === 'channel.php',
			"scripts" => [
				(object) [
					"name" => "htm_s-channel",
					"path" => "$path/scripts/channel.js",
					"src" => "$uri/scripts/channel.js",
					"module" => true
				]
			],
			"styles" => [
				(object) [
					"name" => "htm_s-channel",
					"path" => "$path/styles/channel.css",
					"src" => "$uri/styles/channel.css"
				]
			]
		],
		"page" => (object) [
			"query" => $base === 'page.php',
			"styles" => [
				(object) [
					"name" => "htm_s-page",
					"path" => "$path/styles/page.css",
					"src" => "$uri/styles/page.css"
				]
			]
		],
		"404" => (object) [
			"query" => $base === '404.php',
			"styles" => [
				(object) [
					"name" => "htm_s-404",
					"path" => "$path/styles/page.css",
					"src" => "$uri/styles/page.css"
				]
			]
		],
		"contact" => (object) [
			"query" => $base === 'page-contact-us.php',
			"styles" => [
				(object) [
					"name" => "htm_s-contact",
					"path" => "$path/styles/contact.css",
					"src" => "$uri/styles/contact.css"
				]
			]
		]
	];

	foreach ($links as $name => $item) {
		if (!$item->query) continue;

		if ($item->scripts) {
			foreach ($item->scripts as $script) {
				if (file_exists($script->path)) {
					$ver = filemtime($script->path);
					$scriptId = ($script->module ? 'module-' : '') . $script->name;
					wp_enqueue_script($scriptId, $script->src, [], $ver);

					if ($script->params) {
						wp_localize_script($scriptId, 'params', $script->params);
					}
				}
			}
		}

		if ($item->styles) {
			foreach ($item->styles as $style) {
				if (file_exists($style->path)) {
					$ver = filemtime($style->path);
					wp_enqueue_style($style->name, $style->src, [], $ver);
				}
			}
		}
	}
}

