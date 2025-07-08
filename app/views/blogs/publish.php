<?php
$title = 'Published Blogs - Blog Platform';
require_once 'app/views/template/header.php';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
$blogs = $_SESSION['blogs'] ?? [];
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
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($blogs as $blog): ?>
            <tr>
                <td><?php echo htmlspecialchars($blog['title']); ?></td>
                <td><?php echo htmlspecialchars($blog['First_name'] . ' ' . $blog['Last_name']); ?></td>
                <td><?php echo htmlspecialchars($blog['blog_category']); ?></td>
                <td><?php echo htmlspecialchars($blog['status']); ?></td>
                <td>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="index.php?page=write&action=edit&id=<?php echo $blog['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                          <a href="index.php?page=write&action=edit&id=<?php echo $blog['id']; ?>" class="btn btn-primary btn-sm">Delete</a>
                    <?php elseif ($_SESSION['role'] === 'user' && $blog['author_id'] == $_SESSION['id']): ?>
                        <!-- No edit for users on published blogs -->
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once 'app/views/template/footer.php'; ?>