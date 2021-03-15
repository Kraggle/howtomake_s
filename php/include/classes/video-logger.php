<?php

class videoLogger {

	private
		$file,
		$format;
	private static $instance;

	public function __construct($filename, $format = '[d-M-Y H:i:s]') {
		$this->file = $filename;
		$this->format = $format;

		if (!file_exists($filename))
			file_put_contents($this->file, '');
	}

	public static function getInstance($filename = '', $format = '[d-M-Y H:i:s]') {
		return !isset(self::$instance) ?
			self::$instance = new videoLogger($filename, $format) :
			self::$instance;
	}

	public function put($insert) {
		$timestamp = date($this->format);
		file_put_contents($this->file, "$timestamp &raquo; $insert\n", FILE_APPEND);
	}

	public function get() {
		$content = file_get_contents($this->file);
		return $content;
	}
}
