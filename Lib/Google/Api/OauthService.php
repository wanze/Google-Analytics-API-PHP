<?php

namespace Google\Api;

use Google\Api;


class OauthService extends Oauth
{

    const MAX_LIFETIME_SECONDS = 3600;
    const GRANT_TYPE = 'urn:ietf:params:oauth:grant-type:jwt-bearer';

    protected $email = '';
    protected $privateKey = null;
    protected $password = 'notasecret';

    public function __construct($clientId = '', $email = '', $privateKey = null)
    {
        if (!function_exists('openssl_sign')) {
            throw new Exception('openssl extension for PHP is needed.');
        }
        $this->clientId = $clientId;
        $this->email = $email;
        $this->privateKey = $privateKey;
    }


    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setPrivateKey($key)
    {
        $this->privateKey = $key;
    }

    public function getAccessToken($data = null)
    {

        if (!$this->clientId || !$this->email || !$this->privateKey) {
            throw new Exception('You must provide the clientId, email and a path to your private Key');
        }

        $jwt = $this->generateSignedJWT();

        $params = array(
            'grant_type' => self::GRANT_TYPE,
            'assertion' => $jwt,
        );

        $auth = Http::curl(Oauth::TOKEN_URL, $params, true);
        return json_decode($auth, $this->assoc);

    }

    protected function generateSignedJWT()
    {
        if (!file_exists($this->privateKey) || !is_file($this->privateKey)) {
            throw new Exception('Private key does not exist');
        }

        $header = array(
            'alg' => 'RS256',
            'typ' => 'JWT',
        );

        $t = time();
        $params = array(
            'iss' => $this->email,
            'scope' => Oauth::SCOPE_URL,
            'aud' => Oauth::TOKEN_URL,
            'exp' => $t + self::MAX_LIFETIME_SECONDS,
            'iat' => $t,
        );

        $encodings = array(
            base64_encode(json_encode($header)),
            base64_encode(json_encode($params)),
        );

        $input = implode('.', $encodings);
        $certs = array();
        $pkcs12 = file_get_contents($this->privateKey);
        if (!openssl_pkcs12_read($pkcs12, $certs, $this->password)) {
            throw new Exception('Could not parse .p12 file');
        }
        if (!isset($certs['pkey'])) {
            throw new Exception('Could not find private key in .p12 file');
        }
        $keyId = openssl_pkey_get_private($certs['pkey']);
        if (!openssl_sign($input, $sig, $keyId, 'sha256')) {
            throw new Exception('Could not sign data');
        }

        $encodings[] = base64_encode($sig);
        $jwt = implode('.', $encodings);
        return $jwt;

    }

}