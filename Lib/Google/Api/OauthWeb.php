<?php

namespace Google\Api;

use Google\Api;

class OauthWeb extends Oauth
{

    const AUTH_URL = 'https://accounts.google.com/o/oauth2/auth';
    const REVOKE_URL = 'https://accounts.google.com/o/oauth2/revoke';

    protected $clientSecret = '';
    protected $redirectUri = '';

    public function __construct($clientId = '', $clientSecret = '', $redirectUri = '')
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
    }

    public function setClientSecret($secret)
    {
        $this->clientSecret = $secret;
    }

    public function setRedirectUri($uri)
    {
        $this->redirectUri = $uri;
    }

    public function buildAuthUrl($params = array())
    {

        if (!$this->clientId || !$this->redirectUri) {
            throw new Exception('You must provide the clientId and a redirectUri');
        }

        $defaults = array(
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => Oauth::SCOPE_URL,
            'access_type' => 'offline',
            'approval_prompt' => 'force',
        );
        $params = array_merge($defaults, $params);
        $url = self::AUTH_URL . '?' . http_build_query($params);
        return $url;

    }

    public function getAccessToken($data = null)
    {

        if (!$this->clientId || !$this->clientSecret || !$this->redirectUri) {
            throw new Exception('You must provide the clientId, clientSecret and a redirectUri');
        }

        $params = array(
            'code' => $data,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code',
        );

        $auth = Http::curl(Oauth::TOKEN_URL, $params, true);
        return json_decode($auth, $this->assoc);

    }

    public function refreshAccessToken($refreshToken)
    {
        if (!$this->clientId || !$this->clientSecret) {
            throw new Exception('You must provide the clientId and clientSecret');
        }

        $params = array(
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        );

        $auth = Http::curl(Oauth::TOKEN_URL, $params, true);
        return json_decode($auth, $this->assoc);

    }

    public function revokeAccess($token)
    {
        $params = array('token' => $token);
        $data = Http::curl(self::REVOKE_URL, $params);
        return json_decode($data, $this->assoc);
    }

}