<?php
$title = 'Admin Dashboard - Blog Platform';
require_once 'app/views/template/header.php';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<h1>Admin Dashboard</h1>

<!-- Links to Manage Blogs -->
<h2>Manage Blogs</h2>
<p><a href="index.php?page=published" class="btn btn-info">View Published Blogs</a></p>
<p><a href="index.php?page=pending" class="btn btn-warning">View Pending Blogs</a></p>
<!-- <p><a href="index.php?page=write" class="btn btn-primary">Write New Blog</a></p> -->

<?php require_once 'App/Views/Template/footer.php'; ?>