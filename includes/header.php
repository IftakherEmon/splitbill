<?php
// =======================================================
// Common Header Component: header.php
// Included at the top of every page for consistent HTML structure,
// navigation bar, and session management.
// =======================================================

// Start PHP session if one hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . " - SplitBill" : "SplitBill - Group Expense Tracker"; ?></title>
    
    <!-- Link our custom CSS stylesheet -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Main Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <!-- Brand Logo -->
            <a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'index.php'; ?>" class="brand-logo">
                <span class="logo-icon">💸</span> SplitBill
            </a>

            <!-- Navigation Links -->
            <ul class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Links for logged-in users -->
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li>
                        <span class="user-badge">
                            👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </span>
                    </li>
                    <li><a href="logout.php" class="btn btn-outline btn-sm">Logout</a></li>
                <?php else: ?>
                    <!-- Links for visitors / guests -->
                    <li><a href="index.php">Home</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php" class="btn btn-primary btn-sm">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="main-container">
