<?php include 'app/views/template/header.php'; ?>
<div class="container">
    <h2>Edit Task</h2>
    <div id="message" class="message" style="display: none;"></div>
    <form id="task-edit-form" method="POST" action="index.php?page=edit_task&id=<?php echo htmlspecialchars($task['id']); ?>" onsubmit="return validateForm(event)">
        <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['id']); ?>">
        <?php if ($_SESSION['role'] === 'employee'): ?>
            <!-- EMPLOYEE: Only allowed to update status -->
            <div>
                <label>Status</label>
                <select id="status" name="status">
                    <option value="pending" <?php echo $task['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in_progress" <?php echo $task['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo $task['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
                <span id="status-error" class="error-message"></span>
            </div>
        <?php else: ?>
            <!-- ADMIN/TEAM_LEADER: Full edit access -->
            <div>
                <label>Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($task['title']); ?>">
                <span id="title-error" class="error-message"></span>
            </div>
            <div>
                <label>Description</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($task['description'] ?? ''); ?></textarea>
                <span id="description-error" class="error-message"></span>
            </div>
            <div>
                <label>Assign To</label>
                <select id="assigned_to" name="assigned_to">
                    <option value="">Unassigned</option>
                    <?php while ($user = $users->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo $user['id'] == $task['assigned_to'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <span id="assigned_to-error" class="error-message"></span>
            </div>
            <div>
                <label>Start Date</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($task['start_date']); ?>">
                <span id="start_date-error" class="error-message"></span>
            </div>
            <div>
                <label>End Date</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($task['end_date']); ?>">
                <span id="end_date-error" class="error-message"></span>
            </div>
        <?php endif; ?>
        <button type="submit" id="submit-button">Update Task</button>
    </form>
    <p><a href="index.php?page=tasks">Back to Tasks</a></p>

    <script>
        function validateForm(event) {
            event.preventDefault(); // Prevent default form submission

            // Get form elements
            const messageDiv = document.getElementById("message");
            const submitButton = document.getElementById("submit-button");
            const isEmployee = <?php echo $_SESSION['role'] === 'employee' ? 'true' : 'false'; ?>;
            
            // Initialize error message elements
            const statusErr = document.getElementById("status-error");
            let titleErr, descriptionErr, assignedToErr, startDateErr, endDateErr;
            if (!isEmployee) {
                titleErr = document.getElementById("title-error");
                descriptionErr = document.getElementById("description-error");
                assignedToErr = document.getElementById("assigned_to-error");
                startDateErr = document.getElementById("start_date-error");
                endDateErr = document.getElementById("end_date-error");
            }

            // Get form values
            const status = isEmployee ? document.getElementById("status").value.trim() : null;
            let title, description, assignedTo, startDate, endDate;
            if (!isEmployee) {
                title = document.getElementById("title").value.trim();
                description = document.getElementById("description").value.trim();
                assignedTo = document.getElementById("assigned_to").value;
                startDate = document.getElementById("start_date").value;
                endDate = document.getElementById("end_date").value;
            }

            // Reset error messages
            if (statusErr) statusErr.textContent = "";
            if (!isEmployee) {
                titleErr.textContent = "";
                descriptionErr.textContent = "";
                assignedToErr.textContent = "";
                startDateErr.textContent = "";
                endDateErr.textContent = "";
            }
            messageDiv.style.display = "none";

            let isValid = true;

            // Validate status (for all roles)
            if (isEmployee) {
                const validStatuses = ['pending', 'in_progress', 'completed'];
                if (!validStatuses.includes(status)) {
                    statusErr.textContent = "Please select a valid status.";
                    isValid = false;
                }
            }

            // Validate fields for non-employees
            if (!isEmployee) {
                // Validate title
                if (title === "") {
                    titleErr.textContent = "Title is required.";
                    isValid = false;
                } else if (title.length < 3) {
                    titleErr.textContent = "Title must be at least 3 characters long.";
                    isValid = false;
                } else if (title.length > 100) {
                    titleErr.textContent = "Title cannot exceed 100 characters.";
                    isValid = false;
                }

                // Validate description
                if (description.length > 1000) {
                    descriptionErr.textContent = "Description cannot exceed 1000 characters.";
                    isValid = false;
                }

                // Validate assigned_to (optional)
                if (assignedTo && isNaN(assignedTo) && assignedTo !== "") {
                    assignedToErr.textContent = "Please select a valid user or leave unassigned.";
                    isValid = false;
                }

                // Validate start_date
                if (startDate === "") {
                    startDateErr.textContent = "Start date is required.";
                    isValid = false;
                }

                // Validate end_date and ensure it's after start_date
                if (endDate === "") {
                    endDateErr.textContent = "End date is required.";
                    isValid = false;
                } else if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
                    endDateErr.textContent = "End date must be after start date.";
                    isValid = false;
                }
            }

            if (!isValid) {
                return false;
            }

            // Prepare form data for AJAX
            const form = document.getElementById("task-edit-form");
            const formData = new FormData(form);
            const submitButtonOriginalText = submitButton.innerHTML;

            // Disable submit button and show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';

            // Perform AJAX request (aligned with GeeksforGeeks reference)
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
                return response.text(); // Get raw text to handle non-JSON responses
            })
            .then(text => {
                try {
                    const data = JSON.parse(text); // Attempt to parse JSON
                    messageDiv.textContent = data.message || "Operation completed";
                    messageDiv.className = 'message ' + (data.success ? 'success' : 'error');
                    messageDiv.style.display = 'block';

                    if (data.success) {
                        setTimeout(() => {
                            window.location.href = 'index.php?page=tasks';
                        }, 1000);
                    }
                } catch (e) {
                    console.error("JSON Parse Error:", e, "Response:", text);
                    messageDiv.textContent = "Error updating task: Invalid server response";
                    messageDiv.className = 'message error';
                    messageDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error("Fetch Error:", error);
                messageDiv.textContent = "Error updating task: Unable to connect to server";
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = submitButtonOriginalText;
            });

            return false; // Prevent form submission
        }
    </script>
</div>
<?php include 'app/views/template/footer.php'; ?>