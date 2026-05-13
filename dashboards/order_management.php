<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION["staff_id"]) || $_SESSION["role"] !== "Order Manager") {
    header("Location: ../login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "update_status") {
        $order_id = intval($_POST["order_id"] ?? 0);
        $status   = trim($_POST["status"] ?? "");
        $valid    = ["pending", "processing", "shipped", "delivered", "cancelled"];
        if ($order_id > 0 && in_array($status, $valid)) {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
            $stmt->bind_param("si", $status, $order_id);
            $stmt->execute();
            $stmt->close();
            $message = "Order #" . $order_id . " updated to " . $status . ".";
        }
    }
}

$query = "
    SELECT o.order_id, o.item_count, o.total_amount, o.shipping_address, o.status, o.order_date,
           c.full_name AS customer_name
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.customer_id
    ORDER BY o.order_id DESC
";
$orders = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

$total_orders     = count($orders);
$processing_count = 0;
$delivered_count  = 0;
$revenue          = 0;
foreach ($orders as $o) {
    if ($o["status"] === "processing") $processing_count++;
    if ($o["status"] === "delivered")  $delivered_count++;
    if ($o["status"] !== "cancelled")  $revenue += $o["total_amount"];
}

$page_title = "Order Management";
$active_page = "order";
include '../includes/header.php';
?>

<div class="dash-header">
    <div class="dash-eyebrow">Order Management</div>
    <h1 class="dash-title">Hey <span class="accent-red">Ovy</span> 👋</h1>
    <p class="dash-sub">Orders flowing from desh to bidesh. Keep them moving.</p>
</div>

<?php if ($message): ?>
    <div class="dash-message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Total Orders</div>
        <div class="stat-value"><?= $total_orders ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Processing</div>
        <div class="stat-value accent-blue"><?= $processing_count ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Delivered</div>
        <div class="stat-value accent-green"><?= $delivered_count ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Revenue</div>
        <div class="stat-value accent-red">$<?= number_format($revenue, 0) ?></div>
    </div>
</div>

<section class="dash-section">
    <h2 class="section-title">All Orders</h2>
    <div class="table-wrap">
        <table class="dash-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Destination</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o):
                    $statusBadges = [
                        "pending"    => "badge-purple",
                        "processing" => "badge-blue",
                        "shipped"    => "badge-yellow",
                        "delivered"  => "badge-green",
                        "cancelled"  => "badge-red",
                    ];
                    $badge = $statusBadges[$o["status"]] ?? "badge-blue";
                ?>
                <tr>
                    <td class="accent-red bold">#TD-<?= str_pad($o["order_id"], 3, "0", STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($o["customer_name"] ?? "Unknown") ?></td>
                    <td class="center"><?= $o["item_count"] ?></td>
                    <td class="bold">$<?= number_format($o["total_amount"], 2) ?></td>
                    <td class="muted"><?= htmlspecialchars($o["shipping_address"]) ?></td>
                    <td class="muted"><?= htmlspecialchars($o["order_date"]) ?></td>
                    <td><span class="badge <?= $badge ?>"><?= $o["status"] ?></span></td>
                    <td>
                        <form method="POST" style="display:flex; gap:6px;">
                            <input type="hidden" name="action" value="update_status" />
                            <input type="hidden" name="order_id" value="<?= $o["order_id"] ?>" />
                            <select name="status" class="status-select">
                                <?php foreach (["pending","processing","shipped","delivered","cancelled"] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $opt === $o["status"] ? "selected" : "" ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="dash-btn-small">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($orders)): ?>
                <tr><td colspan="8" class="empty-row">No orders yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php
include '../includes/footer.php';
$conn->close();
?>
