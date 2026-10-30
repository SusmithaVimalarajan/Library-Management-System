# Library Management System

## 1. Project Title

**Library Management System**

---

## 2. Project Description

The Library Management System is a web-based application developed to help a librarian manage library books, students, borrowing and returning activities, book hold requests, email notifications, and reports.

The system provides a simple and organized way to maintain library records and reduce manual work. The librarian can manage books and student information, issue books, record returned books, manage book hold requests, search available books, and generate reports.

The system provides a book catalogue for authenticated librarians and students. Authenticated students can place a temporary hold on an available book. When a book is held, the available quantity is updated immediately. An email notification is sent to the student through SMTP when the hold is approaching its expiry time.

Book holds automatically expire after the configured hold period if the student does not borrow the book. The automatic notification and expiry processes are handled using PHP scripts and Windows Task Scheduler.

---

# 3. Problem Statement

Traditional library management methods often depend on manual records, which can make it difficult to maintain accurate information about books, students, and borrowing activities.

Manual record keeping can also result in:

* Difficulty finding book information
* Difficulty tracking borrowed and returned books
* Errors in maintaining student records
* Time-consuming record management
* Difficulty preparing library reports
* Problems identifying available books
* Difficulty managing temporary book reservations
* Delays in notifying students about held books
* Difficulty tracking expired book holds

Therefore, a computerized Library Management System is required to manage these activities efficiently.

---

# 4. Project Objectives

The main objectives of the project are:

* To develop a simple web-based Library Management System.
* To maintain accurate book records.
* To maintain student/member records.
* To manage book issuing and returning.
* To display currently available books.
* To provide search functionality.
* To allow registered students to place temporary book holds.
* To send email notifications for book holds.
* To automatically expire uncollected book holds.
* To maintain accurate book availability during holds.
* To generate useful library reports.
* To reduce manual record-keeping work.
* To provide secure librarian access to administrative functions.

---

# 5. Main Features

## 5.1 Librarian Login

* Librarian login
* Logout functionality
* Secure access to administrative pages

## 5.2 Dashboard

* Total books
* Total students
* Available books
* Issued books

## 5.3 Book Management

* Add books
* View books
* Search books
* Edit book information
* Delete books
* Track total quantities & available quantities

## 5.4 Student Management

* Add students
* View students
* Search student records
* Edit student information
* Delete student records
* Maintain student registration information

## 5.5 Borrowing Management

* Issue books to students
* Set issue and due dates
* Record returned books
* Automatically update available quantity
* Display borrowing status
* Maintain borrowing history

## 5.6 Book Hold Management

* Public users can search available books
* Registered students can place a hold on a book
* Student registration number is validated
* Student email is collected for notification
* Student phone number is validated
* Duplicate active holds are prevented
* Hold time is recorded automatically
* Hold expiry time is recorded automatically
* Available quantity is reduced when a hold is created
* Students can cancel an active hold
* Librarian can view all hold requests
* Librarian can borrow a book directly from an active hold
* Expired holds remain in the database for record keeping
* Expired holds are clearly displayed to the librarian
* Available quantity is restored when a hold expires or is cancelled

## 5.7 Email Notification

* Email notifications are sent using PHPMailer
* SMTP is used for sending emails
* Students receive a reminder email before hold expiry
* Notification status is recorded in the database

## 5.8 Automatic Hold Expiry

* Active holds are checked automatically
* Expired holds are changed from `ACTIVE` to `EXPIRED`
* Book quantity is automatically restored
* Expired hold records are not deleted
* Librarian cannot borrow or cancel an already expired hold
* Expiry processing is handled by a PHP background script

## 5.9 Reports

* Monthly borrowing report
* Student borrowing report
* Book borrowing report

## 5.10 Authenticated Book Catalogue

After signing in, librarians and students can search available books by:

* Book ID
* Title
* Author.

Students can select **Hold Book** for an available book and submit their email address and phone number. The hold is associated with their signed-in student account. Guests must sign in before accessing the catalogue or holds.

---

# 6. Technologies and Tools Used

## Frontend

* HTML5
* CSS
* JavaScript

## Backend

* PHP 8.0

## Database

* MySQL

## Email

* PHPMailer
* SMTP

## Automation

* Windows Task Scheduler
* PHP scheduled scripts

## Development Environment

* XAMPP
* Apache
* MySQL

## Version Control

* Git
* GitHub

## Development Tools

