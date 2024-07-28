<?php

/**
 * Class ErrorHandler
 * A class to handle exceptions and format them for JSON output.
 * This is mainly for TaskController, this will be used in api/index.php to catch error when $id is null.
 */
class ErrorHandler
{
    /**
     * Handle an exception and output it as a JSON response with HTTP status code 500.
     *
     * @param Throwable $exception The exception to handle.
     * @return void
     */
    public static function handleException(Throwable $exception): void
    {
        // Set the HTTP response status code to 500 (Internal Server Error).
        http_response_code(500);

        // Output the exception details as a JSON-encoded response.
        echo json_encode(
            [
                "code" => $exception->getCode(),        // The exception code.
                "message" => $exception->getMessage(),  // The exception message.
                "file" => $exception->getFile(),        // The file where the exception occurred.
                "line" => $exception->getLine()         // The line number where the exception occurred.
            ]
        );
    }

    // public static function handleError(int $errorNo, string $errStr, string $errFile, int $errLine): void
    // {
    //     throw new ErrorException($errStr, 0, $errorNo, $errFile, $errLine);
    // }
}
