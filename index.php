<?php

include_once( "includes/config.php" );
include_once( "includes/functions.php" );
include_once( "includes/mysql_connect.php" );
include_once( "includes/shopify.php" );

$shopify = new Shopify();
$parameters = $_GET;

include_once("includes/check_token.php");

$products = $shopify->rest_api( "/admin/api/".API_VERSION."/products.json", array(), 'GET' );
// print_r($products);
if( !is_null($products['body']) && array_key_exists( "errors", $products['body'] ) ){
  header("Location: install.php?shop=". $_GET['shop']);
  exit();
}
include_once("includes/header.php");
?>
  <div class="card">
    <h1>🎉 Welcome back to DevTest App</h1>
    <p>Shop: <strong><?php echo htmlspecialchars($shop); ?></strong></p>
    <p>Your app is installed and ready ✅</p>
    <button id="open-picker">Open resource picker</button>
  </div>

  <script>
    document
      .getElementById('open-picker')
      .addEventListener('click', async () => {
        const selected = await shopify.resourcePicker({type: 'product'});
        console.log(selected);
      });
  </script>



<?php
include_once("includes/footer.php");
?>