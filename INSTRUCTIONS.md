# Local Service Finder — Complete Project Guide
## PHP + MySQL | Full Featured

---

## PROJECT STRUCTURE

```
local-service-finder/
├── index.php                  ← Homepage
├── register.php               ← Registration page
├── login.php                  ← Login page
├── logout.php                 ← Logout handler
├── search.php                 ← Search results
├── provider-profile.php       ← Provider profile view
├── book.php                   ← Booking page
├── chat.php                   ← Chat/messaging
├── payment.php                ← Payment page
├── review.php                 ← Leave a review
├── dashboard-customer.php     ← Customer dashboard
├── dashboard-provider.php     ← Provider dashboard
├── config/
│   └── db.php                 ← Database connection
├── includes/
│   ├── header.php             ← Shared HTML header
│   └── footer.php             ← Shared HTML footer
├── actions/
│   ├── register-action.php    ← Handle registration POST
│   ├── login-action.php       ← Handle login POST
│   ├── book-action.php        ← Handle booking POST
│   ├── chat-action.php        ← Handle chat POST
│   ├── payment-action.php     ← Handle payment POST
│   └── review-action.php      ← Handle review POST
├── css/
│   └── style.css              ← Global stylesheet
└── database/
    └── setup.sql              ← Full database schema
```

---

## SETUP INSTRUCTIONS

### Step 1 — Install Required Software

Download and install **XAMPP** (free):
👉 https://www.apachefriends.org/download.html

XAMPP gives you:
- Apache (web server)
- MySQL (database)
- PHP (backend language)
- phpMyAdmin (database GUI)

### Step 2 — Start XAMPP

1. Open XAMPP Control Panel
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**
4. Both should show green "Running" status

### Step 3 — Create the Project Folder

1. Go to: `C:\xampp\htdocs\`
2. Create a folder called: `local-service-finder`
3. Place all project files inside this folder

### Step 4 — Set Up the Database

1. Open your browser → go to: `http://localhost/phpmyadmin`
2. Click **"New"** on the left sidebar
3. Database name: `service_finder` → Click **Create**
4. Click the **SQL** tab
5. Paste the entire content of `database/setup.sql`
6. Click **Go** to run it

### Step 5 — Configure Database Connection

Open `config/db.php` and confirm these settings match your setup:
```php
$host = 'localhost';
$dbname = 'service_finder';
$username = 'root';
$password = '';   // Leave empty for default XAMPP
```

### Step 6 — Run the Project

Open browser → go to: `http://localhost/local-service-finder/`

---

## FEATURES INCLUDED

| Feature | Description |
|---|---|
| Registration | Customer & Provider signup with role selection |
| Login/Logout | Session-based authentication |
| Search | Search by service type and location |
| Provider Profile | Full profile with ratings and reviews |
| Booking | Online booking with date/time selection |
| Chat | Real-time-style messaging between customer & provider |
| Payment | Simulated payment (amount, method selection) |
| Reviews | Star rating + written review after service |
| Dashboards | Separate dashboards for customers and providers |
| Booking Management | Provider can accept/reject/reschedule bookings |

---

## HOW EACH FEATURE WORKS

### Registration & Login
- Users pick role: Customer or Service Provider
- Passwords stored with `password_hash()` (secure)
- Sessions used to keep users logged in

### Search
- Customer types service type + location
- SQL query filters providers by `service_type` and `location`
- Results show rating, experience, and contact

### Booking
- Customer selects date + time + adds notes
- Booking stored in DB with status = 'pending'
- Provider sees it in their dashboard

### Chat
- Messages stored in `messages` table
- Customer and provider can message each other per booking
- Page auto-refreshes every 5 seconds to show new messages

### Payment
- After booking is accepted, customer pays
- Simulated: stores amount + method (Cash/bKash/Card) in DB
- Payment status updates booking

### Reviews
- Only available after booking status = 'completed'
- Customer gives 1-5 stars + text review
- Review shows on provider's public profile

---

## TEST ACCOUNTS (after setup)

You can register manually, or insert these via phpMyAdmin SQL:

```sql
-- Test Customer
INSERT INTO users (name, email, phone, password, role, location)
VALUES ('Rahim Customer', 'customer@test.com', '01700000001',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uRpFZLwau', 'customer', 'Dhaka');

-- Test Provider
INSERT INTO users (name, email, phone, password, role, location, service_type, experience)
VALUES ('Karim Electrician', 'provider@test.com', '01700000002',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uRpFZLwau', 'provider', 'Dhaka', 'Electrician', 5);
```
Password for both test accounts: **password**

---

## COMMON ERRORS & FIXES

| Error | Fix |
|---|---|
| "Connection refused" | Make sure Apache & MySQL are running in XAMPP |
| "Database not found" | Run setup.sql in phpMyAdmin first |
| Blank page | Enable PHP errors: add `ini_set('display_errors',1);` at top of file |
| Session not working | Make sure `session_start()` is at the very top of each PHP file |
| Page not loading | Check URL is exactly `http://localhost/local-service-finder/` |
