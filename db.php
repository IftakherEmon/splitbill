<?php
// =======================================================
// Database Connection File: db.php
// This file connects our PHP application to the MySQL database
// using the simple and standard mysqli_connect() function.
// =======================================================

// 1. Define database connection parameters
// In standard XAMPP, default username is 'root' with no password ("")
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "splitbill_db";

// 2. Establish connection to MySQL
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// 3. Check if connection failed and display a beginner-friendly message
if (!$conn) {
    die("<div style='font-family:sans-serif; padding:20px; background:#fee2e2; color:#991b1b; border:1px solid #f87171; border-radius:8px; margin:20px;'>
            <h3>Database Connection Error!</h3>
            <p>Could not connect to the database <strong>{$db_name}</strong>.</p>
            <p><strong>Troubleshooting Steps for XAMPP:</strong></p>
            <ul>
                <li>Ensure the <strong>MySQL</strong> module is running in your XAMPP Control Panel.</li>
                <li>Import the <code>sql/schema.sql</code> file into phpMyAdmin (<a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a>).</li>
                <li>Verify database name is <code>{$db_name}</code>.</li>
            </ul>
            <p><em>MySQL Error: " . mysqli_connect_error() . "</em></p>
         </div>");
}

// 4. Set character set to utf8 for international names/symbols
mysqli_set_charset($conn, "utf8");
?>
