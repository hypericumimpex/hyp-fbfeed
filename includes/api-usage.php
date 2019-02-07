<?php

if (!defined('ABSPATH')) exit;


if (!class_exists('ElfsightFacebookFeedApiUsage')) {
    class ElfsightFacebookFeedApiUsage {
        private $helper;

        private $pluginFile;

        private $tableName;

		private $usageStoreTime = 43200;

		private $usage;

        public function __construct($helper, $config) {
            $this->helper = $helper;

            $this->pluginFile = $config['plugin_file'];

            $this->tableName = $this->helper->getTableName('usage');

			if (!$this->helper->tableExist('usage')) {
				$this->createTable();
			}

			register_deactivation_hook($this->pluginFile, array($this, 'dropTable'));
        }

		public function isLimited($value) {
			return !empty($token['usage_value']) && $token['usage_value'] > $value;
		}

		private function get() {
			global $wpdb;

			$this->usage = $wpdb->get_row('SELECT * FROM `' . esc_sql($this->tableName) . '` WHERE `id` = 1', ARRAY_A);

            if ($this->usage['updated_at'] + $this->usageStoreTime < time()) {
                $this->usage['usage_value'] = 0;
                $this->usage['usage_data'] = '{}';
            }

			return $this->usage;
		}

		public function update($app_usage) {
			global $wpdb;

			if (empty($app_usage)) {
				return false;
			}

			$usage_value = 0;
			foreach ($app_usage as $usage_param) {
				if ($usage_param > $usage_value) {
					$usage_value = $usage_param;
				}
			}

			$data = array(
                'usage_data' => json_encode($app_usage),
                'usage_value' => $usage_value,
                'updated_at' => time()
            );

            if ($this->exist()) {
                $status = $wpdb->update(
                    $this->tableName,
                    $data,
                    array('id' => 1)
                );
            } else {
                $status = $wpdb->insert(
                    $this->tableName,
                    $data
                );
            }

            return !!$status;
		}

        private function exist() {
            global $wpdb;

            return !!$wpdb->get_var('SELECT COUNT(*) FROM `' . $this->tableName . '` WHERE `id` = 1');
        }

		private function createTable() {
			if (!function_exists('dbDelta')) {
				require(ABSPATH . 'wp-admin/includes/upgrade.php');
			}

			dbDelta(
				'CREATE TABLE `' . esc_sql($this->tableName) . '` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `usage_data` text NOT NULL,
                    `usage_value` int(3) NOT NULL,
                    `updated_at` int(10) NOT NULL,
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