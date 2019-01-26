<?php

if (!defined('ABSPATH')) exit;


if (!class_exists('ElfsightFacebookFeedApiCoreHelper')) {
    class ElfsightFacebookFeedApiCoreHelper {
        public $pluginSlug;

        public function __construct($pluginSlug) {
            $this->pluginSlug = $pluginSlug;
        }

        public function getOptionName($name) {
            return str_replace('-', '_', $this->pluginSlug) . '_' . $name;
        }

        public function getTableName($name) {
            global $wpdb;

            return $wpdb->prefix . $this->getOptionName($name);
        }

        public function tableExist($name) {
            global $wpdb;

            return !!$wpdb->get_var('SHOW TABLES LIKE "' . esc_sql($this->getTableName($name)) . '"');
        }

		public function addQueryParam($url, $key, $val) {
			return $url . (strpos($url,'?') !== false ? '&' : '?') . $key . '=' . $val;
		}

		public function removeQueryParam($url, $key) {
			$result = $url;
			$url_data = parse_url($url);

			if (!empty($url_data['query'])) {
				parse_str($url_data['query'], $query_params);

				if (!empty($query_params[$key])) {
					unset($query_params[$key]);
				}

				$result = $url_data['path'] . '?' . http_build_query($query_params);
			}

			return $result;
		}

        public function array_merge_assoc() {
            $mixed = null;
            $arrays = func_get_args();

            foreach ($arrays as $k => $arr) {
                if ($k === 0) {
                    $mixed = $arr;
                    continue;
                }

                $mixed = array_combine(
                    array_merge(array_keys($mixed), array_keys($arr)),
                    array_merge(array_values($mixed), array_values($arr))
                );
            }

            return $mixed;
        }
    }
}