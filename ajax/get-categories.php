<?php
include_once("../includes/shopify.php");

$shopify = new Shopify();

$query = <<<GRAPHQL
query ShopName {
  shop {
    name
  }
  taxonomy {
    categories(first: 250) {
      edges {
        node {
          id
          fullName
        }
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
$categories = [];
if (isset($response['data']['taxonomy']['categories']['edges'])) {
    foreach ($response['data']['taxonomy']['categories']['edges'] as $edge) {
        $categories[] = [
            'id' => $edge['node']['id'],
            'name' => $edge['node']['fullName']
        ];
    }
}


$query2 = <<<GRAPHQL
query ShopName {
  productTypes(first: 100) {
    edges {
      node
    }
  }
}
GRAPHQL;
$response2 = $shopify->shopify_graphql_api(
    $_GET['shop_url'],
    $_GET['shop_token'],
    $query2,
    []
);
$types = [];
if (isset($response2['data']['productTypes']['edges'])) {
    foreach ($response2['data']['productTypes']['edges'] as $edge) {
        $types[] = [
            'id' => $edge['node'],
            'name' => $edge['node']
        ];
    }
}

// echo json_encode(['success' => true, 'categories' => $response, "shop_url" => $_GET['shop_url'] ,"shop_token" => $_GET['shop_token']  ]);
// exit();

echo json_encode(['success' => true, 'categories' => $categories, 'types' => $types]);