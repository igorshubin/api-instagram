<?php
/**
 * Instagram PHP implementation API
 * URLs: http://www.mauriciocuenca.com/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'config.php';
require_once 'CurlHttpClient.php';

class Instagram {

    /**
     * The name of the GET param that holds the authentication code
     * @var string
     */
    const RESPONSE_CODE_PARAM = 'code';

    /**
     * Format for endpoint URL requests
     * @var string
     */
    protected $_endpointUrls = array(
        'authorize' => 'https://api.instagram.com/oauth/authorize/?client_id=%s&redirect_uri=%s&&scope=%s&response_type=%s',
        'access_token' => 'https://api.instagram.com/oauth/access_token',
        'user' => 'https://api.instagram.com/v1/users/%s/?access_token=%s',
        'user_feed' => 'https://api.instagram.com/v1/users/self/feed?%s',
        'user_recent' => 'https://api.instagram.com/v1/users/%s/media/recent/?access_token=%s&max_id=%s&min_id=%s&max_timestamp=%s&min_timestamp=%s',
        'user_search' => 'https://api.instagram.com/v1/users/search?q=%s&access_token=%s',
        'user_follows' => 'https://api.instagram.com/v1/users/%s/follows?access_token=%s&cursor=%s',
        'user_followed_by' => 'https://api.instagram.com/v1/users/%s/followed-by?access_token=%s',
        'user_requested_by' => 'https://api.instagram.com/v1/users/self/requested-by?access_token=%s',
        'user_relationship' => 'https://api.instagram.com/v1/users/%d/relationship?access_token=%s',
        'user_liked' => 'https://api.instagram.com/v1/users/self/media/liked?access_token=%s',
        'modify_user_relationship' => 'https://api.instagram.com/v1/users/%d/relationship?action=%s&access_token=%s',
        'media' => 'https://api.instagram.com/v1/media/%s?access_token=%s',
        'media_search' => 'https://api.instagram.com/v1/media/search?lat=%s&lng=%s&max_timestamp=%s&min_timestamp=%s&distance=%s&count=%d&access_token=%s',
        'media_popular' => 'https://api.instagram.com/v1/media/popular?access_token=%s',
        'media_comments' => 'https://api.instagram.com/v1/media/%d/comments?access_token=%s',
        'post_media_comment' => 'https://api.instagram.com/v1/media/%s/comments?access_token=%s',
        'delete_media_comment' => 'https://api.instagram.com/v1/media/%s/comments?comment_id=%d&access_token=%s',
        'likes' => 'https://api.instagram.com/v1/media/%d/likes?access_token=%s',
        'post_like' => 'https://api.instagram.com/v1/media/%s/likes',
        'remove_like' => 'https://api.instagram.com/v1/media/%s/likes?access_token=%s',
        'tags' => 'https://api.instagram.com/v1/tags/%s?access_token=%s',
        'tags_recent' => 'https://api.instagram.com/v1/tags/%s/media/recent?max_id=%s&min_id=%s&access_token=%s',
        'tags_search' => 'https://api.instagram.com/v1/tags/search?q=%s&access_token=%s',
        'locations' => 'https://api.instagram.com/v1/locations/%d?access_token=%s',
        'locations_recent' => 'https://api.instagram.com/v1/locations/%d/media/recent/?max_id=%s&min_id=%s&max_timestamp=%s&min_timestamp=%s&access_token=%s',
        'locations_search' => 'https://api.instagram.com/v1/locations/search?lat=%s&lng=%s&foursquare_id=%s&distance=%s&access_token=%s',
        'create_subscriptions' => 'https://api.instagram.com/v1/subscriptions',
        'manage_subscriptions' => 'https://api.instagram.com/v1/subscriptions?%s'
    );

    /**
    * Configuration parameter
    */
    protected $_config = array();

    /**
     * Whether all response are sent as JSON or decoded
     */
    protected $_arrayResponses = false;

    /**
     * OAuth token
     * @var string
     */
    protected $_oauthToken = null;

    /**
     * OAuth token
     * @var string
     */
    protected $_accessToken = null;
    
    /**
     * Access code
     * @var string
     */
    protected $_accessCode = null;

    /**
     * OAuth user object
     * @var object
     */
    protected $_currentUser = null;

    /**
     * Holds the HTTP client instance
     * @param Zend_Http_Client $httpClient
     */
    protected $_httpClient = null;

    /**
     * Constructor needs to receive the config as an array
     * @param mixed $config
     */
    public function __construct($config, $arrayResponses = false) {
        
        $this->_config = $config;
        $this->_arrayResponses = $arrayResponses;
        
        if (empty($config))
            throw new InstagramException('Configuration params are empty or not an array.');
        
        // load tokens from session
        $this->_oauthToken = $this->getOAuthToken();
        $this->_accessToken = $this->getAccessToken();
        
    }

    /**
     * Instantiates the internal HTTP client
     * @param string $uri
     * @param string $method
     */
    protected function _initHttpClient($uri, $method = CurlHttpClient::GET) {
        if ($this->_httpClient == null) {
            $this->_httpClient = new CurlHttpClient($uri);
        } else {
            $this->_httpClient->setUri($uri);
        }
        $this->_httpClient->setMethod($method);
    }

    /**
     * Returns the body of the HTTP client response
     * @return string
     */
    protected function _getHttpClientResponse() {
        return $this->_httpClient->getResponse();
    }

    /**
     * Retrieves the authorization code to be used in every request
     * @return string. The JSON encoded OAuth token
     */
    protected function _setOauthToken() {
        
        $this->_initHttpClient($this->_endpointUrls['access_token'], CurlHttpClient::POST);
        
        $this->_httpClient->setPostParam('client_id', $this->_config['client_id']);
        $this->_httpClient->setPostParam('client_secret', $this->_config['client_secret']);
        $this->_httpClient->setPostParam('grant_type', $this->_config['grant_type']);
        $this->_httpClient->setPostParam('redirect_uri', $this->_config['redirect_uri']);
        $this->_httpClient->setPostParam('code', $this->getAccessCode());

        $this->_oauthToken = $this->_getHttpClientResponse();

        // save tokens to sesion
        if (!isset(json_decode($this->_oauthToken)->error_type)) {
            $this->setSession('InstagramOAuthToken', $this->_oauthToken);
            $this->setAccessToken(json_decode($this->_oauthToken)->access_token);
        }
        
    }

    
    public function debug() {
        
        // trace origin method
        $trace = debug_backtrace();
        
        if (isset($trace[1])) {
           //var_dump($trace);
           $class = $trace[1]['class'];
           $func = $trace[1]['function'];
           $line = $trace[0]['line'];
           echo '<h3 style="padding: 10px;background-color: #e1e1e1;border-radius: 5px;border: 1px solid #ccc;">'.$class.' => '.$func.', line: '.$line.'</h3>';
       }
        
        
        echo '<div style="padding: 10px;border: 1px solid #ccc;border-radius: 5px;">';
        echo '<strong>SESSION:</strong><pre>';
        print_r($_SESSION);
        echo '</pre>';
        
        echo '<strong>GET:</strong><pre>';
        print_r($_GET);
        echo '</pre>';
        
        echo '<strong>access token:</strong>';
        var_dump($this->_accessToken);
        
        echo '<strong>oauth token:</strong>';
        var_dump($this->_oauthToken);
        echo '</div>';
        
        exit;
        
    }

    /**
     * Redirect to url
     * @param $url
     */
    public function redirect( $url ) {
        header('Location: '.$url);
        exit;
    }        
    
    /**
     * Check if any user is logged on
     * @return string
     */
    public function isLogged() {
        return (!$this->_accessToken || !$this->_oauthToken)? false : true;
    }    
    
    /**
     * Return access token from class or from session
     * @return string
     */
    public function getAccessToken() {
        return ($this->_accessToken)? $this->_accessToken : $this->getSession('InstagramAccessToken');
    }
    
    /**
     * Recover oauth token from class or from session
     * @return object
     */
    public function getOAuthToken() {
        return ($this->_oauthToken)? $this->_oauthToken : $this->getSession('InstagramOAuthToken');
    }
    
    
    ////////// SESION ACTIONS ///////////////
    /**
     * Save param to session
     */
    public function setSession($key,$value) {
        
        if(!isset($_SESSION)) @session_start();
        $_SESSION[ $key ] = $value;
        
    }
    
    /**
     * Get param from session
     */
    public function getSession($key) {
        return (isset($_SESSION) && isset($_SESSION[ $key ])) ? $_SESSION[ $key ] : NULL;
    }        
    
    ////////// SESION ACTIONS ///////////////

    /**
     * Return the decoded user object - from class or from session
     * from the OAuth JSON encoded token
     * @return object
     */
    public function getCurrentUser() {
        return ($this->_currentUser)? $this->_currentUser : json_decode($this->_oauthToken)->user;
    }

    /**
     * Gets the code param received during the authorization step
     */
    protected function getAccessCode() {
        return $_GET[self::RESPONSE_CODE_PARAM];
    }

    
    /**
     * Log IN current user
     * @return string $this->_accessToken
     */
    public function logIn() {
        
        if (!$this->isLogged())
            $this->_setOauthToken();
        
    }    
    
    /**
     * Log OUT current user
     */
    public function logOut( $url = '' ) {
        
        $this->setSession('InstagramOAuthToken', null);
        $this->setSession('InstagramAccessToken', null);
        $this->setSession('InstagramData', null);
        
        session_unset();
        session_destroy();
        
        if ($url) {
            header('Location: '.$url);
            exit;                
        }
        
    }    
    
    /**
     * Sets the access token response from OAuth
     * @param string $accessToken
     */
    public function setAccessToken($accessToken) {
        $this->_accessToken = $accessToken;
        // save access token to session
        $this->setSession('InstagramAccessToken', $accessToken);
    }

    /**
     * Surf to Instagram credentials verification page.
     * If the user is already authenticated, redirects to
     * the URI set in the redirect_uri config param.
     * @return string
     */
    public function openAuthorizationUrl() {
        header('Location: ' . $this->getAuthorizationUrl());
        exit(1);
    }

    /**
     * Generate Instagram credentials verification page URL.
     * Usefull for creating a link to the Instagram authentification page.
     * @return string
     */
    public function getAuthorizationUrl() {
        return sprintf($this->_endpointUrls['authorize'],
            $this->_config['client_id'],
            $this->_config['redirect_uri'],
            implode( '+', $this->_config['scope']),
            self::RESPONSE_CODE_PARAM);
    }

    /**
      * Setup subscription
      * @param $params array
      */
	public function createSubscription($params) {
            $this->_initHttpClient($this->_endpointUrls['create_subscriptions'], CurlHttpClient::POST);
            $this->_httpClient->setPostParam('client_id', $this->_config['client_id']);
            $this->_httpClient->setPostParam('client_secret', $this->_config['client_secret']);
            $this->_httpClient->setPostParam('verify_token', $this->_config['verify_token']);
            $this->_httpClient->setPostParam('callback_url', $params['callback_url']);
            $this->_httpClient->setPostParam('object', $params['object']);
                    $this->_httpClient->setPostParam('object_id', $params['object_id']);
            $this->_httpClient->setPostParam('aspect', $params['aspect']);
            return $this->_getHttpClientResponse();
	}
	
    /**
      * List Subscriptions
      * @param $id
      */
	public function listSubscriptions() {
            $getParams = array(
                'client_id' => $this->_config['client_id'],
                'client_secret' => $this->_config['client_secret']
            );
            $uri = sprintf($this->_endpointUrls['manage_subscriptions'], http_build_query($getParams));
            $this->_initHttpClient($uri, CurlHttpClient::GET);
            return $this->_getHttpClientResponse();
	}
	
      /**
      * Delete Subscription by id or type.
      *  id=1 || object=all|tag|user
      * @param $params array
      */
	public function deleteSubscription($params) {
            $getParams = array(
                    'client_id' => $this->_config['client_id'],
                    'client_secret' => $this->_config['client_secret']
            );
            if(isset($params['id'])) {
                    $getParams['id'] = $params['id'];
            } else {
                    $getParams['object'] = $params['object'];
            }		
            $uri = sprintf($this->_endpointUrls['manage_subscriptions'], http_build_query($getParams));
            $this->_initHttpClient($uri, CurlHttpClient::DELETE);
            return $this->_getHttpClientResponse();
	}

    /**
      * Get basic information about a user.
      * @param $id
      */
    public function getUser($id) {
        $endpointUrl = sprintf($this->_endpointUrls['user'], $id, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * See the authenticated user's feed.
     * @param integer $maxId. Return media after this maxId.
     * @param integer $minId. Return media before this minId.
     */
    public function getUserFeed($maxId = null, $minId = null, $count = null) {
        $endpointUrl = sprintf($this->_endpointUrls['user_feed'], http_build_query(array('access_token' => $this->getAccessToken(), 'max_id' => $maxId, 'min_id' => $minId, 'count' => $count)));
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }
    
    /**
     * See the authenticated user's feeds - ALL FEEDS
     * @param $uid user id (or take currently logged)
     * @param $sort by date or popularity (likes)
     * @return $array $data
     */
    public function getUserFeeds( $uid = '', $sort = 'date' ) {

        /////////////////////////////////////////////////
        // 1. GET USER INFO
        
        // 1.1 get user id
        if (!$uid) $uid = $this->getCurrentUser()->id;
           
        // 1.2 get user full info
        $user = json_decode( $this->getUser( $uid ) )->data;
        $count_total = $user->counts->media;

        /////////////////////////////////////////////////
        // 2. GET ALL USER MEDIA

        // 2.1 trying to fetch all media at once
        $data = json_decode($this->getUserFeed('','',$count_total), true);
        $count_data = count($data['data']);

        // check for errors
        if (isset($data['meta']['error_type']))
            throw new InstagramException('Cannot fetch all user media. Error: ['.$data['meta']['error_message'].']');
        
        // save first part
        $this->setSession('InstagramData', $data['data']);

         // 2.2 results are limited - fetch step by step
        if ($count_data != $count_total) {

            // get needed iteration count - minus first data request
            $count_parts = floor($count_total / $count_data);

            for ($i = 1; $i <= $count_parts; $i++) {

                // get each data - by next_max_id
                $next_max_id = ($i == 1)? $data['pagination']['next_max_id'] : $data2['pagination']['next_max_id'];
                $data2 = json_decode($this->getUserFeed($next_max_id,'',$count_data), true);
                
                // check for errors
                if (isset($data2['meta']['error_type']))
                    throw new InstagramException('Cannot fetch all user media. Error: ['.$data['meta']['error_message'].']');

                // save other parts
                $session = $this->getSession('InstagramData');
                $this->setSession('InstagramData', array_merge( $session, $data2['data'] ));

            }

        }
        
        $data = $this->getSession('InstagramData');
        
        /////////////////////////////////////////////////
        // 2. OPTIONAL SORTING (BY LIKES)

        if ($sort != 'date') {
            
            $ids = array();
            $temp = array();
            $sorted = array();
            
            foreach ($data as $media) {
                $temp [ $media['id'] ] = $media;
                $ids[ $media['id'] ] = $media['likes']['count'];
            }
            
            arsort($ids);
            
            foreach ($ids as $id => $sort) {
                $sorted[] = $temp[ $id ];
            }
            
            return $sorted;
            
        }

        return $data;
        
    }    
	
    /**
     * Get information about the current user's relationship (follow/following/etc) to another user.
     * @param n/a
     */
    public function getUserLiked() {
        $endpointUrl = sprintf($this->_endpointUrls['user_liked'], $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get the most recent media published by a user.
     * @param $id. User id
     * @param $maxId. Return media after this maxId
     * @param $minId. Return media before this minId
     * @param $maxTimestamp. Return media before this UNIX timestamp
     * @param $minTimestamp. Return media after this UNIX timestamp
     */
    public function getUserRecent($id, $maxId = '', $minId = '', $maxTimestamp = '', $minTimestamp = '', $count = null) {
        $endpointUrl = sprintf($this->_endpointUrls['user_recent'], $id, $this->getAccessToken(), $maxId, $minId, $maxTimestamp, $minTimestamp, $count);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Search for a user by name.
     * @param string $name. A query string
     */
    public function searchUser($name) {
        $endpointUrl = sprintf($this->_endpointUrls['user_search'], $name, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get the list of users this user follows.
     * @param $id. The user id. "self" is current user
     * @param integer $cursor. Cursor to paginate results
     */
    public function getUserFollows($id, $cursor = '') {
        $endpointUrl = sprintf($this->_endpointUrls['user_follows'], $id, $this->getAccessToken(), $cursor);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get the list of users this user is followed by.
     * @param $id. The user id. "self" is current user
     */
    public function getUserFollowedBy($id) {
        $endpointUrl = sprintf($this->_endpointUrls['user_followed_by'], $id, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * List the users who have requested this user's permission to follow
     */
    public function getUserRequestedBy() {
        $endpointUrl = sprintf($this->_endpointUrls['user_requested_by'], $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get information about the current user's relationship (follow/following/etc) to another user.
     * @param integer $id
     */
    public function getUserRelationship($id) {
        $endpointUrl = sprintf($this->_endpointUrls['user_relationship'], $id, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Modify the relationship between the current user and the target user
     * In order to perform this action the scope must be set to 'relationships'
     * @param integer $id
     * @param string $action. One of follow/unfollow/block/unblock/approve/deny
     */
    public function modifyUserRelationship($id, $action) {
        $endpointUrl = sprintf($this->_endpointUrls['modify_user_relationship'], $id, $action, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl, CurlHttpClient::POST);
        $this->_httpClient->setPostParam("action",$action);
        $this->_httpClient->setPostParam("access_token",$this->getAccessToken());
        return $this->_getHttpClientResponse();
    }

    /**
     * Get information about a media object.
     * @param integer $mediaId
     */
    public function getMedia($id) {
        $endpointUrl = sprintf($this->_endpointUrls['media'], $id, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Search for media in a given area.
     * @param float $lat
     * @param float $lng
     * @param integer $maxTimestamp
     * @param integer $minTimestamp
     * @param integer $distance
     */
    public function mediaSearch($lat, $lng, $maxTimestamp = '', $minTimestamp = '', $distance = '', $count = 40) {
        $endpointUrl = sprintf($this->_endpointUrls['media_search'], $lat, $lng, $maxTimestamp, $minTimestamp, $distance, $count, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get a list of what media is most popular at the moment.
     */
    public function getPopularMedia() {
        
        $endpointUrl = sprintf($this->_endpointUrls['media_popular'], $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get a full list of comments on a media.
     * @param integer $id
     */
    public function getMediaComments($id) {
        $endpointUrl = sprintf($this->_endpointUrls['media_comments'], $id, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Create a comment on a media.
     * @param integer $id
     * @param string $text
     */
    public function postMediaComment($id, $text) {
        $endpointUrl = sprintf($this->_endpointUrls['post_media_comment'], $id, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl, CurlHttpClient::POST);
        $this->_httpClient->setPostParam('text', $text);
        return $this->_getHttpClientResponse();
    }

    /**
     * Remove a comment either on the authenticated user's media or authored by the authenticated user.
     * @param integer $mediaId
     * @param integer $commentId
     */
    public function deleteComment($mediaId, $commentId) {
        $endpointUrl = sprintf($this->_endpointUrls['delete_media_comment'], $mediaId, $commentId, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl, CurlHttpClient::DELETE);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get a list of users who have liked this media.
     * @param integer $mediaId
     */
    public function getLikes($mediaId) {
        $endpointUrl = sprintf($this->_endpointUrls['likes'], $mediaId, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Set a like on this media by the currently authenticated user.
     * @param integer $mediaId
     */
    public function postLike($mediaId) {
        $endpointUrl = sprintf($this->_endpointUrls['post_like'], $mediaId);
        $this->_initHttpClient($endpointUrl, CurlHttpClient::POST);
        $this->_httpClient->setPostParam('access_token', $this->getAccessToken());
        return $this->_getHttpClientResponse();
    }

    /**
     * Remove a like on this media by the currently authenticated user.
     * @param integer $mediaId
     */
    public function removeLike($mediaId) {
        $endpointUrl = sprintf($this->_endpointUrls['remove_like'], $mediaId, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl, CurlHttpClient::DELETE);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get information about a tag object.
     * @param string $tagName
     */
    public function getTags($tagName) {
        $endpointUrl = sprintf($this->_endpointUrls['tags'], $tagName, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get a list of recently tagged media.
     * @param string $tagName
     * @param integer $maxId
     * @param integer $minId
     */
    public function getRecentTags($tagName, $maxId = '', $minId = '') {
        $endpointUrl = sprintf($this->_endpointUrls['tags_recent'], $tagName, $maxId, $minId, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Search for tags by name - results are ordered first as an exact match, then by popularity.
     * @param string $tagName
     */
    public function searchTags($tagName) {
        $endpointUrl = sprintf($this->_endpointUrls['tags_search'], urlencode($tagName), $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get information about a location.
     * @param integer $id
     */
    public function getLocation($id) {
        $endpointUrl = sprintf($this->_endpointUrls['locations'], $id, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get a list of recent media objects from a given location.
     * @param integer $locationId
     */
    public function getLocationRecentMedia($id, $maxId = '', $minId = '', $maxTimestamp = '', $minTimestamp = '') {
        $endpointUrl = sprintf($this->_endpointUrls['locations_recent'], $id, $maxId, $minId, $maxTimestamp, $minTimestamp, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Search for a location by name and geographic coordinate.
     * @see http://instagr.am/developer/endpoints/locations/#get_locations_search
     * @param float $lat
     * @param float $lng
     * @param integer $foursquareId
     * @param integer $distance
     */
    public function searchLocation($lat, $lng, $foursquareId = '', $distance = '') {
        $endpointUrl = sprintf($this->_endpointUrls['locations_search'], $lat, $lng, $foursquareId, $distance, $this->getAccessToken());
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }
}

class InstagramException extends Exception {
}
