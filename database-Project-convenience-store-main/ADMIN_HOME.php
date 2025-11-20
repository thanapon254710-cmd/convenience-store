<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit;
}

require_once 'connect.php';

/* ===============================
   DASHBOARD ANALYTICS QUERIES
   (REAL database values)
================================*/

// TOTAL INCOME
$totalIncome = 0;
$res = $mysqli->query("SELECT IFNULL(SUM(amount_paid),0) AS total FROM payments");
if ($res && $row = $res->fetch_assoc()) $totalIncome = (float)$row['total'];

// TODAY INCOME
$todayIncome = 0;
$res = $mysqli->query("SELECT IFNULL(SUM(amount_paid),0) AS total FROM payments WHERE payment_date = CURDATE()");
if ($res && $row = $res->fetch_assoc()) $todayIncome = (float)$row['total'];

// TOTAL ORDERS
$totalOrders = 0;
$res = $mysqli->query("SELECT COUNT(*) AS c FROM orders");
if ($res && $row = $res->fetch_assoc()) $totalOrders = (int)$row['c'];

// TOTAL CUSTOMERS
$totalCustomers = 0;
$res = $mysqli->query("SELECT COUNT(*) AS c FROM users WHERE role='Customer'");
if ($res && $row = $res->fetch_assoc()) $totalCustomers = (int)$row['c'];

// TOP SELLING PRODUCTS (for bar chart)
$topProducts = [];
$q = "SELECT p.product_name, SUM(od.quantity) AS qty_sold
      FROM orderdetails od
      JOIN products p ON od.product_id = p.product_id
      GROUP BY p.product_id
      ORDER BY qty_sold DESC LIMIT 5";

$res = $mysqli->query($q);
if ($res) while ($row = $res->fetch_assoc()) $topProducts[] = $row;

// LOW STOCK PRODUCTS (for chart + table)
$lowStock = [];
$q = "SELECT product_name, stock_qty
      FROM products
      WHERE stock_qty <= 10
      ORDER BY stock_qty ASC LIMIT 10";

$res = $mysqli->query($q);
if ($res) while ($row = $res->fetch_assoc()) $lowStock[] = $row;

// RECENT 5 ORDERS
$recentOrders = [];
$q = "SELECT o.order_id, o.order_date, o.total_amount, o.status, u.username
      FROM orders o
      LEFT JOIN users u ON o.user_id = u.user_id
      ORDER BY o.order_date DESC LIMIT 5";

$res = $mysqli->query($q);
if ($res) while ($row = $res->fetch_assoc()) $recentOrders[] = $row;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Overview — Number 1 Shop</title>

    <link rel="stylesheet" href="css/style.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
                        ui: ['Inter', 'system-ui']
                    },
                    boxShadow: {
                        'card': '0 10px 20px rgba(9,18,40,0.06)'
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
            }).then((res)=>{ if(res.isConfirmed) window.location='logout.php'; });
        }
    </script>
</head>

<body class="min-h-screen antialiased text-gray-800">
<div class="flex min-h-screen">

<!-- SIDEBAR (unchanged) -->
<aside class="w-64 bg-sidebar p-4 sticky top-0 h-screen overflow-y-auto">
    <div class="bg-white rounded-xl p-4 shadow-card flex flex-col h-full border border-blue-300">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-600 to-cyan-400 text-white flex items-center justify-center">
                <img src="asset/2960679-2182.png" class="w-full h-full rounded-lg" />
            </div>
            <div>
                <div class="text-lg font-semibold">Admin<br><span class="text-sm text-gray-500">Dashboard</span></div>
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

                <li><a href="ADMIN_STOCK.php" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50">
                    <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">📦</span>
                    <span class="text-sm font-medium">Stock</span></a>
                </li>

                <li><a href="ADMIN_ORDERS.php" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50">
                    <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">🧾</span>
                    <span class="text-sm font-medium">Orders</span></a>
                </li>

                <li><a href="ADMIN_CUSTOMERS.php" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50">
                    <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">👥</span>
                    <span class="text-sm font-medium">Customer View</span></a>
                </li>

                <li><a href="#" onclick="confirmLogout()" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50">
                    <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">🚪</span>
                    <span class="text-sm font-medium">Logout</span></a>
                </li>
            </ul>
        </nav>
    </div>
</aside>

<!-- MAIN CONTENT -->
<div class="flex-1 p-8">
    <div class="max-w-screen-xl mx-auto">

        <!-- TITLE -->
        <h1 class="text-2xl font-bold mb-4" style="font-family:'PT Serif',serif;">Admin Overview</h1>

        <!-- KPI CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl p-4 shadow-card">
                <div class="text-xs text-gray-500">Total Income</div>
                <div class="text-2xl text-green-600 font-bold">฿<?= number_format($totalIncome,2) ?></div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-card">
                <div class="text-xs text-gray-500">Today Income</div>
                <div class="text-2xl text-green-600 font-bold">฿<?= number_format($todayIncome,2) ?></div>
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

        <!-- CHARTS AREA -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

            <!-- TOP SELLING CHART -->
            <div class="bg-white rounded-xl p-4 shadow-card">
                <h2 class="text-lg font-semibold mb-3">Top Selling Products</h2>
                <canvas id="topSellingChart"></canvas>
            </div>

            <!-- LOW STOCK CHART -->
            <div class="bg-white rounded-xl p-4 shadow-card">
                <h2 class="text-lg font-semibold mb-3">Low Stock Levels</h2>
                <canvas id="lowStockChart"></canvas>
            </div>

        </div>

        <!-- RECENT ORDERS -->
        <div class="bg-white p-4 rounded-xl shadow-card">
            <h2 class="text-lg font-semibold mb-3">Recent Orders</h2>

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-500">
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($recentOrders)): ?>
                    <tr><td colspan="5" class="py-4 text-center text-gray-400">No orders</td></tr>
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

<!-- =======================
     CHART.JS SCRIPTS
======================= -->

<script>
/* TOP SELLING CHART DATA */
const topLabels = <?= json_encode(array_column($topProducts,'product_name')) ?>;
const topValues = <?= json_encode(array_column($topProducts,'qty_sold')) ?>;

new Chart(document.getElementById('topSellingChart'), {
    type: 'bar',
    data: {
        labels: topLabels,
        datasets: [{
            label: 'Units Sold',
            data: topValues,
            backgroundColor: '#b94a4a'
        }]
    }
});

/* LOW STOCK CHART DATA */
const lowLabels = <?= json_encode(array_column($lowStock,'product_name')) ?>;
const lowValues = <?= json_encode(array_column($lowStock,'stock_qty')) ?>;

new Chart(document.getElementById('lowStockChart'), {
    type: 'bar',
    data: {
        labels: lowLabels,
        datasets: [{
            label: 'Stock Quantity',
            data: lowValues,
            backgroundColor: '#e86d6d'
        }]
    }
});
</script>

</body>
</html>
