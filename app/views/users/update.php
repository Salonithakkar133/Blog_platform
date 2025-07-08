<?php include 'app/views/template/header.php'; ?>

<div class="container mt-4">
    <h2>Update User</h2>

    <div id="messageContainer" class="message" style="display: none;"></div>

    <form id="updateForm" method="post" action="index.php?page=update_user&id=<?= $user['id'] ?>" onsubmit="return validateForm(event)">
        <input type="hidden" name="id" value="<?= $user['id'] ?>">

        <div class="form-group">
            <label>Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="form-control" required>
            <span id="name-error" class="error-message"></span>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control">
            <span id="email-error" class="error-message"></span>
        </div>

        <div class="form-group">
            <label>Password (Leave blank to keep current)</label>
            <input type="password" id="password" name="password" class="form-control">
            <span id="password-error" class="error-message"></span>
        </div>

        <?php if ($_SESSION['role'] === 'admin'): ?>
        <div class="form-group">
            <label>Role</label>
            <select id="role" name="role" class="form-control" required>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="team_leader" <?= $user['role'] === 'team_leader' ? 'selected' : '' ?>>Team Leader</option>
                <option value="employee" <?= $user['role'] === 'employee' ? 'selected' : '' ?>>Employee</option>
            </select>
            <span id="role-error" class="error-message"></span>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary" id="submitBtn">Update</button>
        <a href="index.php?page=users" class="btn btn-secondary ml-2">Cancel</a>
    </form>
</div>

<script>
function validateForm(event) {
    event.preventDefault(); // Prevent default form submission

    // Get form elements
    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    const role = document.getElementById("role") ? document.getElementById("role").value.trim() : null;

    // Get error message elements
    const nameErr = document.getElementById("name-error");
    const emailErr = document.getElementById("email-error");
    const passwordErr = document.getElementById("password-error");
    const roleErr = document.getElementById("role-error");
    const messageContainer = document.getElementById("messageContainer");
    const submitBtn = document.getElementById("submitBtn");

    // Reset error messages
    nameErr.textContent = "";
    emailErr.textContent = "";
    passwordErr.textContent = "";
    if (roleErr) roleErr.textContent = "";
    messageContainer.style.display = "none";

    let isValid = true;

    // Validate name
    if (name === "" || /\d/.test(name)) {
        nameErr.textContent = "Please enter a valid name without numbers.";
        isValid = false;
    }

    // Validate email
    if (email === "" || !email.includes("@") || !email.includes(".com")) {
        emailErr.textContent = "Please enter a valid email address ending with .com.";
        isValid = false;
    }

    // Validate password (if provided)
    if (password !== "" && password.length < 8) {
        passwordErr.textContent = "Password must be at least 8 characters long.";
        isValid = false;
    }

    // Validate role (if admin)
    if (role && role !== "" && !['admin', 'team_leader', 'employee'].includes(role)) {
        roleErr.textContent = "Please select a valid role.";
        isValid = false;
    }

    if (!isValid) {
        return false;
    }

    // Prepare form data for AJAX
    const form = document.getElementById("updateForm");
    const formData = new FormData(form);
    const submitBtnOriginalText = submitBtn.innerHTML;

    // Disable submit button and show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';

    // Perform AJAX request
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        messageContainer.textContent = data.message || "Operation completed";
        messageContainer.className = 'message ' + (data.success ? 'success' : 'error');
        messageContainer.style.display = 'block';

        if (data.success) {
            // Show success message and redirect after 2 seconds
            setTimeout(() => {
                messageContainer.textContent = "User updated successfully";
                messageContainer.className = 'message success';
                messageContainer.style.display = 'block';
                setTimeout(() => {
                    window.location.href = 'index.php?page=users';
                }, 2000);
            }, 500);
        }
    })
    .catch(error => {
        console.error("Fetch Error:", error);
        messageContainer.textContent = "Error updating user: " + error.message;
        messageContainer.className = 'message error';
        messageContainer.style.display = 'block';
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = submitBtnOriginalText;
    });

    return false; // Prevent form submission
}
</script>

<?php include 'app/views/template/footer.php'; ?>