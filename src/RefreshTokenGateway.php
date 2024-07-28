<?php

class RefreshTokenGateway
{
    private PDO $connection;
    private string $key = "5A7134743777217A25432646294A404E635266556A586E3272357538782F413F";

    public function __construct(Database $database)
    {
        $this->connection = $database->connect();
    }

    public function createRefreshToken(string $token, int $expiry)
    {
        $hashed_token = hash_hmac("sha256", $token, $this->key);

        $query = "INSERT INTO `refresh_token`(`token_hash`, `expires_at`) VALUES (:token_hash, :expires_at)";

        $stmt = $this->connection->prepare($query);

        $expiry = htmlspecialchars(strip_tags($expiry));

        $stmt->bindParam(':token_hash', $hashed_token);
        $stmt->bindParam(':expires_at', $expiry);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteRefreshToken(string $token): int
    {
        $hash = hash_hmac("sha256", $token, $this->key);
        $query = "DELETE FROM `refresh_token` WHERE token_hash = :token_hash";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(":token_hash", $hash);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function getByToken(string $token): array | bool
    {
        $hash = hash_hmac("sha256", $token, $this->key);
        $query = "SELECT *  FROM `refresh_token` WHERE token_hash = :token_hash";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(":token_hash", $hash);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteExpiredRefreshToken(): int
    {
        $query = "DELETE FROM `refresh_token` WHERE expires_at < UNIX_TIMESTAMP()";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();

        return $stmt->rowCount();
    }
}
