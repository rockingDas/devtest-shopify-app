<?php
include_once("../includes/shopify.php");

$shopify = new Shopify();

$query = <<<GRAPHQL
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

$response = $shopify->shopify_graphql_api(
    $_GET['shop_url'],
    $_GET['shop_token'],
    $query,
    []
);

$publications = [];
if (isset($response['data']['publications']['edges'])) {
    foreach ($response['data']['publications']['edges'] as $edge) {
        $publications[] = [
            'id' => $edge['node']['id'],
            'name' => $edge['node']['name']
        ];
    }
    echo json_encode(['success' => true, 'publications' => $publications]);
    exit();
}
echo json_encode(['success' => false, 'publications' => $publications]);
