# 💸 SplitBill — Group Expense Tracker & Settlement System

SplitBill is a beginner-friendly full-stack web application developed in **PHP** and **MySQL** for an undergraduate CSE course project. It allows roommates, travel groups, and friends to track shared expenses, calculate net balances, and generate a simplified "Settle Up" plan with minimal payment transactions.

---

## 🛠️ Technology Stack
- **Backend**: Procedural PHP (`mysqli`)
- **Database**: MySQL (relational database with foreign keys)
- **Frontend**: HTML5, CSS3 (Modern, Responsive Vanilla CSS), Vanilla JavaScript
- **Local Server**: XAMPP (Apache + MySQL)

---

## 📁 Project Structure

```text
SplitBill/
│
├── css/
│   └── style.css            # Clean, modern, responsive CSS stylesheet
├── js/
│   └── script.js            # Vanilla JavaScript form validation & checkbox toggles
├── sql/
│   └── schema.sql           # Database schema, table definitions & starter demo data
├── includes/
│   ├── header.php           # Common HTML header, navigation bar & session detection
│   └── footer.php           # Common HTML footer & script tag
│
├── db.php                   # Database connection script using plain mysqli
├── index.php                # Welcoming landing page & feature overview
├── register.php             # User registration with password_hash()
├── login.php                # User login with password_verify() and session start
├── logout.php               # Session destruction and logout handler
├── dashboard.php            # Group creation and group list
├── group.php                # Core group page: expenses, member balances & settle-up
└── README.md                # Documentation & viva presentation guide
```

---

## 🚀 Setup Instructions (Step-by-Step for XAMPP)

### Step 1: Install & Launch XAMPP
1. Download and install [XAMPP](https://www.apachefriends.org/).
2. Open the **XAMPP Control Panel**.
3. Click **Start** next to **Apache** and **MySQL**. Both should turn green.

### Step 2: Place the Project in `htdocs`
1. Make sure this `SplitBill` folder is located inside your XAMPP `htdocs` directory:
   ```text
   C:\xampp\htdocs\SplitBill   (or D:\XAMPP\htdocs\SplitBill)
   ```

### Step 3: Import the Database
1. Open your web browser and go to: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click on the **Import** tab at the top.
3. Click **Choose File** and browse to `SplitBill/sql/schema.sql`.
4. Click **Import** (or **Go**) at the bottom.
5. The database `splitbill_db` and its tables will be created automatically with sample demo data.

### Step 4: Open SplitBill in Browser
1. In your browser, navigate to:
   ```text
   http://localhost/SplitBill
   ```
2. You should see the SplitBill landing page!

---

## 🔑 Demo Accounts (For Quick Testing & Viva)

The database includes 3 pre-configured demo users that belong to a demo group called **"Trip to Cox's Bazar"**:

| Name | Email | Password |
|---|---|---|
| **Tanvir Ahmed** | `tanvir@example.com` | `password123` |
| **Sarah Khan** | `sarah@example.com` | `password123` |
| **Rahim Chowdhury** | `rahim@example.com` | `password123` |
