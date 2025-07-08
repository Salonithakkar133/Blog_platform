<?php
$title = 'Pending Blogs - Blog Platform';
require_once 'app/views/template/header.php';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
$blogs = $_SESSION['blogs'] ?? [];
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<h1>Pending Blogs</h1>
<?php if (empty($blogs)): ?>
    <p>No pending or rejected blogs found.</p>
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
                        <?php if ($blog['status'] === 'pending'): ?>
                            <a href="index.php?action=approve&id=<?php echo $blog['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                            <a href="index.php?action=reject&id=<?php echo $blog['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                        <?php elseif ($blog['status'] === 'rejected'): ?>
                            <a href="index.php?page=pending&action=delete&id=<?php echo $blog['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                        <?php endif; ?>
                    <?php elseif ($_SESSION['role'] === 'user' && $blog['author_id'] == $_SESSION['id'] && $blog['status'] === 'pending'): ?>
                        <a href="index.php?page=write&action=edit&id=<?php echo $blog['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="index.php?page=dashboard&action=delete&id=<?php echo $blog['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once 'app/views/template/footer.php'; ?>