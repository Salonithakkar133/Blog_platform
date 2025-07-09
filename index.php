<?php
session_start();

// Error logging setup
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0775, true);
}
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/application.log');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once 'app/controllers/AuthController.php';
require_once 'app/controllers/Controller.php';
require_once 'app/controllers/BlogController.php';

$authController = new AuthController();
$controller = new Controller();
$blogController = new BlogController();

// ✅ Handle blog actions (write/edit/delete/approve/reject)
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    try {
        // ✅ Handle 'write' blog submission (no ID)
        if ($action === 'write' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $blogController->handleAction('write');
            exit;
        }

        // ✅ Handle other blog actions with or without ID
        $handled = $blogController->handleAction($action, $id);

        // ✅ Fallback for GET-based edit (if not handled internally)
        if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'GET' && !$handled) {
            $blog = $blogController->getBlogById($id);
            if ($blog) {
                $_SESSION['edit_blog_data'] = $blog;
                header("Location: index.php?page=edit");
                exit;
            } else {
                $_SESSION['message'] = 'Blog not found.';
                header("Location: index.php?page=dashboard");
                exit;
            }
        }
    } catch (Exception $e) {
        error_log(date('[Y-m-d H:i:s] ') . "Action failed: " . $e->getMessage() . ", Trace: " . $e->getTraceAsString() . PHP_EOL, 3, __DIR__ . '/logs/application.log');
        $_SESSION['message'] = 'An error occurred. Please try again.';
        header("Location: index.php?page=dashboard");
        exit;
    }
    exit;
}

// ✅ Default page routing
if (!isset($_GET['page']) || empty(trim($_GET['page']))) {
    if (isset($_SESSION['id'])) {
        header("Location: index.php?page=dashboard");
    } else {
        header("Location: index.php?page=login");
    }
    exit;
}

$page = trim($_GET['page']);

switch ($page) {
    case 'login':
        if (isset($_SESSION['id'])) {
            header("Location: index.php?page=dashboard");
            exit;
        }
        $authController->login();
        break;

    case 'register':
        $authController->register();
        break;

    case 'logout':
        $authController->logout();
        break;

    case 'dashboard':
        if (!isset($_SESSION['id'])) {
            header("Location: index.php?page=login");
            exit;
        }
        $role = $_SESSION['role'] === 'admin' ? 'admin' : 'user';
        include_once "app/views/dashboard/$role.php";
        break;

    case 'published':
        if (!isset($_SESSION['id'])) {
            header("Location: index.php?page=login");
            exit;
        }
        $blogs = $_SESSION['role'] === 'admin' 
            ? $blogController->getBlogs(null, 'approved') 
            : $blogController->getBlogs($_SESSION['id'], 'approved');
        $_SESSION['blogs'] = $blogs;
        include_once "app/views/blogs/publish.php";
        break;

    case 'pending':
        if (!isset($_SESSION['id'])) {
            header("Location: index.php?page=login");
            exit;
        }
        $blogs = $blogController->getBlogs($_SESSION['id'], 'pending');
        if ($_SESSION['role'] === 'admin') {
            $allPending = $blogController->getBlogs(null, ['pending', 'rejected']);
            $blogs = array_merge($blogs, $allPending);
            $blogs = array_unique($blogs, SORT_REGULAR);
        }
        $_SESSION['blogs'] = $blogs;
        include_once "app/views/blogs/pending.php";
        break;

    case 'write':
        if (!isset($_SESSION['id'])) {
            header("Location: index.php?page=login");
            exit;
        }
        include_once "app/views/write.php";
        break;

    case 'edit':
        if (!isset($_SESSION['id'])) {
            header("Location: index.php?page=login");
            exit;
        }
        include_once "app/views/blogs/edit.php";
        break;


        

    default:
        if (isset($_SESSION['id'])) {
            header("Location: index.php?page=dashboard");
        } else {
            header("Location: index.php?page=login");
        }
        exit;
}
