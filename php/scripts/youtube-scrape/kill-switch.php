<?php
$kill = filter_var($_REQUEST['kill'], FILTER_VALIDATE_BOOLEAN);

require_once('../../include/functions.php');

videoLogger::getInstance('./info/log' . date('[d-M-Y H]') . '.txt')
	->put(
		$kill
			? 'Kill script has been triggered, before resuming you must `CANCEL KILL`!'
			: 'Kill script has been canceled, you may resume.'
	);
file_put_contents('kill-switch.json', json_encode(['kill' => $kill]));
