<?php include 'app/views/template/header.php'; ?>
<div class="container">
    <h2>Add User</h2>
    <div id="message" class="message" style="display: none;"></div>
    
    <form id="add-user-form" method="POST" action="http://localhost/employee_management_system/index.php?page=add_user" onsubmit="return validateForm(event)" onreset="resetErrors()">
        <div>
            <label>Name</label>
            <input type="text" id="name" name="name"><br><br>
            <span id="name-error" class="error-message"></span>
        </div>
        <div>
            <label>Email</label>
            <input type="email" id="email" name="email">
            <span id="email-error" class="error-message"></span>
        </div>
        <div>
            <label>Password</label>
            <input type="password" id="password" name="password">
            <span id="password-error" class="error-message"></span>
        </div>
        <div>
            <label>Role</label>
            <select name="role" id="role">
                <option value="employee">Employee</option>
                <option value="team_leader">Team Leader</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit" id="submit-button">Add User</button>
    </form>
    
    <p><a href="index.php?page=users">Back to Users</a></p>

    <script>
        function validateForm(event) {
            event.preventDefault(); // Prevent default form submission normally

            // Get form elements from the form
            const name = document.getElementById("name").value.trim();
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value;
            const role = document.getElementById("role").value;

            // Get error message 
            const nameErr = document.getElementById("name-error");
            const emailErr = document.getElementById("email-error");
            const passwordErr = document.getElementById("password-error");
            const messageDiv = document.getElementById("message");
            const submitButton = document.getElementById("submit-button");

            // Reset error messages
            nameErr.textContent = "";
            emailErr.textContent = "";
            passwordErr.textContent = "";
            messageDiv.style.display = "none";

            let isValid = true;

            // Validate name
            if (name === "" || /\d/.test(name)) {
                nameErr.textContent = "Please enter a valid name without numbers.";
                isValid = false;
            }
            if (name === 3) {
                nameErr.textContent = "Please enter a  name more than 3 letters..";
                isValid = false;
            }


            // Validate email
            if (email === "" || !email.includes("@") || !email.includes(".com")) {
                emailErr.textContent = "Please enter a valid email address ending with .com.";
                isValid = false;
            }

            // Validate password
            if (password === "" || password.length < 8) {
                passwordErr.textContent = "Password must be at least 8 characters long.";
                isValid = false;
            }

            if (!isValid) {
                return false;
            }
            const form = document.getElementById("add-user-form");
            const formData = new FormData(form);
            const submitButtonOriginalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';

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
                messageDiv.textContent = data.message || "Operation completed";
                messageDiv.className = 'message ' + (data.success ? 'success' : 'error');
                messageDiv.style.display = 'block';

                if (data.success) {
                    form.reset();
                    setTimeout(() => {
                        messageDiv.textContent = "User added successfully";
                        messageDiv.className = 'message success';
                        messageDiv.style.display = 'block';
                        setTimeout(() => {
                            window.location.href = 'index.php?page=users';
                        }, 2000); 
                    }, 500);
                }
            })
            .catch(error => {
                messageDiv.textContent = "Error adding user: " + error.message;
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = submitButtonOriginalText;
            });

            return false; // Prevent form submission
        }

        function resetErrors() {
            document.getElementById("name-error").textContent = "";
            document.getElementById("email-error").textContent = "";
            document.getElementById("password-error").textContent = "";
            document.getElementById("message").style.display = "none";
        }
    </script>
</div>
<?php include 'app/views/template/footer.php'; ?>