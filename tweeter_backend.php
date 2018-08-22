<?php
/////////////////////////////////////////////////////////
//backend for a twitter post searcher and reply frontend
//gus mueller, march 26 2016
/////////////////////////////////////////////////////////

require_once('tmhOAuth.php');
require_once('tweeter_constants.php');

$dbh = new PDO('mysql:host=' . CONST_DBSERVER . ';dbname=' . CONST_DBNAME , CONST_DBUSER, CONST_DBPASS);
$strSQL = "";
$twitterApiUrl = '1.1/search/tweets.json';


$count = 0;
$action = validGet("action", "tweetlist");
$query = validGet("q", "");
$count = validGet("count", 0);
 
	
if($action == "tweetlist"  && !empty($query))
{	
	$settings = array(
	    'user_token' => CONST_oauth_access_token,
	    'user_secret' => CONST_oauth_access_token_secret,
	    'consumer_key' => CONST_consumer_key,
	    'consumer_secret' => CONST_consumer_secret,
		'curl_ssl_verifypeer' => false //had to do that!
	);
	
	$outCount = 0;
	$requestMethod = 'GET';
 	$arrQuery = array(
		"q"=> htmlspecialchars($query), 
		"count"=>20, 
		"lang"=> "en"
		);
	
	$twitter = new tmhOAuth($settings);
	$http_code = $twitter->request($requestMethod, $twitter->url($twitterApiUrl, ''), $arrQuery);
 	if($http_code == 200)
	{
		  $tweetsJson = $twitter->response['response'];
	}
	
	$obj = json_decode($tweetsJson);
	
	//produce new json object of filtered tweets
	$objOut = new stdClass();
	$objOut->statuses = array();
	//populate the database
	foreach($obj->statuses as $status)
	{
		$twitterId = strval($status->id_str);
		
		$repliedToStatus = twitterIdState($twitterId);
		//echo $twitterId  . "*" . $repliedToStatus . "<BR>";
		//attempts to overwrite existing records will fail because of the twitter_id PK. 
		//if tweet is not in db, twitterIdState returns zero
		//if it is and has been replied to, twitterIdState returns 1
		//if it is and has not been replied to, twitterIdState returns 2  

		if($repliedToStatus == 0)
		{
			$strQuickSQL = "INSERT INTO tweet(twitter_id, created_at, text, user_name, replied_to) VALUES ('" . addslashes(htmlentities($twitterId)) . "','" . fixdate($status->created_at) .  "','". addslashes(htmlentities($status->text)) . "','" . addslashes(htmlentities($status->user->name)) . "',NULL)";
			$dbh->prepare($strQuickSQL);
			$dbh->query($strQuickSQL); 
			
		}
		if($repliedToStatus == 2 || $repliedToStatus == 0)
		{
			if($count==0 || $outCount<=$count)
			{
				$objOut->statuses[] = $status;
				$outCount++;
			}
		}
		if($outCount==0)
		{
			//we didn't produce any tweets from the API, so lets dredge some up from the database
			$records = array();
			$strQuickSQL = "SELECT * FROM tweet WHERE replied_to IS NULL ORDER BY twitter_id DESC LIMIT 0, 20";
			$arrOut = $dbh->query($strQuickSQL);
			
			foreach($arrOut as $record)
			{
				if($count==0 || $outCount<=$count)
				{
					$tweet = new stdClass();
					$user = new stdClass();
					$user->name = $record["user_name"];
					$tweet->created_at = $record["created_at"];
					$tweet->id_str = $record["twitter_id"];
					$tweet->text = $record["text"];
					$tweet->user = $user;
					$objOut->statuses[] = $tweet;
					$outCount++;
				}
			}
		
		}
	}
 	echo json_encode($objOut);

}
else if ($action == "suggestedtweets")
{
	$type = validGet("type", 1);
	$strSQL = "SELECT * FROM suggested_tweet WHERE type_id=" . intval($type);
}
else if ($action == "reply")
{
 	$twitterId = validGet("twitter_id", "");
	$reply = validGet("reply", "");
	$reply = trim($reply);
	//here you would put the API code to actually send the post to twitter if you were actually posting it
	if(!empty($twitterId)  && !empty($reply))
	{
		$strSQL = "UPDATE tweet SET reply='" . addslashes(htmlentities($reply)) . "', replied_to='" .  date("Y-m-d H:i:s") . "' WHERE twitter_id='" . addslashes(htmlentities($twitterId)) . "'";
	}

}


if($strSQL != "")
{
	try 
	{
		$records = array();
		$arrOut = $dbh->query($strSQL);
 
		if($arrOut)
		{
			foreach($arrOut as $record)
			{
				$records[] = $record;
			}
			//produce a JSON dump of whatever records are found
	 		echo json_encode($records);
		}
	    $dbh = null;
	} 
	catch (PDOException $e) 
	{
	    print "error: " . $e->getMessage() . "<br/>";
	    die();
	}
}
$dbh = null;


function validGet($name, $default)
//checks to see if the $_GET variable exists and returns it if so, otherwise it returns $default
{
	if(array_key_exists($name, $_GET))
	{
		return $_GET[$name];
	}
	else
	{
		return $default;
	}
}

function fixdate($in)
{
	//takes a date that might not be in the right format for mysql and fixes it so it is
	$in = strtotime($in);
	return date("Y-m-d H:i:s", $in);
} 

function twitterIdState($twitterId)
{
	//if tweet is not in db, return zero
	//if it is and has been replied to, return 1
	//if it is and has not been replied to, return 2
	GLOBAL $dbh;
	$count = 0;
	$strSQL = "SELECT replied_to FROM tweet WHERE twitter_id ='" . addslashes(htmlentities($twitterId)) . "'";
	foreach($dbh->query($strSQL) as $record)
	{
		if(is_null($record["replied_to"]))
		{
			return 2;
		}
		$count++;
	}
	if($count == 0)
	{
		return 0;
	}
	else if($count > 0)
	{
		return 1;
	}
	return false;
} 
?>