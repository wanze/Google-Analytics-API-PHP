<?php

/**
 * GoogleAnalyticsAPI
 * Simple class which provides methods to set up OAuth 2.0 with Google and query the Google Analytics API v3 with PHP.
 *
 * cURL is required.
 *
 * There are two possibilities to get the Oauth 2.0 tokens from Google:
 * 1) OAuth 2.0 for Web Applications (end-user involved)
 * 2) OAuth 2.0 for Server to Server Applications (openssl required)
 *
 * Please note that this class does not handle error codes returned from Google. But the the http status code
 * is returned along with the data. You can check for the array-key 'status_code', which should be 200 if everything worked.
 *
 * See the readme on GitHub for instructions and examples how to use the class
 *
 * @author Stefan Wanzenried
 * @copyright Stefan Wanzenried
 * <www.everchanging.ch>
 *
 * @version 1.1
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 *
 */
class GoogleAnalyticsAPI {

	const API_URL = 'https://www.googleapis.com/analytics/v3/data/ga';
	const WEBPROPERTIES_URL = 'https://www.googleapis.com/analytics/v3/management/accounts/~all/webproperties';
	const PROFILES_URL = 'https://www.googleapis.com/analytics/v3/management/accounts/~all/webproperties/~all/profiles';

	public $auth = null;
	protected $accessToken = '';
	protected $accountId = '';
	protected $assoc = true;

	/**
	 * Default query parameters
	 *
	 */
	protected $defaultQueryParams = array();


	/**
	 * Constructor
	 *
	 * @access public
	 * @param String $auth (default: 'web') 'web' for Web-applications with end-users involved, 'service' for service applications (server-to-server)
	 */
	public function __construct($auth='web') {

		if (!function_exists('curl_init')) throw new Exception('The curl extension for PHP is required.');
		$this->auth = ($auth == 'web') ? new GoogleOauthWeb() : new GoogleOauthService();
		$this->defaultQueryParams = array(
			'start-date' => date('Y-m-d', strtotime('-1 month')),
			'end-date' => date('Y-m-d'),
			'metrics' => 'ga:visits',
		);

	}

	public function __set($key, $value) {

		switch ($key) {
		case 'auth' :
			if (($value instanceof GoogleOauth) == false) {
				throw new Exception('auth needs to be a subclass of GoogleOauth');
			}
			$this->auth = $value;
			break;
		case 'defaultQueryParams' :
			$this->setDefaultQueryParams($value);
			break;
		default:
			$this->{$key} = $value;
		}

	}

	public function setAccessToken($token) {
		$this->accessToken = $token;
	}

	public function setAccountId($id) {
		$this->accountId = $id;
	}

	/**
	 * Set default query parameters
	 * Useful settings: start-date, end-date, max-results
	 *
	 * @access public
	 * @param array() $params Query parameters
	 */
	public function setDefaultQueryParams(array $params) {
		$params = array_merge($this->defaultQueryParams, $params);
		$this->defaultQueryParams = $params;
	}


	/**
	 * Return objects from json_decode instead of arrays
	 *
	 * @access public
	 * @param mixed $bool true to return objects
	 */
	public function returnObjects($bool) {
		$this->assoc = !$bool;
		$this->auth->returnObjects($bool);
	}


	/**
	 * Query the Google Analytics API
	 *
	 * @access public
	 * @param array $params (default: array()) Query parameters
	 * @return array data
	 */
	public function query($params=array()) {
		return $this->_query($params);
	}


	/**
	 * Get all WebProperties
	 *
	 * @access public
	 * @return array data
	 */
	public function getWebProperties() {

		if (!$this->accessToken) throw new Exception('You must provide an accessToken');

		$data = Http::curl(self::WEBPROPERTIES_URL, array('access_token' => $this->accessToken));
		return json_decode($data, $this->assoc);

	}


	/**
	 * Get all Profiles
	 *
	 * @access public
	 * @return array data
	 */
	public function getProfiles() {

		if (!$this->accessToken) throw new Exception('You must provide an accessToken');

		$data = Http::curl(self::PROFILES_URL, array('access_token' => $this->accessToken));
		return json_decode($data, $this->assoc);

	}

