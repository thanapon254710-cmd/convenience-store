<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit;
}

require_once 'connect.php';

$message = '';

// Allowed order statuses (used for dropdowns + validation)
$allowedStatuses = ['Pending', 'Processing', 'Packed', 'Delivered', 'Completed', 'Cancelled'];

// -------------------------
// 1) Update order status
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $id     = (int)$_POST['order_id'];
    $status = $_POST['status'];

    if (in_array($status, $allowedStatuses, true)) {
        $stmt = $mysqli->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        if ($stmt) {
            $stmt->bind_param('si', $status, $id);
            if ($stmt->execute()) {
                $message = 'Order status updated.';
            } else {
                $message = 'Update failed: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = 'DB error: ' . $mysqli->error;
        }
    } else {
        $message = 'Invalid status selected.';
    }
}

// -------------------------
// 2) Filters & search
// -------------------------
$selectedStatus = $_GET['status'] ?? 'all';
$search         = trim($_GET['search'] ?? '');

// -------------------------
// 3) Summary cards data
// -------------------------
$stats = [
    'total_orders'     => 0,
    'open_orders'      => 0,
    'completed_orders' => 0,
    'cancelled_orders' => 0,
    'total_revenue'    => 0.00,
];

$statsSql = "
    SELECT
        COUNT(*) AS total_orders,
        SUM(CASE WHEN status IN ('Pending','Processing','Packed','Delivered') THEN 1 ELSE 0 END) AS open_orders,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed_orders,
        SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) AS cancelled_orders,
        IFNULL(SUM(total_amount),0) AS total_revenue
    FROM orders
";

if ($res = $mysqli->query($statsSql)) {
    if ($row = $res->fetch_assoc()) {
        $stats['total_orders']     = (int)$row['total_orders'];
        $stats['open_orders']      = (int)$row['open_orders'];
        $stats['completed_orders'] = (int)$row['completed_orders'];
        $stats['cancelled_orders'] = (int)$row['cancelled_orders'];
        $stats['total_revenue']    = (float)$row['total_revenue'];
    }
    $res->close();
}

// -------------------------
// 4) Load orders with filters
// -------------------------
$orders = [];
$where  = "WHERE 1=1 ";

if ($selectedStatus !== 'all' && in_array($selectedStatus, $allowedStatuses, true)) {
    $statusSafe = $mysqli->real_escape_string($selectedStatus);
    $where     .= " AND o.status = '$statusSafe' ";
}

if ($search !== '') {
    $searchSafe = $mysqli->real_escape_string($search);
    $orderId    = (int)$search;
    $where     .= " AND (o.order_id = $orderId OR u.username LIKE '%$searchSafe%') ";
}

$q = "
    SELECT o.order_id, o.order_date, o.total_amount, o.status, o.payment_type,
           u.username
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    $where
    ORDER BY o.order_date DESC, o.order_id DESC
";

if ($res = $mysqli->query($q)) {
    while ($row = $res->fetch_assoc()) {
        $orders[] = $row;
    }
    $res->close();
}

// -------------------------
// 5) Order detail panel
// -------------------------
$orderDetails = [];
$detailOrder  = null;

