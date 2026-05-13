<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION["staff_id"]) || $_SESSION["role"] !== "Payment Manager") {
    header("Location: ../login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "update_delivery") {
        $payment_id = intval($_POST["payment_id"] ?? 0);
        $delivery   = trim($_POST["delivery_status"] ?? "");
        $valid      = ["Pending", "Processing", "Shipped", "In Transit", "Delivered", "Cancelled"];
        if ($payment_id > 0 && in_array($delivery, $valid)) {
            $stmt = $conn->prepare("UPDATE payments SET delivery_status = ? WHERE payment_id = ?");
            $stmt->bind_param("si", $delivery, $payment_id);
            $stmt->execute();
            $stmt->close();
            $message = "Delivery status updated.";
        }
    }

    if ($action === "update_payment") {
        $payment_id = intval($_POST["payment_id"] ?? 0);
        $status     = trim($_POST["payment_status"] ?? "");
        $valid      = ["pending", "completed", "refunded"];
        if ($payment_id > 0 && in_array($status, $valid)) {
            $stmt = $conn->prepare("UPDATE payments SET status = ? WHERE payment_id = ?");
            $stmt->bind_param("si", $status, $payment_id);
            $stmt->execute();
            $stmt->close();
            $message = "Payment status updated.";
        }
    }
}

$query = "
    SELECT p.payment_id, p.order_id, p.amount, p.method, p.status, p.delivery_status, p.payment_date,
           c.full_name AS customer_name
    FROM payments p
    LEFT JOIN orders o    ON p.order_id = o.order_id
    LEFT JOIN customers c ON o.customer_id = c.customer_id
    ORDER BY p.payment_id DESC
";
$payments = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

$total_revenue = 0;
$completed     = 0;
$pending       = 0;
$refunded      = 0;
$method_totals = ["PayPal" => 0, "Card" => 0, "Apple Pay" => 0];

foreach ($payments as $p) {
    if ($p["status"] === "completed") {
        $total_revenue += $p["amount"];
        $completed++;
    }
    if ($p["status"] === "pending")  $pending++;
    if ($p["status"] === "refunded") $refunded++;
    if (isset($method_totals[$p["method"]])) {
        $method_totals[$p["method"]] += $p["amount"];
    }
}
$grand_total = array_sum($method_totals);

$page_title = "Payment & Delivery";
$active_page = "payment";
include '../includes/header.php';
?>

<div class="dash-header">
    <div class="dash-eyebrow">Payment &amp; Delivery</div>
    <h1 class="dash-title">Hello, <span class="accent-red">Mahraz</span>!</h1>
    <p class="dash-sub">Money in, parcels out. The heartbeat of the bazaar.</p>
</div>

<?php if ($message): ?>
    <div class="dash-message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value accent-green">$<?= number_format($total_revenue, 0) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Completed</div>
        <div class="stat-value accent-green"><?= $completed ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending</div>
        <div class="stat-value accent-yellow"><?= $pending ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Refunded</div>
        <div class="stat-value accent-red"><?= $refunded ?></div>
    </div>
</div>

<section class="dash-section">
    <h2 class="section-title">Payment Methods Breakdown</h2>
    <div class="method-grid">
        <?php foreach ($method_totals as $method => $amount):
            $pct = $grand_total > 0 ? ($amount / $grand_total) * 100 : 0;
        ?>
        <div class="method-card">
            <div class="method-header">
                <span class="method-name"><?= htmlspecialchars($method) ?></span>
                <span class="accent-red bold">$<?= number_format($amount, 0) ?></span>
            </div>
            <div class="method-bar">
                <div class="method-bar-fill" style="width: <?= $pct ?>%;"></div>
            </div>
            <div class="muted small"><?= number_format($pct, 1) ?>% of total</div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="dash-section">
    <h2 class="section-title">All Transactions</h2>
    <div class="table-wrap">
        <table class="dash-table">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Date</th>
                    <th>Payment</th>
                    <th>Delivery</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $p):
                    $payBadgeMap = ["completed" => "badge-green", "pending" => "badge-yellow", "refunded" => "badge-red"];
                    $delBadgeMap = ["Delivered" => "badge-green", "In Transit" => "badge-blue", "Shipped" => "badge-yellow", "Pending" => "badge-purple", "Processing" => "badge-blue", "Cancelled" => "badge-red"];
                    $payBadge = $payBadgeMap[$p["status"]] ?? "badge-blue";
                    $delBadge = $delBadgeMap[$p["delivery_status"]] ?? "badge-blue";
                ?>
                <tr>
                    <td class="accent-red bold">PAY-<?= str_pad($p["payment_id"], 3, "0", STR_PAD_LEFT) ?></td>
                    <td class="muted">#TD-<?= str_pad($p["order_id"], 3, "0", STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($p["customer_name"] ?? "—") ?></td>
                    <td class="bold">$<?= number_format($p["amount"], 2) ?></td>
                    <td><span class="badge badge-purple"><?= htmlspecialchars($p["method"]) ?></span></td>
                    <td class="muted"><?= htmlspecialchars($p["payment_date"]) ?></td>
                    <td>
                        <form method="POST" style="display:flex; gap:6px;">
                            <input type="hidden" name="action" value="update_payment" />
                            <input type="hidden" name="payment_id" value="<?= $p["payment_id"] ?>" />
                            <select name="payment_status" class="status-select">
                                <?php foreach (["pending","completed","refunded"] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $opt === $p["status"] ? "selected" : "" ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="dash-btn-small">✓</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" style="display:flex; gap:6px;">
                            <input type="hidden" name="action" value="update_delivery" />
                            <input type="hidden" name="payment_id" value="<?= $p["payment_id"] ?>" />
                            <select name="delivery_status" class="status-select">
                                <?php foreach (["Pending","Processing","Shipped","In Transit","Delivered","Cancelled"] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $opt === $p["delivery_status"] ? "selected" : "" ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="dash-btn-small">✓</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php
include '../includes/footer.php';
$conn->close();
?>
