<?php

class BulkUpdateMeta extends BulkCore {

	public static function get_instance($meta_type) {
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

			self::$instance->$meta_type = new BulkUpdateMeta($user, $pass, $name, $host);
			self::$instance->$meta_type->set_prefix($table_prefix);
			self::$instance->$meta_type->set_meta_type($meta_type);
		}

		return self::$instance->$meta_type;
	}

	/**
	 * Updates a meta field to the given object. If no value already exists
	 * for the object Id and Key, the metadata will be added.
	 * This does NOT add it to the database, just adds a new item to push.
	 *
	 * @param int    $id         Post ID.
	 * @param string $meta_key   Metadata name.
	 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
	 * @param mixed  $prev_value Optional. Previous value to check before updating.
	 *                           If specified, only update existing metadata entries with
	 *                           this value. Otherwise, update all entries. Default empty.
	 * @return this So you can chain commands.
	 */
	public function add($id, $meta_key, $meta_value, $prev_value = '') {
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

		$id_column = ('user' === $this->meta_type) ? 'umeta_id' : 'meta_id';

		$meta_subtype = get_object_subtype($this->meta_type, $id);

		// expected_slashed ($meta_key)
		$raw_meta_key = $meta_key;
		$meta_key     = wp_unslash($meta_key);
		$passed_value = $meta_value;
		$meta_value   = wp_unslash($meta_value);
		$meta_value   = sanitize_meta($meta_key, $meta_value, $this->meta_type, $meta_subtype);

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
		$check = apply_filters("add_{$this->meta_type}_metadata", null, $id, $meta_key, $meta_value, $prev_value);
		if (null !== $check) {
			return $this;
		}

		// Compare existing value to new value if no prev value given and the key exists only once.
		// if (empty($prev_value)) {
		// 	$old_value = get_metadata_raw($this->meta_type, $id, $meta_key);
		// 	if (is_countable($old_value) && count($old_value) === 1) {
		// 		if ($old_value[0] === $meta_value) {
		// 			return $this;
		// 		}
		// 	}
		// }

		// $meta_ids = $this->get_col($this->prepare("SELECT $id_column FROM {$this->table} WHERE meta_key = %s AND {$this->column} = %d", $meta_key, $id));
		// if (empty($meta_ids)) {
		// 	// BulkAddMeta::get_instance($this->meta_type)->add($id, $raw_meta_key, $passed_value);
		// 	return $this;
		// }

		// logger(memory_get_usage());
		// logger(count($this->items));

		$this->items[] = (object) [
			'id'         => $id,
			'meta_key'   => $meta_key,
			'meta_value' => $meta_value,
			'prev_value' => $prev_value
		];

		return $this;
	}

	private function prepare_item($item) {
		$meta_value = $item->meta_value;
		$meta_key = $item->meta_key;
		$id = $item->id;
		$meta_ids = $item->meta_ids;
		$prev_value = $item->prev_value;

		$_meta_value = $meta_value;
		$meta_value  = maybe_serialize($meta_value);

		$data  = compact('meta_value');
		$where = array(
			$this->column => $id,
			'meta_key'    => $meta_key,
		);

		if (!empty($prev_value)) {
			$prev_value          = maybe_serialize($prev_value);
			$where['meta_value'] = $prev_value;
		}

		foreach ($meta_ids as $meta_id) {
			/**
			 * Fires immediately before updating metadata of a specific type.
			 *
			 * The dynamic portion of the hook, `$meta_type`, refers to the meta object type
			 * (post, comment, term, user, or any other type with an associated meta table).
			 *
			 * @since 2.9.0
			 *
			 * @param int    $meta_id     ID of the metadata entry to update.
			 * @param int    $id          ID of the object metadata is for.
			 * @param string $meta_key    Metadata key.
			 * @param mixed  $_meta_value Metadata value. Serialized if non-scalar.
			 */
			do_action("update_{$this->meta_type}_meta", $meta_id, $id, $meta_key, $_meta_value);

			if ('post' === $this->meta_type) {
				/**
				 * Fires immediately before updating a post's metadata.
				 *
				 * @since 2.9.0
				 *
				 * @param int    $meta_id    ID of metadata entry to update.
				 * @param int    $id         Post ID.
				 * @param string $meta_key   Metadata key.
				 * @param mixed  $meta_value Metadata value. This will be a PHP-serialized string representation of the value
				 *                           if the value is an array, an object, or itself a PHP-serialized string.
				 */
				do_action('update_postmeta', $meta_id, $id, $meta_key, $meta_value);
			}
		}

		$data = $this->process_fields($this->table, $data, null);
		if (false === $data) {
			return false;
		}

		$where = $this->process_fields($this->table, $where, null);
		if (false === $where) {
			return false;
		}

		$fields     = array();
		$conditions = array();
		$values     = array();
		foreach ($data as $field => $value) {
			if (is_null($value['value'])) {
				$fields[] = "`$field` = NULL";
				continue;
			}

			$fields[] = "`$field` = " . $value['format'];
			$values[] = $value['value'];
		}
		foreach ($where as $field => $value) {
			if (is_null($value['value'])) {
				$conditions[] = "`$field` IS NULL";
				continue;
			}

			$conditions[] = "`$field` = " . $value['format'];
			$values[]     = $value['value'];
		}

		$fields     = implode(', ', $fields);
		$conditions = implode(' AND ', $conditions);

		$sql = "UPDATE `$this->table` SET $fields WHERE $conditions;";

		logger($sql);

		$this->sqls[] = $this->prepare($sql, $values);

		return true;
	}

	/**
	 * Will build a query and push all of the previously added items to the database.
	 * Once complete this object will be reset.
	 * 
	 * @return void 
	 */
	public function push() {
		$bulk_add = BulkAddMeta::get_instance($this->meta_type);
		if ($bulk_add->has_items()) {
			$bulk_add->push();
		}

		logger($this->items);

		if (!count($this->items))
			return $this;

		foreach ($this->items as $item) {
			$this->prepare_item($item);
		}

		$sql = implode(' ', $this->sqls);

		logger($sql);

		return;
		$result =  $this->query($sql);

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
