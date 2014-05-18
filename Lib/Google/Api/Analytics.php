<?php

namespace Google\Api;

use Google\Api\GoogleOauthService;
use Google\Api\GoogleOauthWeb;

class Analytics
{
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

    public function __construct($auth = 'web')
    {

        if (!function_exists('curl_init')) {
            throw new Exception('The curl extension for PHP is required.');
        }
        $this->auth = ($auth == 'web') ? new OauthWeb() : new OauthService();
        $this->defaultQueryParams = array(
            'start-date' => date('Y-m-d', strtotime('-1 month')),
            'end-date' => date('Y-m-d'),
            'metrics' => 'ga:visits',
        );

    }

    public function __set($key, $value)
    {

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

    public function setAccessToken($token)
    {
        $this->accessToken = $token;
    }

    public function setAccountId($id)
    {
        $this->accountId = $id;
    }

    public function setDefaultQueryParams(array $params)
    {
        $params = array_merge($this->defaultQueryParams, $params);
        $this->defaultQueryParams = $params;
    }

    public function returnObjects($bool)
    {
        $this->assoc = !$bool;
        $this->auth->returnObjects($bool);
    }

    public function query($params = array())
    {
        return $this->_query($params);
    }

    public function getWebProperties()
    {

        if (!$this->accessToken) {
            throw new Exception('You must provide an accessToken');
        }

        $data = Http::curl(self::WEBPROPERTIES_URL, array('access_token' => $this->accessToken));
        return json_decode($data, $this->assoc);

    }

    public function getProfiles()
    {

        if (!$this->accessToken) {
            throw new Exception('You must provide an accessToken');
        }

        $data = Http::curl(self::PROFILES_URL, array('access_token' => $this->accessToken));
        return json_decode($data, $this->assoc);

    }

    public function getVisitsByDate($params = array())
    {

        $defaults = array(
            'metrics' => 'ga:visits',
            'dimensions' => 'ga:date',
        );
        $_params = array_merge($defaults, $params);
        return $this->_query($_params);

    }

    public function getAudienceStatistics($params = array())
    {

        $defaults = array(
            'metrics' => 'ga:visitors,ga:newVisits,ga:percentNewVisits,ga:visits,ga:bounces,ga:pageviews,ga:visitBounceRate,ga:timeOnSite,ga:avgTimeOnSite',
        );
        $_params = array_merge($defaults, $params);
        return $this->_query($_params);

    }

    public function getVisitsByCountries($params = array())
    {

        $defaults = array(
            'metrics' => 'ga:visits',
            'dimensions' => 'ga:country',
            'sort' => '-ga:visits',
        );
        $_params = array_merge($defaults, $params);
        return $this->_query($_params);

    }

    public function getVisitsByCities($params = array())
    {

        $defaults = array(
            'metrics' => 'ga:visits',
            'dimensions' => 'ga:city',
            'sort' => '-ga:visits',
        );
        $_params = array_merge($defaults, $params);
        return $this->_query($_params);

    }

    public function getVisitsByLanguages($params = array())
    {

        $defaults = array(
            'metrics' => 'ga:visits',
            'dimensions' => 'ga:language',
            'sort' => '-ga:visits',
        );
        $_params = array_merge($defaults, $params);
        return $this->_query($_params);

    }

    public function getVisitsBySystemBrowsers($params = array())
    {

        $defaults = array(
            'metrics' => 'ga:visits',
            'dimensions' => 'ga:browser',
            'sort' => '-ga:visits',
        );
        $_params = array_merge($defaults, $params);
        return $this->_query($_params);

    }

    public function getVisitsBySystemOs($params = array())
    {

        $defaults = array(
            'metrics' => 'ga:visits',
            'dimensions' => 'ga:operatingSystem',
            'sort' => '-ga:visits',
        );
        $_params = array_merge($defaults, $params);
        return $this->_query($_params);


    }

    public function getVisitsBySystemResolutions($params = array())
    {

        $defaults = array(
            'metrics' => 'ga:visits',
            'dimensions' => 'ga:screenResolution',
            'sort' => '-ga:visits',
        );
        $_params = array_merge($defaults, $params);
        return $this->_query($_params);

    }

    public function getVisitsByMobileOs($params = array())
    {

        $defaults = array(
            'metrics' => 'ga:visits',
            'dimensions' => 'ga:operatingSystem',
            'sort' => '-ga:visits',
            'segment' => 'gaid::-11',
        );
        $_params = array_merge($defaults, $params);
        return $this->_query($_params);

    }

    public function getVisitsByMobileResolutions($params = array())
    {

        $defaults = array(
            'metrics' => 'ga:visits',
            'dimensions' => 'ga:screenResolution',
            'sort' => '-ga:visits',
            'segment' => 'gaid::-11',
        );
        $_params = array_merge($defaults, $params);
        return $this->_query($_params);

    }

    public function getPageviewsByDate($params = array())
    {

        $defaults = array(
            'metrics' => 'ga:pageviews',
            'dimensions' => 'ga:date',
        );
        $_params = array_merge($defaults, $params);
        return $this->_query($_params);

    }

    public function getContentStatistics($params = array())
    {

        $defaults = array(
            'metrics' => 'ga:pageviews,ga:uniquePageviews',
        );
        $_params = array_merge($defaults, $params);
        return $this->_query($_params);

    }

    public function getContentTopPages($params = array())
    {

        $defaults = array(
            'metrics' => 'ga:pageviews',
            'dimensions' => 'ga:pagePath',
            'sort' => '-ga:pageviews',
        );
        $_params = array_merge($defaults, $params);
        return $this->_query($_params);

    }

    public function getTrafficSources($params = array())
    {

        $defaults = array(
            'metrics' => 'ga:visits',
            'dimensions' => 'ga:medium',
        );
        $_params = array_merge($defaults, $params);
        return $this->_query($_params);

    }

    public function getKeywords($params = array())
    {

        $defaults = array(
            'metrics' => 'ga:visits',
            'dimensions' => 'ga:keyword',
            'sort' => '-ga:visits',
        );
        $_params = array_merge($defaults, $params);
        return $this->_query($_params);

    }

    public function getReferralTraffic($params = array())
    {

        $defaults = array(
            'metrics' => 'ga:visits',
            'dimensions' => 'ga:source',
            'sort' => '-ga:visits',
        );
        $_params = array_merge($defaults, $params);
        return $this->_query($_params);

    }


    protected function _query($params = array())
    {

        if (!$this->accessToken || !$this->accountId) {
            throw new Exception('You must provide the accessToken and an accountId');
        }
        $_params = array_merge(
            $this->defaultQueryParams,
            array('access_token' => $this->accessToken, 'ids' => $this->accountId)
        );
        $queryParams = array_merge($_params, $params);
        $data = Http::curl(self::API_URL, $queryParams);
        return json_decode($data, $this->assoc);

    }
} 