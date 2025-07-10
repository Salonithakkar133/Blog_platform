<?php
// namespace App\Models;
// use Exception;
// use PDO;
// use PDOException;

class User {
    private $conn;
    private $table_name = 'user'; // Matches the confirmed table name

    /**
     * User constructor.
     *
     * @param PDO $db PDO database connection
     * @throws Exception if database connection is invalid
     */
    public function __construct($db) {
        if (!$db || !$db->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
            throw new Exception("Invalid database connection");
        }
        $this->conn = $db;
    }

    /**
     * Registers a new user in the system.
     *
     * @param string $first_name The user's first name
     * @param string $last_name The user's last name
     * @param string $email The user's email address
     * @param string $username The user's chosen username
     * @param string $password The user's password
     * @return bool|string True on success, error message on failure
     * @throws PDOException if query fails
     */
    public function register($first_name, $last_name, $email, $username, $password) {
        if ($this->getUserByEmail($email)) {
            return 'Email already exists';
        }
        if ($this->getUserByUsername($username)) {
            return 'Username already exists';
        }

        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO {$this->table_name} (firstName, lastName, email, username, password, role, created_at) 
            VALUES (:firstName, :lastName, :email, :username, :password, :role, NOW())";
            $stmt = $this->conn->prepare($query);
            $params = [
                'firstName' => $first_name,
                'lastName' => $last_name,
                'email' => $email,
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

    /**
     * Attempts to log a user in with email and password.
     *
     * @param string $email The user's email address
     * @param string $password The user's password
     * @return array|false User data array on success, false on failure
     * @throws PDOException if query fails
     */
    public function login($email, $password) {
        try {
            //Added here username as it was not saved in session 
            $query = "SELECT id, firstName, lastName, email AS email, role,username, password FROM user WHERE email = :email";
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

    /**
     * Retrieves user data by email.
     *
     * @param string $email The user's email address
     * @return array|null User data or null if not found
     */
    public function getUserByEmail($email) {
        $query = "SELECT * FROM {$this->table_name} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves user data by username.
     *
     * @param string $username The user's username
     * @return array|null User data or null if not found
     */
    public function getUserByUsername($username) {
        $query = "SELECT * FROM {$this->table_name} WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>