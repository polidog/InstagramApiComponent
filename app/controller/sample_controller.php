<?php
class SampleController extends AppController
{
	
	var $components = array('InstagramApi');
	
	/**
	 * InstagramApiComponentの設定
	 * @var unknown_type
	 */
	var $instagramApi = array(
		'clientId' => 'xxxxxx',
		'clientSecret' => 'xxxxxxxxxxxxxxxxxxxx',
		'oauthCallbackUrl' => 'http://sample.com/sample/aouthCallback',
	);	
	
	/**
	 * OAuthをスタートさせる
	 */
	function oauthStart() {
		$this->InstagramApi->oauthStart();
	}
	
	/**
	 * callback
	 */
	function oauthCallback() {
		$result = $this->InstagramApi->oauthCallback();
		
		// accessTokenだけを取得する
		var_dump($this->InstagramApi->readAccessToken());
	}
}