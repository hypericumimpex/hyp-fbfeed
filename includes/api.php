<?php

if (!defined('ABSPATH')) exit;


require_once(plugin_dir_path(__FILE__) . '/api-tokens.php');

if (!class_exists('ElfsightFacebookFeedApi')) {
	class ElfsightFacebookFeedApi extends ElfsightFacebookFeedApiCore {
		private $tokens;

		private $routes = array(
			'' => 'requestController'
		);

		public $token;

		public function __construct($config) {
			parent::__construct($config, $this->routes);

			$this->tokens = new ElfsightFacebookFeedApiTokens($this->helper, $config);
			$this->token = $this->tokens->setCurrent();
		}

		public function requestController() {
			$q = $this->input('q');

			$cache_key = $this->cache->key($q, array('access_token', 'fields'));
			$data = $this->cache->get($cache_key);

			if (empty($data)) {
				if (!$this->tokens->isLimited($this->token, 90)) {
					$request_url = $this->buildRequestUrl($q);

					$response = $this->request('GET', $request_url);

					if (!empty($response)) {
						if (!empty($response['headers']) && !empty($response['headers']['x-app-usage'])) {
							$app_usage = json_decode($response['headers']['x-app-usage'], true);

							$this->tokens->update($this->token, $app_usage);

							if (!empty($response['body'])) {
								$result = json_decode($response['body'], true);

								if (!empty($result['error'])) {
									$error = $result['error'];

									switch($error['code']) {
										case 4:
											$this->token = $this->tokens->setCurrent();
											return $this->requestController();
										case 190:
											$this->tokens->update($this->token, 'deleted');
											$this->token = $this->tokens->setCurrent();
											return $this->requestController();

										default:
											break;
									}
								}
							}
						}

						if (!empty($response['body'])) {
							$data = $response['body'];
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
			$url = $this->helper->removeQueryParam($url, 'access_token');
			$url = $this->helper->addQueryParam($url, 'access_token', $this->token['token']);
			$url = $this->helper->addQueryParam($url, 'locale', 'en_US');

			if (stripos($url, 'https://graph.facebook.com') === false) {
				$url = 'https://graph.facebook.com/' . $url;
			}

			return $url;
		}
	}
}