	/*****************************************************************************************************************************
	 *
	 * The following methods implement queries for the most useful statistics, seperated by topics: Audience/Content/Traffic Sources
	 *
	 *****************************************************************************************************************************/

	/*
	 * AUDIENCE
	 *
	 */

	public function getVisitsByDate($params=array()) {

		$defaults = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:date',
		);
		$_params = array_merge($defaults, $params);
		return $this->_query($_params);

	}

	public function getAudienceStatistics($params=array()) {

		$defaults = array(
			'metrics' => 'ga:visitors,ga:newVisits,ga:percentNewVisits,ga:visits,ga:bounces,ga:pageviews,ga:visitBounceRate,ga:timeOnSite,ga:avgTimeOnSite',
		);
		$_params = array_merge($defaults, $params);
		return $this->_query($_params);

	}

	public function getVisitsByCountries($params=array()) {

		$defaults = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:country',
			'sort' => '-ga:visits',
		);
		$_params = array_merge($defaults, $params);
		return $this->_query($_params);

	}

	public function getVisitsByCities($params=array()) {

		$defaults = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:city',
			'sort' => '-ga:visits',
		);
		$_params = array_merge($defaults, $params);
		return $this->_query($_params);

	}

	public function getVisitsByLanguages($params=array()) {

		$defaults = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:language',
			'sort' => '-ga:visits',
		);
		$_params = array_merge($defaults, $params);
		return $this->_query($_params);

	}

	public function getVisitsBySystemBrowsers($params=array()) {

		$defaults = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:browser',
			'sort' => '-ga:visits',
		);
		$_params = array_merge($defaults, $params);
		return $this->_query($_params);

	}

	public function getVisitsBySystemOs($params=array()) {

		$defaults = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:operatingSystem',
			'sort' => '-ga:visits',
		);
		$_params = array_merge($defaults, $params);
		return $this->_query($_params);


	}

	public function getVisitsBySystemResolutions($params=array()) {

		$defaults = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:screenResolution',
			'sort' => '-ga:visits',
		);
		$_params = array_merge($defaults, $params);
		return $this->_query($_params);

	}

	public function getVisitsByMobileOs($params=array()) {

		$defaults = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:operatingSystem',
			'sort' => '-ga:visits',
			'segment' => 'gaid::-11',
		);
		$_params = array_merge($defaults, $params);
		return $this->_query($_params);

	}

	public function getVisitsByMobileResolutions($params=array()) {

		$defaults = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:screenResolution',
			'sort' => '-ga:visits',
			'segment' => 'gaid::-11',
		);
		$_params = array_merge($defaults, $params);
		return $this->_query($_params);

	}

	/*
	 * CONTENT
	 *
	 */

	public function getPageviewsByDate($params=array()) {

		$defaults = array(
			'metrics' => 'ga:pageviews',
			'dimensions' => 'ga:date',
		);
		$_params = array_merge($defaults, $params);
		return $this->_query($_params);

	}

	public function getContentStatistics($params=array()) {

		$defaults = array(
			'metrics' => 'ga:pageviews,ga:uniquePageviews',
		);
		$_params = array_merge($defaults, $params);
		return $this->_query($_params);

	}

	public function getContentTopPages($params=array()) {

		$defaults = array(
			'metrics' => 'ga:pageviews',
			'dimensions' => 'ga:pagePath',
			'sort' => '-ga:pageviews',
		);
		$_params = array_merge($defaults, $params);
		return $this->_query($_params);

	}

	/*
	 * TRAFFIC SOURCES
	 *
	 */

	public function getTrafficSources($params=array()) {

		$defaults = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:medium',
		);
		$_params = array_merge($defaults, $params);
		return $this->_query($_params);

	}

	public function getKeywords($params=array()) {

		$defaults = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:keyword',
			'sort' => '-ga:visits',
		);
		$_params = array_merge($defaults, $params);
		return $this->_query($_params);

	}

	public function getReferralTraffic($params=array()) {

		$defaults = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:source',
			'sort' => '-ga:visits',
		);
		$_params = array_merge($defaults, $params);
		return $this->_query($_params);

	}


	protected function _query($params=array()){

		if (!$this->accessToken || !$this->accountId) {
			throw new Exception('You must provide the accessToken and an accountId');
		}
		$_params = array_merge($this->defaultQueryParams, array('access_token' => $this->accessToken, 'ids' => $this->accountId));
		$queryParams = array_merge($_params, $params);
		$data = Http::curl(self::API_URL, $queryParams);
		return json_decode($data, $this->assoc);

	}

}


