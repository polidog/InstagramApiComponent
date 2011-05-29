<?php
/**
 * InstagramAPI component
 * @author polidog <polidogs@gmail.com>
 * @version 0.0.1
 */
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
	 * AccessTokenを取得するためのURL
	 * @var string
	 */
	var $accessTokenUrl = "https://api.instagram.com/oauth/access_token";
	
	/**
	 * 認証時のコールバックURL
	 * @var string
	 * @access public
	 */
	var $oauthCallbackUrl;
	
	/**
	 * CLIENT ID
	 * @var string
	 * @access public
	 */
	var $clientId;
	
	/**
	 * CLIENT SECRET
	 * @var string
	 * @access public
	 */
	var $clientSecret;

	/**
	 * アプリケーションに対するAPIの許容範囲の設定
	 * @var array
	 */
	var $scope = array(
		'basic','relationships','comments','likes'
	);
	
	
	/**
	 * OAuth用のコールバックの指定
	 * @var array
	 * @access public
	 */
	var $autoStartAction = array(
		'oauthStart' =>'/instagram/index' ,
		'oauthCallback' => '/instagram/callback',
	);
	
	
	
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
	var $accessTokenSessionName = "instagram.accessToken";
	
	/**
	 * HttpSocket instance
	 * @var HttpSocket
	 */
	var $HttpSocket;

	/**
	 * リダイレクトを許可する、しないを選択する
	 * @var boolean
	 * @access public
	 */
	var $redirect	= true;		

	/**
	 * リダイレクト時のURL
	 * @var array
	 * @access public
	 */
	var $redirectUrl = array(
		'oauth_denied'		=> '/',
		'oauth_noauthorize' => '/',
		'oauth_authorize'	=> '/'
	);	
	
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
		App::import('Core', 'HttpSocket');
		$this->HttpSocket = new HttpSockect();
		
	}
	
	/**
	 * beforeFilter後の動作
	 * @param AppController $controller
	 */
	function startup(&$controller) {
	
	}
	
	
	/**
	 * OAuth認証を開始する
	 */
	function oauthStart() {
		if ( is_null($this->oauthCallbackUrl) ) {
			$this->oauthCallbackUrl = "http://".env('SERVER_NAME').$this->autoStartAction['oauthCallback'];
		}
		
		$scope = null;
		if ( is_array($this->scope) ) {
			$scope = implode('+', $this->scope);
		}
		
		$params = array(
		);
		
		$url = $this->authorizeUrl.DS."?".http_build_query(array(
			'client_id' => $this->clientId,
			'redirect_url' => $this->oauthCallbackUrl,
			'response_type' => 'code',
			'scope' => $scope,			
		));
		$this->_redirect($url,null,true);
		
	}
	
	/**
	 * OAuth認証のコールバック
	 */
	function oauthCallback() {
		$code = null;
		if ( !isset($this->_controller->params['named']['code']) ) { 
			$code = $this->_controller->params['named']['code'];
		}
		$accessToken = $this->getAccessTokne($code);
		$this->Session->write($this->accessTokenSessionName,$accessToken);
	}
	
	/**
	 * AccessTokenを取得する
	 * @param string $code
	 * @return array
	 */
	function getAccessTokne($code = null) {
		if ( is_null($code) ) return false;
		$accessToken = $this->HttpSocket->post($this->accessTokenUrl,array(
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'grant_type'	=> 'authorization_code',
			'redirect_url' => $this->oauthCallbackUrl,
			'code' => $code,			
		));
		if ( $accessToken ) {
			$accessToken = json_decode($accessToken,true);
		}
		return $accessToken;
	}

	/**
	 * リダイレクト処理を行う
	 * @param string $type	$this->redirectUrlのキーまたはURLを指定する
	 * @param string $flashMessage　リダイレクト先で表示したいメッセージ
	 * @param boolean $forceRedirect 強制リダイレクトフラグ
	 * @access private
	 */
	function _redirect($type,$flashMessage=null,$forceRedirect = false) {
		
		$redirectFlag = $this->redirect;
		if ( $redirectFlag === false && $forceRedirect === true ) {
			$redirectFlag = true;
		}
		
		if ( $redirectFlag ) {
			$url = $type;
			if ( isset($this->redirectUrl[$type]) ) {
				$url = $this->redirectUrl[$type];
				if ( is_null($url) ) {
					return null;
				}
			}
			
			if ( !is_null($flashMessage) ) {
				$this->Session->setFlash($flashMessage);
			}
			
			$this->_controller->redirect($url);
			
		}
		
		if ( $forceRedirect ) {
			
			if ( !is_null($flashMessage) ) {
				$this->Session->setFlash($flashMessage);
			}
			
			$this->_controller->redirect($type);
		}
	}	
	
}