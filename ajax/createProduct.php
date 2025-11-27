<?php

    include_once( "../includes/shopify.php" );

    // Read raw input and decode JSON
    $input = json_decode(file_get_contents("php://input"), true);

    // echo json_encode(['success' => true, 'data' => $input]);
    // exit();
    $shopify = new Shopify();

    $response = $shopify->createProductWithDetails($input, $input['shop_url'], $input['shop_token']);

    echo json_encode(['success' => true, 'data' => $response]);


    //Example of single product obj
    // {
    //     "title": "test3",
    //     "description": "test3",
    //     "vendor": "Suprakash Brand",
    //     "product_type": "Chair",
    //     "category": "gid://shopify/TaxonomyCategory/fr",
    //     "tags": [
    //         "test3"
    //     ],
    //     "images": [
    //         "https://lizard-correct-uniquely.ngrok-free.app/devtest//uploads/products-image/product_1764219132_8b93237edb0bdd11c94ad5b130f7db06.jpg"
    //     ],
    //     "options": [],
    //     "variants": [
    //         {
    //             "title": "test3",
    //             "price": "1234",
    //             "sku": "test3",
    //             "inventory_quantity": 55,
    //             "option1": "Default Title"
    //         }
    //     ],
    //     "status": "ACTIVE",
    //     "publish_channels": [
    //         "gid://shopify/Publication/157466722534",
    //         "gid://shopify/Publication/157466788070",
    //         "gid://shopify/Publication/167252656358"
    //     ],
    //     "shop_url": "vastruwood.myshopify.com",
    //     "shop_token": "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"
    // }


    //Example of Variant product obj
    // {
    //     "title": "test3",
    //     "description": "test3",
    //     "vendor": "Suprakash Brand",
    //     "product_type": "Chair",
    //     "category": "gid://shopify/TaxonomyCategory/fr",
    //     "tags": [
    //         "test3"
    //     ],
    //     "images": [
    //         "https://lizard-correct-uniquely.ngrok-free.app/devtest//uploads/products-image/product_1764219132_8b93237edb0bdd11c94ad5b130f7db06.jpg"
    //     ],
    //     "options": [
    //         {
    //             "name": "Color",
    //             "values": [
    //                 "Silver",
    //                 "White"
    //             ]
    //         }
    //     ],
    //     "variants": [
    //         {
    //             "title": "Silver",
    //             "price": "1234",
    //             "sku": "test3-SILVER",
    //             "inventory_quantity": 55,
    //             "image_url": [
    //                 "https://lizard-correct-uniquely.ngrok-free.app/devtest//uploads/products-image/product_1764219320_15e3584532771e3b7411af8174c73156.png"
    //             ],
    //             "option1": "Silver",
    //             "option2": null,
    //             "option3": null
    //         },
    //         {
    //             "title": "White",
    //             "price": "1234",
    //             "sku": "test3-WHITE",
    //             "inventory_quantity": 55,
    //             "image_url": [
    //                 "https://lizard-correct-uniquely.ngrok-free.app/devtest//uploads/products-image/product_1764219327_03b12cae2c0a7c9817aac8b71bdf00da.webp"
    //             ],
    //             "option1": "White",
    //             "option2": null,
    //             "option3": null
    //         }
    //     ],
    //     "status": "ACTIVE",
    //     "publish_channels": [
    //         "gid://shopify/Publication/157466722534",
    //         "gid://shopify/Publication/157466788070",
    //         "gid://shopify/Publication/167252656358"
    //     ],
    //     "shop_url": "vastruwood.myshopify.com",
    //     "shop_token": "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"
    // }