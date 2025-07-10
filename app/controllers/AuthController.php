<?php

require_once 'Controller.php';
class AuthController extends Controller {

    /**
     * AuthController constructor.
     *
     * @param \PDO|null $db
     * @throws Exception If the parent constructor fails
     */
    public function __construct($db = null) {
        parent::__construct($db);
    }

    /**
     * Handles user login logic.
     *
     * @return void
     * @throws Exception If model initialization or login process fails.
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'email' => $this->sanitize($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? ''
            ];

            if (!isset($this->models['user'])) {
                throw new Exception("User model not initialized.");
            }
            
            $user = $this->models['user']->login($data['email'], $data['password']);
            if ($user) {
                // print_r($user);
                $_SESSION['id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['firstName'];
                $_SESSION['last_name'] = $user['lastName'];
                print_r($_SESSION);
                $this->redirect('dashboard', ['message' => 'Login successful']);
            } else {
                $this->view('auth/login', ['errors' => ['Invalid email or password'], 'email' => $data['email']]);
            }
        } else {
            if (isset($_SESSION['id'])) {
                $this->redirect('dashboard');
            } else {
                $this->view('auth/login');
            }
        }
    }

    /**
     * Handles user registration logic.
     *
     * @return void
     * @throws Exception To handle unexpected error occurs.
     * @throws PDOException If a database error occurs during registration.
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'first_name' => $this->sanitize($_POST['first_name']),
                'last_name' => $this->sanitize($_POST['last_name']),
                'email' => $this->sanitize($_POST['email']),
                'username' => $this->sanitize($_POST['username']),
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? ''
            ];

            if ($data['password'] !== $data['confirm_password']) {
                $this->view('auth/register', ['errors' => ['Passwords do not match.'], 'data' => $data]);
                return;
            }

            if (!isset($this->models['user'])) {
                throw new Exception("User model not initialized.");
            }

            try {
                $result = $this->models['user']->register(
                    $data['first_name'],
                    $data['last_name'],
                    $data['email'],
                    $data['username'],
                    $data['password']
                );

                if ($result === true) {
                    $this->redirect('login', ['message' => 'Registration successful! Please login.']);
                } else {
                    $this->view('auth/register', ['errors' => [$result], 'data' => $data]);
                }
            } catch (PDOException $e) {
                $this->view('auth/register', ['errors' => ['Database error: ' . $e->getMessage()], 'data' => $data]);
            } catch (Exception $e) {
                $this->view('auth/register', ['errors' => ['Unexpected error: ' . $e->getMessage()], 'data' => $data]);
            }
        } else {
            $this->view('auth/register');
        }
    }

    /**
     * Logs out the user by clearing the session and redirecting.
     *
     * @return void
     */
    public function logout() {
        $_SESSION = [];
        session_destroy();
        $this->redirect('login', ['message' => 'Logged out successfully']);
    }
}
?>