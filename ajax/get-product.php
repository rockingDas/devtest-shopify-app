<?php
include_once("../includes/shopify.php");

$input = json_decode(file_get_contents("php://input"), true);
$shopify = new Shopify();

$productGid = $input['product_id'];

$query = <<<GRAPHQL
query getProduct(\$id: ID!) {
  product(id: \$id) {
    id
    title
    descriptionHtml
    vendor
    productType
    tags
    status
    options {
      id
      name
      values
      position
    }
    images(first: 50) {
      edges {
        node {
          id
          url
        }
      }
    }
    variants(first: 100) {
      edges {
        node {
          id
          title
          price
          sku
          inventoryQuantity
          selectedOptions {
            name
            value
          }
          image {
            url
          }
        }
      }
    }
  }
}
GRAPHQL;

$variables = ["id" => $productGid];

$response = $shopify->shopify_graphql_api(
    $input['shop_url'],
    $input['shop_token'],
    $query,
    $variables
);

// echo json_encode(['success' => true, 'product' => $response]);
// exit;
// Transform data for frontend
$product = $response['data']['product'] ?? null;

if ($product) {
    // Collect variant image URLs
    $variantImageUrls = [];
    foreach ($product['variants']['edges'] as $edge) {
        if (isset($edge['node']['image']['url'])) {
            $variantImageUrls[] = $edge['node']['image']['url'];
        }
    }
    
    // Filter product images to exclude variant images
    $productImages = array_filter(
        array_map(function($edge) {
            return $edge['node']['url'];
        }, $product['images']['edges']),
        function($url) use ($variantImageUrls) {
            return !in_array($url, $variantImageUrls);
        }
    );
    
    $formattedData = [
        'id' => $product['id'],
        'title' => $product['title'],
        'description' => $product['descriptionHtml'],
        'vendor' => $product['vendor'],
        'product_type' => $product['productType'],
        'tags' => $product['tags'],
        'status' => $product['status'],
        'images' => array_values($productImages),
        'options' => array_map(function($option) {
            return [
                'name' => $option['name'],
                'values' => $option['values']
            ];
        }, $product['options']),
        'variants' => array_map(function($edge) {
            $variant = $edge['node'];
            $variantImages = [];
            if (isset($variant['image']['url'])) {
                $variantImages[] = $variant['image']['url'];
            }
            
            return [
                'id' => $variant['id'],
                'title' => $variant['title'],
                'price' => $variant['price'],
                'sku' => $variant['sku'],
                'inventory_quantity' => $variant['inventoryQuantity'],
                'image_url' => $variantImages,
                'option1' => $variant['selectedOptions'][0]['value'] ?? null,
                'option2' => $variant['selectedOptions'][1]['value'] ?? null,
                'option3' => $variant['selectedOptions'][2]['value'] ?? null
            ];
        }, $product['variants']['edges'])
    ];
    
    $isSimpleProduct = count($formattedData['variants']) === 1 && $formattedData['variants'][0]['option1'] === 'Default Title';
    
    $formattedData['is_simple'] = $isSimpleProduct;
    
    echo json_encode(['success' => true, 'product' => $formattedData]);
} else {
    echo json_encode(['success' => false, 'error' => 'Product not found']);
}