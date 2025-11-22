<?php

$server = 'localhost';

$username = 'root';

$password = '';

$database = 'devtest_db';

$conn = mysqli_connect( $server, $username, $password, $database );

if(!$conn){

    die("Error: ". mysqli_connect_error());

}

// if (function_exists('header_remove')) {
//     header_remove('X-Frame-Options');
// }
// header("Content-Security-Policy: frame-ancestors https://admin.shopify.com https://*.myshopify.com;");
// // also ensure cookies:
// ini_set('session.cookie_secure', 1);
// ini_set('session.cookie_samesite', 'None');
// ini_set('session.cookie_httponly', 1);
// session_set_cookie_params([
//     'lifetime' => 0,
//     'path' => '/',
//     'domain' => $_SERVER['HTTP_HOST'],
//     'secure' => true,
//     'httponly' => true,
//     'samesite' => 'None'
// ]);
// session_start();


// setcookie(
//     "my_cookie",    // Name
//     "value",        // Value
//     [
//         "expires" => time() + 3600,   // 1 hour
//         "path" => "/",
//         "domain" => "sd-test.free.nf",
//         "secure" => true,            // HTTPS required
//         "httponly" => true,
//         "samesite" => "None"         // Important for iframe
//     ]
// );


// // make session cookie Shopify-friendly
// ini_set('session.cookie_secure', 1);     // must be HTTPS
// ini_set('session.cookie_httponly', 1);
// ini_set('session.cookie_samesite', 'None'); // important!

// session_set_cookie_params([
//   'lifetime' => 0,
//   'path' => '/',
//   'domain' => $_SERVER['HTTP_HOST'], // or your domain
//   'secure' => true,
//   'httponly' => true,
//   'samesite' => 'None'
// ]);

// // if PHP set XFO earlier, remove it
// if (function_exists('header_remove')) {
//   header_remove('X-Frame-Options');
// }
// // allow Shopify admin to embed
// header("Content-Security-Policy: frame-ancestors https://admin.shopify.com https://*.myshopify.com;");

// session_start();



