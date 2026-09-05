<?php
// =======================================================
// User Registration Page: register.php
// Handles new user account creation with secure password hashing.
// =======================================================

// Start session
session_start();

// If the user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Include database connection
require_once 'db.php';

$error_message = "";
$success_message = "";

// Check if form was submitted via POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Retrieve and sanitize input data
    // mysqli_real_escape_string escapes special characters to prevent basic SQL injection issues
    $name = trim(mysqli_real_escape_string($conn, $_POST['name']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = $_POST['password'];

    // 2. Validate input fields
    if (empty($name) || empty($email) || empty($password)) {
        $error_message = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } else {
        // 3. Check if the email address is already registered
        $check_sql = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "An account with this email already exists. Please log in.";
        } else {
            // 4. Securely hash the password
            // WHAT password_hash() DOES:
            // It uses a strong one-way cryptographic algorithm (Bcrypt) to turn a plain text
            // password like "mypassword123" into an unreadable 60-character string.
            //
            // WHY WE DO THIS (Crucial for viva/course defense):
            // We NEVER store plain text passwords in the database. If a database is ever
            // leaked or inspected, plain text passwords expose user accounts. A hashed password
            // cannot be reversed back to plain text, protecting the user's security.
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // 5. Insert new user into the database
            $insert_sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";
            
            if (mysqli_query($conn, $insert_sql)) {
                $success_message = "Registration successful! You can now log in.";
            } else {
                $error_message = "Database error: Could not register user. " . mysqli_error($conn);
            }
        }
    }
}

$page_title = "Register";
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Create an Account</h2>
        <p class="subtitle">Join SplitBill to manage group expenses easily</p>

        <!-- Display feedback alerts -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <span>⚠️</span> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <span>✅</span> <?php echo htmlspecialchars($success_message); ?>
                <div style="margin-top: 8px;">
                    <a href="login.php" class="btn btn-primary btn-sm">Click here to Login</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form action="register.php" method="POST" onsubmit="return validateRegisterForm();">
            <!-- Full Name -->
            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Tanvir Ahmed" required 
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>

            <!-- Email Address -->
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="e.g. tanvir@example.com" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">Password (min 6 characters)</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Log in here</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
