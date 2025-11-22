<?php

    include_once( "../includes/shopify.php" );

    // Read raw input and decode JSON
    $input = json_decode(file_get_contents("php://input"), true);

    // echo json_encode(['success' => true, 'data' => $input]);
    // exit();
    $shopify = new Shopify();

    $response = $shopify->createProductWithDetails($input, $input['shop_url'], $input['shop_token']);

    echo json_encode(['success' => true, 'data' => $response]);


    // {
    //     "title": "test",
    //     "description": "test",
    //     "vendor": "Suprakash Brand",
    //     "product_type": "Side Table",
    //     "category": "gid://shopify/TaxonomyCategory/hb",
    //     "tags": [
    //         "test"
    //     ],
    //     "images": [
    //         "https://lizard-correct-uniquely.ngrok-free.app/devtest//uploads/products-image/product_1763290344_11de713d5d3abb26988e352b3fc6639a.png",
    //         "https://lizard-correct-uniquely.ngrok-free.app/devtest//uploads/products-image/product_1763290351_8e46c9625d54d5fd0a88b8105f5aa912.jpg"
    //     ],
    //     "variants": [
    //         {
    //             "title": "red",
    //             "price": "34",
    //             "sku": "test-RED",
    //             "inventory_quantity": 56,
    //             "image_url": [
    //                 "https://lizard-correct-uniquely.ngrok-free.app/devtest//uploads/products-image/product_1763290423_9e9662ef988f5a5d46b7f1c1f835d466.png",
    //                 "https://lizard-correct-uniquely.ngrok-free.app/devtest//uploads/products-image/product_1763290443_94ce6b07f9150a151f53bd3fc3f4c66f.jpg"
    //             ],
    //             "option1": "red",
    //             "option2": null,
    //             "option3": null
    //         },
    //         {
    //             "title": "green",
    //             "price": "34",
    //             "sku": "test-GREEN",
    //             "inventory_quantity": 56,
    //             "image_url": [
    //                 "https://lizard-correct-uniquely.ngrok-free.app/devtest//uploads/products-image/product_1763290489_16f7bc18903fd2dc03153cd9f6651861.webp"
    //             ],
    //             "option1": "green",
    //             "option2": null,
    //             "option3": null
    //         }
    //     ],
    //     "status": "ACTIVE",
    //     "publish_channels": [],
    //     "shop_url": "vastruwood.myshopify.com",
    //     "shop_token": "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"
    // }