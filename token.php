<?php

// $currentURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");

// $currentURL .= "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

// // Print it

// echo $currentURL;

include_once( "includes/config.php" );
include_once( "includes/functions.php" );
include_once('includes/mysql_connect.php');

// print_r($_SESSION);

// print_r($_GET);

// die();

$parameters = $_GET;

$shop_url = $parameters['shop'];

$host = $parameters['host'];

$hmac = $parameters['hmac'];

// verify state
// elseif (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
//     die('Invalid state parameter. Possible CSRF attack.');
// }

// Validate request if hmac exists
if (isset($_GET['hmac']) && !verifyHmac($_GET, SECRECT_KEY)) {
    die("Invalid HMAC");
}
else{

    // ✅ valid → now clear it
    unset($_SESSION['oauth_state']);

    try {

        // echo "This is coming from shopify";

        $access_token_point = 'https://' . $shop_url . '/admin/oauth/access_token';

        $var = array(

            "client_id" => API_KEY,

            "client_secret" => SECRECT_KEY,

            "code" => $parameters['code']

        );

        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_URL, $access_token_point );

        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        curl_setopt( $ch, CURLOPT_POST, count($var) );

        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($var) );

        $response = curl_exec($ch);

        curl_close($ch);

        $response = json_decode($response, true);

        // print_r($response);

        $query = "INSERT INTO shops ( shop_url, host , access_token, hmac, install_date ) VALUES ( '".$shop_url."', '".$host."' ,'".$response['access_token']."', '".$hmac."' , NOW()) ON DUPLICATE KEY UPDATE access_token = '".$response['access_token']."', hmac = '".$hmac."', update_date = NOW() ";

        if($conn->query($query)){
            header("Location: index.php?shop={$_GET['shop']}&host={$_GET['host']}");
            exit();
        }

    } catch (Throwable $e) {

        // Log the error (file or monitoring). Do NOT leak sensitive info to end users in production.

        error_log('OAuth token exchange error: ' . $e->getMessage());

        // Friendly message for dev — in prod return a simple error page

        http_response_code(500);

        echo "Error exchanging token: " . htmlspecialchars($e->getMessage());

        exit;

    }

}

