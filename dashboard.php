<?php
// =======================================================
// User Dashboard: dashboard.php
// Displays the groups a user belongs to and allows creating new groups.
// =======================================================

// Start session
session_start();

// 1. Authentication Check: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'db.php';

$current_user_id = $_SESSION['user_id'];
$error_message = "";
$success_message = "";

// 2. Handle "Create New Group" form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_group'])) {
    $group_name = trim(mysqli_real_escape_string($conn, $_POST['group_name']));
    $group_desc = trim(mysqli_real_escape_string($conn, $_POST['group_description']));

    if (empty($group_name)) {
        $error_message = "Group name is required.";
    } else {
        // Step A: Insert the new group into 'groups' table
        $insert_group_sql = "INSERT INTO `groups` (name, description, created_by) 
                             VALUES ('$group_name', '$group_desc', '$current_user_id')";
        
        if (mysqli_query($conn, $insert_group_sql)) {
            // Get the auto-increment ID of the freshly inserted group
            $new_group_id = mysqli_insert_id($conn);

            // Step B: Automatically add the creator as the first member in 'group_members' table
            $add_member_sql = "INSERT INTO group_members (group_id, user_id) 
                               VALUES ('$new_group_id', '$current_user_id')";
            mysqli_query($conn, $add_member_sql);

            $success_message = "Group '{$group_name}' created successfully!";
        } else {
            $error_message = "Failed to create group: " . mysqli_error($conn);
        }
    }
}

// 3. Fetch all groups that the current user belongs to
// We JOIN 'groups' with 'group_members' to find all groups where user_id = $current_user_id
$groups_sql = "SELECT g.id, g.name, g.description, g.created_at, u.name AS creator_name,
               (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = g.id) AS total_members,
               (SELECT IFNULL(SUM(e.amount), 0) FROM expenses e WHERE e.group_id = g.id) AS total_spent
               FROM `groups` g
               JOIN group_members gm ON g.id = gm.group_id
               JOIN users u ON g.created_by = u.id
               WHERE gm.user_id = '$current_user_id'
               ORDER BY g.id DESC";

$groups_result = mysqli_query($conn, $groups_sql);

$page_title = "Dashboard";
include 'includes/header.php';
?>

<!-- Dashboard Welcome Header -->
<div style="margin-bottom: 2rem;">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋</h1>
    <p>Manage your shared groups, track bill splits, and settle balances easily.</p>
</div>

<!-- Display feedback alerts -->
<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger">
        <span>⚠️</span> <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success">
        <span>✅</span> <?php echo htmlspecialchars($success_message); ?>
    </div>
<?php endif; ?>

<div class="grid-dashboard">
    
    <!-- Left Column: Form to Create a New Group -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3>➕ Create New Group</h3>
            </div>
            
            <form action="dashboard.php" method="POST" onsubmit="return validateGroupForm();">
                <input type="hidden" name="create_group" value="1">
                
                <!-- Group Name -->
                <div class="form-group">
                    <label for="group_name" class="form-label">Group Name</label>
                    <input type="text" id="group_name" name="group_name" class="form-control" 
                           placeholder="e.g. Roommates 2026, Sajek Tour" required>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="group_description" class="form-label">Description (Optional)</label>
                    <textarea id="group_description" name="group_description" class="form-control" 
                              placeholder="e.g. Apartment rent, grocery & utilities..."></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-block">Create Group</button>
            </form>
        </div>
    </div>

    <!-- Right Column: User's Groups List -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3>👥 Your Groups</h3>
            </div>

            <?php if (mysqli_num_rows($groups_result) > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Group Name</th>
                                <th>Members</th>
                                <th>Total Expenses</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($group = mysqli_fetch_assoc($groups_result)): ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <a href="group.php?id=<?php echo $group['id']; ?>" style="color: var(--primary); text-decoration: none;">
                                                <?php echo htmlspecialchars($group['name']); ?>
                                            </a>
                                        </strong>
                                        <?php if (!empty($group['description'])): ?>
                                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">
                                                <?php echo htmlspecialchars($group['description']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            👤 <?php echo $group['total_members']; ?> members
                                        </span>
                                    </td>
                                    <td>
                                        <strong>$<?php echo number_format($group['total_spent'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <a href="group.php?id=<?php echo $group['id']; ?>" class="btn btn-primary btn-sm">
                                            Open Group &rarr;
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- Empty state if user is in zero groups -->
                <div style="text-align: center; padding: 2rem 1rem; color: var(--text-muted);">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📂</div>
                    <h4>No groups yet!</h4>
                    <p>Create a new group using the form on the left or ask a friend to add you by your email.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