if (isset($_GET['order_id'])) {
    $oid = (int)$_GET['order_id'];

    // Header
    $stmt = $mysqli->prepare("
        SELECT o.*, u.username
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        WHERE o.order_id = ?
    ");
    if ($stmt) {
        $stmt->bind_param('i', $oid);
        $stmt->execute();
        $detailOrder = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    // Items
    $stmt = $mysqli->prepare("
        SELECT od.*, p.product_name
        FROM orderdetails od
        LEFT JOIN products p ON od.product_id = p.product_id
        WHERE od.order_id = ?
        ORDER BY od.order_id, od.product_id
    ");
    if ($stmt) {
        $stmt->bind_param('i', $oid);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $orderDetails[] = $row;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Orders — Number 1 Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#b94a4a',
                        accent: '#e86d6d',
                        sidebar: '#f4f7fb',
                        soft: '#f7f7f9',
                        outline: '#e8e8ea'
                    },
                    fontFamily: {
                        body: ['"PT Serif"', 'Georgia', 'serif'],
                        ui: ['Inter', 'system-ui', 'Arial']
                    },
                    boxShadow: {
                        'soft-lg': '0 14px 30px rgba(9,18,40,0.08)',
                        'card': '0 10px 30px rgba(15,23,42,0.08)'
                    }
                }
            }
        }
    </script>

    <!-- SweetAlert for logout -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmLogout() {
            Swal.fire({
                title: "Confirm Logout",
                text: "Are you sure?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#b94a4a",
                cancelButtonColor: "#6b7280",
                confirmButtonText: "Logout",
            }).then((res) => {
                if (res.isConfirmed) {
                    window.location = 'logout.php';
                }
            });
        }
    </script>
</head>
<body class="min-h-screen antialiased text-gray-800">
<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-sidebar p-4 sticky top-0 h-screen overflow-y-auto">
        <div class="bg-white border border-blue-300 rounded-xl p-4 shadow-sm flex flex-col h-full">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-600 to-cyan-400 text-white flex items-center justify-center text-lg font-bold">
                    <img src="asset/2960679-2182.png" class="w-full h-full object-cover rounded-lg" />
                </div>
                <div>
                    <div class="text-lg font-semibold">Admin<br/><span class="text-sm text-gray-500">Dashboard</span></div>
                </div>
            </div>

            <nav class="flex-1">
                <ul class="space-y-3">
                    <li>
                        <a href="ADMIN_HOME.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                            <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">📊</span>
                            <span class="text-sm font-medium">Overview</span>
                        </a>
                    </li>
                    <li>
                        <a href="ADMIN_STOCK.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                            <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">📦</span>
                            <span class="text-sm font-medium">Stock</span>
                        </a>
                    </li>
                    <li class="bg-red-50 rounded-lg">
                        <a href="ADMIN_ORDERS.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                            <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-primary">🧾</span>
                            <span class="text-sm font-medium">Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="ADMIN_CUSTOMERS.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                            <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">👥</span>
                            <span class="text-sm font-medium">Customer View</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="confirmLogout()" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                            <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">🚪</span>
                            <span class="text-sm font-medium">Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- MAIN -->
    <div class="flex-1 p-8">
        <div class="max-w-screen-xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold" style="font-family: 'PT Serif', Georgia, serif;">Orders</h1>
                    <div class="text-xs text-gray-500">View, filter and manage customer orders</div>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="mb-4 text-sm text-green-700 bg-green-100 border border-green-200 rounded-lg px-3 py-2">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Summary cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-card p-4 border border-gray-100">
                    <div class="text-xs text-gray-500 mb-1">Total Orders</div>
                    <div class="text-xl font-semibold"><?= (int)$stats['total_orders'] ?></div>
                </div>
                <div class="bg-white rounded-2xl shadow-card p-4 border border-gray-100">
                    <div class="text-xs text-gray-500 mb-1">Open Orders</div>
                    <div class="text-xl font-semibold text-amber-600"><?= (int)$stats['open_orders'] ?></div>
                </div>
                <div class="bg-white rounded-2xl shadow-card p-4 border border-gray-100">
                    <div class="text-xs text-gray-500 mb-1">Completed</div>
                    <div class="text-xl font-semibold text-green-600"><?= (int)$stats['completed_orders'] ?></div>
                </div>
                <div class="bg-white rounded-2xl shadow-card p-4 border border-gray-100">
                    <div class="text-xs text-gray-500 mb-1">Total Revenue</div>
                    <div class="text-xl font-semibold text-primary">฿<?= number_format($stats['total_revenue'], 2) ?></div>
                </div>
            </div>

            <!-- Filters -->
            <form method="get" class="bg-white rounded-2xl shadow-card p-4 mb-6 flex flex-wrap gap-4 items-end">
                <div class="flex flex-col">
                    <label class="text-xs text-gray-500 mb-1">Search (Order ID or Username)</label>
                    <input
                        type="text"
                        name="search"
                        value="<?= htmlspecialchars($search) ?>"
                        class="border border-outline rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
                        placeholder="e.g. 12 or kanadae"
                    >
                </div>

                <div class="flex flex-col">
                    <label class="text-xs text-gray-500 mb-1">Status</label>
                    <select
                        name="status"
                        class="border border-outline rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        <option value="all" <?= $selectedStatus === 'all' ? 'selected' : '' ?>>All statuses</option>
                        <?php foreach ($allowedStatuses as $st): ?>
                            <option value="<?= $st ?>" <?= $st === $selectedStatus ? 'selected' : '' ?>>
                                <?= $st ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-sm hover:bg-accent">
                        Apply
                    </button>
                    <a href="ADMIN_ORDERS.php" class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">
                        Clear
                    </a>
                </div>
            </form>

            <!-- Content layout: table + detail -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Orders table -->
                <div class="bg-white rounded-2xl shadow-card p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-lg font-semibold">Order List</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b text-gray-500 sticky top-0 bg-white">
                            <tr>
                                <th class="py-2 text-left">ID</th>
                                <th class="py-2 text-left">Customer</th>
                                <th class="py-2 text-left">Date</th>
                                <th class="py-2 text-right">Total</th>
                                <th class="py-2 text-right">Status</th>
                                <th class="py-2 text-right"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="6" class="py-4 text-center text-gray-400">
                                        No orders found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $o): ?>
                                    <tr class="border-b last:border-0">
                                        <td class="py-2">#<?= (int)$o['order_id'] ?></td>
                                        <td class="py-2 text-gray-700">
                                            <?= htmlspecialchars($o['username'] ?? 'Guest') ?>
                                        </td>
                                        <td class="py-2 text-gray-500 text-xs">
                                            <?= htmlspecialchars($o['order_date']) ?>
                                        </td>
                                        <td class="py-2 text-right font-semibold">
                                            ฿<?= number_format($o['total_amount'], 2) ?>
                                        </td>
                                        <td class="py-2 text-right">
                                            <form method="post" class="inline-flex items-center gap-1">
                                                <input type="hidden" name="order_id" value="<?= (int)$o['order_id'] ?>">
                                                <select
                                                    name="status"
                                                    class="border rounded px-1 py-0.5 text-xs"
                                                >
                                                    <?php foreach ($allowedStatuses as $st): ?>
                                                        <option value="<?= $st ?>" <?= $st === $o['status'] ? 'selected' : '' ?>>
                                                            <?= $st ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button
                                                    class="px-2 py-0.5 text-xs rounded bg-primary text-white hover:bg-accent"
                                                >
                                                    Save
                                                </button>
                                            </form>
                                        </td>
                                        <td class="py-2 text-right">
                                            <a href="ADMIN_ORDERS.php?order_id=<?= (int)$o['order_id'] ?>&status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($search) ?>"
                                               class="text-xs text-primary underline">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Order detail -->
                <div class="bg-white rounded-2xl shadow-card p-4">
                    <h2 class="text-lg font-semibold mb-3">Order Detail</h2>

                    <?php if (!$detailOrder): ?>
                        <p class="text-sm text-gray-400">
                            Select an order from the left to see details.
                        </p>
                    <?php else: ?>
                        <div class="mb-3 text-sm space-y-1">
                            <div><span class="font-semibold">Order ID:</span> #<?= (int)$detailOrder['order_id'] ?></div>
                            <div><span class="font-semibold">Customer:</span> <?= htmlspecialchars($detailOrder['username'] ?? 'Guest') ?></div>
                            <div><span class="font-semibold">Date:</span> <?= htmlspecialchars($detailOrder['order_date']) ?></div>
                            <div><span class="font-semibold">Status:</span> <?= htmlspecialchars($detailOrder['status']) ?></div>
                            <div><span class="font-semibold">Payment:</span> <?= htmlspecialchars($detailOrder['payment_type']) ?></div>
                        </div>

                        <table class="w-full text-sm mb-3">
                            <thead class="border-b text-gray-500">
                            <tr>
                                <th class="py-2 text-left">Product</th>
                                <th class="py-2 text-right">Qty</th>
                                <th class="py-2 text-right">Subtotal</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($orderDetails as $d): ?>
                                <tr class="border-b last:border-0">
                                    <td class="py-2"><?= htmlspecialchars($d['product_name']) ?></td>
                                    <td class="py-2 text-right"><?= (int)$d['quantity'] ?></td>
                                    <td class="py-2 text-right">
                                        ฿<?= number_format($d['subtotal'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="text-right text-sm font-semibold">
                            Total: ฿<?= number_format($detailOrder['total_amount'], 2) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>
