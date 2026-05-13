<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION["staff_id"]) || $_SESSION["role"] !== "Staff Manager") {
    header("Location: ../login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "update_status") {
        $id = intval($_POST["staff_id"] ?? 0);
        $status = trim($_POST["status"] ?? "");
        $valid = ["active", "inactive", "on_leave"];
        if ($id > 0 && in_array($status, $valid)) {
            $stmt = $conn->prepare("UPDATE staff SET status = ? WHERE staff_id = ?");
            $stmt->bind_param("si", $status, $id);
            $stmt->execute();
            $stmt->close();
            $message = "Staff status updated.";
        }
    }
}

$staff = $conn->query("SELECT * FROM staff ORDER BY staff_id ASC")->fetch_all(MYSQLI_ASSOC);

$total_staff   = count($staff);
$active_count  = 0;
$departments   = [];
foreach ($staff as $s) {
    if ($s["status"] === "active") $active_count++;
    if (!in_array($s["department"], $departments)) $departments[] = $s["department"];
}
$dept_count = count($departments);

$page_title = "Staff Management";
$active_page = "staff";
include '../includes/header.php';
?>

<div class="dash-header">
    <div class="dash-eyebrow">Staff Management</div>
    <h1 class="dash-title">What's up, <span class="accent-red">Shafin</span>!</h1>
    <p class="dash-sub">The team that makes the dokan run. You keep them together.</p>
</div>

<?php if ($message): ?>
    <div class="dash-message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Total Staff</div>
        <div class="stat-value"><?= $total_staff ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active Today</div>
        <div class="stat-value accent-green"><?= $active_count ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Departments</div>
        <div class="stat-value accent-red"><?= $dept_count ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Avg Experience</div>
        <div class="stat-value accent-purple">3 wks</div>
    </div>
</div>

<section class="dash-section">
    <h2 class="section-title">Department Overview</h2>
    <div class="dept-grid">
        <?php foreach ($staff as $s):
            $emojiMap = [
                "Catalogue"  => "🪡",
                "Operations" => "📦",
                "Support"    => "💬",
                "HR"         => "👨‍💼",
                "Finance"    => "💳",
            ];
            $emoji = $emojiMap[$s["department"]] ?? "👤";
        ?>
        <div class="dept-card">
            <div class="dept-emoji"><?= $emoji ?></div>
            <div>
                <div class="dept-name"><?= htmlspecialchars($s["department"]) ?></div>
                <div class="bold"><?= htmlspecialchars($s["full_name"]) ?></div>
                <div class="muted small"><?= htmlspecialchars($s["role"]) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="dash-section">
    <h2 class="section-title">All Staff Members</h2>
    <div class="table-wrap">
        <table class="dash-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Shift</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staff as $s):
                    $badgeMap = ["active" => "badge-green", "inactive" => "badge-red", "on_leave" => "badge-yellow"];
                    $badge = $badgeMap[$s["status"]] ?? "badge-blue";
                ?>
                <tr>
                    <td>S<?= str_pad($s["staff_id"], 3, "0", STR_PAD_LEFT) ?></td>
                    <td class="bold"><?= htmlspecialchars($s["full_name"]) ?></td>
                    <td class="muted"><?= htmlspecialchars($s["username"]) ?></td>
                    <td><?= htmlspecialchars($s["role"]) ?></td>
                    <td><span class="badge badge-blue"><?= htmlspecialchars($s["department"]) ?></span></td>
                    <td class="muted"><?= htmlspecialchars($s["shift"]) ?></td>
                    <td class="muted"><?= htmlspecialchars($s["joined_date"]) ?></td>
                    <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($s["status"]) ?></span></td>
                    <td>
                        <form method="POST" style="display:flex; gap:6px;">
                            <input type="hidden" name="action" value="update_status" />
                            <input type="hidden" name="staff_id" value="<?= $s["staff_id"] ?>" />
                            <select name="status" class="status-select">
                                <?php foreach (["active","inactive","on_leave"] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $opt === $s["status"] ? "selected" : "" ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="dash-btn-small">Save</button>
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
