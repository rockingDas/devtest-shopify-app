<?php
    include_once("../includes/shopify.php");

    $input = json_decode(file_get_contents("php://input"), true);
    $shopify = new Shopify();
    echo json_encode(['success' => true, 'data' => $input]);
    exit();
    $response = $shopify->updateProductWithDetails(
        $input,
        $input['shop_url'],
        $input['shop_token']
    );

    echo json_encode($response);