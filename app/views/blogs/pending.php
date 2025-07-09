<?php
$title = 'Pending/Rejected Blogs - Blog Platform';
require_once 'app/views/template/header.php';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
$blogs = $_SESSION['blogs'] ?? [];

if (!isset($blogs)) {
    $blogController = new BlogController();
    $blogs = array_merge(
        $blogController->getBlogs(null, 'pending'),
        $blogController->getBlogs(null, 'rejected')
    );
    $_SESSION['blogs'] = $blogs;
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
                <td><?php echo htmlspecialchars($blog['First_name'] . ' ' . $blog['Last_name']); ?></td>
                <td><?php echo htmlspecialchars($blog['blog_category']); ?></td>
                <td><?php echo htmlspecialchars($blog['status']); ?></td>
                <td><?php if (!empty($blog['image'])): ?><img src="<?php echo htmlspecialchars($blog['image']); ?>" width="150"><?php endif; ?></td>
                <td>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <?php if ($blog['status'] === 'pending'): ?>
                            <a href="index.php?page=edit&action=edit&id=<?php echo $blog['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="index.php?action=approve&id=<?php echo $blog['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                            <a href="index.php?action=reject&id=<?php echo $blog['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                        <?php elseif ($blog['status'] === 'rejected'): ?>
                            <a href="index.php?page=edit&action=edit&id=<?php echo $blog['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="index.php?action=approve&id=<?php echo $blog['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                            <a href="index.php?action=reject&id=<?php echo $blog['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                        <?php endif; ?>
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

<?php require_once 'app/views/template/footer.php'; ?>