<?php
session_start();
require_once("userconnect.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$userId   = (int)$_SESSION['user_id'];
$cart     = $_SESSION['cart'] ?? [];
$buyNow = ($_SESSION['checkout_mode'] ?? '') === 'buy_now' ? ($_SESSION['buy_now_item'] ?? null) : null;

// Get data from checkout
$subtotal        = (float)($_POST['subtotal'] ?? 0);
$total           = (float)($_POST['total'] ?? 0);
$payment_type    = $_POST['payment_type'] ?? 'qr';
$couponId        = !empty($_POST['couponid']) ? (int)$_POST['couponid'] : null;
$discountPercent = (float)($_POST['discount_percent'] ?? 0);
$couponCode      = $_POST['coupon_code'] ?? '';
$mode            = $_POST['mode'] ?? 'cart';

$discountAmount  = 0.00;

// map payment type to pretty label
$paymentMethodMap = [
    'qr'   => 'QR Payment',
    'card' => 'Credit Card',
    'cash' => 'Cash',
];

$paymentMethod = $paymentMethodMap[$payment_type] ?? 'Cash';
$orderDate = date('Y-m-d');
$status    = 'Pending';

$mysqli->begin_transaction();
try {
    // ---------- 1) INSERT INTO orders ----------
    $q = $mysqli->prepare("INSERT INTO orders (user_id, order_date, total_amount, payment_type, status, coupon_id)
                                  VALUES (?, ?, ?, ?, ?, ?)");

    $q->bind_param(
        "isdssi",
        $userId,
        $orderDate,
        $total,  // already discounted
        $paymentMethod,
        $status,
        $couponId
    );
    $q->execute();

    // Get the new order_id
    $orderId = $mysqli->insert_id;
    $q->close();

    // ---------- 2) APPLY COUPON VIA STORED PROCEDURE (IF ANY) ----------
    if ($couponCode !== '') {
        $call = $mysqli->prepare("CALL redeem_coupon(?, ?)");
        $call->bind_param("si", $couponCode, $orderId);

        if (!$call->execute()) {
            throw new Exception("Coupon error: " . $call->error);
        }
        $call->close();

        // Re-read updated total_amount and coupon_id
        $qCP = $mysqli->prepare("SELECT total_amount, coupon_id
                                        FROM orders
                                        WHERE order_id = ?");
        $qCP->bind_param("i", $orderId);
        $qCP->execute();
        $qCP->bind_result($newTotal, $newCouponId);

        if ($qCP->fetch()) {
            $total      = (float)$newTotal;     // final discounted total
            $couponId   = $newCouponId;        // actual coupon_id used
            $discountAmount = $subtotal - $total; // how much was saved
        }

        $qCP->close();
    } else {
        // no coupon → no discount
        $total         = $subtotal;
        $discountAmount = 0.0;
    }

    // ---------- 3) INSERT INTO orderdetails ----------
    $qDetail = $mysqli->prepare("INSERT INTO orderdetails (order_id, product_id, quantity, subtotal, discount_applied)
                                        VALUES (?, ?, ?, ?, ?)");

    if ($mode === 'buy_now' && $buyNow) {
        // Buy Now → only one item
        $productId       = isset($buyNow['product_id']) ? (int)$buyNow['product_id'] : (int)$buyNow['id'];
        $qty             = 1;

        $qDetail->bind_param(
            "iiidd",
            $orderId,
            $productId,
            $qty,
            $subtotal, //before discount
            $discountAmount //discount value
        );
        $qDetail->execute();

    } else {
        // Normal checkout → use cart items
        foreach ($cart as $item) {
            $productId       = isset($item['product_id']) ? (int)$item['product_id'] : (int)$item['id'];
            $qty             = isset($item['quantity']) ? (int)$item['quantity'] : 1;
            $discountApplied = 0.00;

            $qDetail->bind_param(
                "iiidd",
                $orderId,
                $productId,
                $qty,
                $subtotal,
                $discountAmount
            );
            $qDetail->execute();
        }
    }

    $qDetail->close();

    // ---------- 4) INSERT INTO payments ----------
    $qPay = $mysqli->prepare("INSERT INTO payments (order_id, payment_date, amount_paid, method, transaction_code)
                                     VALUES (?, NOW(), ?, ?, ?)");

    $transactionCode = uniqid('TXN-'); // simple random code

    $qPay->bind_param(
        "idss",
        $orderId,
        $total,
        $paymentMethod,
        $transactionCode
    );
    $qPay->execute();
    $qPay->close();

    // ---------- 5) Commit all ----------
    $mysqli->commit();

    // ---------- 6) Clear session: cart vs buy_now ----------
    if ($mode === 'buy_now') {
        unset($_SESSION['buy_now_item']);
    } else {
        unset($_SESSION['cart']);
    }

    // reset mode so next time we default correctly
    unset($_SESSION['checkout_mode']);

} catch (Exception $e) {
    // Rollback on error
    $mysqli->rollback();
    die("Error processing order: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful — Number 1 Shop</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#b94a4a',
                        accent: '#e86d6d',
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body class="min-h-screen bg-gray-50 flex items-center justify-center">

    <div class="bg-white rounded-2xl shadow-xl p-10 max-w-md text-center">

        <!-- Green check icon -->
        <div class="text-green-500 text-6xl mb-4">
            <i class="fa-solid fa-circle-check animate-bounce"></i>
        </div>

        <h1 class="text-3xl font-bold mb-2 text-gray-800">
            Payment Successful!
        </h1>

        <p class="text-gray-600 mb-6 text-sm">
            Thank you for your purchase. Your order has been processed successfully.
        </p>

        <!-- Order info box -->
        <div class="bg-gray-100 rounded-xl p-4 text-left mb-7">
            <p class="text-sm text-gray-700"><strong>Order Status:</strong> Paid</p>
            <p class="text-sm text-gray-700"><strong>Payment Method:</strong> <?= htmlspecialchars($paymentMethod) ?></p>
            <?php date_default_timezone_set("Asia/Bangkok"); ?>
            <p class="text-sm text-gray-700"><strong>Date:</strong> <?= date("Y-m-d h:i:sa") ?></p>
            <p class="text-sm text-gray-700"><strong>Total Paid:</strong> $<?= number_format($total, 2) ?></p>
            <?php if (!empty($couponCode)): ?>
                <p class="text-sm text-gray-700">
                    <strong>Coupon Code:</strong> <?= htmlspecialchars($couponCode) ?>
                    (Saved $<?= number_format($discountAmount, 2) ?>)
                </p>
            <?php endif; ?>
        </div>

        <!-- Buttons -->
        <div class="flex flex-col space-y-3">

            <a href="HOME.php" 
               class="py-3 bg-primary text-white font-semibold rounded-lg shadow hover:bg-accent transition">
               Back to Home
            </a>

            <a href="preach.php"
               class="py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
               View Order History
            </a>

        </div>

    </div>

</body>
</html>
