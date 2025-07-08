<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) : 'Blog Platform'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php?page=dashboard">Blog Platform</a>
            <?php if (isset($_SESSION['id'])): ?>
                <span class="navbar-text">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</span>
                <a href="index.php?page=logout" class="btn btn-outline-danger ms-2">Logout</a>
            <?php else: ?>
                <a href="index.php?page=login" class="btn btn-outline-primary">Login</a>
                <a href="index.php?page=register" class="btn btn-outline-success">Register</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container mt-4">
<?php
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-info">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}
?>