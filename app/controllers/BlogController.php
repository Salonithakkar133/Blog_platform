<?php
require_once 'Controller.php';

class BlogController extends Controller {
    public function __construct($db = null) {
        parent::__construct($db);
    }

    public function getBlogs($userId = null, $status = null) {
        try {
            $blogs = $this->getModel('blog')->getAllBlogs($userId, $status);
            
            if ($userId && !$status) {
                $pendingBlogs = $this->getModel('blog')->getAllBlogs($userId, 'pending');
                // Fix array merging
                $blogs = array_merge($blogs, $pendingBlogs);
                // Remove duplicates while preserving keys
                $uniqueIds = [];
                $blogs = array_filter($blogs, function($blog) use (&$uniqueIds) {
                    if (in_array($blog['id'], $uniqueIds)) {
                        return false;
                    }
                    $uniqueIds[] = $blog['id'];
                    return true;
                });
            }
            
            return array_values($blogs); // Re-index array
        } catch (Exception $e) {
            error_log("Error in getBlogs: " . $e->getMessage());
            return [];
        }
    }

    public function getBlogById($id) {
        try {
            $blog = $this->getModel('blog')->getBlogById($id);
            if (!$blog) {
                error_log("Blog not found with ID: " . $id);
            }
            return $blog;
        } catch (Exception $e) {
            error_log("Error in getBlogById: " . $e->getMessage());
            return null;
        }
    }

    public function handleAction($action, $id = null) {
        try {
            $this->requireAuth();
            
            if ($action === 'write' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                // ... existing write handling code ...
            }
            elseif ($action === 'edit') {
                return $this->handleEditAction($id);
            }
            // ... other action handlers ...
            
        } catch (Exception $e) {
            error_log("Error in handleAction: " . $e->getMessage());
            $_SESSION['message'] = 'An error occurred. Please try again.';
            $this->redirect('dashboard');
        }
    }

    private function handleEditAction($id) {
        $blog = $this->getBlogById($id);
        
        if (!$blog) {
            $_SESSION['message'] = 'Blog not found.';
            $this->redirect('dashboard');
            return false;
        }

        // Permission check
        if ($_SESSION['role'] !== 'admin' && $blog['author_id'] != $_SESSION['id']) {
            $_SESSION['message'] = 'Unauthorized action.';
            $this->redirect('dashboard');
            return false;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $_SESSION['edit_blog_data'] = $blog;
            $this->redirect('write');
            return true;
        } 
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $this->sanitize($_POST['title'] ?? '');
            $content = $this->sanitize($_POST['content'] ?? '');
            $blog_category = $this->sanitize($_POST['blog_category'] ?? '');
            
            // Handle image upload or keep existing
            $image = $_FILES['image']['name'] 
                ? $this->handleImageUpload($_FILES['image']) 
                : ($_POST['existing_image'] ?? '');

            // Validate inputs
            if (empty($title) || empty($content) || empty($blog_category)) {
                $_SESSION['message'] = 'Missing required fields.';
                $this->redirect('write');
                return false;
            }

            // Determine status (admin keeps current, user sets to pending if published)
            $status = ($_SESSION['role'] === 'admin') 
                ? $blog['status'] 
                : ($blog['status'] === 'approved' ? 'pending' : $blog['status']);

            // Update blog
            if ($this->getModel('blog')->update($id, $title, $content, $blog_category, $image, $status)) {
                $_SESSION['message'] = 'Blog updated successfully!';
                unset($_SESSION['edit_blog_data'], $_SESSION['blogs']);
            } else {
                $_SESSION['message'] = 'Failed to update blog.';
            }
            
            $this->redirect('dashboard');
            return true;
        
    elseif ($action === 'delete') {
            $blog = $this->getBlogById($id);
            if (!$blog) {
                $_SESSION['message'] = 'Blog not found.';
                $this->redirect('dashboard');
                return;
            }

            $this->log("Delete attempt for id=$id, status={$blog['status']}, author_id={$blog['author_id']}");
            if ($_SESSION['role'] !== 'admin' && $blog['author_id'] != $_SESSION['id']) {
                $_SESSION['message'] = 'Unauthorized action.';
                $this->redirect('dashboard');
                return;
            }

            if ($this->getModel('blog')->delete($id)) {
                $_SESSION['message'] = 'Blog deleted successfully!';
                $this->log("Blog deleted successfully for id=$id");
                unset($_SESSION['blogs']);
            } else {
                $errorInfo = $this->getModel('blog')->getLastErrorInfo();
                $_SESSION['message'] = 'Failed to delete blog.';
                $this->log("Blog delete failed: " . print_r($errorInfo, true));
            }
            $this->redirect('dashboard');
        } elseif ($action === 'approve' && $_SESSION['role'] === 'admin') {
            $this->log("Attempting to approve blog id=$id");
            if ($this->getModel('blog')->updateStatus($id, 'approved')) {
                $_SESSION['message'] = 'Blog approved!';
                unset($_SESSION['blogs']);
                $this->redirect('published');
            } else {
                $errorInfo = $this->getModel('blog')->getLastErrorInfo();
                $_SESSION['message'] = 'Failed to approve blog.';
                $this->log("Approve failed: " . print_r($errorInfo, true));
                $this->redirect('pending');
            }
        } elseif ($action === 'reject' && $_SESSION['role'] === 'admin') {
            $this->log("Attempting to reject blog id=$id");
            if ($this->getModel('blog')->updateStatus($id, 'rejected')) {
                $_SESSION['message'] = 'Blog rejected!';
                unset($_SESSION['blogs']);
                $this->redirect('pending');
            } else {
                $errorInfo = $this->getModel('blog')->getLastErrorInfo();
                $_SESSION['message'] = 'Failed to reject blog.';
                $this->log("Reject failed: " . print_r($errorInfo, true));
                $this->redirect('pending');
            }
        } else {
            $this->log("Unknown action: $action for id=$id");
            $_SESSION['message'] = 'Invalid action requested.';
            $this->redirect('dashboard');
        }
    }}
 private function handleImageUpload($file) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }
        $targetFile = $targetDir . uniqid() . '_' . basename($file["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($imageFileType, $allowedTypes)) {
            $this->log("Invalid image type: $imageFileType");
            return '';
        }

        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            $this->log("Image uploaded to: $targetFile");
            return $targetFile;
        } else {
            $this->log("Image upload failed: " . print_r(error_get_last(), true));
            return '';
        }
    }

    private function log($message) {
        error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, __DIR__ . '/../logs/blog_controller.log');
    }
}
