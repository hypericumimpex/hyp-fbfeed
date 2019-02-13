<?php

if (!defined('ABSPATH')) exit;


if (!class_exists('ElfsightFacebookFeedApiCoreUser')) {
    class ElfsightFacebookFeedApiCoreUser {
        private $helper;

        private $pluginFile;

        private $tableName;

        public function __construct($helper, $config) {
            $this->helper = $helper;

            $this->pluginFile = $config['plugin_file'];

            $this->tableName = $this->helper->getTableName('user');

            if (!$this->helper->tableExist('user')) {
                $this->createTable();
            }
        }

        public function get($public_id) {
            global $wpdb;

            $select_sql = 'SELECT * FROM `' . esc_sql($this->tableName) . '`';
            $select_sql .= ' WHERE `public_id` = "' . esc_sql($public_id) . '"';

            return $wpdb->get_row($select_sql, ARRAY_A);
        }

        public function set($public_id, $data) {
            global $wpdb;

            $user_data = array(
                'public_id' => $public_id,
                'data' => $data,
                'updated_at' => time()
            );

            $wpdb->show_errors();

            if ($this->exist($public_id)) {
                $status = $wpdb->update(
                    $this->tableName,
                    $user_data,
                    array('public_id' => $public_id)
                );
            } else {
                $status = $wpdb->insert(
                    $this->tableName,
                    $user_data
                );
            }

            return !!$status;
        }

        private function exist($public_id) {
            global $wpdb;

            return !!$wpdb->get_var('SELECT COUNT(*) FROM `' . $this->tableName . '` WHERE `public_id` = "' . esc_sql($public_id) . '"');
        }

        private function createTable() {
            if (!function_exists('dbDelta')) {
                require(ABSPATH . 'wp-admin/includes/upgrade.php');
            }

            dbDelta(
                'CREATE TABLE `' . esc_sql($this->tableName) . '` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `public_id` varchar(255) NOT NULL,
                    `data` text NOT NULL,
                    `updated_at` text NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;'
            );
        }
    }
}