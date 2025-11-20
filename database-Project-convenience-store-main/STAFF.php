<?php
session_start();
require_once 'userconnect.php';

// ---- ROLE GUARD ----
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') {
    header("Location: index.php");
    exit;
}

/* -------------------------------------------------
   TODAY INCOME (staff only sees today)
------------------------------------------------- */
$res = $mysqli->query("
    SELECT IFNULL(SUM(amount_paid), 0)
    FROM payments
    WHERE payment_date = CURDATE()
");
$row = $res ? $res->fetch_row() : [0];
$todayIncome = (float)$row[0];

/* -------------------------------------------------
   CONSTANTS (pagination)
------------------------------------------------- */
$perPage = 15;

/* -------------------------------------------------
   READ FILTERS / SORT / PAGE FROM GET
------------------------------------------------- */
$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$status   = trim($_GET['status']   ?? '');
$sortKey  = $_GET['sort'] ?? 'name';
$dir      = strtolower($_GET['dir'] ?? 'asc');
$page     = max(1, (int)($_GET['page'] ?? 1));

$dirSql = $dir === 'desc' ? 'DESC' : 'ASC';

$sortMap = [
    'id'       => 'product_id',
    'name'     => 'product_name',
    'category' => 'category',
    'price'    => 'price',
    'stock'    => 'stock_qty',
    'status'   => 'status'
];
$orderBy = $sortMap[$sortKey] ?? $sortMap['name'];

/* -------------------------------------------------
   HANDLE POST ACTIONS (update stock & status)
------------------------------------------------- */
$updateMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $pid    = (int)($_POST['product_id'] ?? 0);

    if ($pid > 0 && $action === 'update_stock') {
        $qty  = (int)($_POST['stock_qty'] ?? 0);
        // staff can set only Active / Out of Stock
        $stat = $_POST['status'] ?? 'Active';

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

/* -------------------------------------------------
   LOAD CATEGORY OPTIONS
------------------------------------------------- */
$categories = [];
$res = $mysqli->query("SELECT DISTINCT category FROM products ORDER BY category");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        if (!empty($row['category'])) {
            $categories[] = $row['category'];
        }
    }
}

/* -------------------------------------------------
   BUILD WHERE CLAUSE + PARAMS FOR FILTERS
------------------------------------------------- */
$whereParts = [];
$types  = '';
$params = [];

$whereParts[] = '1=1';

if ($search !== '') {
    $whereParts[] = '(product_name LIKE ? OR category LIKE ?)';
    $like = '%' . $search . '%';
    $types .= 'ss';
    $params[] = $like;
    $params[] = $like;
}

if ($category !== '') {
    $whereParts[] = 'category = ?';
    $types .= 's';
    $params[] = $category;
}

if ($status !== '') {
    $whereParts[] = 'status = ?';
    $types .= 's';
    $params[] = $status;
}

$whereSql = implode(' AND ', $whereParts);

/* -------------------------------------------------
   COUNT TOTAL ROWS (for pagination)
------------------------------------------------- */
$totalRows = 0;
$sqlCount  = "SELECT COUNT(*) AS cnt FROM products WHERE $whereSql";
$stmt      = $mysqli->prepare($sqlCount);
if ($stmt) {
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $totalRows = (int)$row['cnt'];
    }
    $stmt->close();
}

