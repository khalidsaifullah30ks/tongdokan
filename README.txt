TONG DOKAN — E-Commerce Project
================================

Team:
  Khalid Saifullah         — Customer Management
  Azmain Hossain Ovy       — Order Management
  Md Sakhoyat Hossain Siam — Product Management
  Shafin Zaman             — Staff Management
  M.G.M. Mahraz            — Payment & Delivery


SETUP (XAMPP)
=============

1. Install XAMPP if you haven't already.

2. Copy the entire 'tongdokan' folder into:
       Windows:  C:\xampp\htdocs\tongdokan
       Mac:      /Applications/XAMPP/htdocs/tongdokan

3. Open XAMPP Control Panel and start:
       - Apache
       - MySQL

4. Create the database:
       a) Open browser: http://localhost/phpmyadmin
       b) Click "Import" tab
       c) Choose file: tongdokan.sql
       d) Click "Go" at the bottom
       e) You should now see a "tongdokan" database with 5 tables.

5. Open the website:
       http://localhost/tongdokan/index.php

That's it.


LOGIN CREDENTIALS
=================

  Username   Password       Goes to
  --------   ------------   -----------------------------
  siam       product123     Product Management dashboard
  ovy        order123       Order Management dashboard
  khalid     customer123    Customer Management dashboard
  shafin     staff123       Staff Management dashboard
  mahraz     payment123     Payment & Delivery dashboard


FILE STRUCTURE
==============

tongdokan/
├── index.php                          (public storefront — shared)
├── login.php                          (login page — shared)
├── logout.php                         (logout handler — shared)
├── db_connect.php                     (database connection — shared)
├── styles.css                         (all styles — shared)
├── tongdokan.sql                      (database setup — import once)
├── README.txt                         (this file)
│
├── dashboards/
│   ├── product_management.php         (Siam)
│   ├── order_management.php           (Ovy)
│   ├── customer_management.php        (Khalid)
│   ├── staff_management.php           (Shafin)
│   └── payment_delivery.php           (Mahraz)
│
└── includes/
    ├── header.php                     (shared sidebar/header)
    └── footer.php                     (shared dashboard footer)


WHO OWNS WHAT
=============

When the teacher asks "who did what?", here's the answer:

  - product_management.php    -> Siam
  - order_management.php      -> Ovy
  - customer_management.php   -> Khalid
  - staff_management.php      -> Shafin
  - payment_delivery.php      -> Mahraz

The shared files (index.php, login.php, db_connect.php, styles.css, etc.)
were built together by the team.


DATABASE TABLES
===============

  staff       — login credentials and team info        (Shafin)
  products    — product catalogue                       (Siam)
  customers   — customer accounts                       (Khalid)
  orders      — orders placed by customers              (Ovy)
  payments    — payment + delivery records              (Mahraz)


COMMON ISSUES
=============

* Page says "Connection failed":
    -> MySQL is not running in XAMPP. Start it.

* Page is blank or shows PHP code:
    -> Apache is not running, OR you opened the file directly.
       Always go through http://localhost/tongdokan/...

* "Database doesn't exist":
    -> You forgot to import tongdokan.sql in phpMyAdmin.

* Can't login after import:
    -> Re-import tongdokan.sql. Make sure no errors in import.
