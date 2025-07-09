<?php
$title = 'Register - Blog Platform';
require_once 'app/views/template/header.php';
echo "View loaded<br>"; // Debug output
?>
    <h1>Register</h1>
    <?php if (isset($data['errors']) && !empty($data['errors'])): ?>
        <div class="alert alert-danger">
            <?php foreach ($data['errors'] as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form id="registerForm" action="index.php?page=register" method="POST" onsubmit="return validateForm()">
        <div class="mb-3">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($data['first_name'] ?? ''); ?>">
            <span id="first_name-error" class="text-danger"></span>
        </div>
        <div class="mb-3">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($data['last_name'] ?? ''); ?>">
            <span id="last_name-error" class="text-danger"></span>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>">
            <span id="email-error" class="text-danger"></span>
        </div>
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($data['username'] ?? ''); ?>">
            <span id="username-error" class="text-danger"></span>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password">
            <span id="password-error" class="text-danger"></span>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
            <span id="confirm_password-error" class="text-danger"></span>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
        <p>Already have an account? <a href="index.php?page=login">Login</a></p>
    </form>

    <script>
        function validateForm() {
            const firstName = document.getElementById("first_name").value;
            const lastName = document.getElementById("last_name").value;
            const email = document.getElementById("email").value;
            const username = document.getElementById("username").value;
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;

            const firstNameErr = document.getElementById("first_name-error");
            const lastNameErr = document.getElementById("last_name-error");
            const emailErr = document.getElementById("email-error");
            const usernameErr = document.getElementById("username-error");
            const passwordErr = document.getElementById("password-error");
            const confirmPasswordErr = document.getElementById("confirm_password-error");

            firstNameErr.textContent = "";
            lastNameErr.textContent = "";
            emailErr.textContent = "";
            usernameErr.textContent = "";
            passwordErr.textContent = "";
            confirmPasswordErr.textContent = "";

            let isValid = true;

            if (firstName === "" || /\d/.test(firstName)) {
                firstNameErr.textContent = "Please enter your first name properly (no numbers).";
                isValid = false;
            }

            if (lastName === "" || /\d/.test(lastName)) {
                lastNameErr.textContent = "Please enter your last name properly (no numbers).";
                isValid = false;
            }

            if (email === "" || !email.includes("@") || !email.includes(".")) {
                emailErr.textContent = "Please enter a valid email address.";
                isValid = false;
            }

            if (username === "") {
                usernameErr.textContent = "Please enter a username.";
                isValid = false;
            }

            if (password === "" || password.length < 6) {
                passwordErr.textContent = "Please enter a password with at least 6 characters.";
                isValid = false;
            }

            if (confirmPassword === "" || password !== confirmPassword) {
                confirmPasswordErr.textContent = "Passwords do not match.";
                isValid = false;
            }

            if (isValid) {
                // Remove alert for actual submission
                // alert("Form submitted successfully!");
                return true;
            } else {
                return false;
            }
        }

        function resetErrors() {
            document.getElementById("first_name-error").textContent = "";
            document.getElementById("last_name-error").textContent = "";
            document.getElementById("email-error").textContent = "";
            document.getElementById("username-error").textContent = "";
            document.getElementById("password-error").textContent = "";
            document.getElementById("confirm_password-error").textContent = "";
        }

        document.getElementById("first_name").addEventListener("input", resetErrors);
        document.getElementById("last_name").addEventListener("input", resetErrors);
        document.getElementById("email").addEventListener("input", resetErrors);
        document.getElementById("username").addEventListener("input", resetErrors);
        document.getElementById("password").addEventListener("input", resetErrors);
        document.getElementById("confirm_password").addEventListener("input", resetErrors);
    </script>
<?php require_once 'app/views/template/footer.php'; ?>