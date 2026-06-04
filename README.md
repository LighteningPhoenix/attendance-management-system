# Attendance Management System

A web-based Attendance Management System developed using PHP and MySQL for managing contractor employees, attendance records, and monthly reporting.

## Features

* Employee Master Management
* Contractor Management
* Attendance Import from CSV Files
* Attendance Processing from Raw Punch Data
* Night Shift Attendance Handling
* Weekly Off (WFF) Calculation
* Employee Information Editing
* Monthly Attendance Reports
* Excel Report Generation
* Admin and User Modules

## Tech Stack

* PHP
* MySQL
* HTML
* CSS
* JavaScript

## Installation

1. Clone the repository:

```bash
git clone <repository-url>
```

2. Create a MySQL database.

3. Import the provided SQL file using phpMyAdmin.

4. Update database credentials in:

```text
db_connect.php
```

5. Start Apache and MySQL.

6. Open the application in a browser.

## Usage

### Employee Import

Import employee master data through the Contractor Management module.

### Attendance Import

Upload raw punch CSV files to store attendance punches.

### Attendance Processing

Process imported punch data to generate attendance records.

### Reports

Generate monthly attendance reports in web and Excel formats.

## Project Structure

```text
Backend/
CSS/
JS/
index.php
admin.php
user.php
db_connect.php
```

## Future Improvements

* Dashboard Analytics
* Automated Attendance Scheduling
* Enhanced Reporting
* Role-Based Access Control

## Author
* **Abhinab Rath**
* Internship Project – 2026
* *PHP | MySQL | Web Application Development*
