<?php

class Blog {
    private $db;
    private $lastErrorInfo = [];

    public function __construct($db) {
        $this->db = $db;
    }
   
    /**
     * Retrieves all blog posts with optional filtering
     * 
     * @param int|null $userId Filter by author_id
     * @param string|array|null $status Filter by status one or more status
     * @return array Array of blog posts with author information
     */
    public function getAllBlogs($userId = null, $status = null) {
        $query = "SELECT b.*, u.firstName, u.lastName FROM blog b 
        JOIN user u ON b.author_id = u.id";
        
        $conditions = [];
        $params = [];
        
        if ($userId) {
            $conditions[] = "b.author_id = ?";
            $params[] = $userId;
        }
        
        if ($status) {
            if (is_array($status)) {
                $placeholders = implode(',', array_fill(0, count($status), '?'));
                $conditions[] = "b.status IN ($placeholders)";
                $params = array_merge($params, $status);
            } else {
                $conditions[] = "b.status = ?";
                $params[] = $status;
            }
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        
        error_log("Executing query: $query with params: " . print_r($params, true));
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Query returned ".count($result)." blogs");
        return $result;
    }

    /**
     * Retrieves a single blog post by ID
     * 
     * @param int $id Blog post ID
     * @return array|null Blog post data or null if not found
     */
    public function getBlogById($id) {
        $query = "SELECT b.*, u.firstName, u.lastName FROM blog b 
        JOIN user u ON b.author_id = u.id 
        WHERE b.id = :id";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->setErrorInfo($stmt->errorInfo());
            return $result;
        } catch (PDOException $e) {
            $this->setErrorInfo($e->getMessage());
            return null;
        }
    }

    /**
     * Creates a new blog post
     * 
     * @param string $title Blog post title
     * @param string $content Blog post content
     * @param int $authorId Author user ID
     * @param string $category Blog category
     * @param string $status Initial status
     * @param string $image Optional image path
     * @return bool True on success, false on failure
     */
    public function create($title, $content, $authorId, $category, $status, $image = '') {
        $query = "INSERT INTO blog (title, content, author_id, blog_category, status, image) 
        VALUES (:title, :content, :author_id, :category, :status, :image)";
        
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':title' => $title,
                ':content' => $content,
                ':author_id' => $authorId,
                ':category' => $category,
                ':status' => $status,
                ':image' => $image
            ]);
            $this->setErrorInfo($stmt->errorInfo());
            return $result;
        } catch (PDOException $e) {
            $this->setErrorInfo($e->getMessage());
            return false;
        }
    }

    /**
     * Updates an existing blog post
     * 
     * @param int $id Blog post ID
     * @param string $title New title
     * @param string $content New content
     * @param string $category New category
     * @param string $image New image path
     * @param string|null $status Optional new status
     * @return bool True on success, false on failure
     */
    public function update($id, $title, $content, $category, $image, $status = null) {
        $query = "UPDATE blog SET 
        title = :title, 
        content = :content, 
        blog_category = :category, 
        image = :image";
        
        $params = [
            ':id' => $id,
            ':title' => $title,
            ':content' => $content,
            ':category' => $category,
            ':image' => $image
        ];
        
        if ($status !== null) {
            $query .= ", status = :status";
            $params[':status'] = $status;
        }
        
        $query .= " WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($params);
            $this->setErrorInfo($stmt->errorInfo());
            return $result;
        } catch (PDOException $e) {
            $this->setErrorInfo($e->getMessage());
            return false;
        }
    }

    /**
     * Updates only the status of a blog post
     * 
     * @param int $id Blog post ID
     * @param string $status New status
     * @return bool True on success, false on failure
     */
    public function updateStatus($id, $status) {
        $query = "UPDATE blog SET status = :status WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':id' => $id,
                ':status' => $status
            ]);
            $rowCount = $stmt->rowCount();
            $this->setErrorInfo($stmt->errorInfo());
            if ($rowCount === 0) {
                $this->setErrorInfo(['00000', 0, 'No rows updated (possibly invalid ID)']);
                return false;
            }
            return $result;
        } catch (PDOException $e) {
            $this->setErrorInfo($e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a blog post
     * 
     * @param int $id Blog post ID to delete
     * @return bool True on success, false on failure
     */
    public function delete($id) {
        $query = "DELETE FROM blog WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([':id' => $id]);
            $this->setErrorInfo($stmt->errorInfo());
            return $result;
        } catch (PDOException $e) {
            $this->setErrorInfo($e->getMessage());
            return false;
        }
    }

    /**
     * Gets the last error information
     * 
     * @return array Last error information
     */
    public function getLastErrorInfo() {
        return $this->lastErrorInfo;
    }

    /**
     * Sets error information
     * 
     * @param mixed $errorInfo Error information to store
     * @return void
     */
    private function setErrorInfo($errorInfo) {
        $this->lastErrorInfo = $errorInfo;
    }
}