$totalPages = max(1, (int)ceil($totalRows / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

/* -------------------------------------------------
   LOAD PRODUCTS FOR CURRENT PAGE
------------------------------------------------- */
$products = [];
$sql = "
    SELECT product_id, product_name, category, price, stock_qty, status
    FROM products
    WHERE $whereSql
    ORDER BY $orderBy $dirSql
    LIMIT ?, ?
";
$stmt = $mysqli->prepare($sql);
if ($stmt) {
    $typesPage  = $types . 'ii';
    $paramsPage = $params;
    $paramsPage[] = $offset;
    $paramsPage[] = $perPage;

    if ($typesPage !== '') {
        $stmt->bind_param($typesPage, ...$paramsPage);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}

/* -------------------------------------------------
   COMMON QUERY PARAMS (for links)
------------------------------------------------- */
$baseParams = [
    'search'   => $search,
    'category' => $category,
    'status'   => $status
];

// staff can SET only these
$statusOptions = ['Active', 'Out of Stock'];
$statusFilterOptions = ['Active', 'Out of Stock'];
?>
<!Doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Staff Stock — Number 1 Shop</title>
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
<body class="min-h-screen antialiased text-gray-800 bg-soft">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-sidebar p-4 sticky top-0 h-screen overflow-y-auto">
        <div class="bg-white border border-blue-300 rounded-xl p-4 shadow-sm flex flex-col h-full">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-600 to-cyan-400 text-white flex items-center justify-center text-lg font-bold">
                    <img src="asset/2960679-2182.png" alt="logo" class="w-full h-full rounded-lg">
                </div>
                <div>
                    <div class="text-lg font-semibold">
                        Staff<br/>
                        <span class="text-sm text-gray-500">Dashboard</span>
                    </div>
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
                        <a href="STAFF_CUSTOMERS.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
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

    <!-- MAIN CONTENT -->
    <div class="flex-1 p-8">
        <div class="max-w-screen-xl mx-auto">

            <!-- HEADER -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold" style="font-family: 'PT Serif', Georgia, serif;">Stock Management</h1>
                    <div class="text-xs text-gray-500">
                        Staff can update product stock and change status between
                        <span class="font-semibold text-green-600">Active</span> and
                        <span class="font-semibold text-red-600">Out of Stock</span>.
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-card px-4 py-3">
                    <div class="text-xs text-gray-400">Today Income</div>
                    <div class="mt-1 text-xl font-semibold text-green-600">
                        ฿<?= number_format($todayIncome, 2) ?>
                    </div>
                </div>
            </div>

            <!-- MESSAGE -->
            <?php if ($updateMessage): ?>
                <div class="mb-4 text-sm text-green-700 bg-green-100 border border-green-200 rounded-lg px-3 py-2">
                    <?= htmlspecialchars($updateMessage) ?>
                </div>
            <?php endif; ?>

            <!-- FILTERS (same style as admin) -->
            <form method="get" class="mb-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Search</label>
                    <input type="text" name="search"
                           value="<?= htmlspecialchars($search) ?>"
                           class="border rounded-lg px-3 py-1.5 text-sm w-52"
                           placeholder="Product or category">
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Category</label>
                    <select name="category" class="border rounded-lg px-3 py-1.5 text-sm w-40">
                        <option value="">All</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $cat === $category ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Status</label>
                    <select name="status" class="border rounded-lg px-3 py-1.5 text-sm w-40">
                        <option value="">All</option>
                        <?php foreach ($statusFilterOptions as $opt): ?>
                            <option value="<?= $opt ?>" <?= $opt === $status ? 'selected' : '' ?>>
                                <?= $opt ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <input type="hidden" name="sort" value="<?= htmlspecialchars($sortKey) ?>">
                <input type="hidden" name="dir"  value="<?= htmlspecialchars($dir) ?>">

                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-primary text-white text-sm hover:bg-accent">
                    Apply
                </button>

                <a href="STAFF.php"
                   class="px-3 py-2 rounded-lg border text-sm text-gray-600 hover:bg-gray-50">
                    Reset
                </a>
            </form>

            <!-- TABLE CARD -->
            <div class="bg-white rounded-2xl shadow-card p-4">
                <div class="flex justify-between items-center mb-3 text-xs text-gray-500">
                    <div>
                        <?php if ($totalRows > 0): ?>
                            Showing
                            <span class="font-semibold"><?= $offset + 1 ?></span>
                            –
                            <span class="font-semibold"><?= min($offset + $perPage, $totalRows) ?></span>
                            of
                            <span class="font-semibold"><?= $totalRows ?></span>
                            products
                        <?php else: ?>
                            No products found
                        <?php endif; ?>
                    </div>
                </div>

                <table class="w-full text-sm">
                    <thead class="border-b text-gray-500 bg-gray-50">
                    <tr>
                        <?php
                        function sortLinkStaff($label, $key, $currentKey, $currentDir, $baseParams) {
                            $nextDir = ($currentKey === $key && $currentDir === 'asc') ? 'desc' : 'asc';
                            $params  = array_merge($baseParams, [
                                'sort' => $key,
                                'dir'  => $nextDir,
                                'page' => 1
                            ]);
                            $qs    = http_build_query($params);
                            $arrow = '';
                            if ($currentKey === $key) {
                                $arrow = $currentDir === 'asc' ? '▲' : '▼';
                            }
                            return '<a href="STAFF.php?' . $qs . '" class="flex items-center gap-1">'
                                 . htmlspecialchars($label) . ' <span class="text-[10px]">'
                                 . $arrow . '</span></a>';
                        }
                        ?>
                        <th class="py-2 text-left"><?= sortLinkStaff('ID', 'id', $sortKey, $dir, $baseParams) ?></th>
                        <th class="py-2 text-left"><?= sortLinkStaff('Product', 'name', $sortKey, $dir, $baseParams) ?></th>
                        <th class="py-2 text-left"><?= sortLinkStaff('Category', 'category', $sortKey, $dir, $baseParams) ?></th>
                        <th class="py-2 text-right"><?= sortLinkStaff('Price', 'price', $sortKey, $dir, $baseParams) ?></th>
                        <th class="py-2 text-right"><?= sortLinkStaff('Stock', 'stock', $sortKey, $dir, $baseParams) ?></th>
                        <th class="py-2 text-right"><?= sortLinkStaff('Status', 'status', $sortKey, $dir, $baseParams) ?></th>
                        <th class="py-2 text-right">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="py-4 text-center text-gray-400">
                                No products match your filters.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <?php
                            $qty = (int)$p['stock_qty'];

                            // stock color in Stock column
                            if ($qty <= 10) {
                                $qtyClass = 'text-red-600 font-semibold';
                            } elseif ($qty <= 30) {
                                $qtyClass = 'text-yellow-600 font-semibold';
                            } else {
                                $qtyClass = 'text-gray-800';
                            }

                            // fade + line-through when Out of Stock OR Inactive OR qty<=0
                            $isDimRow = (
                                $p['status'] === 'Out of Stock' ||
                                $p['status'] === 'Inactive' ||
                                $qty <= 0
                            );
                            $rowExtra  = $isDimRow ? 'opacity-70' : '';
                            $nameClass = $isDimRow ? 'text-gray-400 line-through' : 'text-gray-900';

                            // colored status text
                            if ($p['status'] === 'Active') {
                                $statusText  = 'Active';
                                $statusClass = 'text-green-600 font-semibold text-xs';
                            } elseif ($p['status'] === 'Out of Stock') {
                                $statusText  = 'Out of Stock';
                                $statusClass = 'text-red-600 font-semibold text-xs';
                            } else { // Inactive or other (from Admin)
                                $statusText  = htmlspecialchars($p['status']);
                                $statusClass = 'text-gray-500 font-semibold text-xs';
                            }

                            // Enable / Disable label (same logic as admin)
                            if ($p['status'] === 'Active' && $qty > 0) {
                                $actionLabel      = 'Enable';
                                $actionLabelClass = 'text-green-600 font-semibold text-xs';
                            } else {
                                $actionLabel      = 'Disable';
                                $actionLabelClass = 'text-red-600 font-semibold text-xs';
                            }
                            ?>
                            <tr class="border-b last:border-0 hover:bg-gray-50 transition <?= $rowExtra ?>">
                                <td class="py-2"><?= $p['product_id'] ?></td>
                                <td class="py-2 font-medium <?= $nameClass ?>">
                                    <?= htmlspecialchars($p['product_name']) ?>
                                </td>
                                <td class="py-2 text-gray-500"><?= htmlspecialchars($p['category']) ?></td>
                                <td class="py-2 text-right">฿<?= number_format($p['price'], 2) ?></td>
                                <td class="py-2 text-right <?= $qtyClass ?>"><?= $qty ?></td>
                                <td class="py-2 text-right">
                                    <span class="<?= $statusClass ?>"><?= $statusText ?></span>
                                </td>

                                <td class="py-2 text-right">
                                    <form method="post" class="flex items-center justify-end gap-2">
                                        <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">

                                        <input type="number"
                                               name="stock_qty"
                                               class="w-20 border rounded px-1 py-0.5 text-right text-xs"
                                               min="0"
                                               value="<?= $qty ?>">

                                        <select name="status" class="border rounded px-1 py-0.5 text-xs">
                                            <?php foreach ($statusOptions as $opt): ?>
                                                <option value="<?= $opt ?>" <?= $opt === $p['status'] ? 'selected' : '' ?>>
                                                    <?= $opt ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <button type="submit"
                                                name="action"
                                                value="update_stock"
                                                class="px-3 py-1 text-xs rounded-md bg-primary text-white hover:bg-accent transition">
                                            Save
                                        </button>

                                        <span class="<?= $actionLabelClass ?>">
                                            <?= $actionLabel ?>
                                        </span>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>

                <!-- PAGINATION -->
                <?php if ($totalPages > 1): ?>
                    <div class="mt-4 flex items-center justify-between text-xs text-gray-600">
                        <div>
                            Page <span class="font-semibold"><?= $page ?></span> of
                            <span class="font-semibold"><?= $totalPages ?></span>
                        </div>

                        <div class="flex gap-1">
                            <?php
                            if ($page > 1) {
                                $paramsPrev = array_merge($baseParams, [
                                    'sort' => $sortKey,
                                    'dir'  => $dir,
                                    'page' => $page - 1
                                ]);
                                echo '<a href="STAFF.php?' . http_build_query($paramsPrev) . '" 
                                          class="px-3 py-1 border rounded-lg hover:bg-gray-50">Previous</a>';
                            }

                            $startPage = max(1, $page - 2);
                            $endPage   = min($totalPages, $page + 2);
                            for ($i = $startPage; $i <= $endPage; $i++) {
                                $paramsPageLink = array_merge($baseParams, [
                                    'sort' => $sortKey,
                                    'dir'  => $dir,
                                    'page' => $i
                                ]);
                                $class = $i === $page
                                    ? 'px-3 py-1 rounded-lg bg-primary text-white'
                                    : 'px-3 py-1 rounded-lg border hover:bg-gray-50';
                                echo '<a href="STAFF.php?' . http_build_query($paramsPageLink) . '" class="' . $class . '">' . $i . '</a>';
                            }

                            if ($page < $totalPages) {
                                $paramsNext = array_merge($baseParams, [
                                    'sort' => $sortKey,
                                    'dir'  => $dir,
                                    'page' => $page + 1
                                ]);
                                echo '<a href="STAFF.php?' . http_build_query($paramsNext) . '" 
                                          class="px-3 py-1 border rounded-lg hover:bg-gray-50">Next</a>';
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </div>
</div>

</body>
</html>
