<?php
// =======================================================
// User Login Page: login.php
// Validates credentials against MySQL and starts a PHP session.
// =======================================================

// Start session
session_start();

// If user is already logged in, redirect straight to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Include database connection
require_once 'db.php';

$error_message = "";

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Retrieve and sanitize input data
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = $_POST['password'];

    // 2. Simple validation
    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        // 3. Search for user by email in database
        $sql = "SELECT id, name, email, password FROM users WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            // 4. Verify password using password_verify()
            // WHAT password_verify() DOES:
            // It takes the user's typed plain password, hashes it with the exact salt stored
            // inside $user['password'], and checks if they match.
            // If they match, returns true; otherwise false.
            if (password_verify($password, $user['password'])) {
                
                // 5. Credentials are valid! Store session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                // Redirect user to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "Invalid password. Please try again.";
            }
        } else {
            $error_message = "No account found with this email.";
        }
    }
}

$page_title = "Login";
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p class="subtitle">Log in to track your group expenses</p>

        <!-- Display error message if any -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <span>⚠️</span> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="login.php" method="POST" onsubmit="return validateLoginForm();">
            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="e.g. tanvir@example.com" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary btn-block">Log In</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="register.php">Sign up here</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
