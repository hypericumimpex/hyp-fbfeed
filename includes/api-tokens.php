<?php

if (!defined('ABSPATH')) exit;


if (!class_exists('ElfsightFacebookFeedApiTokens')) {
    class ElfsightFacebookFeedApiTokens {
        private $helper;

        private $pluginFile;

        private $tableName;

		private $tokensList = array(
			'395202813876688|73e8ede72008b231a0322e40f0072fe6',
			'1786066011417150|5043eac44ee54731ed404b9db021cdf2',
			'1795317140689602|e25d547c4f12164254f85eead086b0a7',
			'1851314935096786|0de2a9ec77d745d6941850696ce166f9',
			'177899259380474|JgdlhmU-J0dq55bKng0xywDIgIo',
			'128947497630881|rlgLr8wtMUWRw7hbcLcsgoa01-k',
			'1298990730176646|rU4QhoOaYPWQng6-k_QdxRoVNaA',
			'1693018934313805|VYDjx69NpsgkDEAm79cYD0fxJBk',
			'257106408010811|MwCorr7qsyIeU_GjdPFIEw3-_P8',
			'386881278380301|NW_PiECD9TLVe0UNMsB5H9HkPEo',
			'277269689412168|o3k5mzmHsT-prqc2qizxk_DoXEQ',
			'1439406912938596|4JDCXisJ2d-1EWeObBt27DybH5c',
			'927474184059774|ekzzwIV9JnvG-ELYWut9wIvf4Y0',
			'322095208287051|pbCTS6jPuhjR18sl2UPhKQw7eyY',
			'1042396375891598|gn2HiZgDgjTbCMcXsSb6VK91PqM',
			'348613608818294|d4gRX7tNppCrI-DrOGof_O8gwvg',
			'1591407604237466|cHUFs9XDDJa7LDUW9zBxirwGAHE',
			'697312047120344|p8ST5dkrub6IoBZsClmyRBTScB0',
			'1788677371359317|UU7yeB5dsKOT8xLsLA9xSNu4OMQ',
			'1024245627652108|VmyBFUaBhjmvF31kPWdLcwtA0nU',
			'1665626540320930|kDmIPfF8Y0mvV5mPr3927c2nRlM',
			'219254908466738|9AAaE_5GnONhVWUTlEBS8LDiFi8',
			'383334425112756|cxkb0YngoQPVkr7AngA_LOE2TV8',
			'1711513059125773|IuTAeRQAzhUelndJ_n7jPx3yOxs',
			'1677248395890039|CSZsE5C-HJ8cYOraU6J6gwACZys',
			'162288250832230|HvQ8grGeT3QGVEFgRkooK-V55vs',
			'1028332560591295|InX-Kx2LF2tjcfjbz4ddP6wXJ6U',
			'1816228771930249|xW0dj0nD-gWTl9oUEFyz7kCn4Gk',
			'1425919427736604|6NKiBWf5_rR4DuV2z1E_Pk27F2I',
			'198080700214649|natEgdD5R82UoiLXL5UsUK82-O8',
			'452046251639377|sruLhZT7bktRpuPy0txclkvCMWE',
			'282581258595802|QRueniLvr6ppOBW9UcNpJVswGKw',
			'120755681588984|8IamCzI5D56psRs_726PwSgUgos',
			'236542103198412|YZBFLCWsx_ap_c2rmznf_tEbh6E',
			'444110102425340|1xyyWHpqzWy5jNrMnNAsMgIIKVI',
			'294686830545691|3DhoPPXbNBmzlmXXK9cbLnGJTMI',
		);

		private $usageStoreTime = 43200;

        public function __construct($helper, $config) {
            $this->helper = $helper;

            $this->pluginFile = $config['plugin_file'];

            $this->tableName = $this->helper->getTableName('tokens');

			if (!$this->helper->tableExist('tokens')) {
				$this->createTable();
				$this->seedTable();
			}

			register_deactivation_hook($this->pluginFile, array($this, 'dropTable'));
        }

		public function setCurrent() {
			$list = $this->get();
			return $list[0];
		}

		public function isLimited($token, $value) {
			return !empty($token['usage_value']) && $token['usage_value'] > $value;
		}

		private function get($token = false) {
			global $wpdb;

			$result = array();

			$select_sql = 'SELECT * FROM `' . esc_sql($this->tableName) . '`';
			if ($token) {
				$select_sql .= ' WHERE `token` = "' . esc_sql($token) . '"';
			}

			$list = $wpdb->get_results($select_sql, ARRAY_A);

			foreach ($list as &$token) {
				if ($token['updated_at'] + $this->usageStoreTime < time()) {
					$token['usage_value'] = 0;
					$token['usage_data'] = '{}';
				}
				$result[] = $token;
			}

			usort($result, array($this, 'sort'));

			return $result;
		}

		private function sort($a, $b) {
			return $a['usage_value'] > $b['usage_value'];
		}

		public function update($token, $app_usage) {
			global $wpdb;

			if (empty($token) || empty($app_usage)) {
				return false;
			}

			if ($app_usage === 'deleted') {
				return !!$wpdb->delete(
					$this->tableName,
					array('id' => $token['id'])
				);
			}

			$usage_value = 0;
			foreach ($app_usage as $usage_param) {
				if ($usage_param > $usage_value) {
					$usage_value = $usage_param;
				}
			}

			$this->token['app_usage'] = $app_usage;
			$this->token['usage_value'] = $usage_value;

			return !!$wpdb->update(
				$this->tableName,
				array(
					'token' => $token['token'],
					'usage_data' => json_encode($app_usage),
					'usage_value' => $usage_value,
					'updated_at' => time()
				),
				array('id' => $token['id'])
			);
		}

		private function createTable() {
			if (!function_exists('dbDelta')) {
				require(ABSPATH . 'wp-admin/includes/upgrade.php');
			}

			dbDelta(
				'CREATE TABLE `' . esc_sql($this->tableName) . '` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `token` varchar(255) NOT NULL,
                    `usage_data` text NOT NULL,
                    `usage_value` int(3) NOT NULL,
                    `updated_at` int(10) NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;'
			);
		}

		private function seedTable() {
			global $wpdb;

			foreach ($this->tokensList as $token) {
				$wpdb->insert($this->tableName, array(
					'token' => $token,
					'usage_data' => '{}',
					'usage_value' => 0,
					'updated_at' => 0
				));
			}
		}

		public function dropTable() {
			global $wpdb;

			return $wpdb->query('DROP TABLE IF EXISTS `' . $this->tableName . '`');
		}
    }
}