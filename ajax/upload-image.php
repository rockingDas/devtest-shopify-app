<?php

include_once("../includes/shopify.php");

$shopify = new Shopify();

// Handle FILE upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $localUrl = $shopify->downloadAndSaveImage($_FILES['image'], true);
    
    if ($localUrl) {
        echo json_encode(['success' => true, 'url' => $localUrl]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to upload image']);
    }
    exit;
}

// Handle URL download
// if (isset($_POST['image_url']) && !empty($_POST['image_url'])) {
//     $localUrl = $shopify->downloadAndSaveImage($_POST['image_url'], false);
    
//     if ($localUrl) {
//         echo json_encode(['success' => true, 'url' => $localUrl]);
//     } else {
//         echo json_encode(['success' => false, 'error' => 'Failed to download image']);
//     }
//     exit;
// }

echo json_encode(['success' => false, 'error' => 'No image provided']);