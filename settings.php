<?php

    include_once( "includes/config.php" );
    include_once( "includes/functions.php" );
    include_once( "includes/mysql_connect.php" );
    include_once( "includes/shopify.php" );

    $shopify = new Shopify();
    $parameters = $_GET;

    include_once("includes/check_token.php");

    $products = $shopify->rest_api( "/admin/api/2025-07/products.json", ["limit" => 5, "since_id" => 2], 'GET' );
    echo "<pre>";
    print_r($products);
    echo "</pre>";
    if( array_key_exists( "errors", $products['body'] ) ){
        header("Location: install.php?shop=". $_GET['shop']);
        exit();
    }

    include_once("includes/header.php");
?>

<?php
    include_once("includes/footer.php");
?>