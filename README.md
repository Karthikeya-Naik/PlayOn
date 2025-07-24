# 🎮 PlayOn - Sports Facility Booking System

**PlayOn** is a PHP-MySQL based web application for booking sports facilities. It allows users to register, browse available grounds, book time slots, and manage their bookings. Admins can control facilities, view bookings, and maintain user records.

---

## 📌 Features

- 🏟️ Browse and view available sports facilities
- 🕒 Check real-time available time slots
- ✅ Book grounds and manage bookings
- 🛒 Shopping cart functionality
- 💬 Add and view user reviews
- 🔐 User login and registration
- 🔧 Admin panel for management
- 📩 Contact form for inquiries

---

## 🛠️ Tech Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL

---

## ⚙️ Installation & Setup

### ✅ Requirements

- PHP (7.0 or above)
- MySQL
- XAMPP / WAMP / LAMP stack

### 🚀 Local Setup

1. **Download or Clone** this repository:

    ```bash
    git clone https://github.com/yourusername/PlayOn.git
    ```

2. **Move the project folder** into your web server root:

    - For XAMPP:
      ```
      C:/xampp/htdocs/PlayOn
      ```

3. **Create the Database** manually:
   - Open `phpMyAdmin` at `http://localhost/phpmyadmin`
   - Create a new database (e.g., `playon`)
   - Create tables according to your logic (refer to `db_connect.php` and other files)

4. **Configure Database Connection**:
   - Open `db_connect.php`
   - Edit your DB credentials:
     ```php
     $conn = new mysqli("localhost", "root", "", "playon");
     ```

5. **Run the Application**:

---
## 🔐 License
This is a learning/demo project and not intended for production or commercial use.

---
## 📩 Contact
For feedback, ideas, or issues —
Open a GitHub issue
