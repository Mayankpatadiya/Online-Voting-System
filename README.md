# 🗳️ Online Voting System  

A secure full-stack **Online Voting System** built with **Bootstrap, PHP, and MySQL**.  
This project allows users to register, log in, and cast votes, with an admin panel for managing users and results.  

---

## 🚀 Features  
- User Registration & Login  
- Role-based Access (Admin & Voter)  
- Secure Voting (One person, one vote)  
- Admin Dashboard for Results  
- Responsive UI with Bootstrap  

---

## 🛠️ Technologies Used  
- **Frontend:** HTML, CSS, JavaScript, Bootstrap  
- **Backend:** PHP  
- **Database:** MySQL  

---

## 📂 Project Setup  

### 1️⃣ Clone the Repository  
```bash
git clone https://github.com/Mayankpatadiya/Online-Voting-System.git
cd Online-Voting-System


### 2️⃣ Setup Database

Open phpMyAdmin.

Create a new database named votingsystem.

Import the file voting_system.sql (provided in the sql/ folder).

### 3️⃣ Configure Database Connection

Edit config.php (or your database connection file) and update with your credentials:

$host = "localhost";
$user = "root";      // your MySQL username
$pass = "";          // your MySQL password
$db   = "votingsystem";

### 4️⃣ Run the Project

Place the project folder inside htdocs (if using XAMPP).

Start Apache and MySQL in XAMPP Control Panel.

Open in browser:

http://localhost/Online-Voting-System
