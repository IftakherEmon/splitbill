<?php
// =======================================================
// Group Management & Expense Settlement: group.php
// This is the core page where expenses are recorded,
// member balances are computed, and settle-up payments are generated.
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

// 2. Validate Group ID from GET parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$group_id = intval($_GET['id']);

// 3. Fetch Group Info and verify the user is a member of this group
$group_query = "SELECT g.*, u.name AS creator_name 
                FROM `groups` g
                JOIN users u ON g.created_by = u.id
                WHERE g.id = '$group_id' LIMIT 1";
$group_result = mysqli_query($conn, $group_query);
$group = mysqli_fetch_assoc($group_result);

if (!$group) {
    die("<div style='padding:20px; font-family:sans-serif;'>Group not found. <a href='dashboard.php'>Back to Dashboard</a></div>");
}

// Check membership: Is current user in group_members?
$membership_check = "SELECT id FROM group_members WHERE group_id = '$group_id' AND user_id = '$current_user_id'";
$membership_result = mysqli_query($conn, $membership_check);
if (mysqli_num_rows($membership_result) === 0) {
    die("<div style='padding:20px; font-family:sans-serif;'>You are not a member of this group. <a href='dashboard.php'>Back to Dashboard</a></div>");
}


// =======================================================
// 4. ACTION: ADD A NEW MEMBER BY EMAIL
// =======================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action_add_member'])) {
    $member_email = trim(mysqli_real_escape_string($conn, $_POST['member_email']));

    if (empty($member_email)) {
        $error_message = "Please enter an email address.";
    } else {
        // Find if user exists with this email
        $find_user_sql = "SELECT id, name FROM users WHERE email = '$member_email' LIMIT 1";
        $find_user_res = mysqli_query($conn, $find_user_sql);

        if ($find_user_res && mysqli_num_rows($find_user_res) > 0) {
            $user_to_add = mysqli_fetch_assoc($find_user_res);
            $user_to_add_id = $user_to_add['id'];

            // Check if user is already in this group
            $already_member_sql = "SELECT id FROM group_members WHERE group_id = '$group_id' AND user_id = '$user_to_add_id'";
            $already_res = mysqli_query($conn, $already_member_sql);

            if (mysqli_num_rows($already_res) > 0) {
                $error_message = "{$user_to_add['name']} is already a member of this group.";
            } else {
                // Insert into group_members
                $add_sql = "INSERT INTO group_members (group_id, user_id) VALUES ('$group_id', '$user_to_add_id')";
                if (mysqli_query($conn, $add_sql)) {
                    $success_message = "Added {$user_to_add['name']} to the group!";
                } else {
                    $error_message = "Failed to add member: " . mysqli_error($conn);
                }
            }
        } else {
            $error_message = "No user found with email '{$member_email}'. Ask them to register first.";
        }
    }
}


// =======================================================
// 5. ACTION: ADD A NEW EXPENSE
// =======================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action_add_expense'])) {
    $title = trim(mysqli_real_escape_string($conn, $_POST['expense_title']));
    $amount = floatval($_POST['expense_amount']);
    $paid_by = intval($_POST['paid_by']);
    $expense_date = mysqli_real_escape_string($conn, $_POST['expense_date']);
    $split_members = isset($_POST['split_members']) ? $_POST['split_members'] : array();

    // Validation
    if (empty($title)) {
        $error_message = "Please enter an expense title.";
    } elseif ($amount <= 0) {
        $error_message = "Expense amount must be greater than zero.";
    } elseif (empty($expense_date)) {
        $error_message = "Please select an expense date.";
    } elseif (empty($split_members)) {
        $error_message = "Please select at least one member to split the expense with.";
    } else {
        // Step A: Insert master expense record into 'expenses' table
        $insert_expense_sql = "INSERT INTO expenses (group_id, paid_by, title, amount, expense_date) 
                               VALUES ('$group_id', '$paid_by', '$title', '$amount', '$expense_date')";

        if (mysqli_query($conn, $insert_expense_sql)) {
            $new_expense_id = mysqli_insert_id($conn);

            // Step B: Calculate equal split share for each selected member
            $num_selected = count($split_members);
            $split_per_person = round($amount / $num_selected, 2);

            // Step C: Insert individual split records into 'expense_splits' table
            foreach ($split_members as $member_id) {
                $member_id_int = intval($member_id);
                $insert_split_sql = "INSERT INTO expense_splits (expense_id, user_id, split_amount) 
                                     VALUES ('$new_expense_id', '$member_id_int', '$split_per_person')";
                mysqli_query($conn, $insert_split_sql);
            }

            $success_message = "Expense '{$title}' of $" . number_format($amount, 2) . " logged successfully!";
        } else {
            $error_message = "Error logging expense: " . mysqli_error($conn);
        }
    }
}


