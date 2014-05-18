<?php

namespace Google\Api;

class Http
{


    /**
     * Send http requests with curl
     *
     * @access public
     * @static
     * @param mixed $url The url to send data
     * @param array $params (default: array()) Array with key/value pairs to send
     * @param bool $post (default: false) True when sending with POST
     */
    public static function curl($url, $params = array(), $post = false)
    {

        if (empty($url)) {
            return false;
        }

        if (!$post && !empty($params)) {
            $url = $url . "?" . http_build_query($params);
        }
        $curl = curl_init($url);
        if ($post) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        $http_code = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // Add the status code to the json data, useful for error-checking
        $data = preg_replace('/^{/', '{"http_code":' . $http_code . ',', $data);
        curl_close($curl);
        return $data;

    }

}