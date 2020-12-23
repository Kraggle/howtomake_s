<?
$value = get_search_query();
$value = !$value ? '' : $value;
?>

<form role="search" method="get" class="search-form" action="/">
	<input type="text" name="s" class="search-field" placeholder="Search" value="<? echo $value ?>">
	<button type="submit" class="search-submit"><i class="fa-icon type-solid svg-search"></i></button>
</form>

<?
// END
