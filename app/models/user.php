<?php
class User {
    private $conn;
    private $table_name = 'user';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($first_name, $last_name, $email, $username, $password) {
        if ($this->getUserByEmail($email)) {
            return 'Email already exists';
        }
        if ($this->getUserByUsername($username)) {
            return 'Username already exists';
        }

        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO {$this->table_name} (First_name, Last_name, E_mail, username, password, role, created_at) 
            VALUES (:First_name, :Last_name, :E_mail, :username, :password, :role, NOW())";
            $stmt = $this->conn->prepare($query);
            $params = [
                'First_name' => $first_name,
                'Last_name' => $last_name,
                'E_mail' => $email,
                'username' => $username,
                'password' => $hashedPassword,
                'role' => 'user'
            ];
            $result = $stmt->execute($params);
            return $result;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function login($email, $password) {
        try {
            $query = "SELECT id, First_name, Last_name, E_mail AS email, role, password FROM user WHERE E_mail = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                unset($user['password']);
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
    

    public function getUserByEmail($email) {
        $query = "SELECT * FROM {$this->table_name} WHERE E_mail = :E_mail LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['E_mail' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserByUsername($username) {
        $query = "SELECT * FROM {$this->table_name} WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}


?>