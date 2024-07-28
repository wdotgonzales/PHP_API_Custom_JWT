<?php

class TaskGateway
{
    private PDO $connection;

    public function  __construct(Database $database)
    {
        $this->connection = $database->connect();
    }

    /**
     * Get all task.
     * @return array
     */
    public function getAllTask(int $user_id): array
    {
        // SQL query to select all columns from the tbl_tasks table
        $query = "SELECT * FROM tbl_tasks WHERE user_id = :user_id";

        // Prepare the SQL statement
        $stmt = $this->connection->prepare($query);

        $user_id = htmlspecialchars(strip_tags($user_id));

        $stmt->bindParam(':user_id', $user_id);

        // Execute the prepared statement
        $stmt->execute();

        // Initialize an empty array to hold the results
        $results = [];

        // Fetch each row as an associative array
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Convert the 'is_completed' field to a boolean
            $row['is_completed'] = (bool) $row['is_completed'];

            // Append the processed row to the results array
            $results[] = $row;
        }

        // Return the array of results
        return $results;
    }


    /**
     * Get a single task through task id.
     * @param string $id Task id ('products/1' , 'products/2', ...)
     * @return array
     */
    public function getATask(string $id, int $user_id): array
    {
        // SQL query to select all columns from the tbl_tasks table where the id matches the provided id
        $query = "SELECT * FROM tbl_tasks WHERE id = :id AND user_id = :user_id";

        // Prepare the SQL statement
        $stmt = $this->connection->prepare($query);

        // Sanitize the input id to prevent XSS attacks
        $id = htmlspecialchars(strip_tags($id));
        $user_id = htmlspecialchars(strip_tags($user_id));

        // Bind the sanitized id parameter to the prepared statement
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $user_id);

        // Execute the prepared statement
        $stmt->execute();

        // Initialize an empty array to hold the results
        $results = [];

        // Fetch the row as an associative array
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Convert the 'is_completed' field to a boolean
            $row['is_completed'] = (bool) $row['is_completed'];

            // Append the processed row to the results array
            $results[] = $row;
        }

        // Return the array of results
        return $results;
    }

    /**
     * Create a new task in the tbl_tasks table.
     * 
     * This method inserts a new task into the database with the provided name,
     * description, and completion status. It returns true if the task is successfully
     * created, and false otherwise.
     *
     * @param string $name The name of the task.
     * @param string $description The description of the task.
     * @param string $is_completed The completion status of the task (typically '0' for false and '1' for true).
     * @return bool True if the task was successfully created, false otherwise.
     */
    public function createATask(string $name, string $description, string $is_completed, string $user_id): bool
    {
        // SQL query to insert a new task into the tbl_tasks table
        $query = "INSERT INTO `tbl_tasks`(`name`, `description`, `is_completed`, `user_id`) VALUES (:name, :description, :is_completed, :user_id)";

        // Prepare the SQL statement
        $stmt = $this->connection->prepare($query);

        // Sanitize the input parameters to prevent XSS attacks
        $name = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $is_completed = htmlspecialchars(strip_tags($is_completed));
        $user_id = htmlspecialchars(strip_tags($user_id));

        // Bind the sanitized parameters to the prepared statement
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':is_completed', $is_completed);
        $stmt->bindParam(':user_id', $user_id);


        // Execute the prepared statement and return the result
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update an existing task in the tbl_tasks table.
     * 
     * This method updates an existing task in the database with the provided ID,
     * name, description, and completion status. It returns true if the task is 
     * successfully updated, and false otherwise.
     *
     * @param string $id The ID of the task to be updated.
     * @param string $name The new name of the task.
     * @param string $description The new description of the task.
     * @param string $is_completed The new completion status of the task (typically '0' for false and '1' for true).
     * @return bool True if the task was successfully updated, false otherwise.
     */
    public function updateATask(string $id, int $user_id, string $name, string $description, string $is_completed): bool
    {
        // SQL query to update the task in the tbl_tasks table with the specified ID
        $query = "UPDATE `tbl_tasks` SET `name`= :name, `description`= :description, `is_completed`= :is_completed WHERE id = :id AND user_id = :user_id";

        // Prepare the SQL statement
        $stmt = $this->connection->prepare($query);

        // Sanitize the input parameters to prevent XSS attacks
        $id = htmlspecialchars(strip_tags($id));
        $user_id = htmlspecialchars(strip_tags($user_id));
        $name = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $is_completed = htmlspecialchars(strip_tags($is_completed));

        // Bind the sanitized parameters to the prepared statement
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':is_completed', $is_completed);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':id', $id);

        // Execute the prepared statement and return the result
        return $stmt->execute();
    }

    /**
     * Delete an existing task from the tbl_tasks table.
     * 
     * This method deletes a task from the database with the provided ID.
     * It returns true if the task is successfully deleted, and false otherwise.
     *
     * @param string $id The ID of the task to be deleted.
     * @return bool True if the task was successfully deleted, false otherwise.
     */
    public function deleteATask(string $id, int $user_id): bool
    {
        // SQL query to delete the task from the tbl_tasks table with the specified ID
        $query = "DELETE FROM `tbl_tasks` WHERE id = :id AND user_id = :user_id";

        // Prepare the SQL statement
        $stmt = $this->connection->prepare($query);

        // Sanitize the input parameter to prevent XSS attacks
        $id = htmlspecialchars(strip_tags($id));
        $user_id = htmlspecialchars(strip_tags($user_id));

        // Bind the sanitized parameter to the prepared statement
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":user_id", $user_id);

        // Execute the prepared statement and return the result
        return $stmt->execute();
    }
}
