<?php
$value = get_search_query();
$value = !$value ? '' : $value;
?>

<form role="search" method="get" class="search-wrap" action="/search/">
	<input type="text" name="term" class="search-input" placeholder="examples - marketing / investing / selling" value="<?= $value ?>">
	<button class="svg submit"><i class="fa-icon type-regular svg-search"></i></button>
	<input type="hidden" name="type" value="post,video">
	<input type="hidden" name="order" value="desc">
	<input type="hidden" name="orderby" value="relevance">
</form>

<?php
// END