// =======================================================
// 6. ACTION: DELETE AN EXPENSE (Optional helper)
// =======================================================
if (isset($_GET['delete_expense'])) {
    $del_expense_id = intval($_GET['delete_expense']);
    $delete_sql = "DELETE FROM expenses WHERE id = '$del_expense_id' AND group_id = '$group_id'";
    mysqli_query($conn, $delete_sql);
    header("Location: group.php?id=" . $group_id);
    exit();
}


// =======================================================
// 7. FETCH ALL GROUP MEMBERS
// =======================================================
$members_sql = "SELECT u.id, u.name, u.email, gm.joined_at 
                FROM group_members gm
                JOIN users u ON gm.user_id = u.id
                WHERE gm.group_id = '$group_id'
                ORDER BY u.name ASC";
$members_res = mysqli_query($conn, $members_sql);

$group_members = array();
while ($m = mysqli_fetch_assoc($members_res)) {
    $group_members[] = $m;
}


// =======================================================
// 8. FETCH ALL EXPENSES FOR THIS GROUP
// =======================================================
$expenses_sql = "SELECT e.*, u.name AS payer_name 
                 FROM expenses e
                 JOIN users u ON e.paid_by = u.id
                 WHERE e.group_id = '$group_id'
                 ORDER BY e.expense_date DESC, e.id DESC";
$expenses_res = mysqli_query($conn, $expenses_sql);


// =======================================================
// 9. CALCULATE EACH MEMBER'S BALANCE
// Net Balance = (Total Amount Paid By User) - (Total Share Owed By User)
// Positive (+) = The user paid more than their share -> Others owe them money (Gets back)
// Negative (-) = The user paid less than their share -> They owe money to the group (Owes)
// Zero (0)     = Fully settled up
// =======================================================
$member_balances = array();

foreach ($group_members as $member) {
    $uid = $member['id'];

    // Total amount this member paid upfront for the group
    $paid_query = "SELECT IFNULL(SUM(amount), 0) AS total_paid 
                   FROM expenses 
                   WHERE group_id = '$group_id' AND paid_by = '$uid'";
    $paid_res = mysqli_query($conn, $paid_query);
    $paid_data = mysqli_fetch_assoc($paid_res);
    $total_paid = floatval($paid_data['total_paid']);

    // Total share this member is responsible for (from expense_splits)
    $share_query = "SELECT IFNULL(SUM(es.split_amount), 0) AS total_share 
                    FROM expense_splits es
                    JOIN expenses e ON es.expense_id = e.id
                    WHERE e.group_id = '$group_id' AND es.user_id = '$uid'";
    $share_res = mysqli_query($conn, $share_query);
    $share_data = mysqli_fetch_assoc($share_res);
    $total_share = floatval($share_data['total_share']);

    // Net Balance calculation
    $net_balance = round($total_paid - $total_share, 2);

    $member_balances[$uid] = array(
        'id'          => $uid,
        'name'        => $member['name'],
        'email'       => $member['email'],
        'total_paid'  => $total_paid,
        'total_share' => $total_share,
        'net_balance' => $net_balance
    );
}


