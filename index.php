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

$ga->prepareToken();

$ga->setAccessToken($_SESSION['oauth_access_token']);
$ga->setAccountId($account_id);


$defaults = array(
    'start-date' => date('Y-m-d', strtotime('-1 month')),
    'end-date' => date('Y-m-d'),
);
$ga->setDefaultQueryParams($defaults);

$params = array(
    'metrics' => 'ga:visits',
    'dimensions' => 'ga:date',
);
$visits = $ga->query($params);

print "<pre>";
var_dump($visits);
print "</pre>";
