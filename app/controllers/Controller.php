<?php
require_once 'config/database.php';

class Controller {
    protected $db;
    protected $models = [];

    public function __construct($db = null) {
        if ($db) {
            $this->db = $db;
        } else {
            $database = new Database();
            $this->db = $database->getConnection();
        }
        $this->initializeSession();
        $this->loadCoreModels();
    }

    protected function initializeSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function loadCoreModels() {
        require_once 'app/models/User.php';
        require_once 'app/models/Blog.php';
        $this->models['user'] = new User($this->db);
        $this->models['blog'] = new Blog($this->db);
    }

    public function getModel($modelName) {
        return $this->models[$modelName] ?? null;
    }

    protected function view($view, $data = []) {
        if (!is_array($data)) {
            $data = ['message' => $data];
        }
        extract($data);
        require_once "app/views/{$view}.php";
    }

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

    protected function sanitize($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    protected function requireAuth() {
        if (!isset($_SESSION['id'])) {
            $this->redirect('login', 'Please login first');
        }
    }
}
?>