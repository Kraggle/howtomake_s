<?php
// A panel for the home page for additional content and links
?>

<div class="more-side">

	<div class="more-part" part="1"></div>
	<div class="more-part" part="2"></div>
	<div class="more-part" part="3"></div>

	<?php
	// Sign up panel
	do_more_panel(function () { ?>
		<p>Get the latest tips and tricks to making money from home by subscribing here.</p>
		<?php echo do_shortcode('[contact-form-7 id="5297" title="Home Newsletter"]'); //get_template_part('views/widgets/subscription-form') 
		?>
	<?php }, 'Newsletter', [
		'type' => 'form',
		'background' => 'gradient',
		'classes' => 'center-text'
	]);

	// Help quote panel
	do_more_panel(function () {
		echo get_attachment_image_by_slug('How-to-make-money-from-home-logo-white', 'medium') ?>
		<p>We need the help of other marketers and professionals to be able to share your advice to the public. Together we can make this the best platform for earning money from home.</p>
		<p><b>How to Make Money From Home</b></p>
	<?php }, '', [
		'type' => 'quote',
		'background' => 'gradient',
		'classes' => 'center-text'
	]);

	// Benefits to working from home - list
	do_more_panel(function () { ?>
		<ol>
			<li>Passive Income</li>
			<li>Fast way to earn money</li>
			<li>Travel whilst making money</li>
			<li>Millions of options to make money</li>
			<li>Work on your own pace</li>
			<li>No one to boss you at home</li>
			<li>Flexible hours</li>
			<li>Work smart not hard</li>
			<li>Spend more time with your family</li>
			<li>Do what you love, not hate</li>
		</ol>
	<?php }, '10 Benefits to Working from Home');

	// Making money from home - paragraphs
	do_more_panel(function () { ?>
		<p>Making money from home is a great way to earn an income, not only can you earn a lot but also in very little work. How to Make Money From Home UK is always posting what you need to know about earning from home and how to make real money online.</p>
		<p>Nothing that we post are these “Get rich Schemes” they are all legitimate ways of earning money over long periods of time. Using shortcuts to getting rich from home is not the way to go about it, if you want to learn how to make money from home properly then this blog is for you.</p>
	<?php }, 'Making Money from Home');

	// Scams to avoid - paragraphs
	do_more_panel(function () { ?>
		<p>Whilst you are making money from home you are going to come across a lot of websites and adverts telling you how rich you can get instantly. There are so many “shiny things” on the internet that would get anyone hooked into wondering how they did it. I am going to tell you what different scams you should avoid and be careful of. </p>
		<h5>Get Rich Schemes</h5>
		<p>Many people get supped into many different schemes and courses online, our recent favourite was someone claiming to only work 2 hours a week on a full digital agency, I even know a professional marketer who falls for this. The authors of these courses are extremely clever and are trained at tapping into peoples minds. </p>
		<p>If someone is telling you to buy into a scheme for a few hundred quid and promises you thousands in ROI do nothing apart from RUN AWAY! Report them, share them on here to keep other entrepreneurs away from the scams. </p>
		<h5>No#1 Google Ranking in 60 Minutes</h5>
		<p>This is something I have also seen being promoted on Facebook, what a scam. Businessmen with no SEO knowledge get supped into these schemes and software that promise the best results instantly. Google can take weeks to crawl your site, or up to 24-48hours if you submit an index with Google Search Console, apart from that you can NOT rank on Google in a minute or an hour. </p>
	<?php }, 'Scams to Avoid');

	// Top 10 jobs for mums - links
	do_more_panel(function () { ?>
		<ol>
			<li><a href="/making-money/top-10-homeworking-jobs-for-mums#childminding">Childminding</a></li>
			<li><a href="/making-money/top-10-homeworking-jobs-for-mums#dog-minding">Dog minding</a></li>
			<li><a href="/making-money/top-10-homeworking-jobs-for-mums#blogging">Blogging</a></li>
			<li><a href="/making-money/top-10-homeworking-jobs-for-mums#affiliate-marketing">Affiliate marketing</a></li>
			<li><a href="/making-money/top-10-homeworking-jobs-for-mums#transcribing">Transcribing</a></li>
			<li><a href="/making-money/top-10-homeworking-jobs-for-mums#virtual-assistant">Virtual assistant</a></li>
			<li><a href="/making-money/top-10-homeworking-jobs-for-mums#tutor">Tutor</a></li>
			<li><a href="/making-money/top-10-homeworking-jobs-for-mums#cakemaker">Cakemaker</a></li>
			<li><a href="/making-money/top-10-homeworking-jobs-for-mums#online-translator">Online translator</a></li>
			<li class="underline"><a href="/making-money/top-10-homeworking-jobs-for-mums#dropshipping">Dropshipping</a></li>
		</ol>
		<a class="center" href="/top-10-homeworking-jobs-for-mums/" title="Top 10 Jobs For Mums From Home"><b>VIEW FULL ARTICLE</b></a>
	<?php }, 'Top 10 Jobs for Mums');

	// 10 Business ideas to make money instantly - links
	do_more_panel(function () { ?>
		<ol>
			<li><a href="/business/10-businesses-that-make-money-almost-instantly#1">Freelance</a></li>
			<li><a href="/business/10-businesses-that-make-money-almost-instantly#2">Start a website</a></li>
			<li><a href="/business/10-businesses-that-make-money-almost-instantly#3">Landscaping</a></li>
			<li><a href="/business/10-businesses-that-make-money-almost-instantly#4">Social media management</a></li>
			<li><a href="/business/10-businesses-that-make-money-almost-instantly#5">Consulting</a></li>
			<li><a href="/business/10-businesses-that-make-money-almost-instantly#6">VA</a></li>
			<li><a href="/business/10-businesses-that-make-money-almost-instantly#7">Sell used books</a></li>
			<li><a href="/business/10-businesses-that-make-money-almost-instantly#8">Advertising</a></li>
			<li><a href="/business/10-businesses-that-make-money-almost-instantly#9">Sell logos</a></li>
			<li class="underline"><a href="/business/10-businesses-that-make-money-almost-instantly#10">Offer to coach</a></li>
		</ol>
		<a class="center" href="/business/10-businesses-that-make-money-almost-instantly/" title="10 Businesses that Make Money Instantly"><b>VIEW FULL ARTICLE</b></a>
	<?php }, '10 Business Ideas to Make Money Instantly');

	// 20 Sideline business ideas - links
	do_more_panel(function () { ?>
		<ol>
			<li><a href="/business/sideline-business-ideas#1">Write and sell your own books</a></li>
			<li><a href="/business/sideline-business-ideas#2">Start your own VA service</a></li>
			<li><a href="/business/sideline-business-ideas#3">Offer to take on writing tasks for companies</a></li>
			<li><a href="/business/sideline-business-ideas#4">Start a blog</a></li>
			<li><a href="/business/sideline-business-ideas#5">Create and sell items</a></li>
			<li><a href="/business/sideline-business-ideas#6">Buy items to resell</a></li>
			<li><a href="/business/sideline-business-ideas#7">Create a downloadable app</a></li>
			<li><a href="/business/sideline-business-ideas#8">Start a dog sitting business</a></li>
			<li><a href="/business/sideline-business-ideas#9">Become a social media specialist</a></li>
			<li><a href="/business/sideline-business-ideas#10">Become an SEO tutor</a></li>
			<li><a href="/business/sideline-business-ideas#11">Start a Youtube channel</a></li>
			<li><a href="/business/sideline-business-ideas#12">Become a graphic designer</a></li>
			<li><a href="/business/sideline-business-ideas#13">Create custom t-shirts</a></li>
			<li><a href="/business/sideline-business-ideas#14">Become an influencer</a></li>
			<li><a href="/business/sideline-business-ideas#15">Make and sell sweets</a></li>
			<li><a href="/business/sideline-business-ideas#16">Start a cleaning service</a></li>
			<li><a href="/business/sideline-business-ideas#17">Buy and sell gold</a></li>
			<li><a href="/business/sideline-business-ideas#18">Provide painting services</a></li>
			<li><a href="/business/sideline-business-ideas#19">Teach new languages</a></li>
			<li class="underline"><a href="/business/sideline-business-ideas#20">Offer services to writers</a></li>
		</ol>
		<a class="center" href="/business/sideline-business-ideas/" title="20 Fantastic Sideline Business Ideas"><b>VIEW FULL ARTICLE</b></a>
	<?php }, '20 Sideline Business Ideas');

	// Game for money from home - links
	do_more_panel(function () { ?>
		<ol>
			<li><a href="/making-money/top-10-ways-to-make-money-gaming-from-home#become-a-playtester">Become a playtester</a></li>
			<li><a href="/making-money/top-10-ways-to-make-money-gaming-from-home#write-articles-about-games">Write articles about games</a></li>
			<li><a href="/making-money/top-10-ways-to-make-money-gaming-from-home#play-games-for-research-companies">Play games for research companies</a></li>
			<li><a href="/making-money/top-10-ways-to-make-money-gaming-from-home#become-a-youtube-content-creator">Become a Youtube content creator</a></li>
			<li><a href="/making-money/top-10-ways-to-make-money-gaming-from-home#become-a-competitive-player">Become a competitive player</a></li>
			<li><a href="/making-money/top-10-ways-to-make-money-gaming-from-home#become-a-live-streamer">Become a live streamer</a></li>
			<li><a href="/making-money/top-10-ways-to-make-money-gaming-from-home#creating-guides">Creating guides</a></li>
			<li><a href="/making-money/top-10-ways-to-make-money-gaming-from-home#farming-items-in-games">Farming items in games</a></li>
			<li><a href="/making-money/top-10-ways-to-make-money-gaming-from-home#selling-your-accounts">Selling your accounts</a></li>
			<li class="underline"><a href="/making-money/top-10-ways-to-make-money-gaming-from-home#start-a-podcast">Start a podcast</a></li>
		</ol>
		<a class="center" href="/making-money/top-10-ways-to-make-money-gaming-from-home/" title="Top 10 Ways to Game for Money from Home"><b>VIEW FULL ARTICLE</b></a>
	<?php }, '10 Ways to Game for Money from Home');

	// 10 Dropshipping companies - links
	do_more_panel(function () { ?>
		<ol>
			<li><a href="/reviews/top-10-dropshipping-companies#salehoo">Salehoo</a></li>
			<li><a href="/reviews/top-10-dropshipping-companies#oberlo">Oberlo</a></li>
			<li><a href="/reviews/top-10-dropshipping-companies#doba">Doba</a></li>
			<li><a href="/reviews/top-10-dropshipping-companies#sunrise-wholesale">Sunrise Wholesale</a></li>
			<li><a href="/reviews/top-10-dropshipping-companies#wholesale2b">Wholesale2B</a></li>
			<li><a href="/reviews/top-10-dropshipping-companies#megagoods">Megagoods</a></li>
			<li><a href="/reviews/top-10-dropshipping-companies#inventory-source">Inventory Source</a></li>
			<li><a href="/reviews/top-10-dropshipping-companies#worldwide-brands">Worldwide Brands</a></li>
			<li><a href="/reviews/top-10-dropshipping-companies#dropified">Dropified</a></li>
			<li class="underline"><a href="/reviews/top-10-dropshipping-companies#dropbeez">Dropbeez</a></li>
		</ol>
		<a class="center" href="/reviews/top-10-dropshipping-companies/" title="Top 10 Dropshipping Companies"><b>VIEW FULL ARTICLE</b></a>
	<?php }, 'Top 10 Dropshipping Companies');

	?>

</div>

<?php 
// END
