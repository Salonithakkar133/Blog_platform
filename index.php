<?php
require_once 'App/Controllers/AuthController.php';
require_once 'App/Controllers/Controller.php';
require_once 'App/Controllers/BlogController.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$authController = new AuthController();
$controller = new Controller();
$blogController = new BlogController();

// Handle karse  blog actions (write/edit/delete/approve/reject)
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    try {
        // Handle 'write' karse after  blog submission (no ID needed)
        if ($action === 'write' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $blogController->handleAction('write');
            exit;
        }

        //Handle other blog actions with or without ID
        $handled = $blogController->handleAction($action, $id);

        //next time edit goes for approve to admin 
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
        header("Location: index.php?page=dashboard");
        exit;
    }
    exit;
}
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
        include_once "App/Views/Dashboard/$role.php";
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
        include_once "App/Views/Blogs/Publish.php";
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
        include_once "App/Views/Blogs/Pending.php";
        break;

    case 'write':
        if (!isset($_SESSION['id'])) {
            header("Location: index.php?page=login");
            exit;
        }
        include_once "App/Views/Write.php";
        break;

    case 'edit':
        if (!isset($_SESSION['id'])) {
            header("Location: index.php?page=login");
            exit;
        }
        include_once "App/Views/Blogs/Edit.php";
        break;


    default:
        if (isset($_SESSION['id'])) {
            header("Location: index.php?page=dashboard");
        } else {
            header("Location: index.php?page=login");
        }
        exit;
}
