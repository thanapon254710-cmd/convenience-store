<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: index.php');
    exit;
}

// Load customers + stats
$customers = [];
$q = "
    SELECT u.user_id, u.username, u.email, u.phone_number, u.points,
           COUNT(DISTINCT o.order_id) AS order_count,
           IFNULL(SUM(p.amount_paid),0) AS total_spent
    FROM users u
    LEFT JOIN orders o ON u.user_id = o.user_id
    LEFT JOIN payments p ON o.order_id = p.order_id
    WHERE u.role = 'Customer'
    GROUP BY u.user_id, u.username, u.email, u.phone_number, u.points
    ORDER BY u.username
";
if ($res = $mysqli->query($q)) {
    while ($row = $res->fetch_assoc()) {
        $customers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Customers — Number 1 Shop</title>
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
                    <li>
                        <a href="ADMIN_ORDERS.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                            <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">🧾</span>
                            <span class="text-sm font-medium">Orders</span>
                        </a>
                    </li>
                    <li class="bg-red-50 rounded-lg">
                        <a href="ADMIN_CUSTOMERS.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                            <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-primary">👥</span>
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
                    <h1 class="text-2xl font-bold" style="font-family: 'PT Serif', Georgia, serif;">Customers</h1>
                    <div class="text-xs text-gray-500">Customer list, points & spending</div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-card p-4">
                <table class="w-full text-sm">
                    <thead class="border-b text-gray-500">
                    <tr>
                        <th class="py-2 text-left">ID</th>
                        <th class="py-2 text-left">Username</th>
                        <th class="py-2 text-left">Email</th>
                        <th class="py-2 text-left">Phone</th>
                        <th class="py-2 text-right">Orders</th>
                        <th class="py-2 text-right">Total Spent</th>
                        <th class="py-2 text-right">Points</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="7" class="py-4 text-center text-gray-400">
                                No customers yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $c): ?>
                            <tr class="border-b last:border-0">
                                <td class="py-2"><?= (int)$c['user_id'] ?></td>
                                <td class="py-2"><?= htmlspecialchars($c['username']) ?></td>
                                <td class="py-2 text-gray-500"><?= htmlspecialchars($c['email'] ?? '') ?></td>
                                <td class="py-2 text-gray-500"><?= htmlspecialchars($c['phone_number'] ?? '') ?></td>
                                <td class="py-2 text-right"><?= (int)$c['order_count'] ?></td>
                                <td class="py-2 text-right">
                                    ฿<?= number_format($c['total_spent'], 2) ?>
                                </td>
                                <td class="py-2 text-right"><?= (int)$c['points'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
</body>
</html>