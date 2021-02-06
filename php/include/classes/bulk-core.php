<?php

class BulkCore extends wpdb {
	protected static $instance;
	protected $meta_type;

	protected $items = [],
		$insert_start = null,
		$formats = [],
		$values = [],
		$table = null,
		$column = null,
		$sqls = [];

	protected function set_meta_type($meta_type) {
		$this->meta_type = $meta_type;
	}

	public function reset() {
		$this->items = [];
		$this->insert_start = null;
		$this->formats = [];
		$this->values = [];

		return $this;
	}

	public function get_items() {
		return $this->items;
	}

	public function has_items() {
		return count($this->items) > 0;
	}

	public function get_meta_type() {
		return $this->meta_type;
	}
}
