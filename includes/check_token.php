<?php

// Validate request if hmac exists
if (isset($_GET['hmac']) && !verifyHmac($_GET, SECRECT_KEY)) {
    die("Invalid HMAC");
  }
  $shop = $_GET['shop'] ?? null;
  $query = "SELECT * FROM shops WHERE shop_url = '".$parameters['shop']."'";
  $result = $conn->query($query);
  if( $result->num_rows < 1 ){
    header("Location: install.php?shop=". $_GET['shop']);
    exit();
  }else{
    $row = $result->fetch_assoc();
    $shopify->set_url($row['shop_url']);
    $shopify->set_token($row['access_token']);
  }