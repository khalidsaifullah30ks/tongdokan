<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION["staff_id"]) || $_SESSION["role"] !== "Customer Manager") {
    header("Location: ../login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add") {
        $name     = trim($_POST["full_name"] ?? "");
        $email    = trim($_POST["email"] ?? "");
        $phone    = trim($_POST["phone"] ?? "");
        $location = trim($_POST["location"] ?? "");

        if ($name && $email) {
            $stmt = $conn->prepare("INSERT INTO customers (full_name, email, phone, location, status, joined_date) VALUES (?, ?, ?, ?, 'new', CURDATE())");
            $stmt->bind_param("ssss", $name, $email, $phone, $location);
            if ($stmt->execute()) {
                $message = "New customer added.";
            } else {
                $message = "Could not add customer (email may already exist).";
            }
            $stmt->close();
        } else {
            $message = "Name and email are required.";
        }
    }

    if ($action === "delete") {
        $id = intval($_POST["customer_id"] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $message = "Customer removed.";
        }
    }
}

$customers = $conn->query("SELECT * FROM customers ORDER BY total_spent DESC")->fetch_all(MYSQLI_ASSOC);

$total_customers = count($customers);
$vip_count = 0;
$new_count = 0;
$total_revenue = 0;
foreach ($customers as $c) {
    if ($c["status"] === "vip") $vip_count++;
    if ($c["status"] === "new") $new_count++;
    $total_revenue += $c["total_spent"];
}

$page_title = "Customer Management";
$active_page = "customer";
include '../includes/header.php';
?>

<div class="dash-header">
    <div class="dash-eyebrow">Customer Management</div>
    <h1 class="dash-title">Salam, <span class="accent-red">Khalid</span></h1>
    <p class="dash-sub">These are the people who love Bangladesh as much as we do.</p>
</div>

<?php if ($message): ?>
    <div class="dash-message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Total Customers</div>
        <div class="stat-value"><?= $total_customers ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">VIP Customers</div>
        <div class="stat-value accent-purple"><?= $vip_count ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">New This Month</div>
        <div class="stat-value accent-red"><?= $new_count ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value accent-green">$<?= number_format($total_revenue, 0) ?></div>
    </div>
</div>

<section class="dash-section">
    <h2 class="section-title">Add New Customer</h2>
    <form method="POST" class="add-form">
        <input type="hidden" name="action" value="add" />
        <div class="form-row">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required />
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required />
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" />
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" placeholder="City, Country" />
            </div>
        </div>
        <button type="submit" class="dash-btn">Add Customer</button>
    </form>
</section>

<section class="dash-section">
    <h2 class="section-title">All Customers</h2>
    <div class="table-wrap">
        <table class="dash-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Location</th>
                    <th>Orders</th>
                    <th>Spent</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $c):
                    $badgeMap = ["vip" => "badge-purple", "active" => "badge-green", "new" => "badge-blue"];
                    $badge = $badgeMap[$c["status"]] ?? "badge-blue";
                    $label = $c["status"] === "vip" ? "⭐ VIP" : $c["status"];
                ?>
                <tr>
                    <td>C<?= str_pad($c["customer_id"], 3, "0", STR_PAD_LEFT) ?></td>
                    <td class="bold"><?= htmlspecialchars($c["full_name"]) ?></td>
                    <td class="muted"><?= htmlspecialchars($c["email"]) ?></td>
                    <td class="muted"><?= htmlspecialchars($c["phone"]) ?></td>
                    <td class="muted"><?= htmlspecialchars($c["location"]) ?></td>
                    <td class="center"><?= $c["total_orders"] ?></td>
                    <td class="accent-red bold">$<?= number_format($c["total_spent"], 2) ?></td>
                    <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Remove this customer?');" style="display:inline">
                            <input type="hidden" name="action" value="delete" />
                            <input type="hidden" name="customer_id" value="<?= $c["customer_id"] ?>" />
                            <button type="submit" class="link-btn">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($customers)): ?>
                <tr><td colspan="9" class="empty-row">No customers yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php
include '../includes/footer.php';
$conn->close();
?>