// =======================================================
// 10. SETTLE-UP ALGORITHM (Greedy Debt Simplification)
// HOW THIS WORKS (Very important for viva explanation!):
//
// 1. Separate members into two lists:
//    - Debtors: People who have a negative balance (they owe money).
//    - Creditors: People who have a positive balance (money is owed to them).
//
// 2. In each round:
//    - Find the person who owes the most (max debtor).
//    - Find the person who is owed the most (max creditor).
//    - Settle the smaller of the two amounts between them.
//      (e.g., if Rahim owes $70 and Tanvir is owed $200, Rahim pays Tanvir $70.
//       Now Rahim owes $0, and Tanvir is still owed $130).
//    - Repeat this until all debts reach $0.
//
// This produces the minimum number of transactions needed to settle all accounts!
// =======================================================
$debtors = array();   // Members who owe money (net_balance < 0)
$creditors = array(); // Members who get money back (net_balance > 0)

foreach ($member_balances as $mb) {
    if ($mb['net_balance'] < -0.01) {
        // Store as positive amount owed for easier matching
        $debtors[] = array('id' => $mb['id'], 'name' => $mb['name'], 'amount' => abs($mb['net_balance']));
    } elseif ($mb['net_balance'] > 0.01) {
        $creditors[] = array('id' => $mb['id'], 'name' => $mb['name'], 'amount' => $mb['net_balance']);
    }
}

$settlement_plan = array(); // Stores final simplified transactions

$d_idx = 0;
$c_idx = 0;

// Match debtors with creditors until all balances are settled
while ($d_idx < count($debtors) && $c_idx < count($creditors)) {
    $debtor_name = $debtors[$d_idx]['name'];
    $creditor_name = $creditors[$c_idx]['name'];

    $debt_amount = $debtors[$d_idx]['amount'];
    $credit_amount = $creditors[$c_idx]['amount'];

    // Settle the minimum of the two amounts
    $payment = min($debt_amount, $credit_amount);

    if ($payment > 0.01) {
        $settlement_plan[] = array(
            'from'   => $debtor_name,
            'to'     => $creditor_name,
            'amount' => round($payment, 2)
        );
    }

    // Adjust remaining amounts
    $debtors[$d_idx]['amount'] -= $payment;
    $creditors[$c_idx]['amount'] -= $payment;

    // If debtor has paid off everything, move to the next debtor
    if ($debtors[$d_idx]['amount'] < 0.01) {
        $d_idx++;
    }

    // If creditor has received everything, move to the next creditor
    if ($creditors[$c_idx]['amount'] < 0.01) {
        $c_idx++;
    }
}

$page_title = $group['name'];
include 'includes/header.php';
?>

<!-- Group Header Banner -->
<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <a href="dashboard.php" style="text-decoration: none; color: var(--primary); font-size: 0.9rem; font-weight: 600;">&larr; Back to Dashboard</a>
        <h1 style="margin-top: 0.25rem;"><?php echo htmlspecialchars($group['name']); ?></h1>
        <?php if (!empty($group['description'])): ?>
            <p><?php echo htmlspecialchars($group['description']); ?></p>
        <?php endif; ?>
    </div>
    
    <div>
        <span class="user-badge" style="font-size: 0.9rem;">
            Created by <?php echo htmlspecialchars($group['creator_name']); ?>
        </span>
    </div>
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