* Visual Studio Code
* phpMyAdmin
* Web Browser
* Composer

---

# 7. Project Scope

## Included in the Project

The system includes:

* Librarian authentication
* Book management
* Student management
* Book issue management
* Book return management
* Available book search
* Borrowing records
* Monthly reports
* Student reports
* Book reports
* MySQL database management
* Public book search
* Book hold functionality
* Student hold requests
* Hold cancellation
* Librarian hold management
* Automatic hold expiry
* Book quantity restoration after expiry
* Email notification for book holds
* PHPMailer SMTP email integration
* Windows Task Scheduler automation
* Hold notification status tracking

## Not Included in the Current Version

The following features remain outside the scope of Version 1.0:

* Book renewal
* SMS notifications
* Barcode scanning
* Mobile application

These features may be considered for future development.

---

# 8. System Users

## 8.1 Librarian

The librarian is the main system user and can:

* Log in to the system
* Manage books
* Manage students
* Issue books
* Return books
* View borrowing records
* Manage book hold requests
* View active, cancelled, borrowed, and expired holds
* Borrow a book from an active hold
* Cancel an active hold
* Generate reports

## 8.2 Student

Students can sign in with their index number and a password set or reset by the
librarian. Their account page only displays their own borrowing and hold records.

## Change Request Database Update

For an existing database, run `migrations/20261029_cr001_cr002.sql` once after
selecting the configured library database. New installations can use the updated
`schema.sql`. CR002 uses a default fine of 10 LKR per overdue day, configured in
`config/settings.php`.

Students can:

* Search and view available books through the authenticated book catalogue
* Place a temporary hold on an available book
* Provide their registration number
* Provide their email address
* Provide their phone number
* Receive an email notification related to their book hold
* Cancel their hold through the hold confirmation page

---

# 9. Database

The project uses a MySQL database named:

**library_management**

## Main Tables

* `users`
* `students`
* `books`
* `borrowings`
* `book_holds`

### users

Stores librarian login information.

### students

Stores registered student/member information.

### books

Stores book information, total quantity, and available quantity.

### borrowings

Stores book issue and return records.

### book_holds

Stores temporary book hold information, including:

* Hold token
* Student ID
* Book ID
* Email
* Phone number
* Hold time
* Expiry time
* Hold status
* Notification status

The database SQL files are available in:

* `schema.sql`
* `seed.sql`

---

# 10. Installation and Setup

## Step 1: Install XAMPP

Install XAMPP with:

* Apache
* MySQL
* PHP

## Step 2: Copy the Project

Copy the project folder into the XAMPP `htdocs` directory.

Example:

```text
C:\XAMP\htdocs\Library Management System
```

## Step 3: Start XAMPP

Start:

* Apache
* MySQL

## Step 4: Create the Database

Open phpMyAdmin and import:

```text
schema.sql
seed.sql
```

This will create the required database and tables.

## Step 5: Configure Database Connection

Check:

```text
config/db.php
```

Make sure the database connection details match the local XAMPP configuration.

## Step 6: Install PHPMailer

The project uses Composer and PHPMailer for SMTP email notifications.

The required Composer packages are stored inside:

```text
vendor/
```

Open Command Prompt.

Check Composer:

composer --version

Install PHPMailer:

composer require phpmailer/phpmailer

Make sure the following file exists:

vendor/autoload.php

The project uses PHPMailer with SMTP for email notifications.

The system does not use PHP mail().

## Step 7: Configure SMTP

Configure the SMTP settings used by PHPMailer.

The SMTP configuration contains the required:

* SMTP host
* SMTP port
* SMTP username
* SMTP password/app password
* Sender email address
* Sender name

The application uses SMTP instead of PHP `mail()`.

## Step 8: Configure Automatic Hold Processing

The system contains PHP scripts for automatic hold processing:


scripts/send_hold_notifications.php
scripts/expire_holds.php


The notification script checks active holds that have reached their reminder time and sends the email notification.

The expiry script checks active holds whose expiry time has been reached and changes their status to `EXPIRED`.

## Step 9: Configure Windows Task Scheduler

Windows Task Scheduler is configured to run the PHP scripts automatically.
 
  Create Notification Task

First open:

Task Scheduler

Then:

Task Scheduler Library
        ↓
Action
        ↓
Create Task

Create the task:

Library Hold Notification
General

Set:

Name:
Library Hold Notification

Select:

Run whether user is logged on or not

Select:

Run with highest privileges
Triggers → New

Select:

