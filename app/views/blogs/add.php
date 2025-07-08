<?php require_once 'layout.php'; ?>
<h1>Create Blog</h1>
<form id="blogForm" action="index.php?action=createBlog" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" name="title" required>
    </div>
    <div class="mb-3">
        <label for="content" class="form-label">Content</label>
        <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
    </div>
    <div class="mb-3">
        <label for="blog_category" class="form-label">Category</label>
        <select class="form-control" id="blog_category" name="blog_category" required>
            <option value="">Select Category</option>
            <option value="Technology">Technology</option>
            <option value="Lifestyle">Lifestyle</option>
            <option value="Education">Education</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="image" class="form-label">Image</label>
        <input type="file" class="form-control" id="image" name="image">
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
</body>
</html>