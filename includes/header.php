<?php
if (!isset($_SESSION["staff_id"])) {
    header("Location: ../login.php");
    exit;
}

$current_role = $_SESSION["role"];
$staff_name   = $_SESSION["full_name"];
$first_name   = $staff_name;
$active_page  = $active_page ?? "";

$roleRoutes = [
    "Product Manager"  => "product_management.php",
    "Order Manager"    => "order_management.php",
    "Customer Manager" => "customer_management.php",
    "Staff Manager"    => "staff_management.php",
    "Payment Manager"  => "payment_delivery.php",
];
$myDashFile = $roleRoutes[$current_role] ?? "product_management.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?= htmlspecialchars($page_title ?? "Dashboard") ?> - Tong Dokan</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Tiro+Bangla:ital@0;1&family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Bebas+Neue&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="../styles.css" />
</head>
<body>

<div class="dashboard-layout">
    <aside class="dashboard-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">
                <div class="logo-circle-small">
                    <span class="logo-bangla-small">ট</span>
                </div>
                <div>
                    <div class="sidebar-title">TONG DOKAN</div>
                    <div class="sidebar-sub">Admin Panel</div>
                </div>
            </div>
            <div class="sidebar-user">
                <div class="sidebar-user-label">Logged in as</div>
                <div class="sidebar-user-name"><?= htmlspecialchars($first_name) ?></div>
                <div class="sidebar-user-role"><?= htmlspecialchars($current_role) ?></div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="<?= $myDashFile ?>" class="dashboard-tab <?= $active_page === 'dashboard' ? 'active' : '' ?>">
                <span class="tab-dot"></span>
                <span class="tab-label">My Dashboard</span>
            </a>
            <a href="../profile.php" class="dashboard-tab">
                <span class="tab-dot"></span>
                <span class="tab-label">My Profile</span>
            </a>
        </nav>

        <div class="sidebar-bottom">
            <a href="../index.php" class="dashboard-tab dashboard-tab-shop">
                <span>🛍️</span>
                <span class="tab-label">View Shop</span>
            </a>
            <a href="../logout.php" class="dashboard-tab dashboard-tab-logout">
                <span>↪</span>
                <span class="tab-label">Logout</span>
            </a>
        </div>
    </aside>

    <main class="dashboard-main">
