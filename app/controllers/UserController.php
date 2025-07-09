<?php
require_once 'Controller.php';

class UserController extends Controller {
    public function __construct($db = null) {
        parent::__construct($db);
    }

    public function userList() {
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['message'] = 'Unauthorized access.';
            $this->redirect('dashboard');
            return;
        }

        $users = $this->models['user']->getAllUsers();
        $this->view('users/list', ['users' => $users]);
    }
}
?>