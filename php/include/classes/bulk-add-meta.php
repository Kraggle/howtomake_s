<?php

class BulkAddMeta extends BulkCore {

	public static function get_instance($meta_type = 'post') {
		if (!_get_meta_table($meta_type))
			return false;

		if (null === self::$instance)
			self::$instance = (object) [];

		if (null === self::$instance->$meta_type) {
			global $table_prefix;

			$user = defined('DB_USER') ? DB_USER : '';
			$pass = defined('DB_PASSWORD') ? DB_PASSWORD : '';
			$name = defined('DB_NAME') ? DB_NAME : '';
			$host = defined('DB_HOST') ? DB_HOST : '';

			self::$instance->$meta_type = new BulkAddMeta($user, $pass, $name, $host);
			self::$instance->$meta_type->set_prefix($table_prefix);
			self::$instance->$meta_type->set_meta_type($meta_type);
		}

		return self::$instance->$meta_type;
	}

	/**
	 * Adds a meta field to the given post.
	 * This does NOT add it to the database, just adds a new item to push.
	 *
	 * @param int    $id         Post ID.
	 * @param string $meta_key   Metadata name.
	 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
	 * @param bool   $unique     Optional. Whether the same key should not be added.
	 *                           Default false.
	 * @return this So you can chain commands.
	 */
	public function add($id, $meta_key, $meta_value, $unique = false) {
		switch ($this->meta_type) {
			case 'term':
				if (wp_term_is_shared($id)) {
					return new WP_Error('ambiguous_term_id', __('Term meta cannot be added to terms that are shared between taxonomies.'), $id);
				}
				break;

			case 'post':
				// Make sure meta is added to the post, not a revision.
				$the_post = wp_is_post_revision($id);
				if ($the_post)
					$id = $the_post;
				break;

			case 'channel':
				// $this->table = 'wp_yt_channel_meta';
				$this->column = 'object_id';
				break;

			case 'video':
				// $this->table = 'wp_yt_video_meta';
				$this->column = 'post_id';
				break;
		}

		if (!$meta_key || !is_numeric($id)) {
			return $this;
		}

		$id = absint($id);
		if (!$id) {
			return $this;
		}

		if (!$this->table) {
			$this->table = _get_meta_table($this->meta_type);
			if (!$this->table) {
				return false;
			}
		}

		if (!$this->column) {
			$this->column = sanitize_key($this->meta_type . '_id');
		}

		$meta_subtype = get_object_subtype($this->meta_type, $id);

		// expected_slashed ($meta_key)
		$meta_key   = wp_unslash($meta_key);
		$meta_value = wp_unslash($meta_value);
		$meta_value = sanitize_meta($meta_key, $meta_value, $this->meta_type, $meta_subtype);

		/**
		 * Short-circuits adding metadata of a specific type.
		 *
		 * The dynamic portion of the hook, `$meta_type`, refers to the meta object type
		 * (post, comment, term, user, or any other type with an associated meta table).
		 * Returning a non-null value will effectively short-circuit the function.
		 *
		 * @since 3.1.0
		 *
		 * @param null|bool $check      Whether to allow adding metadata for the given type.
		 * @param int       $object_id  ID of the object metadata is for.
		 * @param string    $meta_key   Metadata key.
		 * @param mixed     $meta_value Metadata value. Must be serializable if non-scalar.
		 * @param bool      $unique     Whether the specified meta key should be unique for the object.
		 */
		$check = apply_filters("add_{$this->meta_type}_metadata", null, $id, $meta_key, $meta_value, $unique);
		if (null !== $check) {
			return $this;
		}

		if ($unique && $this->get_var(
			$this->prepare(
				"SELECT COUNT(*) FROM {$this->table} WHERE meta_key = %s AND {$this->column} = %d",
				$meta_key,
				$id
			)
		)) {
			return $this;
		}

		$this->items[] = (object) [
			'id' => $id,
			'meta_key' => $meta_key,
			'meta_value' => $meta_value
		];

		return $this;
	}

	private function prepare_item($item) {
		$meta_value = $item->meta_value;
		$meta_key = $item->meta_key;
		$id = $item->id;

		$_meta_value = $meta_value;
		$meta_value  = maybe_serialize($meta_value);

		/**
		 * Fires immediately before meta of a specific type is added.
		 *
		 * The dynamic portion of the hook, `$meta_type`, refers to the meta object type
		 * (post, comment, term, user, or any other type with an associated meta table).
		 *
		 * @since 3.1.0
		 *
		 * @param int    $object_id   ID of the object metadata is for.
		 * @param string $meta_key    Metadata key.
		 * @param mixed  $_meta_value Metadata value. Serialized if non-scalar.
		 */
		do_action("add_{$this->meta_type}_meta", $id, $meta_key, $_meta_value);

		$data = [
			$this->column => $id,
			'meta_key'    => $meta_key,
			'meta_value'  => $meta_value,
		];

		$data = $this->process_fields($this->table, $data, null);
		if (false === $data) {
			return false;
		}

		$formats = array();
		foreach ($data as $value) {
			if (is_null($value['value'])) {
				$formats[] = 'NULL';
				continue;
			}

			$formats[] = $value['format'];
			$this->values[]  = $value['value'];
		}

		$fields  = '`' . implode('`, `', array_keys($data)) . '`';
		$formats = implode(', ', $formats);

		if (!$this->insert_start)
			$this->insert_start = "INSERT INTO `{$this->table}` ($fields) VALUES ";

		$this->formats[] = "($formats)";

		return true;
	}

	/**
	 * Will build a query and push all of the previously added items to the database.
	 * Once complete this object will be reset.
	 * 
	 * @return void 
	 */
	public function push() {
		if (!count($this->items))
			return $this;

		foreach ($this->items as $item) {
			$this->prepare_item($item);
		}

		$sql = $this->insert_start . implode(', ', $this->formats) . ';';

		// logger($this->prepare($sql, $this->values));

		$result =  $this->query($this->prepare($sql, $this->values));

		if (!$result) {
			return false;
		}

		$ids = list_ids($this->items, 'id', 'string');
		$results = $this->get_results(
			"SELECT *
			FROM {$this->table}
			WHERE meta_id >= {$this->insert_id}
			AND {$this->column} IN ($ids)"
		);

		foreach ($results as $meta) {

			$column = $this->column;
			wp_cache_delete($meta->$column, $this->meta_type . '_meta');
			$_meta_value = maybe_unserialize($meta->meta_value);

			/**
			 * Fires immediately after meta of a specific type is added.
			 *
			 * The dynamic portion of the hook, `$meta_type`, refers to the meta object type
			 * (post, comment, term, user, or any other type with an associated meta table).
			 *
			 * @since 2.9.0
			 *
			 * @param int    $mid         The meta ID after successful update.
			 * @param int    $object_id   ID of the object metadata is for.
			 * @param string $meta_key    Metadata key.
			 * @param mixed  $_meta_value Metadata value. Serialized if non-scalar.
			 */
			do_action("added_{$this->meta_type}_meta", $meta->meta_id, $meta->$column, $meta->meta_key, $_meta_value);
		}

		$this->reset();

		return true;
	}
}
