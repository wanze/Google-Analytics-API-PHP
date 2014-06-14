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

    function getOfflineAccessToken($grantCode, $grantType)
    {
        $oauth2token_url = "https://accounts.google.com/o/oauth2/token";
        $clienttoken_post = array(
            "client_id" => $this->clientId,
            "client_secret" => $this->clientSecret
        );

        if ($grantType === "online") {
            $clienttoken_post["code"] = $grantCode;
            $clienttoken_post["redirect_uri"] = 'http://google-analytics.com/oauth2callback';
            $clienttoken_post["grant_type"] = "authorization_code";
        }

        if ($grantType === "offline") {
            $clienttoken_post["refresh_token"] = $grantCode;
            $clienttoken_post["grant_type"] = "refresh_token";
        }

        $curl = curl_init($oauth2token_url);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $clienttoken_post);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $json_response = curl_exec($curl);
        curl_close($curl);

        $authObj = json_decode($json_response);

        //if offline access requested and granted, get refresh token
        if (isset($authObj->refresh_token)) {
            global $refreshToken;
            $refreshToken = $authObj->refresh_token;
        }

        $accessToken = $authObj->access_token;
        return $accessToken;
    }

}