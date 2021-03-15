<?php

// TODO: Make this work having different instances for each filename input.
/**
 * This, like error_log, outputs whatever you tell it to to a file of your choosing.
 * 
 * @param string $filename This is the path and filename of the file you want to log to.
 * @param string $format   This is the format of the date of each string output to the log.
 */
class outputLogger {

	private
		$file,
		$format;
	private static $instance;

	public function __construct($filename, $format = '[d-M-Y H:i:s]') {
		$this->file = $filename;
		$this->format = $format;

		$path = pathinfo($filename);
		if (!file_exists($path['dirname']))
			mkdir($path['dirname']);

		if (!file_exists($filename))
			file_put_contents($this->file, '');
	}

	public static function getInstance($filename = '', $format = '[d-M-Y H:i:s]') {
		return !isset(self::$instance) ?
			self::$instance = new outputLogger($filename, $format) :
			self::$instance;
	}

	public function put($insert) {
		$timestamp = date($this->format);
		file_put_contents($this->file, "$timestamp $insert\n", FILE_APPEND);

		return $this;
	}

	public function get() {
		$content = file_get_contents($this->file);
		return $content;
	}
}
