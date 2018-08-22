<?php


define('CONST_oauth_access_token', '58240304-V48phveZE794t4lj3n5vFbN2NzwRrSQgJK8kBUSbE');
define('CONST_oauth_access_token_secret', 'mI3fPC2EgYzFO67ggOZZNOG2Ns0CHgWBYcefe5ujvd5SL');
define('CONST_consumer_key', '6vy7Ijc4LB13FVeAgNFP9Z0ZW');
define('CONST_consumer_secret', 'r4YC2xPSlhn9wfX4ih3d3j2irOnznh4F6qCIaAg3D2BcVJz1rn');



if($_SERVER["SERVER_NAME"]=="localhost")
{
	define('CONST_DBUSER', 'root');
	define('CONST_DBPASS', '');
	define('CONST_DBSERVER', 'localhost');
	define('CONST_DBNAME', 'tweeter');
}
else
{
	define('CONST_DBUSER', 'jake');
	define('CONST_DBPASS', 'pineapple12');
	define('CONST_DBSERVER', 'localhost');
	define('CONST_DBNAME', 'tweeter');
}
?>