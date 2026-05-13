<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION["staff_id"])) {
    header("Location: login.php");
    exit;
}

$staff_id = $_SESSION["staff_id"];
$success  = "";
$error    = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "change_username") {
        $new_username = trim($_POST["new_username"] ?? "");
        $password_confirm = trim($_POST["password_confirm"] ?? "");

        if ($new_username === "" || $password_confirm === "") {
            $error = "All fields are required.";
        } else {
            $stmt = $conn->prepare("SELECT password FROM staff WHERE staff_id = ?");
            $stmt->bind_param("i", $staff_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($password_confirm !== $row["password"]) {
                $error = "Current password is incorrect.";
            } else {
                $check = $conn->prepare("SELECT staff_id FROM staff WHERE username = ? AND staff_id != ?");
                $check->bind_param("si", $new_username, $staff_id);
                $check->execute();
                $check->get_result()->num_rows > 0 ? $taken = true : $taken = false;
                $check->close();

                if ($taken) {
                    $error = "That username is already taken. Choose another.";
                } else {
                    $upd = $conn->prepare("UPDATE staff SET username = ? WHERE staff_id = ?");
                    $upd->bind_param("si", $new_username, $staff_id);
                    $upd->execute();
                    $upd->close();
                    $_SESSION["username"] = $new_username;
                    $success = "Username updated to <strong>" . htmlspecialchars($new_username) . "</strong>. You can see this change in phpMyAdmin now.";
                }
            }
        }
    }

    if ($action === "change_password") {
        $current_password = trim($_POST["current_password"] ?? "");
        $new_password     = trim($_POST["new_password"] ?? "");
        $confirm_password = trim($_POST["confirm_password"] ?? "");

        if ($current_password === "" || $new_password === "" || $confirm_password === "") {
            $error = "All fields are required.";
        } elseif ($new_password !== $confirm_password) {
            $error = "New password and confirm password do not match.";
        } elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters.";
        } else {
            $stmt = $conn->prepare("SELECT password FROM staff WHERE staff_id = ?");
            $stmt->bind_param("i", $staff_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($current_password !== $row["password"]) {
                $error = "Current password is incorrect.";
            } else {
                $upd = $conn->prepare("UPDATE staff SET password = ? WHERE staff_id = ?");
                $upd->bind_param("si", $new_password, $staff_id);
                $upd->execute();
                $upd->close();
                $success = "Password updated successfully. You can verify it in phpMyAdmin under the staff table.";
            }
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM staff WHERE staff_id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$stmt->close();

$roleRoutes = [
    "Product Manager"  => "dashboards/product_management.php",
    "Order Manager"    => "dashboards/order_management.php",
    "Customer Manager" => "dashboards/customer_management.php",
    "Staff Manager"    => "dashboards/staff_management.php",
    "Payment Manager"  => "dashboards/payment_delivery.php",
];
$myDash = $roleRoutes[$me["role"]] ?? "index.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>My Profile - Tong Dokan</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Tiro+Bangla:ital@0;1&family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Bebas+Neue&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="styles.css" />
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
                <div class="sidebar-user-name"><?= htmlspecialchars($me["full_name"]) ?></div>
                <div class="sidebar-user-role"><?= htmlspecialchars($me["role"]) ?></div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="<?= $myDash ?>" class="dashboard-tab">
                <span class="tab-dot"></span>
                <span class="tab-label">My Dashboard</span>
            </a>
            <a href="profile.php" class="dashboard-tab active">
                <span class="tab-dot"></span>
                <span class="tab-label">My Profile</span>
            </a>
        </nav>
        <div class="sidebar-bottom">
            <a href="index.php" class="dashboard-tab dashboard-tab-shop">
                <span>🛍️</span>
                <span class="tab-label">View Shop</span>
            </a>
            <a href="logout.php" class="dashboard-tab dashboard-tab-logout">
                <span>↪</span>
                <span class="tab-label">Logout</span>
            </a>
        </div>
    </aside>

    <main class="dashboard-main">
        <div class="dash-header">
            <div class="dash-eyebrow">Account Settings</div>
            <h1 class="dash-title">My <span class="accent-red">Profile</span></h1>
            <p class="dash-sub">Change your username or password here. Changes save to the database instantly.</p>
        </div>

        <?php if ($success): ?>
            <div class="dash-message"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="dash-message-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="profile-grid">

            <div class="dash-section">
                <h2 class="section-title">Current Info</h2>
                <div class="profile-info-row">
                    <span class="profile-label">Full Name</span>
                    <span class="profile-value"><?= htmlspecialchars($me["full_name"]) ?></span>
                </div>
                <div class="profile-info-row">
                    <span class="profile-label">Username</span>
                    <span class="profile-value accent-red bold"><?= htmlspecialchars($me["username"]) ?></span>
                </div>
                <div class="profile-info-row">
                    <span class="profile-label">Role</span>
                    <span class="profile-value"><?= htmlspecialchars($me["role"]) ?></span>
                </div>
                <div class="profile-info-row">
                    <span class="profile-label">Department</span>
                    <span class="profile-value"><?= htmlspecialchars($me["department"]) ?></span>
                </div>
                <div class="profile-info-row">
                    <span class="profile-label">Status</span>
                    <span class="badge badge-green"><?= htmlspecialchars($me["status"]) ?></span>
                </div>
                <div class="profile-info-row">
                    <span class="profile-label">Joined</span>
                    <span class="profile-value muted"><?= htmlspecialchars($me["joined_date"]) ?></span>
                </div>
                
            </div>

            <div>
                <div class="dash-section" style="margin-bottom: 24px;">
                    <h2 class="section-title">Change Username</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="change_username" />
                        <div class="form-group">
                            <label>New Username</label>
                            <input type="text" name="new_username" required minlength="3" placeholder="enter new username" />
                        </div>
                        <div class="form-group">
                            <label>Confirm with Current Password</label>
                            <input type="password" name="password_confirm" required placeholder="your current password" />
                        </div>
                        <button type="submit" class="dash-btn">Update Username</button>
                    </form>
                </div>

                <div class="dash-section">
                    <h2 class="section-title">Change Password</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password" />
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required placeholder="your current password" />
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" required minlength="6" placeholder="min 6 characters" />
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" required placeholder="type new password again" />
                        </div>
                        <button type="submit" class="dash-btn">Update Password</button>
                    </form>
                </div>
            </div>

        </div>
    </main>
</div>

</body>
</html>
<?php $conn->close(); ?>
