<?php
session_start();
require_once 'connect.php';

// AUTH
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: index.php');
    exit;
}

// ------------------ QUERIES ------------------

$totalIncome = 0;
$res = $mysqli->query("SELECT IFNULL(SUM(amount_paid),0) AS total FROM payments");
if ($res && $row = $res->fetch_assoc()) $totalIncome = (float)$row['total'];

$todayIncome = 0;
$res = $mysqli->query("SELECT IFNULL(SUM(amount_paid),0) AS total FROM payments WHERE payment_date = CURDATE()");
if ($res && $row = $res->fetch_assoc()) $todayIncome = (float)$row['total'];

$totalOrders = 0;
$res = $mysqli->query("SELECT COUNT(*) AS c FROM orders");
if ($res && $row = $res->fetch_assoc()) $totalOrders = (int)$row['c'];

$totalCustomers = 0;
$res = $mysqli->query("SELECT COUNT(*) AS c FROM users WHERE role = 'Customer'");
if ($res && $row = $res->fetch_assoc()) $totalCustomers = (int)$row['c'];

$topProducts = [];
$q = "SELECT p.product_id,p.product_name,p.category,SUM(od.quantity) AS qty_sold
      FROM orderdetails od
      JOIN products p ON od.product_id = p.product_id
      GROUP BY p.product_id,p.product_name,p.category
      ORDER BY qty_sold DESC LIMIT 5";
$res = $mysqli->query($q);
if ($res) while ($row = $res->fetch_assoc()) $topProducts[] = $row;

$lowStock = [];
$q = "SELECT product_id,product_name,category,stock_qty,status
      FROM products WHERE stock_qty <= 10 ORDER BY stock_qty ASC LIMIT 10";
$res = $mysqli->query($q);
if ($res) while ($row = $res->fetch_assoc()) $lowStock[] = $row;

$recentOrders = [];
$q = "SELECT o.order_id,o.order_date,o.total_amount,o.status,u.username
      FROM orders o
      LEFT JOIN users u ON o.user_id = u.user_id
      ORDER BY o.order_date DESC,o.order_id DESC LIMIT 5";
$res = $mysqli->query($q);
if ($res) while ($row = $res->fetch_assoc()) $recentOrders[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Overview — Number 1 Shop</title>

    <!-- STYLE -->
    <link rel="stylesheet" href="css/style.css" />
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind theme FIX (your original theme) -->
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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmLogout() {
            Swal.fire({
                title: "Confirm Logout",
                text: "Are you sure you want to logout?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#b94a4a",
                cancelButtonColor: "#6b7280",
                confirmButtonText: "Yes, logout",
                cancelButtonText: "Cancel",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "logout.php";
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
            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-600 to-cyan-400 flex items-center justify-center">
                <img src="asset/2960679-2182.png" class="w-full h-full object-cover rounded-lg" />
            </div>
            <div>
                <div class="text-lg font-semibold">Admin<br/><span class="text-sm text-gray-500">Dashboard</span></div>
            </div>
        </div>

        <nav class="flex-1">
            <ul class="space-y-3">
                <li class="bg-red-50 rounded-lg">
                    <a href="ADMIN_HOME.php" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50">
                        <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">📊</span>
                        <span class="text-sm font-medium">Overview</span>
                    </a>
                </li>

                <li>
                    <a href="ADMIN_STOCK.php" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50">
                        <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">📦</span>
                        <span class="text-sm font-medium">Stock</span>
                    </a>
                </li>

                <li>
                    <a href="ADMIN_ORDERS.php" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50">
                        <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">🧾</span>
                        <span class="text-sm font-medium">Orders</span>
                    </a>
                </li>

                <li>
                    <a href="ADMIN_CUSTOMERS.php" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50">
                        <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">👥</span>
                        <span class="text-sm font-medium">Customer View</span>
                    </a>
                </li>

                <!-- FIXED LOGOUT -->
                <li>
                    <a href="#" onclick="confirmLogout()" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50">
                        <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">🚪</span>
                        <span class="text-sm font-medium">Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>

<!-- MAIN CONTENT -->
<div class="flex-1 p-8">
    <div class="max-w-screen-xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Admin Overview</h1>

        <!-- Mini Cards (Income, Orders, Etc) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl p-4 shadow-card">
                <div class="text-xs text-gray-500">Total Income</div>
                <div class="text-2xl text-green-600 font-bold">฿<?= number_format($totalIncome, 2) ?></div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-card">
                <div class="text-xs text-gray-500">Today Income</div>
                <div class="text-2xl text-green-600 font-bold">฿<?= number_format($todayIncome, 2) ?></div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-card">
                <div class="text-xs text-gray-500">Total Orders</div>
                <div class="text-2xl font-bold"><?= $totalOrders ?></div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-card">
                <div class="text-xs text-gray-500">Total Customers</div>
                <div class="text-2xl font-bold"><?= $totalCustomers ?></div>
            </div>
        </div>

        <!-- Top Selling + Low Stock -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-4 shadow-card rounded-xl">
                <h2 class="text-lg font-semibold mb-2">Top Selling Products</h2>
                <table class="w-full text-sm">
                    <thead><tr><th>Product</th><th>Cat</th><th class="text-right">Sold</th></tr></thead>
                    <tbody>
                        <?php if (empty($topProducts)): ?>
                            <tr><td colspan="3" class="text-center py-3 text-gray-400">No sales data yet</td></tr>
                        <?php else: foreach ($topProducts as $p): ?>
                            <tr class="border-b">
                                <td><?= htmlspecialchars($p['product_name']) ?></td>
                                <td class="text-gray-500"><?= $p['category'] ?></td>
                                <td class="text-right font-semibold"><?= $p['qty_sold'] ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="bg-white p-4 shadow-card rounded-xl">
                <h2 class="text-lg font-semibold mb-2">Low Stock Products</h2>
                <table class="w-full text-sm">
                    <thead>
                        <tr><th>Product</th><th>Cat</th><th>Stock</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($lowStock)): ?>
                        <tr><td colspan="4" class="text-center text-gray-400 py-3">No low stock</td></tr>
                    <?php else: foreach ($lowStock as $p): ?>
                        <tr class="border-b">
                            <td><?= $p['product_name'] ?></td>
                            <td><?= $p['category'] ?></td>
                            <td><?= $p['stock_qty'] ?></td>
                            <td><?= $p['status'] ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RECENT ORDERS -->
        <div class="bg-white mt-8 p-4 shadow-card rounded-xl">
            <h2 class="text-lg font-semibold mb-2">Recent Orders</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr><th>Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php if (empty($recentOrders)): ?>
                    <tr><td colspan="5" class="text-center py-3 text-gray-400">No orders</td></tr>
                <?php else: foreach ($recentOrders as $o): ?>
                    <tr class="border-b">
                        <td>#<?= $o['order_id'] ?></td>
                        <td><?= $o['username'] ?></td>
                        <td><?= $o['order_date'] ?></td>
                        <td class="text-right">฿<?= number_format($o['total_amount'],2) ?></td>
                        <td><?= $o['status'] ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</div>
</body>
</html>
