<?php
include_once("../includes/shopify.php");

$input = json_decode(file_get_contents("php://input"), true);
$shopify = new Shopify();

$productGid = $input['product_id'];

// Get all available publications first
$allPubsQuery = <<<GRAPHQL
query {
  publications(first: 20) {
    edges {
      node {
        id
        name
      }
    }
  }
}
GRAPHQL;

$allPubs = $shopify->shopify_graphql_api(
    $input['shop_url'],
    $input['shop_token'],
    $allPubsQuery,
    []
);

$publicationIds = [];

// Check each publication if product is published to it
foreach ($allPubs['data']['publications']['edges'] as $edge) {
    $pubId = $edge['node']['id'];
    
    $checkQuery = <<<GRAPHQL
    query checkPublication(\$productId: ID!, \$publicationId: ID!) {
      product(id: \$productId) {
        publishedOnPublication(publicationId: \$publicationId)
      }
    }
    GRAPHQL;
    
    $checkResult = $shopify->shopify_graphql_api(
        $input['shop_url'],
        $input['shop_token'],
        $checkQuery,
        [
            'productId' => $productGid,
            'publicationId' => $pubId
        ]
    );
    
    if ($checkResult['data']['product']['publishedOnPublication']) {
        $publicationIds[] = $pubId;
    }
}

echo json_encode([
    'success' => true,
    'publication_ids' => $publicationIds
]);