<?php
require dirname(__FILE__) . '/vendor/autoload.php';
require dirname(__FILE__) . '/settings.php';

use Google\Api\Analytics;

$client_id = $config['clientID'];
$client_secret = $config['clientSecret'];
$redirect_uri = $config['redirectURI'];
$account_id = $config['accountID'];

session_start();

$ga = new Analytics();
$ga->auth->setClientId($client_id);
$ga->auth->setClientSecret($client_secret);
$ga->auth->setRedirectUri($redirect_uri);

if (isset($_GET['force_oauth'])) {
    $_SESSION['oauth_access_token'] = null;
}

if (!isset($_SESSION['oauth_access_token']) && !isset($_GET['code'])) {
    // Go get the url of the authentication page, redirect the client and go get that token!
    $url = $ga->auth->buildAuthUrl();
    header("Location: " . $url);
}

if (!isset($_SESSION['oauth_access_token']) && isset($_GET['code'])) {

    $auth = $ga->auth->getAccessToken($_GET['code']);

    if ($auth['http_code'] == 200) {
        $accessToken = $auth['access_token'];
        $refreshToken = $auth['refresh_token'];
        $tokenExpires = $auth['expires_in'];
        $tokenCreated = time();

        $_SESSION['oauth_access_token'] = $accessToken;
    } else {
        die("Sorry, something wend wrong retrieving the oAuth tokens");
    }
}

$ga->setAccessToken($_SESSION['oauth_access_token']);
$ga->setAccountId($account_id);


$defaults = array(
    'start-date' => date('Y-m-d', strtotime('-1 month')),
    'end-date' => date('Y-m-d'),
);
$ga->setDefaultQueryParams($defaults);

$params = array(
    'metrics'       => 'ga:visits',
    'dimensions'    => 'ga:date',
);
$visits = $ga->query($params);

print "<pre>";
var_dump($visits);
print "</pre>";
