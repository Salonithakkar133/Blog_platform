<?php
$title = 'User Dashboard - Blog Platform';
require_once 'app/views/template/header.php';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<h1>User Dashboard</h1>

<!-- Links to View Blogs -->
<h2>Your Blogs</h2>
<p><a href="index.php?page=published" class="btn btn-info">View Published Blogs</a></p>
<p><a href="index.php?page=pending" class="btn btn-warning">View Pending Blogs</a></p>
<p><a href="index.php?page=write" class="btn btn-primary">Write New Blog</a></p>

<?php require_once 'app/views/template/footer.php'; ?>