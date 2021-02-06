<?php

$path = array_diff(scandir('./'), ['.', '..']);

$latest = (object) [
	'time' => 0,
	'file' => null
];

foreach ($path as $file) {
	if (preg_match('/^log/', $file) && ($time = filemtime($file)) > $latest->time) {
		$latest->time = $time;
		$latest->file = $file;
	}
}

if ($latest->file)
	echo json_encode(['content' => file_get_contents($latest->file)]);
else
	echo json_encode(['fail' => true]);
