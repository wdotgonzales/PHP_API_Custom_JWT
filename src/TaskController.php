<?php

/**
 * Class TaskController
 * A class to manage tasks and process HTTP requests.
 */
class TaskController
{
    // @var TaskGateway The gateway for task data operations.
    private TaskGateway $gateway;

    /**
     * TaskController constructor.
     * @param TaskGateway $gateway The gateway for task data operations. 
     */
    public function __construct(TaskGateway $gateway, private int $user_id)
    {
        $this->gateway = $gateway;
    }

    /**
     * Process HTTP requests for task management based on the request method and task ID.
     *
     * @param string $methodRequest The HTTP request method (e.g., GET, POST, PATCH, DELETE).
     * @param string|null $id The task ID (optional). If null, handles requests for all tasks.
     * @return void
     */
    public function processRequest(string $methodRequest, ?string $id): void
    {
        // Check if $id is null to determine if the request is for all tasks or a specific task.
        if ($id === null) {
            // Handle requests for all tasks.
            // -> /products
            switch ($methodRequest) {
                case "GET":
                    echo json_encode($this->gateway->getAllTask($this->user_id));
                    break;
                case "POST":
                    // Decode the JSON-encoded request body into an associative array
                    $postRequestValues = json_decode(file_get_contents('php://input'), true);

                    // Extract the values from the decoded request
                    $name = $postRequestValues['name'];
                    $description = $postRequestValues['description'];
                    $is_completed = $postRequestValues['is_completed'];

                    // Call the createATask method to create a new task with the extracted values
                    $result = $this->gateway->createATask($name, $description, $is_completed, $this->user_id);

                    // Respond to the client indicating whether the task creation was successful
                    $this->responseCreatedTask($result);

                    break;
                default:
                    $this->responseMethodNotAllowed("GET, POST");
            }
        } else {
            // Handle requests for a specific task identified by $id.
            // -> /products/1 , /products/2 , /products/3 , ...

            // If getATask return output array is less than 1, this code will execute
            if (count($this->gateway->getATask($id, $this->user_id)) < 1) {
                $this->responseNotFound($id);
                return;
            }

            // If task id exist, this code will execute
            switch ($methodRequest) {
                case "GET":
                    echo json_encode($this->gateway->getATask($id, $this->user_id));
                    break;
                case "PATCH":
                    // Decode the JSON-encoded request body into an associative array
                    $patchRequestValues = json_decode(file_get_contents('php://input'), true);

                    // Extract the values from the decoded request
                    $name = $patchRequestValues['name'];
                    $description = $patchRequestValues['description'];
                    $is_completed = $patchRequestValues['is_completed'];

                    $result = $this->gateway->updateATask($id, $this->user_id, $name, $description, $is_completed);
                    $this->responseUpdateTask($result, $id);
                    break;
                case "DELETE":
                    $result = $this->gateway->deleteATask($id, $this->user_id);
                    $this->responseDeleteTask($result, $id);
                    break;
                default:
                    $this->responseMethodNotAllowed("GET, PATCH, DELETE");
            }
        }
    }

    /**
     * Respond with a 405 Method Not Allowed status code and the allowed methods.
     * @param string $allow_methods A comma-separated list of allowed HTTP methods.
     * @return void
     */
    private function responseMethodNotAllowed(string $allow_methods): void
    {
        // Set the HTTP response status code to 405 (Method Not Allowed).
        http_response_code(405);

        // Set the Allow header with the list of allowed methods.
        header("Allow: $allow_methods");
    }


    /**
     * Respond with a 404 Not Found status code and a JSON-encoded error message.
     * 
     * This method sets the HTTP response status code to 404 (Not Found)
     * and sends a JSON-encoded error message indicating that the requested task
     * with the specified ID does not exist.
     *
     * @param string $id The ID of the task that was not found.
     * @return void
     */
    private function responseNotFound(string $id): void
    {
        // Set the HTTP response status code to 404 (Not Found).
        http_response_code(404);

        // Prepare an error message in JSON format indicating the task with the specified ID was not found.
        $error_message = ['message' => "Task with ID : $id, does not exist"];

        // Output the JSON-encoded error message.
        echo json_encode($error_message);
    }


    /**
     * Respond with the result of the task creation.
     * 
     * This method sets the appropriate HTTP response code and sends a JSON-encoded message
     * indicating whether the task was successfully created or not.
     *
     * @param bool $result The result of the task creation (true if successful, false otherwise).
     * @return void
     */
    private function responseCreatedTask(bool $result): void
    {
        if ($result) {
            // Set the HTTP response status code to 201 (Created) if the task was successfully created
            http_response_code(201);
            echo json_encode(['message' => 'Task successfully created']);
        } else {
            // Set the HTTP response status code to 500 (Internal Server Error) if the task creation failed
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create task']);
        }
    }

    /**
     * Respond with the result of the task update.
     * 
     * This method sets the appropriate HTTP response code and sends a JSON-encoded message
     * indicating whether the task was successfully updated or not.
     *
     * @param bool $result The result of the task update (true if successful, false otherwise).
     * @param string $id The ID of the task that was updated.
     * @return void
     */
    private function responseUpdateTask(bool $result, string $id): void
    {
        if ($result) {
            // Set the HTTP response status code to 200 (OK) if the task was successfully updated
            http_response_code(200);
            echo json_encode(['message' => "Task: '$id' is successfully updated"]);
        } else {
            // Set the HTTP response status code to 500 (Internal Server Error) if the task update failed
            http_response_code(500);
            echo json_encode(['message' => 'Failed to update task']);
        }
    }

    /**
     * Respond with the result of the task deletion.
     * 
     * This method sets the appropriate HTTP response code and sends a JSON-encoded message
     * indicating whether the task was successfully deleted or not.
     *
     * @param bool $result The result of the task deletion (true if successful, false otherwise).
     * @param string $id The ID of the task that was deleted.
     * @return void
     */
    private function responseDeleteTask(bool $result, string $id): void
    {
        if ($result) {
            // Set the HTTP response status code to 200 (OK) if the task was successfully deleted
            http_response_code(200);
            echo json_encode(['message' => "Task: '$id' is successfully deleted"]);
        } else {
            // Set the HTTP response status code to 500 (Internal Server Error) if the task deletion failed
            http_response_code(500);
            echo json_encode(['message' => 'Failed to delete task']);
        }
    }
}
