// =======================================================
// SplitBill - Client-Side JavaScript Validation & Helpers
// Simple, readable, vanilla JavaScript functions.
// =======================================================

// 1. Validate Registration Form
// Ensures no field is left blank, email has basic format, and password is at least 6 characters.
function validateRegisterForm() {
    var name = document.getElementById("name").value.trim();
    var email = document.getElementById("email").value.trim();
    var password = document.getElementById("password").value;

    if (name === "") {
        alert("Please enter your full name.");
        document.getElementById("name").focus();
        return false; // Prevents form submission
    }

    if (email === "") {
        alert("Please enter your email address.");
        document.getElementById("email").focus();
        return false;
    }

    if (password.length < 6) {
        alert("Password must be at least 6 characters long.");
        document.getElementById("password").focus();
        return false;
    }

    return true; // Allows form submission
}

// 2. Validate Login Form
// Ensures email and password fields are filled out.
function validateLoginForm() {
    var email = document.getElementById("email").value.trim();
    var password = document.getElementById("password").value;

    if (email === "") {
        alert("Please enter your registered email.");
        document.getElementById("email").focus();
        return false;
    }

    if (password === "") {
        alert("Please enter your password.");
        document.getElementById("password").focus();
        return false;
    }

    return true;
}

// 3. Validate Add Expense Form
// Ensures title is provided, amount is greater than 0, and at least one member is selected to split.
function validateExpenseForm() {
    var title = document.getElementById("expense_title").value.trim();
    var amount = parseFloat(document.getElementById("expense_amount").value);
    var date = document.getElementById("expense_date").value;
    var checkboxes = document.querySelectorAll('input[name="split_members[]"]:checked');

    if (title === "") {
        alert("Please enter an expense description/title.");
        document.getElementById("expense_title").focus();
        return false;
    }

    if (isNaN(amount) || amount <= 0) {
        alert("Please enter a valid expense amount greater than 0.");
        document.getElementById("expense_amount").focus();
        return false;
    }

    if (date === "") {
        alert("Please select the date of the expense.");
        document.getElementById("expense_date").focus();
        return false;
    }

    // Check if at least one member is checked to share the bill
    if (checkboxes.length === 0) {
        alert("Please select at least one member to split this expense with.");
        return false;
    }

    return true;
}

// 4. Validate Create Group Form
function validateGroupForm() {
    var groupName = document.getElementById("group_name").value.trim();

    if (groupName === "") {
        alert("Please enter a name for your new group.");
        document.getElementById("group_name").focus();
        return false;
    }

    return true;
}

// 5. Validate Add Member by Email Form
function validateAddMemberForm() {
    var email = document.getElementById("member_email").value.trim();

    if (email === "") {
        alert("Please enter the email address of the user you want to add.");
        document.getElementById("member_email").focus();
        return false;
    }

    return true;
}

// 6. Helper: Toggle Select All / Deselect All Member Checkboxes
function toggleAllMembers(sourceCheckbox) {
    var checkboxes = document.querySelectorAll('input[name="split_members[]"]');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = sourceCheckbox.checked;
    }
}
