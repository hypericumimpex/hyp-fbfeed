<?php

if (!defined('ABSPATH')) exit;


if (!class_exists('ElfsightFacebookFeedApiCoreCache')) {
    class ElfsightFacebookFeedApiCoreCache {
		private $helper;

		private $cacheTime;
		private $pluginFile;

		private $tableName;

        public function __construct($helper, $config) {
			$this->helper = $helper;

			$this->pluginFile = $config['plugin_file'];
            $this->cacheTime = !empty($config['cache_time']) ? $config['cache_time'] : 43200;

			$this->tableName = $this->helper->getTableName('cache');

            if (!$this->helper->tableExist('cache')) {
                $this->createTable();
            }

			register_deactivation_hook($this->pluginFile, array($this, 'dropTable'));
        }

		public function key($query, $excluded_params = array('access_token')) {
			$key = $query;

			foreach ($excluded_params as $param) {
				$key = $this->helper->removeQueryParam($key, $param);
			}

			return preg_replace('#\?$#', '', $key);
		}

        public function get($key, $check_expire = true) {
			global $wpdb;

			$select_sql = 'SELECT * FROM `' . esc_sql($this->tableName) . '`';
			$select_sql .= ' WHERE `key` = "' . esc_sql($key) . '"';

			$cache = $wpdb->get_row($select_sql, ARRAY_A);

			if (!$cache || ($check_expire && time() > $cache['updated_at'] + $this->cacheTime)) {
				return null;
			}

			return $cache['data'];
        }

        public function set($key, $data) {
			global $wpdb;

			$cache = array(
				'key' => $key,
				'data' => $data,
				'updated_at' => time()
			);

			if ($this->exist($key)) {
				$status = $wpdb->update(
					$this->tableName,
					$cache,
					array('key' => $key)
				);
			} else {
				$status = $wpdb->insert(
					$this->tableName,
					$cache
				);
			}

			return !!$status;
        }

        private function exist($key) {
            global $wpdb;

	        return !!$wpdb->get_var('SELECT COUNT(*) FROM `' . $this->tableName . '` WHERE `key` = "' . esc_sql($key) . '"');
	    }

        private function createTable() {
            if (!function_exists('dbDelta')) {
                require(ABSPATH . 'wp-admin/includes/upgrade.php');
            }

            dbDelta(
                'CREATE TABLE `' . esc_sql($this->tableName) . '` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `key` varchar(255) NOT NULL,
                    `data` mediumtext NOT NULL,
                    `updated_at` text NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;'
            );
        }

        public function dropTable() {
			global $wpdb;

			return $wpdb->query('DROP TABLE IF EXISTS `' . $this->tableName . '`');
		}
    }
}