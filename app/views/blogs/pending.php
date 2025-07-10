<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php
$title = 'Pending/Rejected Blogs - Blog Platform';
require_once 'App/Views/Template/header.php';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
$blogController = new BlogController();
if ($_SESSION['role'] === 'admin') {
    $blogs = $blogController->getBlogs(null, ['pending', 'rejected']);
} else {
    $blogs = $blogController->getBlogs($_SESSION['id'], ['pending', 'rejected']);
}
?>
<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<h1>Pending/Rejected Blogs</h1>
<?php if (empty($blogs)): ?>
    <p>No pending or rejected blogs found.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Content</th>
                <th>Author</th>
                <th>Category</th>
                <th>Status</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($blogs as $blog): ?>
            <tr>
                <td><?php echo htmlspecialchars($blog['title']); ?></td>
                <td><?php echo htmlspecialchars($blog['content']); ?></td>
                <td><?php echo htmlspecialchars($blog['firstName'] . ' ' . $blog['lastName']); ?></td>
                <td><?php echo htmlspecialchars($blog['blog_category']); ?></td>
                <td><?php echo htmlspecialchars($blog['status']); ?></td>
                <td><?php if (!empty($blog['image'])): ?><img src="<?php echo htmlspecialchars($blog['image']); ?>" width="150"><?php endif; ?></td>
                <td>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <?php if ($blog['status'] === 'pending' || $blog['status'] === 'rejected'): ?>
                            <a href="index.php?page=edit&action=edit&id=<?php echo $blog['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="index.php?action=approve&id=<?php echo $blog['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                            <a href="index.php?action=reject&id=<?php echo $blog['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                            <a href="index.php?action=delete&id=<?php echo $blog['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this blog?')">Delete</a>
                        <?php endif; ?>
                    <?php elseif ($blog['author_id'] == $_SESSION['id']): ?>
    <?php if ($blog['status'] === 'rejected'): ?>
        <a href="index.php?action=delete&id=<?php echo $blog['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this rejected blog?')">Delete</a>
    <?php else: ?>
        <a href="index.php?page=edit&action=edit&id=<?php echo $blog['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
        <a href="index.php?action=delete&id=<?php echo $blog['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this blog?')">Delete</a>
    <?php endif; ?>
<?php endif; ?>

                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once 'App/Views/Template/footer.php'; ?>