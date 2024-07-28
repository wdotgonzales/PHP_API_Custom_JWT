<?php

declare(strict_types=1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");

// // Handle preflight requests
// if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
//     // Indicate allowed methods
//     // Allow specific HTTP methods
//     header("Access-Control-Allow-Methods:POST");

//     // Allow specific headers
//     header("Access-Control-Allow-Headers: Content-Type");

//     // No content
//     http_response_code(204);
//     exit();
// }

// Include Autoload.php (this is necessary to load all dependencies managed by Composer. (including the Dotenv library)
require dirname(__DIR__) . '/vendor/autoload.php';

// Include the Database class file.
require dirname(__DIR__) . "/src/Database.php";

// Include the UserGateway class file.
require dirname(__DIR__) . "/src/UserGateway.php";

// Include the JWTCodec class file.
require dirname(__DIR__) . "/src/JWTCodec.php";

// Include the JWTCodec class file.
require dirname(__DIR__) . "/src/RefreshTokenGateway.php";


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Create an instance of Dotenv and load the environment variables from the .env file
    // __DIR__ gives the directory of the current file, and we're navigating one level up to find the .env file
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    // Now you can use the environment variables defined in your .env file throughout your application
    // Example: Accessing environment variables
    $dbHost = $_ENV['DB_HOST'];
    $dbName = $_ENV['DB_NAME'];
    $dbUser = $_ENV['DB_USERNAME'];
    $dbPass = $_ENV['DB_PASSWORD'];

    // Create a new instance of Database (params from .env)
    $database = new Database($dbHost, $dbName, $dbUser, $dbPass);
    $connection = $database->connect();

    // Decode the JSON-encoded request body into an associative array
    $postRequestValues = (array) json_decode(file_get_contents('php://input'), true);

    // Check if the "username" and "password" keys exist in the POST request
    if (!array_key_exists("token", $postRequestValues)) {
        // If not, set HTTP response code to 400 (Bad Request) and return a JSON error message
        http_response_code(400);
        echo json_encode([
            'message' => "missing token"
        ]);
        exit;
    }

    $refresh_token = $postRequestValues["token"];
    $refresh_token_gateway = new RefreshTokenGateway($database);

    $refresh_token_gateway->deleteRefreshToken($refresh_token);

} else {
    // If the request method is not POST, set HTTP response code to 405 (Method Not Allowed) and indicate that only POST is allowed
    http_response_code(405);
    header("Allow: POST");
    exit;
}
