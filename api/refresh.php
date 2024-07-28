<?php

// Set the content type to JSON and allow access from any origin
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Load all dependencies managed by Composer, including the Dotenv library
require dirname(__DIR__) . '/vendor/autoload.php';

// Load the Database class
require dirname(__DIR__) . "/src/Database.php";

// Load the UserGateway class
require dirname(__DIR__) . "/src/UserGateway.php";

// Load the JWTCodec class
require dirname(__DIR__) . "/src/JWTCodec.php";

// Load the RefreshTokenGateway class
require dirname(__DIR__) . "/src/RefreshTokenGateway.php";

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Load environment variables from the .env file
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    // Retrieve database connection details from environment variables
    $dbHost = $_ENV['DB_HOST'];
    $dbName = $_ENV['DB_NAME'];
    $dbUser = $_ENV['DB_USERNAME'];
    $dbPass = $_ENV['DB_PASSWORD'];

    // Create a new instance of the Database class and establish a connection
    $database = new Database($dbHost, $dbName, $dbUser, $dbPass);
    $connection = $database->connect();

    // Decode the JSON-encoded request body into an associative array
    $postRequestValues = (array) json_decode(file_get_contents('php://input'), true);

    // Check if the "token" key exists in the POST request
    if (!array_key_exists("token", $postRequestValues)) {
        // If not, set HTTP response code to 400 (Bad Request) and return a JSON error message
        http_response_code(400);
        echo json_encode([
            'message' => "missing token"
        ]);
        exit;
    }

    // Set the refresh token expiry time to 12 hours (43200 seconds) from the current time
    $refresh_token_expiry = time() + 43200;
    $refresh_token = $postRequestValues["token"];

    // Create a new instance of the RefreshTokenGateway class
    $refresh_token_gateway = new RefreshTokenGateway($database);

    // Check if the provided refresh token exists in the whitelist
    if ($refresh_token_gateway->getByToken($refresh_token) === false) {
        // If not, set HTTP response code to 400 (Bad Request) and return a JSON error message
        http_response_code(400);
        echo json_encode([
            'message' => "invalid token (not on whitelist)"
        ]);
        exit;
    }

    // Delete the existing refresh token and create a new one with the same token value and expiry time
    $refresh_token_gateway->deleteRefreshToken($refresh_token);
    $refresh_token_gateway->createRefreshToken($refresh_token, $refresh_token_expiry);

    // Create a new instance of the JWTCodec class and decode the provided refresh token
    $jwt = new JWTCodec;
    $payload = $jwt->decode($postRequestValues["token"]);

    // Extract the user ID from the token payload
    $user_id = $payload['sub'];

    // Create a new instance of the UserGateway class and retrieve user details by user ID
    $user_gateway = new UserGateway($database);
    $userDetails = $user_gateway->getDataByID($user_id);

    // Check if the user details were successfully retrieved
    if ($userDetails === false) {
        // If not, set HTTP response code to 401 (Unauthorized) and return a JSON error message
        http_response_code(401);
        echo json_encode(['message' => 'invalid authentication']);
        exit;
    }

    $new_access_token = $jwt->encode([
        "sub" => $userDetails["id"],
        "name" => $userDetails['name'],
        "api_key" => $userDetails["api_key"],
        "exp" => time() + 20
    ]);

    echo json_encode([
        'new_access_token' => $new_access_token,
        'new_refresh_token' => $refresh_token
    ]);
} else {
    // If the request method is not POST, set HTTP response code to 405 (Method Not Allowed) and indicate that only POST is allowed
    http_response_code(405);
    header("Allow: POST");
    exit;
}
