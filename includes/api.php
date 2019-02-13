<?php

if (!defined('ABSPATH')) exit;


require_once(plugin_dir_path(__FILE__) . '/api-usage.php');

if (!class_exists('ElfsightFacebookFeedApi')) {
	class ElfsightFacebookFeedApi extends ElfsightFacebookFeedApiCore {
		private $routes = array(
			'' => 'requestController'
		);

		private $usage;

		public function __construct($config) {
			parent::__construct($config, $this->routes);

			$this->usage = new ElfsightFacebookFeedApiUsage($this->helper, $config);
		}

        public function requestController() {
            $q = $this->input('q');

            $cache_key = $this->cache->key($q, array('access_token', 'fields'));
            $cache_data = $this->cache->get($cache_key);

            $data = array();

            if (empty($cache_data)) {
                if (!$this->usage->isLimited(75)) {
                    $request_url = $this->buildRequestUrl($q);

                    $response = $this->request('GET', $request_url);

                    if (!empty($response)) {
                        if (!empty($response['headers']) && !empty($response['headers']['x-app-usage'])) {
                            $app_usage = json_decode($response['headers']['x-app-usage'], true);

                            $this->usage->update($app_usage);
                        }

                        if (!empty($response['body'])) {
                            $data = $response['body'];

                            if (!empty($data['error'])) {
                                $error = $data['error'];
                                return $this->fbError($error['code'], $error['type'] . ': ' . $error['message']);
                            }
                        }

                        if (!empty($response['http_code']) && $response['http_code'] === '200') {
                            $this->cache->set($cache_key, $data);
                        }

                    } else {
                        return $this->error();
                    }
                } else {
                    $data = $this->cache->get($cache_key, false);

                    if (empty($data)) {
                        return $this->fbError(4, '(#4) Application request limit reached');
                    }
                }
            } else {
                $data = $cache_data;
            }

            return $this->response($data, true);
        }

		public function fbError($code, $message, $fbtrace_id = null) {
            $error = array(
	            'error' => array(
		            'code' => $code,
		            'message' => $message
	            )
            );

			if ($fbtrace_id) {
				$fbtrace_id && $error['error']['fbtrace_id'] = $fbtrace_id;
			}

            $this->response($error);
		}

		public function buildRequestUrl(&$url) {
			$url = $this->helper->addQueryParam($url, 'locale', 'en_US');

			if (stripos($url, 'https://graph.facebook.com') === false) {
				$url = 'https://graph.facebook.com/' . $url;
			}

			return $url;
		}
	}
}