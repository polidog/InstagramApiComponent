<?php
/**
 * InstagramAPI component
 * @author polidog <polidogs@gmail.com>
 * @version 0.0.1
 */
class InstagramApiComponent extends Object
{
	const REQUEST_METHOD_GET = 'get';
	const REQUEST_METHOD_POST = 'post';
	
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
	var $apiBaseUrl	= "https://api.instagram.com/v1";

	/**
	 * access tokenを保存するためn
	 * @var object 
	 */
	var $sessionBaseName = "instagram";
	
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
		$this->HttpSocket = new HttpSocket();
		
	}
	
	/**
	 * beforeFilter後の動作
	 * @param AppController $controller
	 */
	function startup(&$controller) {
		if ( !empty($this->oauthCallbackUrl) ) {
			$this->oauthCallbackUrl = Router::url($this->oauthCallbackUrl,true);
		}
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
			$scope = implode(' ', $this->scope);
		}
		
		$url = $this->authorizeUrl.DS."?".http_build_query(array(
			'client_id' => $this->clientId,
			'redirect_uri' => $this->oauthCallbackUrl,
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
		if ( isset($this->_controller->params['url']['code']) ) { 
			$code = $this->_controller->params['url']['code'];
		}
		$accessToken = $this->getAccessTokne($code);
		
		$this->saveAccessToken($accessToken['access_token']);
		return $accessToken;
	}
	
	/**
	 * AccessTokenを取得する
	 * @param string $code
	 * @return array
	 */
	function getAccessTokne($code = null) {
		if ( is_null($code) ) return false;
		$uri = $this->accessTokenUrl."?".http_build_query(array(
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'grant_type'	=> 'authorization_code',
			'redirect_uri' => $this->oauthCallbackUrl,
			'code' => $code,			
		));
		$accessToken = $this->HttpSocket->post($this->accessTokenUrl,array(
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'grant_type'	=> 'authorization_code',
			'redirect_uri' => $this->oauthCallbackUrl,
			'code' => $code,			
		));
		if ( $accessToken ) {
			$accessToken = json_decode($accessToken,true);
		}
		return $accessToken;
	}

	/**
	 * @param string $accessToken
	 */
	function saveAccessToken($accessToken) {
		$this->Session->write($this->sessionBaseName.'.accessToken',$accessToken);
	}
	
	/**
	 * 保存しているアクセストークンを取得する
	 * @return string
	 */
	function readAccessToken() {
		return $this->Session->read($this->sessionBaseName.'.accessToken');		
	}
	
	/**
	 * APIをコールする
	 * @param string $path
	 * @param array $data
	 * @param string $method
	 * @param boolean $assoc
	 */
	function callApi($path,$data = array(),$method=self::REQUEST_METHOD_GET,$decode = true,$assoc = true) {
		$accessToken = $this->readAccessToken();
		if ( !$accessToken ) {
			return false;
		}		
		$url = $this->apiBaseUrl.$path.'?access_token='.$accessToken;
		$result = $this->HttpSocket->$method($url,$data);
		if ( $decode ) {
			$result = json_decode($result,$assoc);
		}
		return $result;
	}
	
	
	function getUsers() {
		
	}
	
	/**
	 * __callメソッドの実装
	 * @param string $method
	 * @param array $args
	 */
	function __call( $method, $args ) {
		$pattern = "/^(api)([a-zA-Z1-9_]*)/i";
		if ( preg_match($pattern, $method, $matches) ) {
			if ( isset($matches[2]) ) {
				
				$apiPath = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '/\\1', $matches[2]));
				$apiArgs = array($apiPath);
				
				if ( is_array($args) ) {
					foreach($args as $arg ) {
						$apiArgs[] = $arg;
					}
				}
				return call_user_func_array( array( $this, 'callApi'), $apiArgs);
			}
		}		
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