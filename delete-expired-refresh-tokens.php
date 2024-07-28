<?php

// Include Autoload.php (this is necessary to load all dependencies managed by Composer. (including the Dotenv library)
require dirname(__FILE__) . '/vendor/autoload.php';

// Include the Database class file.
require dirname(__FILE__) . "/src/Database.php";

require dirname(__FILE__) . "/src/RefreshTokenGateway.php";

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

$refresh_token_gateway = new RefreshTokenGateway($database);

