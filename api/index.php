<?php

// All URLs to go through this one script, index.php.
// Front Controller - This simply means that all requests are sent through one single script and the script decides what to do.

// Enforce strict types for better type safety.
declare(strict_types=1);

// Allow from any origin
header("Access-Control-Allow-Origin: *");

// Allow specific HTTP methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Indicate allowed methods
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

    // Indicate allowed headers
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    // No content
    http_response_code(204);
    exit();
}


// Include the ErrorHandler class file.
require dirname(__DIR__) . "/src/ErrorHandler.php";

require dirname(__DIR__) . "/src/JWTCodec.php";

// Set the global exception handler to the handleException method of the ErrorHandler class.
set_exception_handler("ErrorHandler::handleException");

/**
 * Retrieve the full path from the server's request URI
 * parse_url && PHP_URL_PATH - This is useful when you want to work specifically with the path of the current URL without including the domain or query parameters (/task?id=2)
 */
$fullPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Split the path into an array using '/' as the delimiter
$path = explode('/', $fullPath);

// Get the resource name from the array (e.g., 'products')
$resource = $path[3];

// Get the resource ID (e.g., 'products/1') from the array if it exists, otherwise set it to null
$id = isset($path[4]) ? $path[4] : null;

// Check if the resource is not 'tasks'
if ($resource != 'tasks') {
    // If it's not 'products', return a 404 Not Found HTTP status code
    http_response_code(404);
    exit; // Exit the script
}

// Get all HTTP request headers
$headers = apache_request_headers();

// Check if the Authorization header exists and follows the "Bearer <token>" format
if (!preg_match("/^Bearer\s+(.*)$/", $headers['Authorization'], $matches)) {
    // If not, set HTTP response code to 400 (Bad Request) and return a JSON error message
    http_response_code(400);
    echo json_encode(['message' => 'Incomplete authorization header']);
    exit;
}

// Extract the token from the Authorization header
$token = $matches[1];

// Initialize JWTCodec class;
$jwt = new JWTCodec();

// The token will be decoded using decode() method from JWTCodec class, the method will return an associative array version of the payload
$extractedPayload = $jwt->decode($token);

/* At this point, $extractedPayload contains the payload from the token */

/* -- This code will execute if resource = 'tasks' -- */

// Check if the $extractedPayload['api_key'] header is not set or is empty
if (empty($extractedPayload['api_key'])) {
    // Set the HTTP response code to 400 (Bad Request)
    http_response_code(400);

    // Return a JSON response indicating the missing API key
    echo json_encode(['message' => 'missing API key']);

    // Terminate the script to prevent further execution
    exit;
}

// Retrieve the API key from the $extractedPayload['api_key']
$api_key = $extractedPayload['api_key'];

// Include Autoload.php (this is necessary to load all dependencies managed by Composer. (including the Dotenv library)
require dirname(__DIR__) . '/vendor/autoload.php';

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

// Include the Database class file.
require dirname(__DIR__) . "/src/Database.php";

// Create a new instance of Database (params from .env)
$database = new Database($dbHost, $dbName, $dbUser, $dbPass);

// // This PHP code sets the Content-Type header of the HTTP response to indicate that the response body will be in JSON format and use UTF-8 character encoding.
// header('Content-type: application/json; charset=UTF-8');

// Include the TaskController class file.
require dirname(__DIR__) . "/src/TaskController.php";

// Include the Database class file.
require dirname(__DIR__) . "/src/TaskGateway.php";

// Include the UserGateway class file.
require dirname(__DIR__) . '/src/UserGateway.php';

// Create a new instance of UserGateway. (param from database instance)
$user_gateway = new UserGateway($database);

// Checks if API is valid, if valid returns array, if not returns false.
$user = $user_gateway->getByAPIKey($api_key);

// This will execute if api is not valid (false) and it will script will terminate from here.
if ($user == false) {
    http_response_code(401);
    echo json_encode(['message' => 'Api key is invalid']);
    exit;
}

// Get user id through api key on tbl_users
$user_id = $user_gateway->getUserId($api_key)['id'];

// Create a new instance of TaskGateway. (param from database instance)
$task_gateway = new TaskGateway($database);

// Create a new instance of TaskController. (param from TaskGateway instance)
$controller = new TaskController($task_gateway, $user_id);

// Process the incoming HTTP request based on the request method and $id parameter.
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
