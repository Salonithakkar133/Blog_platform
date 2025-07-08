<?php include 'app/views/template/header.php'; ?>
<div class="container">
    <h2>My Profile</h2>
    
    <!-- Message container for both AJAX and regular messages -->
    <div id="messageContainer">
        <?php if (!empty($message)): ?>
            <p class="<?php echo strpos(strtolower($message), 'failed') !== false || strpos(strtolower($message), 'already') !== false || strpos(strtolower($message), 'password') !== false ? 'error' : 'message'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>
    </div>
    
    <?php if (empty($user) || !is_array($user)): ?>
        <p class="error">Error: User data not found. Please try logging in again.</p>
        <?php error_log("User data is empty or invalid in profile.php: " . print_r($user, true)); ?>
    <?php else: ?>
        <?php
            $image_path = 'C:\xampp\htdocs\employee_management_system\uploads\\' . ($user['profile_image'] ?? '');
            $web_path = '/employee_management_system/uploads/' . ($user['profile_image'] ?? '');
        ?>

        <!-- Profile image section -->
        <div id="profileImageContainer">
            <?php if (!empty($user['profile_image']) && file_exists($image_path)): ?>
                <img src="<?php echo htmlspecialchars($web_path); ?>?t=<?php echo time(); ?>" width="120" alt="Profile Image" id="profileImage">
            <?php else: ?>
                <p><em>No profile image uploaded.</em></p>
            <?php endif; ?>
        </div>

        <form method="POST" action="index.php?page=profile" enctype="multipart/form-data" id="profileForm">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($user['profile_image'] ?? ''); ?>">
            
            <div>
                <label>Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
            </div>
            
            <div>
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>
            
            <div>
                <label>New Password (leave blank to keep current)</label>
                <input type="password" name="password">
            </div>
            
            <div>
                <label>Role</label>
                <input type="text" value="<?php echo htmlspecialchars($user['role'] ?? ''); ?>" readonly>
            </div>
            
            <div>
                <label>Upload Profile Image</label>
                <input type="file" name="profile_image" accept="image/*" id="imageUpload">
            </div>
            
            <button type="submit" id="submitBtn">Update Profile</button>
        </form>
    <?php endif; ?>

    <script>
        document.getElementById("profileForm").addEventListener("submit", function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const submitBtn = document.getElementById('submitBtn');
            const originalBtnText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
            
            fetch(form.action, {
                method: "POST",
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                // Update message container
                const messageClass = data.success ? 'message' : 'error';
                document.getElementById('messageContainer').innerHTML = 
                    `<p class="${messageClass}">${data.message}</p>`;
                
                // Update profile image if changed
                if (data.success && data.image_url) {
                    const imgContainer = document.getElementById('profileImageContainer');
                    imgContainer.innerHTML = `<img src="${data.image_url}?t=${Date.now()}" width="120" alt="Profile Image" id="profileImage">`;
                }
                
                // Optionally reload after delay
                if (data.success) {
                    setTimeout(() => {
                        if (data.requires_refresh) {
                            window.location.reload();
                        }
                    }, 1500);
                }
            })
            .catch(error => {
                console.error("AJAX Error:", error);
                document.getElementById('messageContainer').innerHTML = 
                    '<p class="error">Error updating profile. Please try again.</p>';
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
        
        // Preview image before upload
        document.getElementById('imageUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('profileImageContainer').innerHTML = 
                        `<img src="${event.target.result}" width="120" alt="Profile Preview" id="profileImage">`;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</div>
<?php include 'app/views/template/footer.php'; ?>