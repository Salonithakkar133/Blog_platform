<?php
$title = 'Write Blog - Blog Platform';
require_once 'app/views/template/header.php';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
$blog = null;
$blogController = new BlogController();

if (isset($_SESSION['edit_id'])) {
    $blog = $blogController->getBlogById($_SESSION['edit_id']);
    unset($_SESSION['edit_id']); // Clear after use to prevent reuse
}
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<h1><?php echo $blog ? 'Edit Blog' : 'Write New Blog'; ?></h1>
<form method="post" action="index.php?action=write" enctype="multipart/form-data">
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($blog['title'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
        <label for="content">Content</label>
        <textarea name="content" id="content" class="form-control" required><?php echo htmlspecialchars($blog['content'] ?? ''); ?></textarea>
    </div>
    <div class="form-group">
        <label for="blog_category">Category</label>
        <input type="text" name="blog_category" id="blog_category" class="form-control" value="<?php echo htmlspecialchars($blog['blog_category'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
        <label for="image">Image</label>
        <input type="file" name="image" id="image" class="form-control-file">
        <?php if ($blog && $blog['image']): ?>
            <p>Current Image: <img src="<?php echo htmlspecialchars($blog['image']); ?>" alt="Current" style="max-width: 200px;"></p>
        <?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary"><?php echo $blog ? 'Update Blog' : 'Submit Blog'; ?></button>
</form>

<?php require_once 'app/views/template/footer.php'; ?>