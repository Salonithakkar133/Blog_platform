<?php include 'app/views/template/header.php'; ?>
<div class="container">
    <h2>Assign User Role Edit</h2>
    <div id="message" class="message" style="display: none;"></div>
    
    <?php if ($user): ?>
    <form id="edit-role-form" method="POST" action="index.php?page=edit_user&id=<?php echo $user['id']; ?>">
        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
        <div>
            <label>Name</label>
            <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" disabled>
        </div>
        <div>
            <label>Email</label>
            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
        </div>
        <div>
            <label>Role</label>
            <select name="role" required>
                <option value="pending" <?php echo $user['role'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="employee" <?php echo $user['role'] === 'employee' ? 'selected' : ''; ?>>Employee</option>
                <option value="team_leader" <?php echo $user['role'] === 'team_leader' ? 'selected' : ''; ?>>Team Leader</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>
        <button type="submit">Update Role</button>
    </form>
    <?php else: ?>
    <p class="error">User not found.</p>
    <?php endif; ?>
    
    <p><a href="index.php?page=users">Back to Users</a></p>

    <script>
        document.getElementById('edit-role-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const messageDiv = document.getElementById('message');
            const submitButton = form.querySelector('button[type="submit"]');
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
            
            fetch(form.action, {
                method: 'POST',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                messageDiv.textContent = data.message;
                messageDiv.className = 'message ' + (data.success ? 'success' : 'error');
                messageDiv.style.display = 'block';
                
                if (data.success) {
                    setTimeout(() => {
                        window.location.href = 'index.php?page=users';
                    }, 1500);
                }
            })
            .catch(error => {
                messageDiv.textContent = 'Error updating role. Please try again.';
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = 'Update Role';
            });
        });
    </script>
</div>
<?php include 'app/views/template/footer.php'; ?>