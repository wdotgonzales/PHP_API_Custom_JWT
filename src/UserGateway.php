<?php

class UserGateway
{
    private PDO $connection;
    public function  __construct(Database $database)
    {
        $this->connection = $database->connect();
    }

    public function getByAPIKey(string $apiKey): array | false
    {
        $query = "SELECT * FROM `tbl_users` WHERE api_key = :api_key";
        $stmt = $this->connection->prepare($query);
        $apiKey = htmlspecialchars(strip_tags($apiKey));
        $stmt->bindParam(':api_key', $apiKey);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserId(string $api_key)
    {
        $query = "SELECT id FROM `tbl_users` WHERE api_key = :api_key";
        $stmt = $this->connection->prepare($query);
        $api_key = htmlspecialchars(strip_tags($api_key));
        $stmt->bindParam(':api_key', $api_key);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDataByUsername(string $username): array | false
    {
        $query = "SELECT * FROM `tbl_users` WHERE username = :username";
        $stmt = $this->connection->prepare($query);
        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDataByID(int $id): array | false
    {
        $query = "SELECT * FROM `tbl_users` WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
