<?php
require_once 'Config/Database.php';
/**
 * Base controller class that provides core functionality for all controllers
 * including database access, model loading, view rendering, and request handling.
 */
class Controller {
    protected $db;

    protected $models = [];

    /**
     * Controller constructor.
     *
     * @param PDO|null $db Optional database connection instance.
     *                      If not provided, creates a new connection.
     * @throws PDOException If database connection fails
     */
    public function __construct($db = null) {
        if ($db) {
            $this->db = $db;
        } else {
            try {
                $database = new Database();
                $this->db = $database->getConnection();
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo "Database connection failed: " . $e->getMessage() . "<br>";
                exit;
            }
        }
        $this->initializeSession();
        $this->loadCoreModels();
    }

    /**
     * Initializes the session if not already started.
     *
     * @return void
     */
    protected function initializeSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Loads core models required by the application.
     *
     * @throws Exception If required model files are missing
     * @return void
     */
    protected function loadCoreModels() {
        if (!file_exists('App/models/User.php') || !file_exists('App/models/Blog.php')) {
            throw new Exception("Required model files are missing");
        }
        require_once 'App/Models/User.php';
        require_once 'App/Models/Blog.php';
        $this->models['user'] = new User($this->db);
        $this->models['blog'] = new Blog($this->db);
    }

    /**
     * Gets a model instance by name.
     *
     * @param string $modelName Name of the model to retrieve
     * @return object|null The model instance or null if not found
     */
    public function getModel($modelName) {
        return $this->models[$modelName] ?? null;
    }

    /**
     * Renders a view with optional data.
     *
     * @param string $view Name of the view file (without .php extension)
     * @param array $data Associative array of data to pass to the view
     * @return void
     */
    protected function view($view, $data = []) {
        if (!is_array($data)) {
            $data = ['message' => $data];
        }
        extract($data);
        require_once "App/Views/{$view}.php";
    }

    /**
     * Redirects to another page with optional flash message/data.
     *
     * @param string $location Target page/route
     * @param array|string $data Data to pass via session (string treated as message)
     * @return void
     */
    protected function redirect($location, $data = []) {
        if (is_string($data)) {
            $_SESSION['message'] = $data;
        } elseif (is_array($data) && isset($data['message'])) {
            $_SESSION['message'] = $data['message'];
        }
        foreach ($data as $key => $value) {
            if ($key !== 'message') {
                $_SESSION[$key] = $value;
            }
        }
        header("Location: index.php?page=$location");
        exit();
    }

    /**
     * Sanitizes input data to prevent XSS attacks.
     *
     * @param string|array $input Input to sanitize
     * @return string|array Sanitized output
     */
    protected function sanitize($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Ensures user is authenticated before proceeding.
     * Redirects to login page if not authenticated.
     *
     * @return void
     */
    protected function requireAuth() {
        if (!isset($_SESSION['id'])) {
            $this->redirect('login', 'Please login first');
        }
    }
}