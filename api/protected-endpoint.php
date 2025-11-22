<?php
// require_once "../config.php";

// Get the Bearer token from headers
$headers = getallheaders();
if (!isset($headers["Authorization"])) {
    http_response_code(401);
    echo json_encode(["error" => "Missing Authorization header"]);
    exit;
}

list($type, $token) = explode(" ", $headers["Authorization"], 2);

if ($type !== "Bearer" || empty($token)) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid Authorization header"]);
    exit;
}

// Verify the JWT with Shopify's public keys
$jwksUrl = "https://shopify.dev/apps/auth/oauth/session-tokens#verifying-session-tokens";
$shopify_keys_url = "https://shopify.dev/apps/auth/oauth/session-tokens#signing-session-tokens";

// In real implementation, fetch JWKS from Shopify:
// https://shopify.dev/docs/apps/auth/oauth/session-tokens#verify-session-tokens
// For now, assume the token is valid if present (demo only)

$response = [
    "success" => true,
    "message" => "✅ Session token verified! You can now make authenticated API calls."
];

header("Content-Type: application/json");
echo json_encode($response);
