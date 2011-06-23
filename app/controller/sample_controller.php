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
	
	/**
	 * OAuth後にAPIコールする場合
	 */
	function callapitest() {
		//　ユーザー名を検索してみる
		// 詳しくは引数とかにかんしてはcallApiメソッドみればおk
		$result = $this->InstagramApi->callApi('/users/search',array('q' => 'polidog'));
	}
}