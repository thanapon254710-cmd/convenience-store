<?php
session_start();
require_once("userconnect.php");

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];

$sql = "SELECT 
        o.order_id,
        o.order_date,
        o.status,
        o.total_amount,
        p.product_name,
        od.quantity
        FROM orders o
        JOIN orderdetails od ON o.order_id = od.order_id
        JOIN products p      ON od.product_id = p.product_id
        WHERE o.user_id = ?
        ORDER BY o.order_date DESC, o.order_id DESC";

$q = $mysqli->prepare($sql);
$q->bind_param("i", $userId);
$q->execute();
$result = $q->get_result();

$orders = [];

// Group rows by order_id
while ($row = $result->fetch_assoc()) {
    $orderId      = (int)$row['order_id'];
    $orderDate    = $row['order_date'];
    $status       = $row['status'];
    $totalAmount  = (float)$row['total_amount'];
    $productName  = $row['product_name'];
    $qty          = (int)$row['quantity'];

    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'id'     => 'ORD-' . str_pad($orderId, 4, '0', STR_PAD_LEFT),
            'date'   => $orderDate,
            'status' => $status,
            'total'  => $totalAmount,
            'items'  => []
        ];
    }

    $orders[$orderId]['items'][] = [
        'name' => $productName,
        'qty'  => $qty
    ];
}
$q->close();

// We now want ALL orders → just use array_values to reindex
$ordersList = array_values($orders);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History — Number 1 Shop</title>

    <script src="https://cdn.tailwindcss.com"></script>
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
                    boxShadow: {
                        card: "0 10px 20px rgba(0,0,0,0.06)"
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
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>

<body class="min-h-screen bg-white text-gray-800 flex">

<aside class="w-64 bg-sidebar p-4 sticky top-0 h-screen overflow-y-auto">
    <div class="bg-white border border-blue-300 rounded-xl p-4 shadow-sm flex flex-col h-full">
        <div class="flex items-center gap-3 mb-6">
            <a href="HOME.php" class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-600 to-cyan-400 text-white flex items-center justify-center text-lg font-bold">
                    <img src="asset/2960679-2182.png">
                </div>
                <div>
                    <div class="text-lg font-semibold">Convenience<br/><span class="text-sm text-gray-500">Store</span></div>
                </div>
            </a>
        </div>

        <nav class="flex-1">
            <ul class="space-y-3">
                <!--Tab Bar-->
                <li><a href="HOME.php"     class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">🏠</span><span class="text-sm font-medium">Home</span></a></li>
                <li><a href="WISHLIST.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">❤️</span><span class="text-sm font-medium">Wishlist </span></a></li>
                <li><a href="checkout.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💳</span><span class="text-sm font-medium">Checkout</span></a></li>
                <li class="bg-red-50 rounded-lg"><a href="preach.php"   class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-primary">📜</span><span class="text-sm font-medium">Preach History</span></a></li>
                <li><a href="contact.php"  class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💬</span><span class="text-sm font-medium">Contact us</span></a></li>
                <li><a href="setting.php"  class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">⚙️</span><span class="text-sm font-medium">Setting</span></a></li>
                <li><a href="#" onclick="confirmLogout()" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50"> <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">🚪</span><span class="text-sm font-medium">Logout</span></a></li>
            </ul>
        </nav>
    </div>
</aside>


<!-- ========== MAIN CONTENT ========== -->
<div class="flex-1 p-10">

    <h1 class="text-3xl font-bold mb-6" style="font-family:'PT Serif'">Order History</h1>

    <?php if (empty($ordersList)): ?>
        <div class="bg-white p-6 rounded-2xl shadow-card text-center">
            <p class="text-gray-500">You don't have any orders yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($ordersList as $order): ?>
            <div class="bg-white rounded-2xl shadow-card p-6 mb-6 max-w-3xl">

                <!-- ORDER HEADER -->
                <div class="flex justify-between">
                    <div>
                        <div class="font-semibold text-lg"><?= htmlspecialchars($order['id']) ?></div>
                        <div class="text-gray-500 text-sm"><?= htmlspecialchars($order['date']) ?></div>
                    </div>

                    <?php
                    $status = $order['status'];
                    $colorClass = "text-gray-600 font-medium";

                    if ($status === "Delivered" || $status === "Completed") {
                        $colorClass = "text-green-600 font-semibold";
                    } elseif ($status === "Processing" || $status === "Packed" || $status === "Pending") {
                        $colorClass = "text-orange-500 font-semibold";
                    } elseif ($status === "Cancelled") {
                        $colorClass = "text-red-500 font-semibold";
                    }
                    ?>
                    <div class="<?= $colorClass ?>"><?= htmlspecialchars($status) ?></div>
                </div>

                <!-- ITEMS -->
                <div class="mt-4">
                    <div class="font-semibold mb-1">Items:</div>
                    <ul class="list-disc ml-6 text-sm">
                        <?php foreach ($order['items'] as $item): ?>
                            <li><?= htmlspecialchars($item['name']) ?> (<?= (int)$item['qty'] ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- TOTAL -->
                <div class="mt-5 font-bold text-right text-gray-700">
                    Total: $<?= number_format($order['total'], 2) ?>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

</body>
</html>
