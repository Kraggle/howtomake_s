import { jQuery as $ } from '../src/jquery-3.5.1.js';
import V from '../custom/Paths.js';
import SVG from '../custom/SVGIcons.js';
// import '../src/imgColor.js';
import { clamp as $clamp } from '../src/clamp.js';


const timer = {
	category: 1500,
	video: 1500,
	channel: 1500,
	featured: 1500,
	trending: 1500
};

export default {

	build() {

		const menu = {
			mobile: null,
			desktop: $('.menu-back, header.for-desktop'),

			class: {
				mobile: 'body header.for-mobile',
				desktop: 'body header.for-desktop, body .menu-back',
			},

			build: {
				desktop() {
					load.icons();
					load.tabs.desktop();
					load.channels();
					load.featured();
					load.trending();

					const showTab = el =>
						$(`.tab-category[category=${el.attr('category')}]`, el.parents('.tab-wrap'))
							.addClass('active').siblings('.active').removeClass('active');

					const hoverTabs = el => el.addClass('hovered').siblings('.hovered').removeClass('hovered');

					// INFO:: This hovers the tab menus so the first tab displays each time you enter the menu
					$('.primary-nav').off('mouseenter').on('mouseenter', '.with-tabs.is-created', function() {
						const el = $('.tab-list .tab-button:first-of-type', this);
						hoverTabs(el);
						showTab(el);
						load.content(el);
					});

					$('.primary-nav .with-tabs').off('mouseover').on('mouseover', '.tab-button:not(.hovered)', function() {
						hoverTabs($(this));
						showTab($(this));
						load.content($(this));
					});

					// INFO:: This controls the menu background animation
					$('.primary-nav .nav > .has-sub-menu').on('mouseenter', function() {
						const el = $('.menu-box', this);
						$('.menu-back').css({
							height: `${el.outerHeight()}px`,
							transform: 'translate(-50%, 0)'
						}).addClass('open');
					}).on('mouseleave', function() {
						$('.menu-back').css({
							height: '30px',
							transform: 'translate(-50%, -30px)'
						}).removeClass('open');
					});

					/* Top account menu and auth links */
					$('.top-account .account-menu').hover( function() {
						
						$('.top-account .account-menu .submenu').stop(true).slideDown(200).addClass('open');
					}, function() {
						$('.top-account .account-menu .submenu').stop(true).slideUp(200).removeClass('open');
					});

				},

				mobile() {
					load.icons();
					load.tabs.mobile();
					load.channels(true);
					load.featured(true);

					$('.banner .hamburger').off('click').on('click', () =>
						$('.banner .hamburger, .primary-nav').toggleClass('open'));

					$('.top-search .search-submit').off('click').on('click', function(e) {
						if (!$(this).hasClass('open') && !$('.top-search .search-field').is(':focus')) {
							e.preventDefault();

							$(`${$('body').width() < 662 ? '.brand, ' : ''}.top-search, .search-submit, .menu-spacer`,
								$(this).parents('.lower')).addClass('open');

							setTimeout(() => {
								$('.top-search .search-field').focus();
							}, 200);
						}
					});

					$('.top-search .search-field').off('blur').on('blur', function() {
						setTimeout(() => {
							$('.brand, .top-search, .search-submit, .menu-spacer',
								$(this).parents('.lower')).removeClass('open');
						}, 50);
					});

					const toggleMenu = (el, openMe) => el[openMe ? 'slideDown' : 'slideUp']('600');

					$('.nav > .menu-item > a').off('click').on('click', function() {

						const el = $(this).parents('.menu-item'),
							close = el.hasClass('active'),
							old = el.parents('.nav').find('.menu-item.active');

						old.length && old.removeClass('active');
						old.length && old.hasClass('has-sub-menu') && toggleMenu($('.menu-box', old), false);

						!close && el.hasClass('has-sub-menu') && toggleMenu($('.menu-box', el), true);
						!close && el.addClass('active');
					});

					$('.primary-nav .with-tabs').off('click').on('click', '.tab-button', function() {

						const el = $(this).parents('.tab-wrap'),
							close = $(this).hasClass('active'),
							old = $(`.tab-category.active`, el),
							open = $(`.tab-category[category=${$(this).attr('category')}]`, el);

						!close && load.content($(this));

						old.length && toggleMenu(old, false);
						$('.active', el).removeClass('active');

						!close && (
							toggleMenu(open, true),
							$(this).addClass('active'),
							open.addClass('active')
						);
					});

				}
			}
		};
		$.get(`${V.theme}/views/partials/header-mobile.html`, response => menu.mobile = $(response));

		$('<img class="hidden-image" />').appendTo('.menu-back');

		const switchTimer = timed();
		const switchHeader = (isMobile) => {

			switchTimer.run(() => {

				const mobile = isMobile && !$(menu.class.mobile).length,
					desktop = !isMobile && !$(menu.class.desktop).length;

				if (!mobile && !desktop) {
					hideLoader();
					return;
				}

				const to = mobile ? 'mobile' : 'desktop',
					from = mobile ? 'desktop' : 'mobile';

				menu[to].prependTo('.body-wrap');
				menu[from] = $(menu.class[from]).clone(true);
				$(menu.class[from]).remove();
				menu.build[to]();

				hideLoader();
			}, 250);
		};

		const hideLoader = () => {
			const back = $('.page-loader');
			if (!back.length) return;

			setTimeout(() => {
				const img = $('.lower .brand .logo'),
					logo = $('.page-loader .loader-logo'),
					loader = $('.page-loader .loader');

				$([document.documentElement, document.body]).scrollTop(0);

				logo.css({
					left: logo.offset().left,
					top: logo.offset().top
				});

				logo.css({
					left: img.offset().left,
					top: img.offset().top,
					height: img.height(),
					width: img.width(),
					margin: 'unset',
					transform: 'translateY(0)'
				});

				loader.css('opacity', 0);

				setTimeout(() => {
					back.css('opacity', 0);

					setTimeout(() => back.remove(), 1200);

					const id = window.location.href.match(/#[^?]+/);

					if (id) {
						$([document.documentElement, document.body]).animate({
							scrollTop: $(id[0]).offset().top - 170
						}, 800);
					}
				}, 750);
			}, 250);
		};

		const onResize = () => {
			const $b = $('body');

			if ($(window).width() <= 1176) {
				$b.addClass('for-mobile').removeClass('for-desktop');
				switchHeader(true);
			} else if ($(window).width() > 1176) {
				$b.addClass('for-desktop').removeClass('for-mobile');
				switchHeader(false);
			}

		};
		$(window).on('resize', onResize);

		$(window).on('scroll', () => {
			const $b = $('.banner.for-desktop, body');

			if ($(window).scrollTop() > 200 && !$b.hasClass('scrolled'))
				$b.addClass('scrolled');
			else if ($(window).scrollTop() <= 200 && $b.hasClass('scrolled'))
				$b.removeClass('scrolled');
		});

		onResize();
		$('body').hasClass('for-desktop') && menu.build.desktop();
	}
};

const load = {

	icons() {
		$('.fa-icon').each(function() {
			const cls = $(this).attr('class'),
				name = cls.match(/(?<=svg-)([^ "]+)/),
				el = cls.match(/(?<=el-)([^ "]+)/),
				type = cls.match(/(?<=type-)([^ "]+)/),
				color = cls.match(/(?<=color-)([^ "]+)/),
				hover = cls.match(/(?<=hover-)([^ "]+)/);

			if (name && type) {
				$.get(`${V.fonts}/font-awesome/${type[0]}/${name[0]}.svg`, data => {
					const element = el ? $(el[0], this) : $(this);
					element.html($(data).find('svg'));
					$('svg', this).css('opacity', '1');

					if (hover) {
						setTimeout(() => {
							element.on('mouseover', function() {
								!$(this).data('color') && $(this).data('color', $(this).css('background-color'));
								$(this).css('background-color', `#${hover[0]}80`);
							}).on('mouseout', function() {
								$(this).css('background-color', $(this).data('color'));
							});
						}, 1000);
					}

					color && element.css('background-color', `#${color[0]}`);
				});
			}
		});
	},

	// INFO:: This adds content to the channels menu
	channels(isMobile = false) {
		$('.nav .is-list:not(.is-created)').each(function() {
			const _me = $(this),
				cls = _me.attr('class'),
				type = (cls.match(/(?<=type-)([^ "]+)/) || [])[0],
				tax = (cls.match(/(?<=tax-)([^ "]+)/) || [])[0];
			_me.data({ type, tax });

			if (tax) {

				setTimeout(() => {
					$.ajax({
						url: V.ajax,
						data: {
							action: 'get_categories',
							nonce: $('.banner').data('nonce'),
							taxonomy: tax
						}
					}).done(function(data) {
						data = JSON.parse(data.replace(/0$/, ''));

						let box = $('<div class="list-wrap menu-box" />').appendTo(_me);
						isMobile && (
							box.removeClass('list-wrap'),
							box = $('<div class="list-wrap" />').appendTo(box)
						);

						if (data.length) {
							$.each(data, (i, cat) => {
								box.append(
									`<a href="${cat.link}" category="${cat.slug}" data-id="${cat.term_id}" class="list-button">
											<img class="list-image" src="${cat.image}" />
											<span class="list-count">${cat.count} Videos</span>
											<span class="list-title">${cat.name}</span>
										</a>`
								);
							});
						}
					});

					_me.addClass('is-created');
					timer.channel = 0;
				}, timer.channel);
			}
		});
	},

	// INFO:: This loads the featured posts
	featured(isMobile = false) {
		const el = $('.has-sub-menu.is-featured:not(.is-created)');

		if (!el.length) return;

		el.append(`
				<div class="${!isMobile ? 'featured-wrap ' : ''}menu-box">
					${isMobile ? '<div class="featured-wrap">' : ''}
						<a id="${ID()}" href="#" class="tab-post loader"></a>
						<a id="${ID()}" href="#" class="tab-post loader"></a>
						<a id="${ID()}" href="#" class="tab-post loader"></a>
						<a id="${ID()}" href="#" class="tab-post loader"></a>
					${isMobile ? '</div>' : ''}
				</div>
			`).addClass('is-created');

		const wrap = $('.featured-wrap'),
			loaders = $('.loader', wrap);

		loaders.html('<div class="load-ripple"><div></div><div></div></div>');

		setTimeout(() => {
			$.ajax({
				url: V.ajax,
				data: {
					action: 'get_featured',
					nonce: $('.banner').data('nonce')
				}
			}).done(function(data) {
				data = JSON.parse(data.replace(/0$/, ''));

				if (data.length) {

					$(loaders).each(function(i) {
						const item = data[i];

						$(this).attr('href', item ? item.link : '#').html(item ? `
								<div class="thumb" style="background-image: url(${item.image})">
									<div class="time">
										<p>
											<span class="number">${item.readTime}</span>
											<span class="small"> min${item.readTime == 1 ? '' : 's'}</span>
										</p>
									</div>
								</div>
								<span class="title">${item.title}</span>` : ''
						);

						if (item) {
							// !!item.image.match(/\.jpg$/) &&
							// 	$('.hidden-image').attr('src', item.image).imgColor(`#${$(this).attr('id')} .thumb`);

							const title = $('.title', this),
								text = title.text();
							$clamp(title.get(0), { clamp: 3 });
							$(this).attr('title', text);
						}
					});

				} else $(loaders).html('');
			});
			timer.featured = 0;
		}, timer.featured);
	},

	// INFO:: This adds the tabs to the menus
	tabs: {
		desktop() {
			$('.nav .with-tabs:not(.is-created)').each(function() {
				const _me = $(this),
					cls = _me.attr('class'),
					type = (cls.match(/(?<=type-)([^ "]+)/) || [])[0],
					tax = (cls.match(/(?<=tax-)([^ "]+)/) || [])[0];
				$(this).data({ type, tax });

				if (tax) {
					setTimeout(() => {

						$.ajax({
							url: V.ajax,
							data: {
								action: 'get_categories',
								nonce: $('.banner').data('nonce'),
								taxonomy: tax
							}
						}).done(function(data) {
							data = JSON.parse(data.replace(/0$/, ''));

							_me.append($(
								`<div class="tab-wrap menu-box">
									<div class="tab-list"></div>
									<div class="tab-box"></div>
								</div>`
							));

							if (data.length) {
								$.each(data, (i, cat) => {
									$('.tab-list', _me).append(
										`<a href="${cat.link}" category="${cat.slug}" data-id="${cat.term_id}" class="tab-button">
											<span class="tab-title">${cat.name}</span>
											<i class="tab-icon"></i>
										</a>`
									);

									$('.tab-box', _me).append(
										`<div class="tab-category" category="${cat.slug}" data-page=0>
											<a id="${ID()}" href="#" class="tab-post loader"></a>
											<a id="${ID()}" href="#" class="tab-post loader"></a>
											<a id="${ID()}" href="#" class="tab-post loader"></a>
											<div class="tab-tub back">
												<div class="tab-nav start disabled" action="start" category="${cat.slug}">${SVG.fast}</div>
												<div class="tab-nav prev disabled" category="${cat.slug}">${SVG.arrow}</div>
											</div>
											<a href="${cat.link}" class="tab-nav all" category="${cat.slug}">${SVG.all}</a>
											<div class="tab-tub forward">
												<div class="tab-nav next disabled" category="${cat.slug}">${SVG.arrow}</div>
												<div class="tab-nav end disabled" action="end" category="${cat.slug}">${SVG.fast}</div>
											</div>
										</div>`
									);

									$(`.tab-category[category=${cat.slug}]`, _me).on('click', 'div.tab-nav:not(.disabled)', function() {
										load.content(
											$(this).parents('.tab-wrap').find(`.tab-button[category=${$(this).attr('category')}]`),
											$(this).hasClass('next') ? 1 : -1,
											$(this).attr('action')
										);
									});
								});
							}
						});

						_me.addClass('is-created');
						timer[tax] = 0;
					}, timer[tax]);
				}
			});
		},

		mobile() {
			$('.nav .with-tabs:not(.is-created)').each(function() {
				const _me = $(this),
					cls = _me.attr('class'),
					type = (cls.match(/(?<=type-)([^ "]+)/) || [])[0],
					tax = (cls.match(/(?<=tax-)([^ "]+)/) || [])[0];
				_me.data({ type, tax });

				if (tax) {
					setTimeout(() => {

						$.ajax({
							url: V.ajax,
							data: {
								action: 'get_categories',
								nonce: $('.banner').data('nonce'),
								taxonomy: tax
							}
						}).done(function(data) {
							data = JSON.parse(data.replace(/0$/, ''));

							_me.append($(
								`<div class="menu-box for-tabs">
									<div class="tab-wrap"></div>
								</div>`
							));

							if (data.length) {
								$.each(data, (i, cat) => {
									$('.tab-wrap', _me).append(
										`<div category="${cat.slug}" data-id="${cat.term_id}" class="tab-button">
											<span class="tab-title">${cat.name}</span>
											<i class="tab-icon"></i>
										</div>
										<div class="tab-category" category="${cat.slug}" data-page=0>
											<div class="tab-box">
												<a id="${ID()}" href="#" class="tab-post loader"></a>
												<a id="${ID()}" href="#" class="tab-post loader"></a>
												<a id="${ID()}" href="#" class="tab-post loader"></a>
												<div class="tab-flex">
													<div class="tab-tub back">
														<div class="tab-nav start disabled" action="start" category="${cat.slug}">${SVG.fast}</div>
														<div class="tab-nav prev disabled" category="${cat.slug}">${SVG.arrow}</div>
													</div>
													<a href="${cat.link}" class="tab-nav all" category="${cat.slug}">${SVG.all}</a>
													<div class="tab-tub forward">
														<div class="tab-nav next disabled" category="${cat.slug}">${SVG.arrow}</div>
														<div class="tab-nav end disabled" action="end" category="${cat.slug}">${SVG.fast}</div>
													</div>
												</div>
											</div>
										</div>`
									);

									$(`.tab-category[category=${cat.slug}]`, _me).on('click', 'div.tab-nav:not(.disabled)', function() {
										load.content(
											$(this).parents('.tab-wrap').find(`.tab-button[category=${$(this).attr('category')}]`),
											$(this).hasClass('next') ? 1 : -1,
											$(this).attr('action')
										);
									});
								});
							}
						});

						_me.addClass('is-created');

						timer[tax] = 0;
					}, timer[tax]);
				}
			});
		}
	},

	// INFO:: This controls the content of the tabbed menus
	content(el, next = 0, action = null) {
		next != 0 && $(el).data('loaded', false);

		if (!$(el).data('loaded')) {
			$(el).data('loaded', true);

			const category = $(el).attr('category'),
				termId = $(el).data('id'),
				tab = $(el).parents('.tab-wrap').find(`.tab-category[category=${category}]`),
				loaders = $('.loader', tab),
				type = $(el).parents('.with-tabs').data('type');

			let page = (parseInt(tab.data('page')) + next),
				count = tab.data('count');
			count % 3 == 0 && (count -= 1);
			!!action && (page = action == 'start' ? 0 : Math.floor(count / 3));

			tab.data('page', page);

			loaders.html('<div class="load-ripple"><div></div><div></div></div>');

			$.ajax({
				url: V.ajax,
				data: {
					action: 'get_posts',
					nonce: $('.banner').data('nonce'),
					termId,
					category,
					type,
					page: page * 3,
					getCount: !tab.data('hasCount')
				}
			}).done(function(data) {
				data = JSON.parse(data.replace(/0$/, ''));

				if (data.items.length) {

					data.count && tab.data('count', data.count).data('hasCount', true);
					count = tab.data('count');
					$('.tab-nav', tab).removeClass('disabled');

					page == 0 && $('.prev, .start', tab).addClass('disabled');
					page * 3 >= count - 3 && $('.next, .end', tab).addClass('disabled');

					$(loaders).each(function(i) {
						const item = data.items[i];
						$(this).attr('href', item ? item.link : '#').html(item ? `
								<div class="thumb" style="background-image: url(${item.image})">
									<div class="time">
										<p>
											<span class="number">${item.readTime}</span>
											<span class="small"> min${item.readTime == 1 ? '' : 's'}</span>
										</p>
									</div>
								</div>
								<span class="title">${item.title}</span>` : ''
						);

						if (item) {
							// !!item.image.match(/\.jpg$/) &&
							// 	$('.hidden-image').attr('src', item.image).imgColor(`#${$(this).attr('id')} .thumb`);

							const title = $('.title', this),
								text = title.text();
							$clamp(title.get(0), { clamp: 3 });
							$(this).attr('title', text);
						}
					});

				} else $(loaders).html('');
			});
		}
	},

	// INFO:: This loads the trending posts
	trending() {

		setTimeout(() => {

			$.ajax({
				url: V.ajax,
				data: {
					action: 'get_trending',
					nonce: $('.banner').data('nonce')
				}
			}).done(function(data) {
				data = JSON.parse(data.replace(/0$/, ''));

				$('.trending-wrap:not(.is-created)').append(`
						<a id="${ID()}" href="#" class="trending-button disabled"></a>
						<a id="${ID()}" href="#" class="trending-button disabled"></a>
						<a id="${ID()}" href="#" class="trending-button disabled"></a>
						<a id="${ID()}" href="#" class="trending-button disabled"></a>
					`).addClass('is-created');

				$('.trending-wrap .trending-button').each(function(i) {
					const item = data[i];
					if (item) {
						$(this).attr('href', item.link).text(item.title);
					} else $(this).addClass('empty');
				});

				const enableOne = () => {
					const els = $('.trending-wrap .trending-button.disabled:not(.empty)'),
						i = Math.floor(Math.random() * els.length) + 1;
					els.eq(i).removeClass('disabled').siblings().addClass('disabled');
				};
				enableOne();

				setInterval(enableOne, 10000);
			});
			timer.trending = 0;
		}, timer.trending);
	}
};

$.fn.changeType = function(newType) {
	const attrs = {};

	return this.each(function() {

		$.each($(this)[0].attributes, function(idx, attr) {
			attrs[attr.nodeName] = attr.nodeValue;
		});

		$(this).replaceWith(function() {
			return $(`<${newType}/>`, attrs).append($(this).contents());
		});
	});
};

const ID = () => {
	return '_' + Math.random().toString(36).substr(2, 9);
};

class Timed {
	run(callback, time) {
		this.time = time;
		this.callback = callback;

		this._action();
	}

	_action() {
		this.timeout && (clearTimeout(this.timeout));
		this.timeout = setTimeout(this.callback, this.time);
	}
}

function timed() {
	return new Timed();
}
