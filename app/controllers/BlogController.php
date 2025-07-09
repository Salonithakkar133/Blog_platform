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
                $rejectedBlogs = $this->getModel('blog')->getAllBlogs($userId, 'rejected');
                $blogs = array_merge($blogs, $pendingBlogs, $rejectedBlogs);
                $uniqueIds = [];
                $blogs = array_filter($blogs, function($blog) use (&$uniqueIds) {
                    if (in_array($blog['id'], $uniqueIds)) {
                        return false;
                    }
                    $uniqueIds[] = $blog['id'];
                    return true;
                });
            }

            return array_values($blogs);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getBlogById($id) {
        try {
            $blog = $this->getModel('blog')->getBlogById($id);
            if (!$blog) {
                return null;
            }
            return $blog;
        } catch (Exception $e) {
            return null;
        }
    }

    public function handleAction($action, $id = null) {
        try {
            $this->requireAuth();

            if ($action === 'write' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $title = $this->sanitize($_POST['title'] ?? '');
                $content = $this->sanitize($_POST['content'] ?? '');
                $blog_category = $this->sanitize($_POST['blog_category'] ?? '');
                $image = $_FILES['image']['name'] ? $this->handleImageUpload($_FILES['image']) : '';

                if (empty($title) || empty($content) || empty($blog_category)) {
                    $_SESSION['message'] = 'Missing required fields.';
                    $this->redirect('write');
                    return;
                }

                if ($this->getModel('blog')->create($title, $content, $_SESSION['id'], $blog_category, 'pending', $image)) {
                    $_SESSION['message'] = 'Blog submitted for approval!';
                    unset($_SESSION['blogs']);
                } else {
                    $_SESSION['message'] = 'Failed to submit blog. Please try again.';
                }
                $this->redirect($_SESSION['role'] === 'admin' ? 'admin_dashboard' : 'user_dashboard');
            }
            elseif ($action === 'edit') {
                return $this->handleEditAction($id);
            }
            elseif ($action === 'delete') {
                return $this->handleDeleteAction($id);
            }
            elseif ($action === 'approve' && $_SESSION['role'] === 'admin') {
                return $this->handleApproveAction($id);
            }
            elseif ($action === 'reject' && $_SESSION['role'] === 'admin') {
                return $this->handleRejectAction($id);
            }
            else {
                $_SESSION['message'] = 'Invalid action requested.';
                $this->redirect($_SESSION['role'] === 'admin' ? 'admin_dashboard' : 'user_dashboard');
            }

        } catch (Exception $e) {
            $_SESSION['message'] = 'An error occurred. Please try again.';
            $this->redirect($_SESSION['role'] === 'admin' ? 'admin_dashboard' : 'user_dashboard');
        }
    }

    private function handleEditAction($id) {
        $blog = $this->getBlogById($id);

        if (!$blog) {
            $_SESSION['message'] = 'Blog not found.';
            $this->redirect($_SESSION['role'] === 'admin' ? 'admin_dashboard' : 'user_dashboard');
            return false;
        }

        if ($_SESSION['role'] !== 'admin' && $blog['author_id'] != $_SESSION['id']) {
            $_SESSION['message'] = 'Unauthorized action.';
            $this->redirect($_SESSION['role'] === 'admin' ? 'admin_dashboard' : 'user_dashboard');
            return false;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $_SESSION['edit_blog_data'] = $blog;
            $this->redirect('edit');
            return true;
        }
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $this->sanitize($_POST['title'] ?? '');
            $content = $this->sanitize($_POST['content'] ?? '');
            $blog_category = $this->sanitize($_POST['blog_category'] ?? '');
            $image = $_FILES['image']['name'] 
                ? $this->handleImageUpload($_FILES['image']) 
                : ($_POST['existing_image'] ?? '');

            if (empty($title) || empty($content) || empty($blog_category)) {
                $_SESSION['message'] = 'Missing required fields.';
                $this->redirect('edit');
                return false;
            }

            $status = $blog['status'];
            if ($_SESSION['role'] === 'admin' && isset($_POST['approve'])) {
                $status = 'approved';
                $_SESSION['message'] = 'Blog approved and published successfully!';
            } elseif ($blog['status'] === 'rejected' && $_SESSION['role'] !== 'admin') {
                $status = 'pending';
                $_SESSION['message'] = 'Blog updated and submitted for review!';
            } else {
                $_SESSION['message'] = 'Blog updated successfully!';
            }

            if ($this->getModel('blog')->update($id, $title, $content, $blog_category, $image, $status)) {
                unset($_SESSION['edit_blog_data'], $_SESSION['blogs']);
                if ($_SESSION['role'] === 'admin' && $status === 'approved') {
                    $this->redirect('pending');
                } else {
                    $this->redirect($_SESSION['role'] === 'admin' ? 'admin_dashboard' : 'user_dashboard');
                }
            } else {
                $_SESSION['message'] = 'Failed to update blog.';
                $this->redirect('edit');
            }
            return true;
        }
    }

    private function handleDeleteAction($id) {
        $blog = $this->getBlogById($id);
        if (!$blog) {
            $_SESSION['message'] = 'Blog not found.';
            $this->redirect($_SESSION['role'] === 'admin' ? 'admin_dashboard' : 'user_dashboard');
            return false;
        }

        if ($_SESSION['role'] !== 'admin' && $blog['author_id'] != $_SESSION['id']) {
            $_SESSION['message'] = 'Unauthorized action.';
            $this->redirect($_SESSION['role'] === 'admin' ? 'admin_dashboard' : 'user_dashboard');
            return false;
        }

        if ($this->getModel('blog')->delete($id)) {
            $_SESSION['message'] = 'Blog deleted successfully!';
            unset($_SESSION['blogs']);
        } else {
            $_SESSION['message'] = 'Failed to delete blog.';
        }
        $this->redirect($_SESSION['role'] === 'admin' ? 'admin_dashboard' : 'user_dashboard');
        return true;
    }

    private function handleApproveAction($id) {
        $blog = $this->getBlogById($id);
        if (!$blog) {
            $_SESSION['message'] = 'Blog not found.';
            $this->redirect('pending');
            return false;
        }

        if ($this->getModel('blog')->updateStatus($id, 'approved')) {
            $_SESSION['message'] = 'Blog approved!';
            unset($_SESSION['blogs']);
            $this->redirect('pending');
        } else {
            $_SESSION['message'] = 'Failed to approve blog. Error: Unknown error';
            $this->redirect('pending');
        }
        return true;
    }

    private function handleRejectAction($id) {
        if ($this->getModel('blog')->updateStatus($id, 'rejected')) {
            $_SESSION['message'] = 'Blog rejected!';
            unset($_SESSION['blogs']);
            $this->redirect('pending');
        } else {
            $_SESSION['message'] = 'Failed to reject blog.';
            $this->redirect('pending');
        }
        return true;
    }

    private function handleImageUpload($file) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }
        $targetFile = $targetDir . uniqid() . '_' . basename($file["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($imageFileType, $allowedTypes)) {
            return '';
        }

        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            return $targetFile;
        } else {
            return '';
        }
    }
}
?>