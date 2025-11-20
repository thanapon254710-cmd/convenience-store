<?php
session_start();
require_once 'userconnect.php';

// ---- ROLE GUARD ----
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') {
    header("Location: index.php");
    exit;
}

// Today income (staff can see only today)
$res = $mysqli->query("
    SELECT IFNULL(SUM(amount_paid), 0)
    FROM payments
    WHERE payment_date = CURDATE()
");
$row = $res ? $res->fetch_row() : [0];
$todayIncome = (float)$row[0];

// Stock list (all products, ordered by category then name)
$productsSql = "
    SELECT product_id, product_name, category, price, stock_qty, status
    FROM products
    ORDER BY category, product_name
";
$productsResult = $mysqli->query($productsSql);

// Handle stock updates (staff can only change stock_qty and status)
$updateMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $pid  = (int)($_POST['product_id'] ?? 0);
    $qty  = (int)($_POST['stock_qty'] ?? 0);
    $stat = $_POST['status'] ?? 'Active';

    if ($pid > 0) {
        $stmt = $mysqli->prepare("
            UPDATE products
            SET stock_qty = ?, status = ?
            WHERE product_id = ?
        ");
        if ($stmt) {
            $stmt->bind_param("isi", $qty, $stat, $pid);
            if ($stmt->execute()) {
                $updateMessage = "Stock updated for product ID {$pid}.";
            } else {
                $updateMessage = "Update failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $updateMessage = "Prepare failed: " . $mysqli->error;
        }
    }
}
?>
<!Doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Staff Dashboard — Number 1 Shop</title>
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
                    <img src="asset/2960679-2182.png" alt="logo">
                </div>
                <div>
                    <div class="text-lg font-semibold">Staff<br/><span class="text-sm text-gray-500">Dashboard</span></div>
                    <div class="text-xs text-gray-400 mt-1">
                        <?= htmlspecialchars($_SESSION['username'] ?? 'Staff') ?>
                    </div>
                </div>
            </div>

            <nav class="flex-1">
                <ul class="space-y-3">
                    <li class="bg-red-50 rounded-lg">
                        <a href="STAFF.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                            <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-primary">📦</span>
                            <span class="text-sm font-medium">Stock</span>
                        </a>
                    </li>
                    <li>
                        <a href="HOME.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                            <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">🛒</span>
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

    <!-- MAIN CONTENT -->
    <div class="flex-1 p-8">
        <div class="max-w-screen-xl mx-auto">

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold" style="font-family: 'PT Serif', Georgia, serif;">Stock Management</h1>
                    <div class="text-xs text-gray-500">Update product quantities & status</div>
                </div>

                <div class="bg-white rounded-2xl shadow-card px-4 py-3">
                    <div class="text-xs text-gray-400">Today Income</div>
                    <div class="mt-1 text-xl font-semibold text-green-600">
                        ฿<?= number_format($todayIncome, 2) ?>
                    </div>
                </div>
            </div>

            <?php if ($updateMessage): ?>
                <div class="mb-4 text-xs text-green-600">
                    <?= htmlspecialchars($updateMessage) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-card p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-semibold">All Products</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-gray-400 text-xs border-b">
                            <tr>
                                <th class="py-2 text-left">Product</th>
                                <th class="py-2 text-left">Category</th>
                                <th class="py-2 text-right">Price</th>
                                <th class="py-2 text-center">Stock</th>
                                <th class="py-2 text-center">Status</th>
                                <th class="py-2 text-center">Update</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($productsResult && $productsResult->num_rows > 0): ?>
                            <?php while ($row = $productsResult->fetch_assoc()): ?>
                                <tr class="border-b last:border-0">
                                    <td class="py-2">
                                        <?= htmlspecialchars($row['product_name']) ?>
                                    </td>
                                    <td class="py-2">
                                        <?= htmlspecialchars($row['category']) ?>
                                    </td>
                                    <td class="py-2 text-right">
                                        ฿<?= number_format($row['price'], 2) ?>
                                    </td>
                                    <td class="py-2 text-center">
                                        <?= (int)$row['stock_qty'] ?>
                                    </td>
                                    <td class="py-2 text-center">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </td>
                                    <td class="py-2">
                                        <form method="POST" class="flex items-center justify-center gap-2">
                                            <input type="hidden" name="product_id" value="<?= (int)$row['product_id'] ?>">
                                            <input type="number" name="stock_qty" min="0"
                                                   class="w-16 border rounded px-1 py-0.5 text-xs text-center"
                                                   value="<?= (int)$row['stock_qty'] ?>">
                                            <select name="status" class="border rounded px-1 py-0.5 text-xs">
                                                <option value="Active" <?= $row['status']==='Active'?'selected':''; ?>>Active</option>
                                                <option value="Inactive" <?= $row['status']==='Inactive'?'selected':''; ?>>Inactive</option>
                                                <option value="Out of Stock" <?= $row['status']==='Out of Stock'?'selected':''; ?>>Out of Stock</option>
                                            </select>
                                            <button type="submit" name="update_stock"
                                                    class="text-xs px-2 py-1 rounded bg-primary text-white hover:bg-accent">
                                                Save
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-3 text-center text-gray-400">
                                    No products found.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>

</body>
</html>