<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit;
}

require_once 'connect.php';

// =============== TOTAL INCOME ============
$totalIncome = 0;
$todayIncome = 0;

$sql = "SELECT IFNULL(SUM(total_amount), 0) AS total FROM orders";
$res = $mysqli->query($sql);
if ($row = $res->fetch_assoc()) {
    $totalIncome = (float)$row['total'];
}

$sql = "SELECT IFNULL(SUM(total_amount), 0) AS total FROM orders WHERE DATE(order_date) = CURDATE()";
$res = $mysqli->query($sql);
if ($row = $res->fetch_assoc()) {
    $todayIncome = (float)$row['total'];
}

// =============== TOTAL ORDERS ============
$res = $mysqli->query("SELECT COUNT(*) AS c FROM orders");
$row = $res->fetch_assoc();
$totalOrders = (int)$row['c'];

// =============== TOTAL CUSTOMERS ============
$res = $mysqli->query("SELECT COUNT(*) AS c FROM users WHERE role='Customer'");
$row = $res->fetch_assoc();
$totalCustomers = (int)$row['c'];

// =============== RECENT ORDERS ============
$recentOrders = [];
$sql = "
    SELECT o.order_id, o.order_date, o.total_amount, o.status,
           u.username
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_id DESC
    LIMIT 5
";
$res = $mysqli->query($sql);
while ($row = $res->fetch_assoc()) {
    $recentOrders[] = $row;
}

// =============== TOP SELLING PRODUCTS ============
$topProducts = [];
$sql = "
    SELECT p.product_name, SUM(od.quantity) AS sold
    FROM orderdetails od
    LEFT JOIN products p ON od.product_id = p.product_id
    GROUP BY od.product_id
    ORDER BY sold DESC
    LIMIT 5
";
$res = $mysqli->query($sql);
while ($row = $res->fetch_assoc()) {
    $topProducts[] = $row;
}

// =============== LOW STOCK ============
$lowStock = [];
$sql = "
    SELECT product_name, stock_qty
    FROM products
    WHERE stock_qty <= 10
    ORDER BY stock_qty ASC
    LIMIT 5
";
$res = $mysqli->query($sql);
while ($row = $res->fetch_assoc()) {
    $lowStock[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Overview — Number 1 Shop</title>
    <link rel="stylesheet" href="css/style.css" />
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#b94a4a',
                        accent: '#e86d6d',
                        sidebar: '#f4f7fb',
                        outline: '#e8e8ea'
                    },
                    fontFamily: {
                        body: ['"PT Serif"', 'Georgia', 'serif'],
                        ui: ['Inter', 'system-ui', 'Arial']
                    },
                    boxShadow: {
                        'card': '0 10px 30px rgba(15,23,42,0.08)'
                    }
                }
            }
        };
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
<div class="flex">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-sidebar p-4 sticky top-0 h-screen overflow-y-auto">
        <div class="bg-white border border-blue-300 rounded-xl p-4 shadow-sm flex flex-col h-full">

            <!-- Logo -->
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-lg overflow-hidden">
                    <img src="asset/2960679-2182.png" class="w-full h-full object-cover" />
                </div>
                <div>
                    <h2 class="text-lg font-semibold">Admin</h2>
                    <p class="text-sm text-gray-500 -mt-1">Dashboard</p>
                </div>
            </div>

            <!-- NAV -->
            <nav class="flex-1">
                <ul class="space-y-3">

                    <li class="bg-red-50 rounded-lg">
                        <a href="ADMIN_HOME.php" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50">
                            📊 <span class="text-sm font-medium">Overview</span>
                        </a>
                    </li>

                    <li>
                        <a href="ADMIN_STOCK.php" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50">
                            📦 <span class="text-sm font-medium">Stock</span>
                        </a>
                    </li>

                    <li>
                        <a href="ADMIN_ORDERS.php" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50">
                            🧾 <span class="text-sm font-medium">Orders</span>
                        </a>
                    </li>

                    <li>
                        <a href="ADMIN_CUSTOMERS.php" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50">
                            👥 <span class="text-sm font-medium">Customer View</span>
                        </a>
                    </li>

                    <li>
                        <a href="#" onclick="confirmLogout()" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50">
                            🚪 <span class="text-sm font-medium">Logout</span>
                        </a>
                    </li>

                </ul>
            </nav>

        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-8">
        <div class="max-w-screen-xl mx-auto">

            <h1 class="text-2xl font-bold mb-6">Admin Overview</h1>

            <!-- STATS CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-4 rounded-2xl shadow-card text-center">
                    <p class="text-sm text-gray-500">Total Income</p>
                    <p class="text-2xl font-semibold text-green-600">฿<?= number_format($totalIncome, 2) ?></p>
                </div>

                <div class="bg-white p-4 rounded-2xl shadow-card text-center">
                    <p class="text-sm text-gray-500">Today Income</p>
                    <p class="text-2xl font-semibold text-green-600">฿<?= number_format($todayIncome, 2) ?></p>
                </div>

                <div class="bg-white p-4 rounded-2xl shadow-card text-center">
                    <p class="text-sm text-gray-500">Total Orders</p>
                    <p class="text-2xl font-semibold"><?= $totalOrders ?></p>
                </div>

                <div class="bg-white p-4 rounded-2xl shadow-card text-center">
                    <p class="text-sm text-gray-500">Total Customers</p>
                    <p class="text-2xl font-semibold"><?= $totalCustomers ?></p>
                </div>
            </div>

            <!-- TOP + LOW STOCK -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

                <!-- Top Selling -->
                <div class="bg-white rounded-2xl shadow-card p-4">
                    <h2 class="font-semibold mb-3">Top Selling Products</h2>
                    <?php if (empty($topProducts)): ?>
                        <p class="text-gray-400 text-sm">No sales yet.</p>
                    <?php else: foreach ($topProducts as $item): ?>
                        <div class="mb-2 text-sm flex justify-between">
                            <span><?= htmlspecialchars($item['product_name']) ?></span>
                            <span class="font-semibold text-primary"><?= (int)$item['sold'] ?> sold</span>
                        </div>
                    <?php endforeach; endif; ?>
                </div>

                <!-- Low Stock -->
                <div class="bg-white rounded-2xl shadow-card p-4">
                    <h2 class="font-semibold mb-3">Low Stock Levels</h2>
                    <?php if (empty($lowStock)): ?>
                        <p class="text-gray-400 text-sm">No low stock products.</p>
                    <?php else: foreach ($lowStock as $item): ?>
                        <div class="mb-2 text-sm flex justify-between">
                            <span><?= htmlspecialchars($item['product_name']) ?></span>
                            <span class="font-semibold text-red-600"><?= (int)$item['stock_qty'] ?> left</span>
                        </div>
                    <?php endforeach; endif; ?>
                </div>

            </div>

            <!-- RECENT ORDERS -->
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
                                if ($st === "Pending")     $badge = "bg-amber-100 text-amber-700";
                                if ($st === "Completed")   $badge = "bg-green-100 text-green-700";
                                if ($st === "Cancelled")   $badge = "bg-red-100 text-red-700";
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 font-semibold text-gray-800">#<?= (int)$o['order_id'] ?></td>
                                <td class="py-3"><?= htmlspecialchars($o['username']) ?></td>
                                <td class="py-3 text-gray-500 text-xs"><?= $o['order_date'] ?></td>
                                <td class="py-3 text-right font-semibold">฿<?= number_format($o['total_amount'], 2) ?></td>
                                <td class="py-3 text-right">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?= $badge ?>">
                                        <?= $st ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

</div>
</body>
</html>
