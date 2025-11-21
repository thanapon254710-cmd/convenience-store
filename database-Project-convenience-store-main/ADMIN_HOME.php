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
$todayIncome = 0;

// 1) Overall total income
$sql = "SELECT IFNULL(SUM(total_amount), 0) AS total FROM orders";
if ($res = $mysqli->query($sql)) {
    if ($row = $res->fetch_assoc()) {
        $totalIncome = (float)$row['total'];
    }
    $res->close();
}

// 2) Today income
$sql = "SELECT IFNULL(SUM(total_amount), 0) AS total
        FROM orders
        WHERE DATE(order_date) = CURDATE()";
if ($res = $mysqli->query($sql)) {
    if ($row = $res->fetch_assoc()) {
        $todayIncome = (float)$row['total'];
    }
    $res->close();
}

// TOTAL ORDERS
$totalOrders = 0;
$sql = "SELECT COUNT(*) AS cnt FROM orders";
if ($res = $mysqli->query($sql)) {
    if ($row = $res->fetch_assoc()) {
        $totalOrders = (int)$row['cnt'];
    }
    $res->close();
}

// TOTAL CUSTOMERS (role = Customer)
$totalCustomers = 0;
$sql = "SELECT COUNT(*) AS cnt FROM users WHERE role = 'Customer'";
if ($res = $mysqli->query($sql)) {
    if ($row = $res->fetch_assoc()) {
        $totalCustomers = (int)$row['cnt'];
    }
    $res->close();
}

// RECENT ORDERS (for bottom table)
$recentOrders = [];
$sql = "
    SELECT o.order_id,
           o.order_date,
           o.total_amount,
           o.status,
           u.username
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_id DESC
    LIMIT 5
";
if ($res = $mysqli->query($sql)) {
    while ($row = $res->fetch_assoc()) {
        $recentOrders[] = $row;
    }
    $res->close();
}

// TOP SELLING PRODUCTS (for chart)
$topProducts = [];
$sql = "
    SELECT p.product_name,
           SUM(od.quantity) AS qty_sold
    FROM orderdetails od
    JOIN products p ON od.product_id = p.product_id
    GROUP BY od.product_id
    ORDER BY qty_sold DESC
    LIMIT 5
";
if ($res = $mysqli->query($sql)) {
    while ($row = $res->fetch_assoc()) {
        $topProducts[] = $row;
    }
    $res->close();
}

// LOW STOCK (for chart)
$lowStock = [];
$sql = "
    SELECT product_name, stock_qty
    FROM products
    ORDER BY stock_qty ASC
    LIMIT 5
";
if ($res = $mysqli->query($sql)) {
    while ($row = $res->fetch_assoc()) {
        $lowStock[] = $row;
    }
    $res->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Overview — Number 1 Shop</title>
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Tailwind CDN -->
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
            }).then((res) => {
                if (res.isConfirmed) {
                    window.location = 'logout.php';
                }
            });
        }
    </script>

    <!-- Chart.js for graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="min-h-screen antialiased text-gray-800">
<div class="flex min-h-screen">

    <aside class="w-64 bg-sidebar p-4 sticky top-0 h-screen overflow-y-auto">
        <div class="bg-white border border-blue-300 rounded-xl p-4 shadow-sm flex flex-col h-full">
            <div class="flex items-center gap-3 mb-6">
                <a href="ADMIN_HOME.php" class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-600 to-cyan-400 text-white flex items-center justify-center">
                        <img src="asset/2960679-2182.png" class="w-full h-full rounded-lg" />
                    </div>
                    <div>
                        <div class="text-m font-semibold">Admin<span class="text-m text-gray-500"> Dashboard</span></div>
                    </div>
                </a>
            </div>

            <nav class="flex-1">
                <ul class="space-y-3">
                    <!--Tab Bar-->
                    <li class="bg-red-50 rounded-lg"><a href="ADMIN_HOME.php" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">📊</span><span class="text-sm font-medium">Overview</span></a></li>
                    <li><a href="ADMIN_STOCK.php" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">📦</span><span class="text-sm font-medium">Stock</span></a></li>
                    <li><a href="ADMIN_ORDERS.php" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">🧾</span><span class="text-sm font-medium">Orders</span></a></li>
                    <li><a href="ADMIN_CUSTOMERS.php" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">👥</span><span class="text-sm font-medium">Customer View</span></a></li>
                    <li><a href="#" onclick="confirmLogout()" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">🚪</span><span class="text-sm font-medium">Logout</span></a></li>
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

            <!-- CHARTS -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Top Selling Chart Card -->
                <div class="bg-white rounded-2xl shadow-card p-4">
                    <h2 class="text-base font-semibold mb-3">Top Selling Products</h2>
                    <canvas id="topSellingChart" height="140"></canvas>
                </div>

                <!-- Low Stock Chart Card -->
                <div class="bg-white rounded-2xl shadow-card p-4">
                    <h2 class="text-base font-semibold mb-3">Low Stock Levels</h2>
                    <canvas id="lowStockChart" height="140"></canvas>
                </div>
            </div>

            <!-- RECENT ORDERS (IMPROVED DESIGN, graphs kept) -->
            <div class="bg-white rounded-2xl shadow-card p-4">
                <h2 class="text-lg font-semibold mb-3">Recent Orders</h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-xs uppercase tracking-wide text-gray-500 bg-gray-50 border-b">
                            <tr>
                                <th class="py-2 text-left">Order</th>
                                <th class="py-2 text-left">Customer</th>
                                <th class="py-2 text-left">Date</th>
                                <th class="py-2 text-right">Total</th>
                                <th class="py-2 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                        <?php if (empty($recentOrders)): ?>
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-400">
                                    No recent orders.
                                </td>
                            </tr>
                        <?php else: foreach ($recentOrders as $o): ?>
                            <?php
                                $st = $o['status'];
                                $badge = "bg-gray-100 text-gray-700";
                                if ($st === "Pending")   $badge = "bg-amber-100 text-amber-700";
                                if ($st === "Completed") $badge = "bg-green-100 text-green-700";
                                if ($st === "Cancelled") $badge = "bg-red-100 text-red-700";
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 font-semibold text-gray-800">#<?= (int)$o['order_id'] ?></td>
                                <td class="py-3"><?= htmlspecialchars($o['username']) ?></td>
                                <td class="py-3 text-gray-500 text-xs"><?= htmlspecialchars($o['order_date']) ?></td>
                                <td class="py-3 text-right font-semibold">
                                    ฿<?= number_format($o['total_amount'], 2) ?>
                                </td>
                                <td class="py-3 text-right">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?= $badge ?>">
                                        <?= htmlspecialchars($st) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
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
