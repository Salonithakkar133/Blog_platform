<?php
$title = 'Login - Blog Platform';
require_once 'App/Views/Template/header.php';
$errors = $data['errors'] ?? [];
$email = $data['email'] ?? '';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']); // Clear message after display
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h1>Login</h1>
<form method="POST" action="index.php?page=login">
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary">Login</button>
    <p>Don't have an account? <a href="index.php?page=register">Register here</a></p>
</form>

<?php require_once 'App/Views/Template/footer.php'; ?>