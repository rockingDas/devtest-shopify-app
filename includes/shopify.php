<?php

  include_once( "config.php" );

  class Shopify{

    public $shop_url;
    public $access_token;

    public function set_url($url){
        $this->shop_url = $url;
    }
    public function set_token($token){
        $this->access_token = $token;
    }
    public function get_url(){
        return $this->shop_url;
    }
    public function get_token(){
        return $this->access_token;
    }
    
    // public function rest_api( $api_endpoint, $query = array(), $method = 'GET' ){
    //     $url = 'https://'. $this->shop_url . $api_endpoint;

    //     if( in_array( $method, array( 'GET', 'DELETE' ) ) && !is_null( $query ) ){
    //         $url = $url . '?' . http_build_query( $query );
    //     }

    //     $curl = curl_init($url);
    //     curl_setopt( $curl, CURLOPT_HEADER, true );
    //     curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    //     curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
    //     curl_setopt( $curl, CURLOPT_MAXREDIRS, 3 );
    //     curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
    //     curl_setopt( $curl, CURLOPT_TIMEOUT, 30 );
    //     curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 30 );
    //     curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $method );

    //     $headers = [];
    //     if( !is_null( $this->access_token ) ){
    //         $headers[] = "X-Shopify-Access-Token: ". $this->access_token;
    //         curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
    //     }

    //     if( $method != "GET" && in_array( $method, array( 'POST', 'PUT' ) ) ){
    //         if( is_array( $query ) )  $query = http_build_query( $query );
    //         curl_setopt( $curl, CURLOPT_POSTFIELDS, $query );
    //     }

    //     $response = curl_exec( $curl );
    //     $error = curl_errno($curl);
    //     $error_msg = curl_error( $curl );
    //     curl_close($curl);

    //     if( $error ){
    //         return $error_msg;
    //     }else{
    //         $response = preg_split( "/\r\n\r\n|\n\n|\r\r/", $response, 2 );
    //         return $response;
    //     }
    // }

    public function rest_api($api_endpoint, $query = [], $method = 'GET', $shop_url = "", $access_token = "") {

      $this->shop_url = ($this->shop_url == "") ? $shop_url : $this->shop_url;
      $this->access_token = ($this->access_token == "") ? $access_token : $this->access_token;
      
        $url = 'https://' . $this->shop_url . $api_endpoint;
    
        if (in_array($method, ['GET', 'DELETE']) && !empty($query)) {
            $url .= '?' . http_build_query($query);
        }
    
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    
        $headers = [];
        if (!empty($this->access_token)) {
            $headers[] = "X-Shopify-Access-Token: " . $this->access_token;
        }
    
        if (in_array($method, ['POST', 'PUT'])) {
            $query = json_encode($query);
            $headers[] = "Content-Type: application/json";
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }
    
        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
    
        $response = curl_exec($curl);
        $error = curl_errno($curl);
        $error_msg = curl_error($curl);
        curl_close($curl);
    
        if ($error) {
            return $error_msg;
        } else {
            list($respHeaders, $body) = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);

            // return array( 'headers' => $respHeaders, 'body' => json_decode($body, true) );

            $head = array();
            $head_content = explode( "\n", $respHeaders );
            $head['status'] = $head_content[0];

            array_shift($head_content);

            $head = [];

            foreach ($head_content as $content) {
                if (strpos($content, ':') !== false) {
                    [$name, $value] = explode(':', $content, 2);
                    $name  = trim($name);
                    $value = trim($value);
            
                    // If value looks like JSON, decode it
                    if ($thisValue = json_decode($value, true)) {
                        $head[$name] = $thisValue;
                    } else {
                        $head[$name] = $value;
                    }
                }
            }
            

            return array( 'headers' => $head, 'body' => json_decode($body, true) );
        }
    }
    
    public function shopify_graphql_api($shop, $access_token, $query, $variables = [])
    {
        $url = "https://{$shop}/admin/api/".API_VERSION."/graphql.json";

        $payload = [
            "query"     => $query,
            "variables" => (object)$variables
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "X-Shopify-Access-Token: {$access_token}"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }

        curl_close($ch);

        return json_decode($response, true); // returns decoded array
    }


    public function fetchProductsGraphQL($cursor = null, $direction = 'after', $limit = 5, $title = '', $status = '') {
        $cursorArg = $cursor ? $direction.': "'.$cursor.'",' : '';

        $gs_selector = $direction == 'before' ? "last" : "first";
        
        // Build query filter string
        $filters = [];
        if (!empty($title)) {
            $filters[] = 'title:*'.addslashes($title).'*';
        }
        if (!empty($status)) {
            $filters[] = 'status:'.$status;
        }
        $queryFilter = !empty($filters) ? 'query: "'.implode(' ', $filters).'",' : '';
    
        $query = <<<GQL
        {
          products($gs_selector: $limit, $cursorArg $queryFilter) {
            edges {
              cursor
              node {
                id
                title
                status
                createdAt
                images(first: 1) {
                  edges {
                    node {
                      id
                      url
                      altText
                    }
                  }
                }
              }
            }
            pageInfo {
              hasNextPage
              hasPreviousPage
              startCursor
              endCursor
            }
          }
        }
        GQL;
    
        return $this->shopify_graphql_api($this->shop_url, $this->access_token, $query);
    }


    public function createSimpleProduct($title, $description, $price, $sku, $shop_url, $access_token) {
      $query = <<<GRAPHQL
      mutation productCreate(\$input: ProductInput!) {
        productCreate(input: \$input) {
          product {
            id
            title
            handle
            status
            variants(first: 1) {
              edges {
                node {
                  id
                  title
                  price
                  sku
                }
              }
            }
          }
          userErrors {
            field
            message
          }
        }
      }
      GRAPHQL;
  
      $variables = [
          "input" => [
              "title" => $title,
              "descriptionHtml" => $description,
              "vendor" => "Suprakash Brand",
              "productType" => "General",
              "status" => "ACTIVE"
          ]
      ];
  
      $response = $this->shopify_graphql_api(
          $shop_url,
          $access_token,
          $query,
          $variables
      );
  
      // Check for errors
      if (isset($response['data']['errors']) || 
          (isset($response['data']['productCreate']['userErrors']) && 
            !empty($response['data']['productCreate']['userErrors']))) {
          error_log("Product creation errors: " . json_encode($response));
          return $response;
      }
  
      // Get the default variant ID
      // $variantId = $response['data']['productCreate']['product']['variants']['edges'][0]['node']['id'];
      $product_gid = $response['data']['productCreate']['product']['id'] ?? null;
      $variant_gid = $response['data']['productCreate']['product']['variants']['edges'][0]['node']['id'] ?? null;

      $product_id = null;
      if ($product_gid) {
          // Extract the numeric part only
          $product_id = preg_replace('/\D/', '', $product_gid);
          echo "Variant ID: " . $product_id;
      }
      $variant_id = null;
      if ($variant_gid) {
          // Extract the numeric part only
          $variant_id = preg_replace('/\D/', '', $variant_gid);
          echo "Variant ID: " . $variant_id;
      }
      if ($variant_id) {
        // Update the default variant with price and SKU
        $updateResponse = $this->updateVariantREST($variant_id, $price, $sku, $shop_url, $access_token);
        
        // Log the update response to debug
        error_log("Variant update response: " . json_encode($updateResponse));
      }
  
      return array( 'response1' => $response, 'response2' => $updateResponse );
    }



    // private function updateVariantGraphQL($productId, $variantId, $price, $sku, $shop_url, $access_token) {
    //   $query = <<<GRAPHQL
    //   mutation productVariantsBulkUpdate(\$productId: ID!, \$variants: [ProductVariantsBulkInput!]!) {
    //     productVariantsBulkUpdate(productId: \$productId, variants: \$variants) {
    //       product {
    //         id
    //       }
    //       productVariants {
    //         id
    //         price
    //         sku
    //       }
    //       userErrors {
    //         field
    //         message
    //       }
    //     }
    //   }
    //   GRAPHQL;

    //   $variables = [
    //       "productId" => $productId,
    //       "variants" => [
    //           [
    //               "id" => $variantId,
    //               "price" => (string) $price
    //           ]
    //       ]
    //   ];

    //   $response = $this->shopify_graphql_api(
    //       $shop_url,
    //       $access_token,
    //       $query,
    //       $variables
    //   );
      
    //   // Now update SKU separately using inventoryItem
    //   // if (isset($response['data']['productVariantsBulkUpdate']['productVariants'][0]['id'])) {
    //   //   $updateResponse = $this->updateVariantSKU($variantId, $sku, $shop_url, $access_token);
    //   // }not working now but without this price can updated
      
    //   return array( 'response3' => $response, 'response4' => $updateResponse );
    // }

    // private function updateVariantSKU($variantId, $sku, $shop_url, $access_token) {
    //   // First, get the inventoryItem ID from the variant
    //   $query = <<<GRAPHQL
    //   query getInventoryItem(\$id: ID!) {
    //     productVariant(id: \$id) {
    //       id
    //       inventoryItem {
    //         id
    //       }
    //     }
    //   }
    //   GRAPHQL;

    //   $variables = ["id" => $variantId];
      
    //   $response = $this->shopify_graphql_api($shop_url, $access_token, $query, $variables);
      
    //   $inventoryItemId = $response['data']['productVariant']['inventoryItem']['id'] ?? null;
      
    //   if (!$inventoryItemId) {
    //       return null;
    //   }

    //   // Update the SKU via inventoryItem
    //   $updateQuery = <<<GRAPHQL
    //   mutation inventoryItemUpdate(\$id: ID!, \$input: InventoryItemUpdateInput!) {
    //     inventoryItemUpdate(id: \$id, input: \$input) {
    //       inventoryItem {
    //         id
    //         sku
    //       }
    //       userErrors {
    //         field
    //         message
    //       }
    //     }
    //   }
    //   GRAPHQL;

    //   $updateVariables = [
    //       "id" => $inventoryItemId,
    //       "input" => [
    //           "sku" => (string) $sku
    //       ]
    //   ];

    //   return $this->shopify_graphql_api($shop_url, $access_token, $updateQuery, $updateVariables);
    // }



    // $updateResponse = $this->updateVariantREST($variant_id, $price, $sku, $shop_url, $access_token);
    private function updateVariantREST($variantId, $variantData, $shop_url, $access_token) {
    // Step 1: Update variant (price, sku, enable inventory management)
      $data = [
        "variant" => [
            "id" => (int)$variantId,
            "price" => (string)$variantData['price'],
            "sku" => (string)$variantData['sku'],
            "inventory_quantity" => (int)$variantData['inventory_quantity'],
            "inventory_management" => "shopify"
        ]
      ];
      $variantResponse = $this->rest_api( "/admin/api/".API_VERSION."/variants/{$variantId}.json", $data, 'PUT' , $shop_url, $access_token);



      // Step 2: Update inventory quantity separately
      $inventoryItemId = $variantResponse['body']['variant']['inventory_item_id'];

      if (isset($variantData['inventory_quantity']) && isset($inventoryItemId)) {
        // Get location ID first
        $locations = $this->rest_api(
            "/admin/api/".API_VERSION."/locations.json",
            null,
            'GET',
            $shop_url,
            $access_token
        );
        
        if (!empty($locations['body'])) {
            $locationId = $locations['body']['locations'][0]['id'];
            
            // Set inventory level
            $inventoryData = [
                "location_id" => $locationId,
                "inventory_item_id" => $inventoryItemId,
                "available" => (int)$variantData['inventory_quantity']
            ];
            
            $inventory_levels = $this->rest_api(
                "/admin/api/".API_VERSION."/inventory_levels/set.json",
                $inventoryData,
                'POST',
                $shop_url,
                $access_token
            );
        }
      }
      return [
        'success' => true,
        'variantResponse' => $variantResponse,
        'locations' => $locations,
        'inventoryData' => $inventoryData,
        'inventory_levels' => $inventory_levels
      ];
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////


    public function createProductWithDetails($productData, $shop_url, $access_token) {

      $media = [];
      if (!empty($productData['images'])) {
          foreach ($productData['images'] as $index => $imageUrl) {
              $media[] = [
                  "originalSource" => $imageUrl,
                  "alt" => $productData['title'] . " - Image " . ($index + 1),
                  "mediaContentType" => "IMAGE"
              ];
          }
      }
  
      // Check if it's a simple product
      $isSimpleProduct = empty($productData['options']) || (count($productData['variants']) === 1 && $productData['variants'][0]['option1'] === 'Default Title');
  
      // Prepare options only for variant products
      $productOptions = [];
      if (!$isSimpleProduct && isset($productData['options']) && count($productData['options']) > 0) {
          foreach ($productData['options'] as $index => $option) {
              $productOptions[] = [
                  "name" => $option['name'],
                  "position" => $index + 1,
                  "values" => array_map(function($value) {
                      return ["name" => $value];
                  }, $option['values'])
              ];
          }
      }
  
      // Create product
      $query = <<<GRAPHQL
      mutation productCreate(\$input: ProductInput!, \$media: [CreateMediaInput!]) {
        productCreate(input: \$input, media: \$media) {
          product {
            id
            title
            handle
            status
            variants(first: 100) {
              edges {
                node {
                  id
                  title
                }
              }
            }
          }
          userErrors {
            field
            message
          }
        }
      }
      GRAPHQL;
      
      $variables = [
          "input" => [
              "title" => $productData['title'],
              "descriptionHtml" => $productData['description'],
              "vendor" => $productData['vendor'] ?? "Suprakash Brand",
              "productType" => $productData['product_type'] ?? "General",
              "tags" => $productData['tags'] ?? [],
              "status" => $productData['status'] ?? "ACTIVE",
              "productOptions" => $productOptions
          ],
          "media" => $media
      ];
      
      $response = $this->shopify_graphql_api($shop_url, $access_token, $query, $variables);
  
      $results = [
          'product' => $response,
          'variants_updated' => [],
          'variants_created' => [],
          'variants_deleted' => []
      ];
  
      $product_gid = $response['data']['productCreate']['product']['id'] ?? null;
      if ($product_gid) {
          $productId = preg_replace('/\D/', '', $product_gid);
          $createdVariants = $response['data']['productCreate']['product']['variants']['edges'] ?? [];
          
          if ($isSimpleProduct) {
              // SIMPLE PRODUCT: Update the default variant
              $variantId = preg_replace('/\D/', '', $createdVariants[0]['node']['id'] ?? '');
              if ($variantId) {
                  $results['variants_updated'][] = $this->updateVariantREST($variantId, $productData['variants'][0], $shop_url, $access_token);
              }
          } else {
              // VARIANT PRODUCT: Delete all auto-created variants, then create yours
              $results['variants_deleted'][] = $this->deleteAllVariants($productId, $shop_url, $access_token);
              
              // Wait a moment for deletion to complete
              sleep(1);
              
              // Create all custom variants
              foreach ($productData['variants'] as $variantData) {
                  $createResponse = $this->createVariantREST($productId, $variantData, $shop_url, $access_token);
                  $results['variants_created'][] = $createResponse;
                  
                  // If variant created successfully, update inventory
                  if (isset($createResponse['body']['variant']['id'])) {
                    $results['variants_updated'][] = $this->updateVariantInventory($createResponse['body']['variant']['inventory_item_id'], $variantData['inventory_quantity'], $shop_url, $access_token);
                  }
              }
          }
      }
  
      // Publish to channels
      if (!empty($productData['publish_channels'])) {
          $this->publishToChannels($product_gid, $productData['publish_channels'], $shop_url, $access_token);
      }
      
      return ['success' => true] + $results;
  }


  private function deleteAllVariants($productId, $shop_url, $access_token) {
    $response = [];
    // Get all variants
    $variants = $this->rest_api(
        "/admin/api/" . API_VERSION . "/products/{$productId}/variants.json",
        null,
        'GET',
        $shop_url,
        $access_token
    );
    $response['variants'] = $variants;
    
    if (!empty($variants['body']['variants'])) {
      // Delete each variant
      foreach ($variants['body']['variants'] as $variant) {
        $delete = $this->rest_api(
            "/admin/api/" . API_VERSION . "/variants/{$variant['id']}.json",
            null,
            'DELETE',
            $shop_url,
            $access_token
        );
        $response['delete'][] = $delete;
      }
    }
    return ['success' => true] + $response;
  }

  private function updateVariantInventory($inventoryItemId, $quantity, $shop_url, $access_token) {
    // Get location first
    $locations = $this->rest_api(
        "/admin/api/" . API_VERSION . "/locations.json",
        null,
        'GET',
        $shop_url,
        $access_token
    );
    
    if (!empty($locations['body']['locations'])) {
      $locationId = $locations['body']['locations'][0]['id'];
      
      // Set inventory level
      $inventoryData = [
          "location_id" => $locationId,
          "inventory_item_id" => $inventoryItemId,
          "available" => (int)$quantity
      ];
      
      $inventory_levels = $this->rest_api(
          "/admin/api/" . API_VERSION . "/inventory_levels/set.json",
          $inventoryData,
          'POST',
          $shop_url,
          $access_token
      );
    }

    return [
      'success' => true,
      'locations' => $locations,
      'inventoryData' => $inventoryData,
      'inventory_levels' => $inventory_levels
    ];
  }


  private function updateProductVariants($product_gid, $createdVariants, $variantsData, $shop_url, $access_token) {
    $productId = preg_replace('/\D/', '', $product_gid);
    $response = [];
    $response2 = [];
    foreach ($variantsData as $variantData) {
        // Find matching created variant by options
        $matchedVariant = $this->findMatchingVariant($createdVariants, $variantData);
        
        if ($matchedVariant) {
            $variantId = preg_replace('/\D/', '', $matchedVariant['node']['id']);
            
            // Update price, SKU, inventory
            $response[] = $this->updateVariantREST($variantId, $variantData, $shop_url, $access_token);
            
            // Upload variant-specific images
            if (!empty($variantData['image_url'])) {
              $response2[] = $this->uploadVariantImages($productId, $variantId, $variantData['image_url'], $shop_url, $access_token);
            }
        }
    }
    return ['success' => true, 'response' => $response, 'response2' => $response2];
  }
  
    private function findMatchingVariant($createdVariants, $variantData) {
        foreach ($createdVariants as $variant) {
            $options = $variant['node']['selectedOptions'];
            $match = true;
            
            // Check if all options match
            if (isset($variantData['option1']) && $options[0]['value'] !== $variantData['option1']) $match = false;
            if (isset($variantData['option2']) && isset($options[1]) && $options[1]['value'] !== $variantData['option2']) $match = false;
            if (isset($variantData['option3']) && isset($options[2]) && $options[2]['value'] !== $variantData['option3']) $match = false;
            
            if ($match) return $variant;
        }
        return null;
    }
    
    private function uploadVariantImages($productId, $variantId, $imageUrls, $shop_url, $access_token) {
        foreach ($imageUrls as $imageUrl) {
            $data = [
                "image" => [
                    "src" => $imageUrl,
                    "variant_ids" => [(int)$variantId]
                ]
            ];
            
            $this->rest_api(
                "/admin/api/" . API_VERSION . "/products/{$productId}/images.json",
                $data,
                'POST',
                $shop_url,
                $access_token
            );
        }
    }
    
    private function handleVariants($product_gid, $existingVariants, $newVariants, $shop_url, $access_token) {
        $responses = [];
        
        // Update default variant if only one variant provided
        if (count($newVariants) === 1 && !empty($existingVariants)) {
            $variant_gid = $existingVariants[0]['node']['id'];
            $variant_id = preg_replace('/\D/', '', $variant_gid);
            
            $variantData = $newVariants[0];
            $response = $this->updateVariantREST(
                $variant_id,
                $variantData,
                $shop_url,
                $access_token
            );
            
            $responses[] = $response;
        } 
        // Create multiple variants
        // else if (count($newVariants) > 1) {
        //     foreach ($newVariants as $variantData) {
        //         $response = $this->createVariantREST(
        //             preg_replace('/\D/', '', $product_gid),
        //             $variantData,
        //             $shop_url,
        //             $access_token
        //         );
        //         $responses[] = $response;
        //     }
        // }
        
        return $responses;
    }
    
    private function createVariantREST($productId, $variantData, $shop_url, $access_token) {
      $data = [
          "variant" => [
              "product_id" => (int)$productId,
              "price" => (string)$variantData['price'],
              "sku" => (string)$variantData['sku'],
              "inventory_quantity" => (int)$variantData['inventory_quantity'],
              "inventory_management" => "shopify",
              "option1" => $variantData['option1'] ?? null,
              "option2" => $variantData['option2'] ?? null,
              "option3" => $variantData['option3'] ?? null
          ]
      ];
      
      $response = $this->rest_api(
          "/admin/api/" . API_VERSION . "/products/{$productId}/variants.json",
          $data,
          'POST',
          $shop_url,
          $access_token
      );
      
      // Upload variant images
      if (!empty($variantData['image_url']) && isset($response['body']['variant']['id'])) {
          $this->uploadVariantImages($productId, $response['body']['variant']['id'], $variantData['image_url'], $shop_url, $access_token);
      }
      
      return $response;
    }
    
    // Example usage:
    /*
    $productData = [
        'title' => 'Test Product',
        'description' => '<p>Product description</p>',
        'vendor' => 'My Brand',
        'product_type' => 'Clothing',
        'tags' => ['summer', 'sale', 'new'],
        'category' => 'gid://shopify/TaxonomyCategory/aa-1-2',
        'status' => 'ACTIVE',
        'images' => [
            'https://example.com/image1.jpg',
            'https://example.com/image2.jpg'
        ],
        'variants' => [
            [
                'title' => 'Small / Red',
                'price' => '29.99',
                'sku' => 'SMALL-RED-001',
                'inventory_quantity' => 100,
                'option1' => 'Small',
                'option2' => 'Red'
            ],
            [
                'title' => 'Medium / Blue',
                'price' => '29.99',
                'sku' => 'MED-BLUE-001',
                'inventory_quantity' => 50,
                'option1' => 'Medium',
                'option2' => 'Blue'
            ]
        ]
    ];
    
    $result = $this->createProductWithDetails($productData, $shop_url, $access_token);
    */


    public function downloadAndSaveImage($source, $isFile = false) {
      try {
          // Generate filename
          if ($isFile) {
              // For uploaded files
              $extension = pathinfo($source['name'], PATHINFO_EXTENSION);
              $imageContent = file_get_contents($source['tmp_name']);
          } else {
              // For URL downloads
              $imageContent = file_get_contents($source);
              if ($imageContent === false) {
                  throw new Exception('Failed to download image');
              }
              $extension = pathinfo(parse_url($source, PHP_URL_PATH), PATHINFO_EXTENSION);
          }
          
          if (empty($extension)) $extension = 'jpg';
          $filename = 'product_' . time() . '_' . md5(uniqid()) . '.' . $extension;
          
          // Save to uploads directory
          $uploadDir = $_SERVER['DOCUMENT_ROOT'] .'/'. API_FOLDER_NAME .'/uploads/products-image/';
          if (!file_exists($uploadDir)) {
              mkdir($uploadDir, 0755, true);
          }
          
          $filepath = $uploadDir . $filename;
          file_put_contents($filepath, $imageContent);
          
          // Return ngrok URL
          return APP_URL.'/uploads/products-image/' . $filename;
          
      } catch (Exception $e) {
          return null;
      }
    }

    private function publishToChannels($productId, $publicationIds, $shop_url, $access_token) {
      if (empty($publicationIds)) return;
      
      $query = <<<GRAPHQL
      mutation publishablePublish(\$id: ID!, \$input: [PublicationInput!]!) {
        publishablePublish(id: \$id, input: \$input) {
          publishable {
            availablePublicationsCount {
              count
            }
          }
          userErrors {
            field
            message
          }
        }
      }
      GRAPHQL;
      
      $input = array_map(function($pubId) {
          return ["publicationId" => $pubId];
      }, $publicationIds);
      
      $variables = [
          "id" => $productId,
          "input" => $input
      ];
      
      $response = $this->shopify_graphql_api($shop_url, $access_token, $query, $variables);

      return $response;
    }


  }