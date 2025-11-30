<?php
    include_once("../includes/shopify.php");

    $input = json_decode(file_get_contents("php://input"), true);
    $shopify = new Shopify();
    // echo json_encode(['success' => true, 'data' => $input]);
    // exit();
    $response = $shopify->updateProductWithDetails(
        $input,
        $input['shop_url'],
        $input['shop_token']
    );

    echo json_encode($response);



    // {
    //     "product_id": "gid://shopify/Product/9496726995174",
    //     "is_edit": true,
    //     "title": "test",
    //     "description": "test",
    //     "vendor": "Suprakash Brand",
    //     "product_type": "Shoe Rack",
    //     "category": null,
    //     "tags": [
    //         "test"
    //     ],
    //     "images": [
    //         "https://cdn.shopify.com/s/files/1/0750/4343/8822/files/product_1764183303_54b54b5b9b92e3be4619248100f0a09e_88268bbb-7159-4d97-9082-2c33024d3df4.png?v=1764188610"
    //     ],
    //     "options": [
    //         {
    //             "name": "Color",
    //             "values": [
    //                 "red",
    //                 "green"
    //             ]
    //         }
    //     ],
    //     "variants": [
    //         {
    //             "id": "gid://shopify/ProductVariant/46916789272806",
    //             "title": "red",
    //             "price": "352.00",
    //             "sku": "test-RED",
    //             "inventory_quantity": 346,
    //             "image_url": [
    //                 "https://cdn.shopify.com/s/files/1/0750/4343/8822/files/product_1764183344_6d26e26f97bf104b7657cb0e441f5689_cb463801-eb88-4377-b842-2fba4dbd8f88.png?v=1764188613"
    //             ],
    //             "option1": "red",
    //             "option2": null,
    //             "option3": null
    //         },
    //         {
    //             "id": "gid://shopify/ProductVariant/46916790255846",
    //             "title": "green",
    //             "price": "352.00",
    //             "sku": "test-GREEN",
    //             "inventory_quantity": 346,
    //             "image_url": [
    //                 "https://cdn.shopify.com/s/files/1/0750/4343/8822/files/product_1764183349_81b4936ba5f92ca7d83716580bab4f7c_251610ca-9c93-482a-a683-2514d2b148d2.webp?v=1764188618"
    //             ],
    //             "option1": "green",
    //             "option2": null,
    //             "option3": null
    //         }
    //     ],
    //     "status": "ACTIVE",
    //     "publish_channels": [
    //         "gid://shopify/Publication/157466722534"
    //     ],
    //     "shop_url": "vastruwood.myshopify.com",
    //     "shop_token": "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"
    // }