Begin the task:
On a schedule

Set:

Daily: ✓

Set:

Repeat task every:
1 minute

Set:

For a duration:
Indefinitely

Make sure:

Enabled: ✓

Click:

OK
Actions → New

Select:

Action:
Start a program
Program/script

Use the PHP executable:

C:\xampp\php\php.exe
Add arguments
"C:\xampp\htdocs\Library Management System\scripts\send_hold_notifications.php"
Start in
C:\xampp\htdocs\Library Management System

Click:OK


Conditions 

Open the Conditions tab.

Important:

Leave all options unchecked.

Do not select:

Start the task only if the computer is on AC power

or other unnecessary conditions.


Settings

Use the following settings:

☑ Allow task to be run on demand
☑ Run task as soon as possible after a scheduled start is missed

For task failure:

☑ If the task fails, restart every:

1 minute

Set:

Attempt to restart up to:

3 times

Select:

☑ If the running task does not end when requested,
   force it to stop

For multiple instances:

If the task is already running:

Do not start a new instance

Click: OK



The Library Hold Notification task is now configured.



    Create Expiry Task

Create another Task Scheduler task.

Go to:

Task Scheduler
        ↓
Task Scheduler Library
        ↓
Action
        ↓
Create Task

Create:

Library Hold Expiry
General

Set:

Name:
Library Hold Expiry

Select:

Run whether user is logged on or not

Select:

Run with highest privileges
Triggers → New

Select:

Begin the task:
On a schedule

Set:

Daily: ✓

Set:

Repeat task every:
1 minute

Set:

For a duration:
Indefinitely

Make sure:

Enabled: ✓

Click:

OK
Actions → New

Select:

Action:
Start a program
Program/script
C:\xampp\php\php.exe
Add arguments
"C:\xampp\htdocs\Library Management System\scripts\expire_holds.php"
Start in
C:\xampp\htdocs\Library Management System

Click:OK



Conditions

Open the Conditions tab.

Important:

Leave all options unchecked.

Settings

Use:

☑ Allow task to be run on demand
☑ Run task as soon as possible after a scheduled start is missed

For task failure:

☑ If the task fails, restart every:

1 minute

Set:

Attempt to restart up to:

3 times

Select:

☑ If the running task does not end when requested,
   force it to stop

For multiple instances:

If the task is already running:

Do not start a new instance

Click:OK



The Library Hold Expiry task is now configured.

   Check Task Scheduler

The Task Scheduler Library should contain:

Library Hold Notification
Library Hold Expiry

Both tasks should show:

Status: Ready

The tasks should run every:

1 minute

You can also right-click each task and select:

Run

to test it manually.

The tasks can be configured to run every minute so that hold reminders and expiry processing are handled automatically.

## Step 10: Run the System

Open the project in a web browser.

Example:

```text
http://localhost/Library%20Management%20System/
```

---

# 11. Project Structure


```text
Library Management System/
│
├── config/
│   ├── db.php
│   └── mail.php
│
├── includes/
│   └── auth.php
|   └── navbar.php
│
├── public/
│   ├── books/
│   ├── students/
│   ├── borrowings/
│   ├── reports/
|   |── hold/
|   |     ├── create.php
|   |     |── confirmation.php
│   │     ├----holdcancel.php
│   ├── holdss/
│   │   ├── index.php
│   │   ├── cancel.php
│   │   └── borrow.php
│   └── index.php
│
├── scripts/
│   ├── send_hold_notifications.php
│   ├── expire_holds.php
│   ├── send_hold_notifications.bat
│   └── expire_holds.bat
│
├── assets/
│
├── vendor/
│   └── phpmailer/
│
├── schema.sql
├── seed.sql
├── composer.json
├── composer.lock
├── README.md
├── README.txt
└── .gitignore
```

---

# 12. Student Details

Student Name: T. Manoja
Course: Software Change Management
Project: Library Management System
Technology: PHP, MySQL, HTML, CSS, JavaScript
Development Environment: XAMPP
Email Technology: PHPMailer SMTP
Automation: Windows Task Scheduler
Version Control: Git and GitHub

---

# 13. Documentation

Project-related documentation is available in the documentation folder.

The documentation may include:

* Project Proposal
* Software Requirements Documentation

---

# 14. Version

Version: 1.0
Project Status: Final

The current Version 1.0 includes book hold management, SMTP email notification, and automatic hold expiry functionality.

---


15.
username: admin
password:123456