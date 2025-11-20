<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: index.php');
    exit;
}

$message = '';

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $id = (int)$_POST['order_id'];
    $status = $_POST['status'];

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
}

// Load orders
$orders = [];
$q = "
    SELECT o.order_id, o.order_date, o.total_amount, o.status, o.payment_type,
           u.username
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC, o.order_id DESC
";
if ($res = $mysqli->query($q)) {
    while ($row = $res->fetch_assoc()) {
        $orders[] = $row;
    }
}

// If order detail requested
$orderDetails = [];
$detailOrder = null;
if (isset($_GET['order_id'])) {
    $oid = (int)$_GET['order_id'];
    // Order header
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
        JOIN products p ON od.product_id = p.product_id
        WHERE od.order_id = ?
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
                        'card': '0 10px 20px rgba(9,18,40,0.06)'
                    }
                }
            }
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
                        <a href="logout.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
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
                    <div class="text-xs text-gray-500">View and manage customer orders</div>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="mb-4 text-sm text-green-700 bg-green-100 border border-green-200 rounded-lg px-3 py-2">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Orders list -->
                <div class="bg-white rounded-2xl shadow-card p-4">
                    <h2 class="text-lg font-semibold mb-3">All Orders</h2>
                    <div class="max-h-[480px] overflow-y-auto pr-1">
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
                                        No orders yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $o): ?>
                                    <tr class="border-b last:border-0">
                                        <td class="py-2">#<?= (int)$o['order_id'] ?></td>
                                        <td class="py-2 text-gray-700">
                                            <?= htmlspecialchars($o['username'] ?? 'Guest') ?>
                                        </td>
                                        <td class="py-2 text-gray-500"><?= htmlspecialchars($o['order_date']) ?></td>
                                        <td class="py-2 text-right font-semibold">
                                            ฿<?= number_format($o['total_amount'], 2) ?>
                                        </td>
                                        <td class="py-2 text-right">
                                            <form method="post" class="inline-flex items-center gap-1">
                                                <input type="hidden" name="order_id" value="<?= (int)$o['order_id'] ?>">
                                                <select name="status" class="border rounded px-1 py-0.5 text-xs">
                                                    <?php foreach (['Pending','Processing','Packed','Delivered','Completed','Cancelled'] as $st): ?>
                                                        <option value="<?= $st ?>" <?= $st === $o['status'] ? 'selected' : '' ?>>
                                                            <?= $st ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button class="px-2 py-0.5 text-xs rounded bg-primary text-white hover:bg-accent">
                                                    Save
                                                </button>
                                            </form>
                                        </td>
                                        <td class="py-2 text-right">
                                            <a href="ADMIN_ORDERS.php?order_id=<?= (int)$o['order_id'] ?>"
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
                        <p class="text-sm text-gray-400">Select an order from the left to see details.</p>
                    <?php else: ?>
                        <div class="mb-3 text-sm">
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