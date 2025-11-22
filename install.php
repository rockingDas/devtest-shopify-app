<?php

include_once( "includes/config.php" );

$shop           = $_GET['shop'];

$redirect_uri   = urlencode( APP_URL."token.php" );

$state          = bin2hex( random_bytes( 12 ) );

// save in session
$_SESSION['oauth_state'] = $state;

$access_mode    = 'per-user';

$outh_url = 'https://'.$shop.'/admin/oauth/authorize?client_id='.API_KEY.'&scope='.SCOPES.'&redirect_uri='.$redirect_uri.'&state='.$state.'&grant_options[]='.$access_mode;

header('Location:'. $outh_url);

exit();



// $currentURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");

// $currentURL .= "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

// // Print it

// echo $currentURL;





