<?php
ini_set('display_errors', 1);
require_once('TwitterAPIExchange.php');

/** Set cache file **/
$tweet_file = 'TweetCache.json';

/** Set cache time in minutes **/
$cache_time = 2;

/** Set access tokens here **/
$settings = array(
    'oauth_access_token' => "",
    'oauth_access_token_secret' => "",
    'consumer_key' => "",
    'consumer_secret' => ""
);

ReadLatestUpdate();
    		 
 function ReadLatestUpdate(){
	global $tweet_file;
    global $cache_time;
	
	if(!file_exists($tweet_file)){
		UpdateTimeline();
		return;
	}
	$handle = fopen($tweet_file,'r');
	$strUpdateDate = fgets($handle);
	fclose($handle);
	if(empty($strUpdateDate)){
		//file is empty
		UpdateTimeline();
	}
	else{
		$updateDate = new DateTime($strUpdateDate);
		$now = new DateTime("now");
		$since = $updateDate->diff($now);
		
		$minutes = $since->days * 24 * 60 + $since->h * 60 + $since->i;
		
		if($minutes > $cache_time){
			//reload feed
			UpdateTimeline();
		}
		else{
			//read cache
			ReadFromCache();
		}
		
	}
 }
 
 function ReadFromCache(){
	global $tweet_file;
	$handle = fopen($tweet_file,'r');
	$data = fgets($handle); //skip first line
	$data = '';
	while(!feof($handle)){
		$data.= fgets($handle);
	}
	fclose($handle);
	echo $data;
 }
 
 function UpdateCache($timeline){
	global $tweet_file;
	$handle = fopen($tweet_file,'w') or die ('Cannot open cache file');
	$data = date('m/d/Y h:i:s a', time())."\r\n".$timeline;
	fwrite($handle,$data);
	fclose($handle);
 }
 
 function UpdateTimeline(){
 global $settings;
 /** Perform a GET request and echo the response **/
$url = 'https://api.twitter.com/1.1/statuses/home_timeline.json';
$getfield = '?count=30';
$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
$timeline = $twitter->setGetfield($getfield)
			->buildOauth($url, $requestMethod)
             ->performRequest();
			 
			 //save to cache
			 UpdateCache($timeline);
			 
			 //echo results;
			 echo $timeline;
 }
