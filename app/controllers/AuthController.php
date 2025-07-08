<?php
require_once 'Controller.php';

class AuthController extends Controller {
    public function __construct($db = null) {
        parent::__construct($db);
    }
   public function login() {
    error_log("enter login method: "); // Debug log
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'email' => $this->sanitize($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? ''
            ];

            $user = $this->models['user']->login($data['email'], $data['password']);
            if ($user) {
                $_SESSION['id'] = $user['id'];
                $_SESSION['email'] = $user['E_mail'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['First_name'];
                $_SESSION['last_name'] = $user['Last_name'];
                $this->redirect('dashboard', ['message' => 'Login successful']);
            } else {
                error_log("Login failed for email: " . $data['email']); // Debug log
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

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'first_name' => $this->sanitize($_POST['first_name']),
                'last_name' => $this->sanitize($_POST['last_name']),
                'email' => $this->sanitize($_POST['email']),
                'username' => $this->sanitize($_POST['username']),
                'password' => $_POST['password']
            ];

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
                error_log("Registration Error: " . $e->getMessage());
                $this->view('auth/register', ['errors' => ['Database error'], 'data' => $data]);
            }
        } else {
            $this->view('auth/registration');
        }
    }

    public function logout() {
        $_SESSION = array();
        session_destroy();
        $this->redirect('login', ['message' => 'Logged out successfully']);
    }
}
?>