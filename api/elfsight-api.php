<?php

if (!defined('ABSPATH')) exit;


if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);

    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

if (PHP_VERSION_ID > 50600) {
    require __DIR__ . '/vendor/autoload.php';
}

require_once(plugin_dir_path(__FILE__) . '/includes/helper.php');
require_once(plugin_dir_path(__FILE__) . '/includes/cache.php');
require_once(plugin_dir_path(__FILE__) . '/includes/options.php');
require_once(plugin_dir_path(__FILE__) . '/includes/user.php');

if (!class_exists('ElfsightFacebookFeedApiCore')) {
    abstract class ElfsightFacebookFeedApiCore {
        public $pluginSlug;
		public $pluginFile;

        private $routes;

        public function __construct($config, $routes) {
            $this->pluginSlug = $config['slug'];
			$this->pluginFile = $config['plugin_file'];

            $this->routes = $routes;

            $this->helper = new ElfsightFacebookFeedApiCoreHelper($this->pluginSlug);
            $this->cache = new ElfsightFacebookFeedApiCoreCache($this->helper, $config);
			$this->options = new ElfsightFacebookFeedApiCoreOptions($this->helper, $config);
			$this->user = new ElfsightFacebookFeedApiCoreUser($this->helper, $config);

            add_action('wp_ajax_nopriv_' . $this->helper->getOptionName('api'), array($this, 'run'));
            add_action('wp_ajax_' . $this->helper->getOptionName('api'), array($this, 'run'));
        }

        public function run() {
            $method = $this->input('method', '', false);

            $route = $this->routes[$method];

            if (empty($route) || !method_exists($this, $route)) {
                $this->error(400, 'invalid request', 'requested route not found');
            }

            return call_user_func(array($this, $route));
        }

        public function request($type, $url, $options = array()) {
            $type = strtoupper($type);
			$headers = !empty($this->client['headers']) ? $this->client['headers'] : array();

            if (!empty($options['headers'])) {
                $headers = $this->helper->array_merge_assoc($headers, $options['headers']);
            }

            $headers_raw_list = array();

            foreach ($headers as $header_key => $header_value) {
                $headers_raw_list[] = $header_key . ': ' . $header_value;
            }
            unset($header_key, $header_value);

            $curl = curl_init();

            $curl_options = array(
                CURLOPT_URL            => $url,
                CURLOPT_HEADER         => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_FOLLOWLOCATION => !empty($options['follow']) && $options['follow']
            );

            if (!empty($headers_raw_list)) {
                $curl_options[CURLOPT_HTTPHEADER] = $headers_raw_list;
            }

            curl_setopt_array($curl, $curl_options);

            $response = curl_exec($curl);
            $info   = curl_getinfo($curl);
            $error  = curl_error($curl);

            curl_close($curl);

            if ($info['http_code'] === 0) {
                $this->error(400, 'transport error', $error);
            }

            if (isset($info['content_type']) && isset($info['size_download'])) {
                header('Content-Type: ' . $info['content_type']);
                header('Content-Length: ' . $info['size_download']);
            }

            return $this->requestResponse($response);
        }

        public function requestResponse($response_str) {
			@list ($response_headers_str, $response_body_encoded, $alt_body_encoded) = explode("\r\n\r\n", $response_str);

			if ($alt_body_encoded) {
				$response_headers_str = $response_body_encoded;
				$response_body_encoded = $alt_body_encoded;
			}

			$response_body = $response_body_encoded;

			$response_headers_raw_list = explode("\r\n", $response_headers_str);
			$response_http = array_shift($response_headers_raw_list);

			preg_match('#^([^\s]+)\s(\d+)\s?([^$]+)?$#', $response_http, $response_http_matches);
			array_shift($response_http_matches);

			list ($response_http_protocol, $response_http_code, $response_http_message) = $response_http_matches;

			$response_headers = array();
			$response_cookies = array();

			foreach ($response_headers_raw_list as $header_row) {
				list ($header_key, $header_value) = explode(': ', $header_row, 2);

				if (strtolower($header_key) === 'set-cookie') {
					$cookie_params = explode('; ', $header_value);

					if (empty($cookie_params[0])) {
						continue;
					}

					list ($cookie_name, $cookie_value) = explode('=', $cookie_params[0]);
					$response_cookies[$cookie_name] = $cookie_value;

				} else {
					$response_headers[$header_key] = $header_value;
				}
			}
			unset($header_row, $header_key, $header_value, $cookie_name, $cookie_value);

			return array(
				'status' => 1,
				'http_protocol' => $response_http_protocol,
				'http_code' => $response_http_code,
				'http_message' => $response_http_message,
				'headers' => $response_headers,
				'cookies' => $response_cookies,
				'body' => $response_body
			);
		}

        public function response($data, $json = false) {
			$callback = $this->input('callback', null, false);
            $output = $json ? $data : json_encode($data);

			if (!empty($callback)) {
				$callback = htmlspecialchars(strip_tags($callback));
				$validate_callback = preg_match('#^jQuery[0-9]*\_[0-9]*$#', $callback);

				if ($validate_callback) {
					$output = '/**/ ' . $callback . '(' . $output . ')';
				}
			}

            if (ob_get_length()) {
                ob_clean();
            }

            header('Access-Control-Allow-Origin: *');
            header('Content-type: application/json; charset=utf-8');
            exit($output);
        }

        public function error($code = 400, $error_message = 'service is unavailable now', $additional = '') {
            $error = array(
                'meta' => array(
                    'code' => $code,
                    'error_message' => $error_message
                )
            );

            if ($additional) {
                $additional && $error['meta']['_additional'] = $additional;
            }

            $this->response($error);
        }

        public function input($name, $default = null, $check_empty = true) {
            $value = isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;

            if (empty($value) && $check_empty) {
                $this->error(400, 'invalid request', $name . ' is not defined');
            }

            return $value;
        }
    }
}