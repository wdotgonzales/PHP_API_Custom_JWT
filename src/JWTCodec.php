<?php

/**
 * Class JWTCodec
 *
 * This class provides methods to encode a JWT (JSON Web Token).
 */
class JWTCodec
{
    /**
     * Encode the payload into a JWT.
     *
     * @param array $payload The payload data to be encoded into the JWT.
     * @return string The encoded JWT.
     */
    public function encode(array $payload)
    {
        // Create the header array and encode it to JSON
        $header = json_encode([
            'typ' => 'JWT',  // Token type
            'alg' => 'HS256' // Algorithm used for signature
        ]);

        // Base64 URL encode the header
        $header = $this->base64urlEncode($header);

        // Encode the payload to JSON
        $payload = json_encode($payload);

        // Base64 URL encode the payload
        $payload = $this->base64urlEncode($payload);

        // Create the signature using HMAC with SHA-256 algorithm
        $signature = hash_hmac(
            "sha256", // Hashing algorithm
            $header . "." . $payload, // Data to be hashed (header and payload)
            "5A7134743777217A25432646294A404E635266556A586E3272357538782F413F", // Secret key
            true // Output raw binary data
        );

        // Base64 URL encode the signature
        $signature = $this->base64urlEncode($signature);

        // Concatenate header, payload, and signature to form the JWT
        return $header . "." . $payload . "." . $signature;
    }

    /**
     * Decode a JWT token and return the payload as an array.
     *
     * @param string $token The JWT token to decode.
     * @return array The decoded payload as an associative array.
     */
    public function decode(string $token): array
    {
        // Use a regular expression to split the token into its parts: header, payload, and signature
        if (preg_match("/^(?<header>.+)\.(?<payload>.+)\.(?<signature>.+)$/", $token, $matches) !== 1) {
            // If the token format is invalid, respond with a 400 Bad Request status and an error message
            http_response_code(400);
            echo json_encode(["error" => "Invalid Token Format"]);
            exit;
        }

        // Create a signature using the header and payload from the token and a secret key
        $signature = hash_hmac(
            "sha256", // Hashing algorithm
            $matches["header"] . "." . $matches["payload"], // Data to be hashed (header and payload)
            "5A7134743777217A25432646294A404E635266556A586E3272357538782F413F", // Secret key
            true // Output raw binary data
        );

        // Decode the signature part of the token
        $signature_from_token = $this->base64urlDecode($matches['signature']);

        // Check if the signature we created matches the one from the token
        if (!hash_equals($signature, $signature_from_token)) {
            // If the signature does not match, respond with a 401 Unauthorized status and an error message
            http_response_code(401);
            echo json_encode(["error" => "Signature does not match"]);
            exit;
        }

        // Decode the payload part of the token (which contains the actual data) and return it
        $payload = json_decode($this->base64urlDecode($matches["payload"]), true);
        
        // Check if the token has expired
        if ($payload['exp'] < time()) {
            // Set the HTTP response code to 401 Unauthorized
            http_response_code(401);

            // Return a JSON-encoded error message
            echo json_encode(['message' => 'Token has expired']);

            // Exit the script to ensure no further code is executed
            exit;
        }

        // If the token is not expired, return the payload
        return $payload;
    }



    /**
     * Base64 URL encode a string.
     *
     * This method encodes a string using Base64 URL encoding, which is a variant
     * of Base64 encoding that is URL-safe. It replaces "+" with "-", "/" with "_",
     * and removes "=" padding.
     *
     * @param string $text The string to be encoded.
     * @return string The Base64 URL encoded string.
     */
    private function base64urlEncode(string $text): string
    {
        return str_replace(
            ["+", "/", "="], // Characters to be replaced
            ["-", "_", ""],  // Replacement characters
            base64_encode($text) // Base64 encode the input text
        );
    }

    /**
     * Decode a Base64 URL-encoded string.
     *
     * @param string $text The Base64 URL-encoded string to decode.
     * @return string The decoded string.
     */
    private function base64urlDecode(string $text): string
    {
        return base64_decode(str_replace(
            ["-", "_"], // Characters to replace
            ["+", "/"], // Replacement characters
            $text
        ));
    }
}
