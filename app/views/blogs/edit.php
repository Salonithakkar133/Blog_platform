<?php
require_once 'App/Views/Template/header.php';
// Fetch blog from session set in controller
$blog = $_SESSION['edit_blog_data'] ?? null;
if (!$blog) {
    echo "<p>No blog data found for editing.</p>";
    require_once 'App/Views/Template/footer.php';
    return;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Blog</title>
</head>
<body>

<h2>Edit Blog</h2>

<form method="POST" action="index.php?action=edit&id=<?= $blog['id'] ?>" enctype="multipart/form-data">
    
    <label>Title:</label><br>
    <input type="text" name="title" value="<?= htmlspecialchars($blog['title']) ?>" required><br><br>

    <label>Content:</label><br>
    <textarea name="content" rows="8" cols="50" required><?= htmlspecialchars($blog['content']) ?></textarea><br><br>

    <label>Category:</label><br>
    <select name="blog_category" required>
        <?php
        $categories = ['Technology', 'Health', 'Travel', 'Education'];
        foreach ($categories as $cat): ?>
            <option value="<?= $cat ?>" <?= ($blog['blog_category'] === $cat) ? 'selected' : '' ?>>
                <?= $cat ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Current Image:</label><br>
    <?php if (!empty($blog['image'])): ?>
        <img src="<?= $blog['image'] ?>" width="150"><br>
        <input type="hidden" name="existing_image" value="<?= $blog['image'] ?>">
    <?php endif; ?>
    <input type="file" name="image"><br><br>

    <button type="submit">Update Blog</button>
    <?php if ($_SESSION['role'] === 'admin' && $blog['status'] === 'rejected'): ?>
        <button type="submit" name="approve" value="1" class="btn btn-success">Approve</button>
    <?php endif; ?>
</form>

</body>
</html>

<?php require_once 'App/Views/Template/footer.php'; ?>