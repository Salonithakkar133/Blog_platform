<?php
class Blog {
    private $db;
    private $lastErrorInfo = [];

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllBlogs($userId = null, $status = null) {
        $query = "SELECT b.*, u.First_name, u.Last_name FROM blog b JOIN user u ON b.author_id = u.id WHERE 1=1";
        $params = [];
        
        if ($userId) {
            $query .= " AND b.author_id = :userId";
            $params[':userId'] = $userId;
        }
        
        if ($status) {
            if (is_array($status)) {
                $placeholders = implode(',', array_fill(0, count($status), '?'));
                $query .= " AND b.status IN ($placeholders)";
                $params = array_merge($params, $status);
            } else {
                $query .= " AND b.status = :status";
                $params[':status'] = $status;
            }
        }
        
        $query .= " ORDER BY b.created_at DESC";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $this->setErrorInfo($stmt->errorInfo());
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->setErrorInfo($e->getMessage());
            return [];
        }
    }

    public function getBlogById($id) {
        $query = "SELECT b.*, u.First_name, u.Last_name FROM blog b JOIN user u ON b.author_id = u.id WHERE b.id = :id";
        
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

    public function update($id, $title, $content, $category, $image, $status = null) {
        $query = "UPDATE blog SET 
                 title = :title, 
                 content = :content, 
                 blog_category = :category, 
                 image = :image";
        
        if ($status !== null) {
            $query .= ", status = :status";
        }
        
        $query .= " WHERE id = :id";
        
        try {
            $params = [
                ':id' => $id,
                ':title' => $title,
                ':content' => $content,
                ':category' => $category,
                ':image' => $image
            ];
            
            if ($status !== null) {
                $params[':status'] = $status;
            }
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($params);
            $this->setErrorInfo($stmt->errorInfo());
            return $result;
        } catch (PDOException $e) {
            $this->setErrorInfo($e->getMessage());
            return false;
        }
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE blog SET status = :status WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':id' => $id,
                ':status' => $status
            ]);
            $this->setErrorInfo($stmt->errorInfo());
            return $result;
        } catch (PDOException $e) {
            $this->setErrorInfo($e->getMessage());
            return false;
        }
    }

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

    public function getLastErrorInfo() {
        return $this->lastErrorInfo;
    }

    private function setErrorInfo($errorInfo) {
        $this->lastErrorInfo = $errorInfo;
        if (!empty($errorInfo) && $errorInfo[0] !== '00000') {
            error_log(date('[Y-m-d H:i:s] ') . "Blog Model Error: " . print_r($errorInfo, true) . PHP_EOL, 3, __DIR__ . '/../logs/blog_model.log');
        }
    }
}