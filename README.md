#Google Analytics API PHP

Clone this repo and run [composer](http://getcomposer.com). `composer install` then it will autoload the classes and you are done!

Simple class to set up Oauth 2.0 with Google and query the Google Analytics API v3 with PHP. Curl is required!
The class supports getting the access tokens for *web applications* and *service accounts* registered in the Google APIs console.   
See the documentation for further informations: https://developers.google.com/accounts/docs/OAuth2

##Install via Composer
Just add the following line in your `composer.json` and update your dependencies via running composer update command.
`"shahariaazam/google-analytics-api-php": "dev-master"`

##1. Basic Setup

* Create a Project in the Google APIs Console: https://code.google.com/apis/console/
* Enable the Analytics API under Services
* Under API Access: Create an Oauth 2.0 Client-ID
* Give a Product-Name, choose *Web Application* or *Service Account* depending on your needs
* Web Application: Set a redirect-uri in the project which points to your apps url
* Service Account: Download the private key (.p12 file)

##2. Set up Auth

Depending on the chosen application type, the setup is slightly different. This section describes both ways independently.

###Web applications

```php
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
```

##Query the Google Analytics API

Once you have a valid accessToken and an Account-ID, you can query the Google Analytics API.
You can set some default Query Parameters that will be executed with every query.

```php
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
```
###Metrics & Dimensions Reference:
https://developers.google.com/analytics/devguides/reporting/core/dimsmets

###Google Analytics Query Explorer for testing queries and results:
http://ga-dev-tools.appspot.com/explorer/