/**
 * Abstract Auth class
 *
 */
abstract class GoogleOauth {

	const TOKEN_URL = 'https://accounts.google.com/o/oauth2/token';
	const SCOPE_URL = 'https://www.googleapis.com/auth/analytics.readonly';

	protected $assoc = true;
	protected $clientId = '';

	public function __set($key, $value) {
		$this->{$key} = $value;
	}

	public function setClientId($id) {
		$this->clientId = $id;
	}

	public function returnObjects($bool) {
		$this->assoc = !$bool;
	}

	/**
	 * To be implemented by the subclasses
	 *
	 */
	public function getAccessToken($data=null) {}

}


/**
 * Oauth 2.0 for service applications requiring a private key
 * openssl extension for PHP is required!
 * @extends GoogleOauth
 *
 */
class GoogleOauthService extends GoogleOauth {

	const MAX_LIFETIME_SECONDS = 3600;
	const GRANT_TYPE = 'urn:ietf:params:oauth:grant-type:jwt-bearer';

	protected $email = '';
	protected $privateKey = null;
	protected $password = 'notasecret';

	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $clientId (default: '') Client-ID of your project from the Google APIs console
	 * @param string $email (default: '') E-Mail address of your project from the Google APIs console
	 * @param mixed $privateKey (default: null) Path to your private key file (*.p12)
	 */
	public function __construct($clientId='', $email='', $privateKey=null) {
		if (!function_exists('openssl_sign')) throw new Exception('openssl extension for PHP is needed.');
		$this->clientId = $clientId;
		$this->email = $email;
		$this->privateKey = $privateKey;
	}


	public function setEmail($email) {
		$this->email = $email;
	}

	public function setPrivateKey($key) {
		$this->privateKey = $key;
	}


	/**
	 * Get the accessToken in exchange with the JWT
	 *
	 * @access public
	 * @param mixed $data (default: null) No data needed in this implementation
	 * @return array Array with keys: access_token, expires_in
	 */
	public function getAccessToken($data=null) {

		if (!$this->clientId || !$this->email || !$this->privateKey) {
			throw new Exception('You must provide the clientId, email and a path to your private Key');
		}

		$jwt = $this->generateSignedJWT();

		$params = array(
			'grant_type' => self::GRANT_TYPE,
			'assertion' => $jwt,
		);

		$auth = Http::curl(GoogleOauth::TOKEN_URL, $params, true);
		return json_decode($auth, $this->assoc);

	}


	/**
	 * Generate and sign a JWT request
	 * See: https://developers.google.com/accounts/docs/OAuth2ServiceAccount
	 *
	 * @access protected
	 */
	protected function generateSignedJWT() {

		// Check if a valid privateKey file is provided
		if (!file_exists($this->privateKey) || !is_file($this->privateKey)) {
			throw new Exception('Private key does not exist');
		}

		// Create header, claim and signature
		$header = array(
			'alg' => 'RS256',
			'typ' => 'JWT',
		);

		$t = time();
		$params = array(
			'iss' => $this->email,
			'scope' => GoogleOauth::SCOPE_URL,
			'aud' => GoogleOauth::TOKEN_URL,
			'exp' => $t + self::MAX_LIFETIME_SECONDS,
			'iat' => $t,
		);

		$encodings = array(
			base64_encode(json_encode($header)),
			base64_encode(json_encode($params)),
		);

		// Compute Signature
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

		// Generate JWT
		$encodings[] = base64_encode($sig);
		$jwt = implode('.', $encodings);
		return $jwt;

	}

}




