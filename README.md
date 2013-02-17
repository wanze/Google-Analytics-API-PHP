#Google Analytics API PHP

Simple class to set up Oauth 2.0 with Google and query the Google Analytics API v3 with PHP.
Curl is required! 

##1. Basic Setup

* Create a Project in the Google APIs Console: https://code.google.com/apis/console/
* Enable the Analytics API under Services
* Set a redirect-Uri in the Project which points to your App

##2. Set up Auth

```php
include('GoogleAnalyticsAPI.class.php');

$ga = new GoogleAnalyticsAPI();
$ga->auth->setClientId('your_client_id');
$ga->auth->setClientSecret('your_client_secret');
$ga->auth->setRedirectUri('redirect_uri');

//Get the Auth-Url
$url = $ga->auth->buildAuthUrl();
```

Provide a link to the auth-url. The has to log in with his Google Account and will be redirected back to the redirect-Uri along with a code
The code is needed to get the tokens

```php
$code = $_GET['code'];
$auth = $ga->auth->getAccessToken($code);

//Try to get the AccessToken
if ($auth['http_code'] == 200) {
	$accessToken = $auth['access_token'];
	$refreshToken = $auth['refresh_token'];
	$tokenExpires = $auth['expires_in'];
	$tokenCreated = time();
} else {
	//error...
}
```

With the accessToken you can query the API for the given time in $tokenExpires.
If you need to query the API byond the expire-time, you should store the refreshToken along with a timestamp in the Database / Session.
If the accessToken expires, you can get a new one with the refreshToken.

```php
//Check if the accessToken is expired
if ((time() - $tokenCreated) >= $tokenExpires) {
	$auth = $ga->auth->refreshAccessToken($refreshToken);
	//Get the accessToken as above and save it int the Database / Session
}
```

##3. Find the Account-ID

Before you can query the API, you need the ID of the Account you want to query the data.
The ID can be found manually in the Google Analytic Options of with the class:

```php
$profiles = $ga->getProfiles();
$accounts = array();
foreach ($profiles['items'] as $item) {
	$id = "ga:{$item['id']}";
	$name = $item['name'];
	$accounts[$id] = $name;
}
//Print out the Accounts with Id => Name, the array-key is the ID you have to remember
print_r($account);
```
##4. Query the Google Analytics API

Once you have a valid accessToken and an Account-ID, you can query the Google Analytics API.
You can set some default Query Parameters that will be executed with every query.

```php
//Set the accessToken and Account-Id
$ga->setAccessToken($accessToken);
$ga->setAccountId('ga:xxxxxxx');

//Set the default params. For example the start/end dates and max-results
$defaults = array(
	'start-date' => date('Y-m-d', strtotime('-1 month')),
	'end-date' => date('Y-m-d'),
);
$ga->setDefaultQueryParams($defaults);

//Example1: Get visits by date
$params = array(
	'metrics' => 'ga:visits',
	'dimensions' => 'ga:date',
);
$visits = $ga->query($params);

//Example2: Get visits by country
$params = array(
	'metrics' => 'ga:visits',
	'dimensions' => 'ga:country',
	'sort' => '-ga:visits',
	'max-results' => 30,
	'start-date' => '2013-01-01' //Overwrite this from the defaultQueryParams
); 
$visitsByCountry = $ga->query($params);

//Same data as Example1 but with the built in method:
$visits = $ga->getVisitsByDate();
```
###Metrics & Dimensions Reference:
https://developers.google.com/analytics/devguides/reporting/core/dimsmets

###Google Analytics Query Explorer for testing queries and results:
http://ga-dev-tools.appspot.com/explorer/