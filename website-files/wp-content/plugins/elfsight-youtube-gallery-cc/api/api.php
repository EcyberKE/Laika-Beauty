<?php

namespace ElfsightYoutubeGalleryApi;


if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/vendor/autoload.php';

class Api extends Core\Api {
    private $routes = array(
        '' => 'requestController'
    );

    const API_BASE_URL = 'https://www.googleapis.com/youtube/v3';

    static $API_KEY;

    public function __construct($config) {
        parent::__construct($config, $this->routes);
    }

    public function requestController() {
        $q = $this->input('q');

        $cache_key = $this->Cache->keyFromQuery($q, array('fields', 'callback', '_'));
        $data = $this->Cache->get($cache_key);

        if (empty($data)) {
            $request_url = $this->buildRequestUrl($q);

            $response = $this->request('GET', $request_url, array(
                'headers' => [
                    'Referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_SERVER['SCRIPT_URI']
                ]
            ));

            if ($this->checkResponse($response, false)) {
                $data = $response['body'];
                $data_arr = json_decode($response['body'], true);

                if (!empty($data_arr['error'])) {
                    $error = $data_arr['error']['errors'][0];
                    $error_msg = "{$error['message']}: {$error['reason']} ({$error['domain']})";

                    return $this->error($data_arr['error']['code'], $error_msg, $error);
                }

                if ((int) $response['http_code'] === 200) {
                    $this->Cache->set($cache_key, $data);
                }
            } else {
                return $this->error();
            }
        }

        return $this->response($data);
    }


    public function buildRequestUrl($url) {
        self::$API_KEY = $this->input('key') ? $this->input('key') : get_option($this->Helper->getOptionName('api_key'), null);

        $url = $this->Helper->removeQueryParam($url, 'key');
        $url = $this->Helper->addQueryParam($url, 'key', self::$API_KEY);

        return self::API_BASE_URL . urldecode($url);
    }
}
