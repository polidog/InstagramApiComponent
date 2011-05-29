<?php
App::import('Core', 'HttpSocket');
class InstagramApiComponent extends Object
{
	
	/**
	 * 使用するコンポーネント
	 * @var array
	 * @access private
	 */
	var $components = array('Session');
	
	/**
	 * コントローラ
	 * @var AppController 
	 * @access private
	 */
	var $_controller;
	
	/**
	 * OAuth認証時のURL
	 * @var string
	 * @access public
	 */
	var $authorizeUrl = "http://api.instagram.com/oauth/authorize";
	
	/**
	 * API通信時に使用するURL
	 * @var string
	 * @access public
	 */
	var $apiBaseUrl	= "http://api.instagram.com/v1/";

	/**
	 * access token
	 * @var object 
	 */
	var $accessToken;
	
	/**
	 * HttpSocket instance
	 * @var HttpSocket
	 */
	var $httpSocket;
	
	
	/**
	 * オブジェクト起動時
	 * @param AppController $controller
	 * @param array $settings
	 */
	function initialize(&$controller,$settings=null ) {
		$this->_controller = &$controller;
		
		// パラメータをセットする
		if ( isset($controller->instagramApi) && is_array($controller->instagramApi)) {
			foreach( $controller->instagramApi as $key => $value ) {
				$this->$key = $value;
			}
		}
		
		$this->httpSocket = new HttpSockect();
		
	}
	
	/**
	 * beforeFilter後の動作
	 * @param AppController $controller
	 */
	function startup(&$controller) {
		
//		$oauthStartAction = false;
//		if ( isset($this->autoStartAction['oauthStart']) ) {
//			$oauthStartAction = $this->autoStartAction['oauthStart'];
//		}
//		
//		$oauthCallbackAction = false;
//		if ( isset($this->autoStartAction['oauthCallback']) ) {
//			$oauthCallbackAction = $this->autoStartAction['oauthCallback'];
//		}
//		
//		if ( !$oauthCallbackAction && !$oauthStartAction ) {
//			return true;
//		}
//		
//		$url = '';
//		
//		if ( isset($controller->params['url']['url']) ) {
//			$url = Router::normalize($controller->params['url']['url']);
//		}
//		
//		switch($url) {
//			case $oauthStartAction :
//				$this->oauthStart();
//				break;
//			case $oauthCallbackAction :
//				$this->oauthCallback();
//				break;
//		}
		
	}
	
	
	/**
	 * OAuth認証を開始する
	 */
	function oauthStart() {
		if ( is_null($this->oauthCallbackUrl) ) {
			$this->oauthCallbackUrl = "http://".env('SERVER_NAME').$this->autoStartAction['oauthCallback'];
		}
		$requestToken = $this->OauthConsumer->getRequestToken('Twitter', 'http://twitter.com/oauth/request_token', $this->oauthCallbackUrl); 
		$this->Session->write('twitter_request_token', $requestToken);
		$this->_redirect($this->_getAuthorizeUrl( $requestToken->key ),null,true );
	}
	
	/**
	 * OAuth認証のコールバック
	 */
	function oauthCallback() {
		

		
	}

	
	
}