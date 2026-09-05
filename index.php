<?php
// =======================================================
// Landing Page: index.php
// The front page that introduces SplitBill to visitors.
// =======================================================

// Start session to check if user is already logged in
session_start();

// If user is already logged in, redirect straight to their dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$page_title = "Welcome";
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero">
    <h1>Share Expenses Without The Stress 🏖️</h1>
    <p>
        Split bills with roommates, friends, and travel buddies. 
        Track who paid what, calculate individual balances automatically, 
        and settle debts with the minimum number of payments!
    </p>
    <div class="hero-actions">
        <a href="register.php" class="btn btn-primary" style="padding: 0.8rem 1.8rem; font-size: 1.05rem;">Get Started Free</a>
        <a href="login.php" class="btn btn-outline" style="padding: 0.8rem 1.8rem; font-size: 1.05rem;">Login to Account</a>
    </div>
</div>

<!-- Features Grid -->
<div class="grid-3" style="margin-bottom: 2.5rem;">
    <!-- Feature 1 -->
    <div class="card" style="text-align: center;">
        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">👥</div>
        <h3>Create Expense Groups</h3>
        <p>Organize your shared costs into groups like "Roommates", "Cox's Bazar Tour", or "Weekend Dinner".</p>
    </div>

    <!-- Feature 2 -->
    <div class="card" style="text-align: center;">
        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🧾</div>
        <h3>Log Shared Expenses</h3>
        <p>Record who paid for an expense and select exactly which group members share the bill.</p>
    </div>

    <!-- Feature 3 -->
    <div class="card" style="text-align: center;">
        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">⚡</div>
        <h3>Smart Settle-Up</h3>
        <p>Our algorithm calculates net balances and provides a simplified payment plan so everyone settles quickly.</p>
    </div>
</div>

<!-- Demo / Viva Quick-Test Helper Card -->
<div class="card" style="background-color: #f8fafc; border: 1px dashed #cbd5e1;">
    <div class="card-header">
        <h3>💡 Demo Accounts </h3>
    </div>
    <p style="margin-bottom: 0.75rem;">You can use these pre-loaded accounts after importing <code>sql/schema.sql</code>:</p>
    <div class="table-responsive">
        <table class="table" style="background: white; border-radius: 6px;">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Tanvir Ahmed</strong></td>
                    <td><code>tanvir@example.com</code></td>
                    <td><code>password123</code></td>
                    <td><a href="login.php" class="btn btn-outline btn-sm">Login as Tanvir</a></td>
                </tr>
                <tr>
                    <td><strong>Sarah Khan</strong></td>
                    <td><code>sarah@example.com</code></td>
                    <td><code>password123</code></td>
                    <td><a href="login.php" class="btn btn-outline btn-sm">Login as Sarah</a></td>
                </tr>
                <tr>
                    <td><strong>Rahim Chowdhury</strong></td>
                    <td><code>rahim@example.com</code></td>
                    <td><code>password123</code></td>
                    <td><a href="login.php" class="btn btn-outline btn-sm">Login as Rahim</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
