<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION["staff_id"]) || $_SESSION["role"] !== "Product Manager") {
    header("Location: ../login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add") {
        $name     = trim($_POST["product_name"] ?? "");
        $desc     = trim($_POST["description"] ?? "");
        $price    = floatval($_POST["price"] ?? 0);
        $stock    = intval($_POST["stock_quantity"] ?? 0);
        $category = trim($_POST["category"] ?? "");
        $origin   = trim($_POST["origin"] ?? "");
        $emoji    = trim($_POST["emoji"] ?? "📦");
        $tag      = trim($_POST["tag"] ?? "New");

        if ($name && $price > 0) {
            $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, stock_quantity, category, origin, emoji, tag) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdissss", $name, $desc, $price, $stock, $category, $origin, $emoji, $tag);
            $stmt->execute();
            $stmt->close();
            $message = "Product added successfully.";
        } else {
            $message = "Please provide name and price.";
        }
    }

    if ($action === "delete") {
        $id = intval($_POST["product_id"] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $message = "Product removed.";
        }
    }
}

$products = $conn->query("SELECT * FROM products ORDER BY product_id DESC")->fetch_all(MYSQLI_ASSOC);

$total_products = count($products);
$active_count   = 0;
$low_stock      = 0;
$total_value    = 0;
foreach ($products as $p) {
    if ($p["stock_quantity"] > 5) $active_count++;
    if ($p["stock_quantity"] > 0 && $p["stock_quantity"] <= 5) $low_stock++;
    $total_value += $p["price"] * $p["stock_quantity"];
}

$page_title = "Product Management";
$active_page = "product";
include '../includes/header.php';
?>

<div class="dash-header">
    <div class="dash-eyebrow">Product Management</div>
    <h1 class="dash-title">Welcome back, <span class="accent-red">Siam</span></h1>
    <p class="dash-sub">Every product tells a story. You are the one who puts it on the shelf.</p>
</div>

<?php if ($message): ?>
    <div class="dash-message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Total Products</div>
        <div class="stat-value accent-red"><?= $total_products ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">In Stock</div>
        <div class="stat-value accent-green"><?= $active_count ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Low Stock</div>
        <div class="stat-value accent-yellow"><?= $low_stock ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Stock Value</div>
        <div class="stat-value">$<?= number_format($total_value, 0) ?></div>
    </div>
</div>

<section class="dash-section">
    <h2 class="section-title">Add New Product</h2>
    <form method="POST" class="add-form">
        <input type="hidden" name="action" value="add" />
        <div class="form-row">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="product_name" required />
            </div>
            <div class="form-group">
                <label>Price (USD)</label>
                <input type="number" name="price" step="0.01" min="0" required />
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" placeholder="e.g. Saree & Fabric" />
            </div>
            <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" name="stock_quantity" min="0" />
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Origin (District)</label>
                <input type="text" name="origin" placeholder="e.g. Dhaka" />
            </div>
            <div class="form-group">
                <label>Tag</label>
                <input type="text" name="tag" placeholder="e.g. New, Heritage" />
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="2"></textarea>
        </div>
        <button type="submit" class="dash-btn">Add Product</button>
    </form>
</section>

<section class="dash-section">
    <h2 class="section-title">All Products</h2>
    <div class="table-wrap">
        <table class="dash-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Origin</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p):
                    $stock = (int)$p["stock_quantity"];
                    if ($stock === 0) { $badge = "badge-red"; $status_text = "Out"; }
                    elseif ($stock <= 5) { $badge = "badge-yellow"; $status_text = "Low"; }
                    else { $badge = "badge-green"; $status_text = "Active"; }
                ?>
                <tr>
                    <td>#<?= $p["product_id"] ?></td>
                    <td><span class="product-emoji"><?= htmlspecialchars($p["emoji"] ?? "📦") ?></span> <?= htmlspecialchars($p["product_name"]) ?></td>
                    <td><span class="badge badge-blue"><?= htmlspecialchars($p["category"]) ?></span></td>
                    <td class="accent-red bold">$<?= number_format($p["price"], 2) ?></td>
                    <td><?= $stock ?></td>
                    <td class="muted"><?= htmlspecialchars($p["origin"]) ?></td>
                    <td><span class="badge <?= $badge ?>"><?= $status_text ?></span></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Remove this product?');" style="display:inline">
                            <input type="hidden" name="action" value="delete" />
                            <input type="hidden" name="product_id" value="<?= $p["product_id"] ?>" />
                            <button type="submit" class="link-btn">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($products)): ?>
                <tr><td colspan="8" class="empty-row">No products yet. Add one above.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php
include '../includes/footer.php';
$conn->close();
?>
