<?php
$title = 'Published Blogs - Blog Platform';
require_once 'App/Views/Template/header.php';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
$blogs = $_SESSION['blogs'] ?? [];

if (!isset($blogs)) {
    $blogController = new BlogController();
    $blogs = $blogController->getBlogs(null, 'approved');
    $_SESSION['blogs'] = $blogs;
}
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<h1>Published Blogs</h1>
<?php if (empty($blogs)): ?>
    <p>No published blogs found.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
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
                <td><?php echo htmlspecialchars($blog['firstName'] . ' ' . $blog['lastName']); ?></td>
                <td><?php echo htmlspecialchars($blog['blog_category']); ?></td>
                <td><?php echo htmlspecialchars($blog['status']); ?></td>
                <td><?php if (!empty($blog['image'])): ?><img src="<?php echo htmlspecialchars($blog['image']); ?>" width="150"><?php endif; ?></td>
                <td>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="index.php?page=edit&action=edit&id=<?php echo $blog['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="index.php?action=delete&id=<?php echo $blog['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this blog?')">Delete</a>
                    <?php elseif ($_SESSION['role'] === 'user' && $blog['author_id'] == $_SESSION['id']): ?>
                        <a href="index.php?page=edit&action=edit&id=<?php echo $blog['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="index.php?action=delete&id=<?php echo $blog['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this blog?')">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once 'App/Views/Template/footer.php'; ?>