/**
 * Oauth 2.0 for web applications
 * @extends GoogleOauth
 *
 */
class GoogleOauthWeb extends GoogleOauth {

	const AUTH_URL = 'https://accounts.google.com/o/oauth2/auth';
	const REVOKE_URL = 'https://accounts.google.com/o/oauth2/revoke';

	protected $clientSecret = '';
	protected $redirectUri = '';


	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $clientId (default: '') Client-ID of your web application from the Google APIs console
	 * @param string $clientSecret (default: '') Client-Secret of your web application from the Google APIs console
	 * @param string $redirectUri (default: '') Redirect URI to your app - must match with an URL provided in the Google APIs console
	 */
	public function __construct($clientId='', $clientSecret='', $redirectUri='') {
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		$this->redirectUri = $redirectUri;
	}

	public function setClientSecret($secret) {
		$this->clientSecret = $secret;
	}

	public function setRedirectUri($uri) {
		$this->redirectUri = $uri;
	}

	/**
	 * Build auth url
	 * The user has to login with his Google Account and give your app access to the Analytics API
	 *
	 * @access public
	 * @param array $params Custom parameters
	 * @return string The auth login-url
	 */
	public function buildAuthUrl($params = array()) {

		if (!$this->clientId || !$this->redirectUri) {
			throw new Exception('You must provide the clientId and a redirectUri');
		}

		$defaults = array(
			'response_type' => 'code',
			'client_id' => $this->clientId,
			'redirect_uri' => $this->redirectUri,
			'scope' => GoogleOauth::SCOPE_URL,
			'access_type' => 'offline',
			'approval_prompt' => 'force',
		);
		$params = array_merge($defaults, $params);
		$url = self::AUTH_URL . '?' . http_build_query($params);
		return $url;

	}


	/**
	 * Get the AccessToken in exchange with the code from the auth along with a refreshToken
	 *
	 * @access public
	 * @param mixed $data The code received with GET after auth
	 * @return array Array with the following keys: access_token, refresh_token, expires_in
	 */
	public function getAccessToken($data=null) {

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

		$auth = Http::curl(GoogleOauth::TOKEN_URL, $params, true);
		return json_decode($auth, $this->assoc);

	}


	/**
	 * Get a new accessToken with the refreshToken
	 *
	 * @access public
	 * @param mixed $refreshToken The refreshToken
	 * @return array Array with the following keys: access_token, expires_in
	 */
	public function refreshAccessToken($refreshToken) {

		if (!$this->clientId || !$this->clientSecret) {
			throw new Exception('You must provide the clientId and clientSecret');
		}

		$params = array(
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'refresh_token' => $refreshToken,
			'grant_type' => 'refresh_token',
		);

		$auth = Http::curl(GoogleOauth::TOKEN_URL, $params, true);
		return json_decode($auth, $this->assoc);

	}


	/**
	 * Revoke access
	 *
	 * @access public
	 * @param mixed $token accessToken or refreshToken
	 */
	public function revokeAccess($token) {

		$params = array('token' => $token);
		$data = Http::curl(self::REVOKE_URL, $params);
		return json_decode($data, $this->assoc);
	}


}



/**
 * Send data with curl
 *
 */
class Http {


	/**
	 * Send http requests with curl
	 *
	 * @access public
	 * @static
	 * @param mixed $url The url to send data
	 * @param array $params (default: array()) Array with key/value pairs to send
	 * @param bool $post (default: false) True when sending with POST
	 */
	public static function curl($url, $params=array(), $post=false) {

		if (empty($url)) return false;

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
		$http_code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
		// Add the status code to the json data, useful for error-checking
		$data = preg_replace('/^{/', '{"http_code":'.$http_code.',', $data);
		curl_close($curl);
		return $data;

	}

}

?>
