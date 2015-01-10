<?php
    /*
     * Basic usage example:
     *  - Redirect to the oAuth page if no access token is present
     *  - Handles the 'code' return from the oAuth page,
     *    fetches an access token save it in a session variable
     *  - Makes an API request using the access token in the session var
     *
     * Make sure to request your API-key first at: 
     *    https://console.developers.google.com
     */
      
    // From the APIs console
    $client_id = 'xxxxxxxxxxxxxxxxxxxxxx.apps.googleusercontent.com';
    $email = 'xxxxxxxxxxxxxxxxxxxxxxxxxxx@developer.gserviceaccount.com';
    $privatekey = 'xxxxxxxxxxxxxxxx.p12';
    
  
    session_start();
   include('GoogleAnalyticsAPI.class.php');

$ga = new GoogleAnalyticsAPI('service');
$ga->auth->setClientId($client_id); // From the APIs console
$ga->auth->setEmail($email); // From the APIs console
$ga->auth->setPrivateKey($privatekey); // Path to the .p12 file
    
    /*
     *  Step 3: Do real stuff!
     *          If we're here, we sure we've got an access token
     */
  $auth = $ga->auth->getAccessToken();

// Try to get the AccessToken
if ($auth['http_code'] == 200) {
    $accessToken = $auth['access_token'];
    $tokenExpires = $auth['expires_in'];
    $tokenCreated = time();
    
    print_r($auth);
} else {
    echo "somthing went wrong";
}


$ga->setAccessToken($accessToken);
$ga->setAccountId('ga:xxxxx'); // GA ID

// Load profiles
$profiles = $ga->getProfiles();
$accounts = array();
foreach ($profiles['items'] as $item) {
    $id = "ga:{$item['id']}";
    $name = $item['name'];
    $accounts[$id] = $name;
}
// Print out the Accounts with Id => Name. Save the Id (array-key) of the account you want to query data. 
// See next chapter how to set the account-id.
print_r($accounts);
    
    // Set the default params. For example the start/end dates and max-results
   // Set the accessToken and Account-Id
$ga->setAccessToken($accessToken);
$ga->setAccountId('ga:xxxxxx'); // GA ID

// Set the default params. For example the start/end dates and max-results
$defaults = array(
    'start-date' => date('Y-m-d', strtotime('-3 month')), // get data of 3 months back
    'end-date' => date('Y-m-d'),
);
$ga->setDefaultQueryParams($defaults);

echo "<br><br><br>Example1: Get visits by date <br><br><br>";
$params = array(
    'metrics' => 'ga:visits',
    'dimensions' => 'ga:date',
);
$visits = $ga->query($params);
print_r($visits);


echo  "<br><br><br>Example2: Get visits by country<br><br><br>";
$params = array(
    'metrics' => 'ga:visits',
    'dimensions' => 'ga:country',
    'sort' => '-ga:visits',
    'max-results' => 30,
    'start-date' => '2014-12-10' //Overwrite this from the defaultQueryParams
); 
$visitsByCountry = $ga->query($params);
print_r($visitsByCountry);


echo "<br><br><br> Example3: Same data as Example1 but with the built in method:<br> <br><br>";
$visits = $ga->getVisitsByDate();
print_r($visits);


echo "<br><br><br>Example4: Get visits by Operating Systems and return max. 100 results<br><br> <br>";
$visitsByOs = $ga->getVisitsBySystemOs(array('max-results' => 100));
print_r($visitsByOs);

echo "<br><br><br>Example5: Get referral traffic <br><br><br>";
$referralTraffic = $ga->getReferralTraffic();
print_r($referralTraffic);


echo " <br><br><br>Example6: Get visits by languages <br><br><br>";
$visitsByLanguages = $ga->getVisitsByLanguages();

print_r($visitsByLanguages);
