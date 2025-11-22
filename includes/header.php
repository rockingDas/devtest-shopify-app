<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>DevTest App</title>
    <meta name="shopify-api-key" content="<?= API_KEY ?>" />
    <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>
    <link rel="stylesheet" href="css/uptown.css">
    <style>
      body { font-family: Arial, sans-serif; padding: 30px; background: #f9f9f9; text-align: center; }
      .card { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: inline-block; }
      h1 { color: #2c3e50; }
    </style>
  </head>
  <body>
    <!-- https://sd-test.free.nf/devtest/ -->
    <!-- Shopify App UI root -->

    <?php
        include_once("includes/nav_menu.php");
    ?>

        <main>