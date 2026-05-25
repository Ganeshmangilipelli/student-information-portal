# Student Information Portal (SIP)

A full-stack web-based **Student Information Portal** developed using **PHP, MySQL, HTML, CSS, and JavaScript**.  
This project enables students and teachers to interact digitally through messaging, event management, and a book borrowing system.

---

## 🚀 Live Demo

🔗 https://ganeshm005.infinityfreeapp.com

---

# ✨ Features

## 👨‍🎓 Student Module
- Student Registration & Login
- View campus events
- Send messages to teachers
- Borrow books from seniors
- Upload books for juniors

## 👨‍🏫 Teacher Module
- Teacher Registration & Login
- Send messages to students
- Share announcements
- Communicate with branches/students

## 📚 Book Borrowing System
- Upload books with details
- Track borrowed books
- Availability status system
- Book image support

## 📅 Event Management
- Add campus events
- Display upcoming events dynamically

---

# 🛠️ Tech Stack

## Frontend
- HTML5
- CSS3
- JavaScript

## Backend
- PHP

## Database
- MySQL

## Hosting & Deployment
- InfinityFree
- XAMPP (Local Development)

---

# 📂 Project Structure

```bash
SIP/
│
├── login.php
├── signup.php
├── main.php
├── message.php
├── borrow.php
├── upload_book.php
├── fetch_events.php
├── campus_events.php
├── db.php
├── logout.php
├── uploads/
└── databasetables.txt
```

---

# ⚙️ How to Run Locally

## 1️⃣ Install XAMPP
Download and install XAMPP.

## 2️⃣ Start Services
Open XAMPP Control Panel and start:
- Apache
- MySQL

## 3️⃣ Move Project Folder
Copy the project folder into:

```bash
xampp/htdocs/
```

## 4️⃣ Create Database
Open:

```bash
http://localhost/phpmyadmin
```

Create a database named:

```bash
database
```

## 5️⃣ Import Tables
Open the SQL tab and run the queries from:

```bash
databasetables.txt
```

## 6️⃣ Configure Database Connection

Update `db.php`:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "database";
```

## 7️⃣ Run the Project

Open:

```bash
http://localhost/SIP/login.php
```

---

# 🌐 Deployment

This project is deployed using **InfinityFree** hosting platform.

---

# 📸 Screenshots

## Login Page
![Login Page](assets/login.png)

## Dashboard
![Dashboard](assets/dashboard.png)

## Book Borrowing System
![Books](assets/books.png)

---

# 🔮 Future Improvements

- Password Hashing
- Responsive Design
- Admin Dashboard
- AI Chatbot Integration
- Notifications System
- Attendance Management
- Role-Based Authentication

---

# 👨‍💻 Author

**Ganesh Mangilipelli**

- GitHub: https://github.com/Ganeshmangilipelli
- LinkedIn: https://linkedin.com/Ganeshmagilipelli

---

# ⭐ Support

If you liked this project, give it a ⭐ on GitHub.
