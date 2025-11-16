<?php
session_start();

// ---- SAMPLE ORDER DATA ----
$orders = [
    [
        "id" => "ORD-1001",
        "date" => "2025-01-08",
        "status" => "Delivered",
        "items" => [
            ["name" => "Premium Chocolate Bar", "qty" => 1],
        ],
        "total" => 45.50
    ],
    [
        "id" => "ORD-1003",
        "date" => "2025-01-15",
        "status" => "Processing",
        "items" => [
            ["name" => "Assorted Gummy Bears", "qty" => 1],
        ],
        "total" => 4.00
    ],
];

// Filter only delivered + processing
$orders_filtered = [];
foreach ($orders as $o) {
    if ($o['status'] === "Delivered" || $o['status'] === "Processing") {
        $orders_filtered[] = $o;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History — Convenience Store</title>

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

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>

<body class="min-h-screen bg-white text-gray-800 flex">

<!-- ========== SIDEBAR ========== -->
<aside class="w-64 bg-sidebar p-4 sticky top-0 h-screen overflow-y-auto">
    <div class="bg-white border border-blue-300 rounded-xl p-4 shadow-sm flex flex-col h-full">

        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-600 to-cyan-400 text-white flex items-center justify-center">
                <img src="asset/2960679-2182.png">
            </div>
            <div class="text-lg font-semibold leading-tight">
                Convenience<br><span class="text-sm text-gray-500">Store</span>
            </div>
        </div>

        <nav class="flex-1">
                    <ul class="space-y-3">
                        <!--Tab Bar-->
                        <li><a href="HOME.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-primary">🏠</span><span class="text-sm font-medium">Home</span></a></li>
                        <li><a href="WISHLIST.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">❤️</span><span class="text-sm font-medium">Wishlist </span></a></li>
                        <li><a href="checkout.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💳</span><span class="text-sm font-medium">Checkout</span></a></li>
                        <li><a href="userpage.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">👤</span><span class="text-sm font-medium">Profile</span></a></li>
                        <li class="bg-red-50 rounded-lg"><a href="preach.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">📜</span><span class="text-sm font-medium">Preach History</span></a></li>
                        <li><a href="contact.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💬</span><span class="text-sm font-medium">Contact us</span></a></li>
                        <li><a href="setting.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">⚙️</span><span class="text-sm font-medium">Setting</span></a></li>
                    </ul>
                </nav>

        <div class="mt-6 bg-gradient-to-br from-red-200 to-red-400 text-white rounded-2xl p-4 shadow">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-2xl">＋</div>
                <div>
                    <div class="text-sm font-semibold">Need Help</div>
                    <p class="text-xs mt-1 opacity-90">We are ready to assist you.</p>
                </div>
            </div>
            <button class="mt-4 bg-white text-red-600 text-sm px-3 py-2 rounded-md shadow">Customer Service</button>
        </div>

    </div>
</aside>


<!-- ========== MAIN CONTENT ========== -->
<div class="flex-1 p-10">

    <h1 class="text-3xl font-bold mb-6" style="font-family:'PT Serif'">Order History</h1>

    <?php foreach ($orders_filtered as $order): ?>
        <div class="bg-white rounded-2xl shadow-card p-6 mb-6 max-w-3xl">

            <!-- ORDER HEADER -->
            <div class="flex justify-between">
                <div>
                    <div class="font-semibold text-lg"><?= $order['id'] ?></div>
                    <div class="text-gray-500 text-sm"><?= $order['date'] ?></div>
                </div>

                <!-- TEXT COLOR ONLY -->
                <?php
                $status = $order['status'];
                $colorClass = "text-gray-600 font-medium";

                if ($status === "Delivered") {
                    $colorClass = "text-green-600 font-semibold";
                } elseif ($status === "Processing") {
                    $colorClass = "text-orange-500 font-semibold";
                }
                ?>
                <div class="<?= $colorClass ?>"><?= $status ?></div>
            </div>

            <!-- ITEMS -->
            <div class="mt-4">
                <div class="font-semibold mb-1">Items:</div>
                <ul class="list-disc ml-6 text-sm">
                    <?php foreach ($order['items'] as $item): ?>
                        <li><?= htmlspecialchars($item['name']) ?> (<?= $item['qty'] ?>)</li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- TOTAL -->
            <div class="mt-5 font-bold text-right text-gray-700">
                Total: $<?= number_format($order['total'], 2) ?>
            </div>

        </div>
    <?php endforeach; ?>

</div>

</body>
</html>