<!-- Top Section: Balances Summary & Settle-Up Suggestions -->
<div class="grid-2" style="margin-bottom: 2rem;">
    
    <!-- 1. Balances Summary Table -->
    <div class="card">
        <div class="card-header">
            <h3>📊 Member Balances</h3>
        </div>
        <p style="font-size: 0.85rem; margin-bottom: 0.75rem;">
            Net Balance = <strong>Total Paid</strong> minus <strong>Total Share</strong>
        </p>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Total Paid</th>
                        <th>Share</th>
                        <th>Net Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($member_balances as $mb): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($mb['name']); ?></strong>
                                <?php if ($mb['id'] == $current_user_id): ?>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">(You)</span>
                                <?php endif; ?>
                            </td>
                            <td>$<?php echo number_format($mb['total_paid'], 2); ?></td>
                            <td>$<?php echo number_format($mb['total_share'], 2); ?></td>
                            <td>
                                <?php if ($mb['net_balance'] > 0.01): ?>
                                    <span class="badge badge-positive">
                                        + $<?php echo number_format($mb['net_balance'], 2); ?> (Gets back)
                                    </span>
                                <?php elseif ($mb['net_balance'] < -0.01): ?>
                                    <span class="badge badge-negative">
                                        - $<?php echo number_format(abs($mb['net_balance']), 2); ?> (Owes)
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-zero">Settled ($0.00)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Settle-Up Suggestions Card -->
    <div class="card">
        <div class="card-header">
            <h3>⚡ Settle-Up Suggestions</h3>
        </div>
        <p style="font-size: 0.85rem; margin-bottom: 0.75rem;">
            Simplified payment plan to resolve all debts with minimal transactions:
        </p>

        <?php if (!empty($settlement_plan)): ?>
            <div style="margin-top: 0.5rem;">
                <?php foreach ($settlement_plan as $step): ?>
                    <div class="settle-card">
                        <div class="settle-from-to">
                            <strong><?php echo htmlspecialchars($step['from']); ?></strong>
                            <span class="settle-arrow">&rarr; pays &rarr;</span>
                            <strong><?php echo htmlspecialchars($step['to']); ?></strong>
                        </div>
                        <div class="settle-amount">
                            $<?php echo number_format($step['amount'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem 1rem; color: var(--text-muted);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">🎉</div>
                <strong>All settled up!</strong>
                <p style="margin-top: 4px; font-size: 0.9rem;">No pending debts in this group right now.</p>
            </div>
        <?php endif; ?>
    </div>

</div>


<!-- Middle Section: Add Expense & Add Member Forms -->
<div class="grid-2" style="margin-bottom: 2rem;">
    
    <!-- Form A: Add New Expense -->
    <div class="card">
        <div class="card-header">
            <h3>🧾 Log New Expense</h3>
        </div>
        
        <form action="group.php?id=<?php echo $group_id; ?>" method="POST" onsubmit="return validateExpenseForm();">
            <input type="hidden" name="action_add_expense" value="1">
            
            <!-- Expense Title -->
            <div class="form-group">
                <label for="expense_title" class="form-label">Expense Description / Title</label>
                <input type="text" id="expense_title" name="expense_title" class="form-control" 
                       placeholder="e.g. Grocery, Dinner, Uber ride" required>
            </div>

            <div class="grid-2" style="gap: 0.75rem;">
                <!-- Amount -->
                <div class="form-group">
                    <label for="expense_amount" class="form-label">Amount ($)</label>
                    <input type="number" step="0.01" min="0.01" id="expense_amount" name="expense_amount" class="form-control" 
                           placeholder="0.00" required>
                </div>

                <!-- Date -->
                <div class="form-group">
                    <label for="expense_date" class="form-label">Date</label>
                    <input type="date" id="expense_date" name="expense_date" class="form-control" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <!-- Paid By Dropdown -->
            <div class="form-group">
                <label for="paid_by" class="form-label">Who Paid upfront?</label>
                <select id="paid_by" name="paid_by" class="form-control" required>
                    <?php foreach ($group_members as $m): ?>
                        <option value="<?php echo $m['id']; ?>" <?php echo ($m['id'] == $current_user_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['name']); ?> <?php echo ($m['id'] == $current_user_id) ? '(You)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Split Between Members Checkboxes -->
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <label class="form-label" style="margin-bottom: 0;">Split Equally Between:</label>
                    <label style="font-size: 0.8rem; cursor: pointer; color: var(--primary);">
                        <input type="checkbox" checked onchange="toggleAllMembers(this)"> Select All
                    </label>
                </div>

                <div class="member-checkbox-list">
                    <?php foreach ($group_members as $m): ?>
                        <label class="member-checkbox-item">
                            <input type="checkbox" name="split_members[]" value="<?php echo $m['id']; ?>" checked>
                            <span><?php echo htmlspecialchars($m['name']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary btn-block">Add Expense</button>
        </form>
    </div>

    <!-- Form B: Add Member by Email & Member List -->
    <div>
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3>➕ Add Member</h3>
            </div>
            <p style="font-size: 0.85rem;">Add a friend to this group using their registered email address.</p>
            
            <form action="group.php?id=<?php echo $group_id; ?>" method="POST" onsubmit="return validateAddMemberForm();">
                <input type="hidden" name="action_add_member" value="1">
                
                <div class="form-group">
                    <label for="member_email" class="form-label">User's Email</label>
                    <input type="email" id="member_email" name="member_email" class="form-control" 
                           placeholder="e.g. friend@example.com" required>
                </div>

                <button type="submit" class="btn btn-outline btn-block">Add to Group</button>
            </form>
        </div>

        <!-- Current Members Card -->
        <div class="card">
            <div class="card-header">
                <h3>👥 Group Members (<?php echo count($group_members); ?>)</h3>
            </div>
            
            <div>
                <?php foreach ($group_members as $m): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                        <div>
                            <strong><?php echo htmlspecialchars($m['name']); ?></strong>
                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($m['email']); ?></div>
                        </div>
                        <?php if ($m['id'] == $group['created_by']): ?>
                            <span class="badge badge-primary">Creator</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>


<!-- Bottom Section: Expense History Table -->
<div class="card">
    <div class="card-header">
        <h3>📜 Expense History</h3>
    </div>

    <?php if (mysqli_num_rows($expenses_res) > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Paid By</th>
                        <th>Split Among</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($exp = mysqli_fetch_assoc($expenses_res)): ?>
                        <?php
                            // Fetch who split this expense
                            $exp_id = $exp['id'];
                            $split_users_sql = "SELECT u.name, es.split_amount 
                                                FROM expense_splits es
                                                JOIN users u ON es.user_id = u.id
                                                WHERE es.expense_id = '$exp_id'";
                            $split_users_res = mysqli_query($conn, $split_users_sql);
                        ?>
                        <tr>
                            <td style="white-space: nowrap; color: var(--text-muted); font-size: 0.9rem;">
                                <?php echo date("M d, Y", strtotime($exp['expense_date'])); ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($exp['title']); ?></strong>
                            </td>
                            <td>
                                <strong style="color: var(--primary);">$<?php echo number_format($exp['amount'], 2); ?></strong>
                            </td>
                            <td>
                                <span class="member-tag">
                                    👤 <?php echo htmlspecialchars($exp['payer_name']); ?>
                                </span>
                            </td>
                            <td>
                                <?php while ($su = mysqli_fetch_assoc($split_users_res)): ?>
                                    <span class="member-tag" style="font-size: 0.8rem;">
                                        <?php echo htmlspecialchars($su['name']); ?> ($<?php echo number_format($su['split_amount'], 2); ?>)
                                    </span>
                                <?php endwhile; ?>
                            </td>
                            <td>
                                <a href="group.php?id=<?php echo $group_id; ?>&delete_expense=<?php echo $exp['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this expense?');" 
                                   class="btn btn-outline btn-sm" style="color: var(--danger); border-color: #fecaca;">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 2rem 1rem; color: var(--text-muted);">
            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📝</div>
            <h4>No expenses logged yet!</h4>
            <p>Use the form above to record the first shared bill for this group.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
