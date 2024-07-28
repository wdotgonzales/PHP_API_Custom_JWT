<?php

header('Content-Type: application/json');

// Include Autoload.php (this is necessary to load all dependencies managed by Composer. (including the Dotenv library)
require dirname(__FILE__) . '/vendor/autoload.php';

// Include the Database class file.
require dirname(__FILE__) . "/src/Database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Create an instance of Dotenv and load the environment variables from the .env file
    // __DIR__ gives the directory of the current file, and we're navigating one level up to find the .env file
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/./');
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
    $postRequestValues = json_decode(file_get_contents('php://input'), true);

    $query = "INSERT INTO `tbl_users`(`name`, `username`, `password_hash`, `api_key`) VALUES (:name, :username, :password_hash, :api_key)";
    $stmt = $connection->prepare($query);

    $password_hash = password_hash($postRequestValues['password'], PASSWORD_DEFAULT);

    $api_key = bin2hex(random_bytes(16));

    $stmt->bindParam(':name', $postRequestValues['name']);
    $stmt->bindParam(':username', $postRequestValues['username']);
    $stmt->bindParam(':password_hash', $password_hash);
    $stmt->bindParam(':api_key', $api_key);

    if (!$stmt->execute()) {
        echo json_encode([
            'message' => 'Fail to register. Please contact administrator'
        ]);
        http_response_code(500);
    }

    echo json_encode([
        'message' => 'Thank you for registering!',
        'api_key' => $api_key
    ]);
    http_response_code(200);
} else {
    echo json_encode([
        'message' => 'No permission to access'
    ]);

    http_response_code(500);
}
