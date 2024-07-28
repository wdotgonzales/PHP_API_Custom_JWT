<?php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Include Autoload.php (this is necessary to load all dependencies managed by Composer. (including the Dotenv library)
require dirname(__FILE__) . '/vendor/autoload.php';

// Include the Database class file.
require dirname(__FILE__) . "/src/Database.php";

// Include the UserGateway class file.
require dirname(__FILE__) . "/src/UserGateway.php";

// Include the JWTCodec class file.
require dirname(__FILE__) . "/src/JWTCodec.php";

require dirname(__FILE__) . "/src/RefreshTokenGateway.php";


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
    $postRequestValues = (array) json_decode(file_get_contents('php://input'), true);

    // Check if the "username" and "password" keys exist in the POST request
    if (!array_key_exists("username", $postRequestValues) || !array_key_exists("password", $postRequestValues)) {
        // If not, set HTTP response code to 400 (Bad Request) and return a JSON error message
        http_response_code(400);
        echo json_encode([
            'message' => "missing login credentials"
        ]);
        exit;
    }

    // Instantiate a UserGateway object to interact with the database
    $user_gateway = new UserGateway($database);

    // Retrieve user data from the database using the provided username
    $user_data = $user_gateway->getDataByUsername($postRequestValues["username"]);

    // If no user data is found, set HTTP response code to 401 (Unauthorized) and return an error message
    if ($user_data == false) {
        http_response_code(401);
        echo json_encode(['message' => 'invalid authentication']);
        exit;
    }

    // Verify the provided password against the stored password hash
    if (!password_verify($postRequestValues['password'], $user_data['password_hash'])) {
        // If the password is incorrect, set HTTP response code to 401 (Unauthorized) and return an error message
        http_response_code(401);
        echo json_encode(['message' => 'invalid authentication']);
        exit;
    }

    // Create the payload array with user information
    $payload = [
        // 'sub' stands for 'subject' and typically contains the user ID
        "sub" => $user_data["id"],

        // 'name' contains the name of the user
        "name" => $user_data['name'],

        // 'api_key' contains the API key associated with the user
        "api_key" => $user_data["api_key"],

        // 'exp' contains the expiration time of the token, set to 20 seconds from the current time (20 seconds lifespan before it expires)
        "exp" => time() + 20
    ];

    // Instantiate a new JWTCodec object
    $jwt = new JWTCodec;

    // Encode the payload array into a JWT token
    $access_token = $jwt->encode($payload);

    // Refresh Token Expiry (5 days lifespan)
    $refresh_token_expiry = time() + 43200;

    // Refresh Token 
    $refresh_token = $jwt->encode([
        "sub" => $user_data["id"],
        "api_key" => $user_data["api_key"],
        // 5 Days Lifespan
        "exp" => $refresh_token_expiry
    ]);

    $refresh_token_gateway = new RefreshTokenGateway($database);
    $refresh_token_gateway->createRefreshToken($refresh_token, $refresh_token_expiry);

    // Return the access token as a JSON response
    echo json_encode([
        "access_token" => $access_token,
        "refresh_token" => $refresh_token
    ]);
} else {
    // If the request method is not POST, set HTTP response code to 405 (Method Not Allowed) and indicate that only POST is allowed
    http_response_code(405);
    header("Allow: POST");
    exit;
}
