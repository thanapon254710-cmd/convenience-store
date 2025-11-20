<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: index.php');
    exit;
}

// Handle stock update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_stock') {
    $id   = (int)($_POST['product_id'] ?? 0);
    $qty  = (int)($_POST['stock_qty'] ?? 0);
    $stat = $_POST['status'] ?? 'Active';

    $stmt = $mysqli->prepare("UPDATE products SET stock_qty = ?, status = ? WHERE product_id = ?");
    if ($stmt) {
        $stmt->bind_param('isi', $qty, $stat, $id);
        if ($stmt->execute()) {
            $message = 'Stock updated.';
        } else {
            $message = 'Update failed: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = 'DB error: ' . $mysqli->error;
    }
}

// Load products
$products = [];
$q = "SELECT product_id, product_name, category, price, stock_qty, status FROM products ORDER BY category, product_name";
if ($res = $mysqli->query($q)) {
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Stock — Number 1 Shop</title>
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

    <!-- SIDEBAR (same as admin, Stock tab active) -->
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
                    <li class="bg-red-50 rounded-lg">
                        <a href="ADMIN_STOCK.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                            <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-primary">📦</span>
                            <span class="text-sm font-medium">Stock</span>
                        </a>
                    </li>
                    <li>
                        <a href="ADMIN_ORDERS.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                            <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">🧾</span>
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
                    <h1 class="text-2xl font-bold" style="font-family: 'PT Serif', Georgia, serif;">Stock Management</h1>
                    <div class="text-xs text-gray-500">View and update product stock levels</div>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="mb-4 text-sm text-green-700 bg-green-100 border border-green-200 rounded-lg px-3 py-2">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-card p-4">
                <table class="w-full text-sm">
                    <thead class="border-b text-gray-500">
                    <tr>
                        <th class="py-2 text-left">ID</th>
                        <th class="py-2 text-left">Product</th>
                        <th class="py-2 text-left">Category</th>
                        <th class="py-2 text-right">Price</th>
                        <th class="py-2 text-right">Stock</th>
                        <th class="py-2 text-right">Status</th>
                        <th class="py-2 text-right">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="py-4 text-center text-gray-400">
                                No products found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr class="border-b last:border-0">
                                <td class="py-2"><?= (int)$p['product_id'] ?></td>
                                <td class="py-2"><?= htmlspecialchars($p['product_name']) ?></td>
                                <td class="py-2 text-gray-500"><?= htmlspecialchars($p['category']) ?></td>
                                <td class="py-2 text-right">฿<?= number_format($p['price'], 2) ?></td>
                                <td class="py-2 text-right">
                                    <form method="post" class="inline-flex items-center justify-end gap-2">
                                        <input type="hidden" name="action" value="update_stock">
                                        <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
                                        <input type="number" name="stock_qty"
                                               class="w-20 border rounded px-1 py-0.5 text-right text-xs"
                                               value="<?= (int)$p['stock_qty'] ?>">
                                </td>
                                <td class="py-2 text-right">
                                        <select name="status" class="border rounded px-1 py-0.5 text-xs">
                                            <?php foreach (['Active','Inactive','Out of Stock'] as $st): ?>
                                                <option value="<?= $st ?>" <?= $st === $p['status'] ? 'selected' : '' ?>>
                                                    <?= $st ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                </td>
                                <td class="py-2 text-right">
                                        <button type="submit"
                                                class="px-2 py-1 text-xs rounded-md bg-primary text-white hover:bg-accent">
                                            Save
                                        </button>
                                    </form>
                                </td>
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