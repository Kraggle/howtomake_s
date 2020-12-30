<?php
$value = get_search_query();
$value = !$value ? '' : $value;
?>

<form role="search" method="get" class="search-wrap" action="/">
	<input type="text" name="s" class="search-input" placeholder="examples - dog/bike/fitness" value="<?php echo $value ?>">
	<input type="submit" class="search-submit" value="Search" />
</form>