<?php
session_start();
require_once("userconnect.php");

$cart   = $_SESSION['cart'] ?? [];
$buyNow = $_SESSION['buy_now_item'] ?? null;
$couponCheck = $_GET['coupon'] ?? null;
$couponId     = null;
$discountRaw  = 0;   
$min_coupon   = 0;

$couponMessage      = "";
$couponMessageClass = ""; 
$couponFound        = false;

// default mode if not set yet
if (!isset($_SESSION['checkout_mode'])) {
    // if they came from sidebar Checkout link, assume cart mode
    $_SESSION['checkout_mode'] = !empty($cart) ? 'cart' : 'buy_now';
}

$mode = $_SESSION['checkout_mode'];  // 'buy_now' or 'cart'

if (!empty($couponCheck)) {
    $couponSafe = trim($couponCheck);
    $q = $mysqli->prepare("
            SELECT coupon_id, discount_percent, min_purchase 
            FROM coupons 
            WHERE coupon_code = ? AND status = 'Active' 
            LIMIT 1");     
    $q->bind_param('s', $couponSafe);
    $q->execute();
    $result = $q->get_result();
    if ($row = $result->fetch_assoc()) {
        $couponFound  = true;
        $couponId    = (int)$row['coupon_id'];
        $discountRaw = (float)$row['discount_percent'];
        $min_coupon = (float)$row['min_purchase'];
    } else {
        $couponMessage      = "Coupon code is invalid or inactive.";
        $couponMessageClass = "text-red-500";
    }
    $q->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Out — Number 1 Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Tailwind theme extension - Identical to other pages for consistent styling
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
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="min-h-screen antialiased text-gray-800 bg-soft">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-sidebar p-4 sticky top-0 h-screen overflow-y-auto">
        <div class="bg-white border border-blue-300 rounded-xl p-4 shadow-sm flex flex-col h-full">
            <div class="flex items-center gap-3 mb-6">
                <a href="HOME.php" class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-600 to-cyan-400 text-white flex items-center justify-center text-lg font-bold"><img src="asset/2960679-2182.png"></div>
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
                    <li class="bg-red-50 rounded-lg"><a href="checkout.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-primary">💳</span><span class="text-sm font-medium">Checkout</span></a></li>
                    <li><a href="preach.php"   class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">📜</span><span class="text-sm font-medium">Preach History</span></a></li>
                    <li><a href="contact.php"  class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💬</span><span class="text-sm font-medium">Contact us</span></a></li>
                    <li><a href="setting.php"  class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">⚙️</span><span class="text-sm font-medium">Setting</span></a></li>
                    <li><a href="#" onclick="confirmLogout()" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50"> <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">🚪</span><span class="text-sm font-medium">Logout</span></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- MAIN -->
    <div class="flex-1 p-8">
        <div class="max-w-screen-xl mx-auto">

            <!-- back link -->
            <div class="mb-8">
                <a href="HOME.php"
                   class="text-lg text-gray-500 font-medium hover:text-primary transition flex items-center">
                    <i class="fas fa-chevron-left mr-3 text-sm"></i>
                    Shopping Continue
                </a>
            </div>

            <div class="flex gap-8">

                <!-- CART -->
                <div class="flex-grow">
                    <div class="bg-white rounded-2xl shadow-card p-6">
                        <?php
                        $cart = $_SESSION['cart'] ?? [];
                        $totalQty = 0;

                        if ($mode === 'buy_now' && $buyNow) {
                            $totalQty = 1;
                        } elseif (!empty($cart)) {
                            $totalQty = array_sum(array_column($cart, 'quantity'));
                        } else {
                            $totalQty = 0;
                        }
                        ?>

                        <h2 class="text-xl font-bold mb-1">Your Shopping Cart</h2>
                        <p class="text-sm text-gray-600 mb-6">
                            You have <?= $totalQty ?> item(s) in your cart
                        </p>

                        <div
                            class="grid grid-cols-4 font-semibold text-sm text-gray-500 pb-2 border-b border-outline mb-4">
                            <p class="col-span-2">Product Details</p>
                            <p class="text-center">Quantity</p>
                            <p class="text-right">Price</p>
                        </div>

                        <?php
                        // default subtotal
                        $subprice = 0;

                        // CASE 1: Buy Now flow
                        if ($mode === 'buy_now' && $buyNow) {
                            $item     = $buyNow;
                            $itemname = htmlspecialchars($item["name"]);
                            $img      = htmlspecialchars($item["image"]);
                            $subprice = (float)$item["price"];
                            $qty      = 1;
                            ?>
                            <div class="grid grid-cols-4 items-center py-4 border-b border-soft last:border-b-0">
                                <div class="flex items-center gap-4 col-span-2">
                                    <img src="<?= $img ?>" alt="Product Image"
                                         class="w-16 h-16 object-cover rounded-lg border border-outline">
                                    <div>
                                        <p class="font-medium"><?= $itemname ?></p>
                                    </div>
                                </div>
                                <div class="text-center flex justify-center items-center">
                                    <span class="truncate pr-2"><?= $qty ?></span>
                                </div>
                                <div class="text-right flex justify-end items-center gap-3">
                                    <p class="font-semibold text-gray-800">$<?= number_format($subprice, 2) ?></p>
                                </div>
                            </div>
                            <?php
                            // CASE 2: Completely empty cart
                        } elseif (empty($cart)) {
                            ?>
                            <div class="py-10 text-center text-gray-500">
                                <p class="text-gray-600 text-lg">Your cart is empty!</p>
                            </div>
                            <?php
                            // CASE 3: Normal cart items
                        } else {
                            foreach ($cart as $index => $item):
                                $itemname = htmlspecialchars($item['name']);
                                $price = $item['price'];
                                $qty = $item['quantity'];
                                $subprice += $price * $qty;
                                $img = !empty($item['image']) ? $item['image'] : 'asset/example-product-1.png';
                                ?>
                                <div
                                    class="grid grid-cols-4 items-center py-4 border-b border-soft last:border-b-0">
                                    <div class="flex items-center gap-4 col-span-2">
                                        <img src="<?= $img ?>" alt="Product Image"
                                             class="w-16 h-16 object-cover rounded-lg border border-outline">
                                        <div>
                                            <p class="font-medium"><?= $itemname ?></p>
                                        </div>
                                    </div>
                                    <div class="text-center flex justify-center items-center">
                                        <span class="truncate pr-2"><?= $qty ?></span>
                                    </div>
                                    <div class="text-right flex justify-end items-center gap-3">
                                        <p class="font-semibold text-gray-800">
                                            $<?= number_format($price * $qty, 2) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php
                            endforeach;
                        }
                        ?>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="w-96 flex-shrink-0 space-y-6">

                    <!-- ORDER SUMMARY -->
                    <div class="bg-white rounded-2xl shadow-card p-6">
                        <h3 class="text-xl font-bold mb-4 border-b pb-3">Order Summary</h3>
                        <div class="text-base space-y-3">
                            <div class="flex justify-between">
                                <p class="text-gray-600">Subtotal</p>
                                <p class="font-medium">$<?= number_format($subprice, 2) ?></p>
                            </div>

                            <div class="flex justify-between">
                                <p class="text-gray-600">Shipping Estimate</p>
                                <?php
                                $minimum  = 100;
                                $shipping = ($subprice >= $minimum || $subprice == 0) ? 0 : 3.50;

                                $taxBase = $subprice + $shipping;
                                $tax = round($taxBase * 0.07, 2); 

                                $subtotal = $subprice + $shipping + $tax;         
                                $discountAmount = 0.0;      

                                if ($couponId !== null && $discountRaw > 0 && $subtotal > $min_coupon) {
                                    $discountAmount = round($subtotal * ($discountRaw / 100), 2);
                                }

                                $total = $subtotal - $discountAmount;

                                if (!empty($couponCheck) && $couponFound) {
                                    if ($subprice == 0) {
                                        // ADDED: can't use coupon with empty cart
                                        $couponMessage      = "Add items to your cart before applying a coupon.";
                                        $couponMessageClass = "text-red-500";
                                        $discountAmount     = 0;
                                        $total              = $subtotal;
                                    } elseif ($subtotal <= $min_coupon) {
                                        // ADDED: subtotal too low for min_purchase rule
                                        $couponMessage      = "Minimum order amount to use this coupon is $" . number_format($min_coupon, 2) . ".";
                                        $couponMessageClass = "text-red-500";
                                        $discountAmount     = 0;
                                        $total              = $subtotal;
                                    } elseif ($discountAmount > 0) {
                                        // ADDED: successful coupon
                                        $couponMessage      = "Coupon is successfully applied! You saved $" . number_format($discountAmount, 2) . ".";
                                        $couponMessageClass = "text-green-600";
                                    } else {
                                        // ADDED: generic fail-safe message
                                        $couponMessage      = "Coupon could not be applied. Please check your cart total.";
                                        $couponMessageClass = "text-red-500";
                                    }
                                }

                                ?>
                                <?php if ($subprice > $minimum): ?>
                                    <p class="font-medium">Free</p>
                                <?php elseif ($subprice == 0): ?>
                                    <p class="font-medium">$0.00</p>
                                <?php else: ?>
                                    <p class="font-medium">$3.50</p>
                                <?php endif; ?>
                            </div>

                            <div class="flex justify-between">
                                <p class="text-gray-600">Tax (7%)</p>
                                <p class="font-medium">$<?= number_format($tax, 2) ?></p>
                            </div>

                            <!-- Discount Code -->
                            <form class="flex justify-between items-center mt-2">
                                <p class="text-gray-600">Coupon Code</p>
                                <input 
                                    type="text" 
                                    name="coupon"
                                    placeholder="Enter code"
                                    value="<?= htmlspecialchars($couponCheck ?? '') ?>"
                                    class="py-1 px-2 w-32 text-center rounded-md border border-gray-300 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
                                >
                                <button type="submit"
                                    class="px-3 py-1 text-xs font-semibold rounded-md bg-primary text-white hover:bg-accent">
                                    Apply
                                </button>
                            </form>
                            
                            <!-- show coupon success / error message under the input -->
                            <?php if (!empty($couponCheck) && !empty($couponMessage)): ?>
                                <p class="mt-2 text-xs <?= $couponMessageClass ?>">
                                    <?= htmlspecialchars($couponMessage) ?>
                                </p>
                            <?php endif; ?>

                            <div class="flex justify-between font-bold text-lg border-t pt-3 mt-3">
                                <p>Total (Tax incl.)</p>
                                <p class="text-primary">$<?= number_format($total, 2) ?></p>
                            </div>

                        </div>
                    </div>

                    <!-- PAYMENT CARD -->
                    <div
                        class="bg-gradient-to-br from-accent to-primary text-white rounded-2xl shadow-card p-6">
                        <p class="font-bold text-xl mb-4">Payment</p>

                        <!-- PAYMENT METHOD TABS -->
                        <div class="mb-4 border-b border-white/30 pb-3">
                            <p class="text-sm opacity-80 mb-3">Payment Method</p>
                            <div class="flex gap-2">
                                <!-- QR FIRST -->
                                <button type="button" data-method-btn="qr"
                                        class="method-btn flex-1 flex items-center justify-center gap-2 py-1.5 text-xs font-semibold rounded-md bg-white text-primary shadow-sm">
                                    <span>QR Code</span>
                                    <img src="asset/qr-icon.png" alt="QR"
                                         class="w-5 h-5 object-contain">
                                </button>

                                <!-- CARD MIDDLE -->
                                <button type="button" data-method-btn="card"
                                        class="method-btn flex-1 flex items-center justify-center gap-1 py-1.5 text-xs font-semibold rounded-md bg-white/20 text-white/80 border border-white/40 hover:bg-white/25">
                                    <img src="asset/mastercard.png" class="w-5 h-5 bg-white rounded p-0.5"
                                         alt="Mastercard">
                                    <img src="asset/Visa Payment Card.png"
                                         class="w-5 h-5 bg-white rounded p-0.5" alt="Visa">
                                </button>

                                <!-- CASH LAST -->
                                <button type="button" data-method-btn="cash"
                                        class="method-btn flex-1 flex items-center justify-center gap-2 py-1.5 text-xs font-semibold rounded-md bg-white/20 text-white/80 border border-white/40 hover:bg-white/25">
                                    <i class="fa-solid fa-money-bill-wave text-sm"></i>
                                    <span>Cash</span>
                                </button>
                            </div>
                        </div>

                        <?php
                        // decide which checkout mode this page is using
                        $checkoutMode = isset($_SESSION['buy_now_item']) ? 'buy_now' : 'cart';
                        ?>

                        <!-- PAYMENT FORMS -->
                        <form action="after_checkout.php" method="POST" class="space-y-4">
                            <input type="hidden" name="subtotal" value="<?= htmlspecialchars($subtotal) ?>"> <!-- pre-discount -->
                            <input type="hidden" name="total" value="<?= htmlspecialchars($subtotal) ?>"> <!-- pre-discount -->
                            <input type="hidden" name="payment_type" id="payment_type" value="qr"> 
                            <input type="hidden" name="couponid" id="couponid" value="<?= htmlspecialchars($couponId ?? '') ?>">
                            <input type="hidden" name="discount_percent" id="couponcode" value="<?= htmlspecialchars($discountRaw) ?>">
                            <input type="hidden" name="coupon_code"   value="<?= htmlspecialchars($couponCheck) ?>">
                            <input type="hidden" name="mode" value="<?= $mode ?>">

                            <!-- QR SECTION -->
                            <div id="section-qr" class="space-y-3">
                                <p class="text-xs opacity-85">
                                    Scan this QR with your banking app to pay.
                                </p>
                                <div
                                    class="w-full bg-white rounded-xl p-3 flex items-center justify-center shadow-inner">
                                    <!-- Replace with your real QR image -->
                                    <img src="asset/QR CODE.png" alt="QR Code"
                                         class="w-40 h-40 object-contain">
                                </div>
                                <p class="text-xs mt-2 opacity-80 text-center">
                                    After payment is completed, click “Pay Now” to confirm.
                                </p>
                            </div>

                            <!-- CARD SECTION -->
                            <div id="section-card" class="space-y-4 hidden">
                                <div>
                                    <label for="nameoncard"
                                           class="block text-xs font-medium mb-1 opacity-80">Name on
                                        Card</label>
                                    <input type="text" id="nameoncard" name="nameoncard"
                                           placeholder="Enter name on card"
                                           class="w-full py-2 px-3 rounded-md border border-white/50 bg-white/20 text-white placeholder-white/70 focus:outline-none focus:ring-1 focus:ring-white">
                                </div>

                                <div>
                                    <label for="cardnumber"
                                           class="block text-xs font-medium mb-1 opacity-80">Card
                                        Number</label>
                                    <input type="text" id="cardnumber" name="cardnumber"
                                           placeholder="XXXX XXXX XXXX XXXX"
                                           class="w-full py-2 px-3 rounded-md border border-white/50 bg-white/20 text-white placeholder-white/70 focus:outline-none focus:ring-1 focus:ring-white">
                                </div>

                                <div class="flex gap-4">
                                    <div class="flex-1">
                                        <label for="exdate"
                                               class="block text-xs font-medium mb-1 opacity-80">Expiration
                                            Date</label>
                                        <input type="text" id="exdate" name="exdate"
                                               placeholder="MM/YYYY"
                                               class="w-full py-2 px-3 rounded-md border border-white/50 bg-white/20 text-white placeholder-white/70 focus:outline-none focus:ring-1 focus:ring-white">
                                    </div>
                                    <div class="flex-1">
                                        <label for="cvv"
                                               class="block text-xs font-medium mb-1 opacity-80">CVV</label>
                                        <input type="text" id="cvv" name="cvv" placeholder="CVV"
                                               class="w-full py-2 px-3 rounded-md border border-white/50 bg-white/20 text-white placeholder-white/70 focus:outline-none focus:ring-1 focus:ring-white">
                                    </div>
                                </div>
                            </div>

                            <!-- CASH SECTION -->
                            <div id="section-cash" class="space-y-3 hidden">
                                <p class="text-xs opacity-85">
                                    You will pay with cash at the counter / upon delivery.
                                </p>
                                <ul class="text-xs opacity-80 list-disc list-inside space-y-1">
                                    <li>Please prepare the exact amount if possible.</li>
                                    <li>Our staff will confirm your order before collecting cash.</li>
                                </ul>
                            </div>

                            <!-- PAY BUTTON -->
                            <button id="paynow"
                                class="w-full py-3 mt-6 font-semibold rounded-md shadow-lg transition
                                <?= $subprice == 0 ? 'bg-white/60 text-primary/50 cursor-not-allowed' : 'bg-white text-primary hover:bg-gray-100' ?>"
                                <?= $subprice == 0 ? 'disabled' : '' ?>>
                                Pay Now $<?= number_format($total, 2) ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Simple tab switching for QR / Card / Cash
    const methodButtons = document.querySelectorAll('.method-btn');
    const sections = {
        qr: document.getElementById('section-qr'),
        card: document.getElementById('section-card'),
        cash: document.getElementById('section-cash'),
    };

    let currentMethod = 'qr'; // default
    const paymentTypeInput = document.getElementById('payment_type');

    function setMethod(method) {
        currentMethod = method;

        // update hidden input for form
        if (paymentTypeInput) {
            paymentTypeInput.value = method;   // "qr", "card", or "cash"
        }

        // toggle sections
        Object.keys(sections).forEach(key => {
            if (key === method) {
                sections[key].classList.remove('hidden');
            } else {
                sections[key].classList.add('hidden');
            }
        });

        // toggle button styles
        methodButtons.forEach(btn => {
            const m = btn.getAttribute('data-method-btn');
            if (m === method) {
                btn.classList.remove('bg-white/20', 'text-white/80', 'border-white/40');
                btn.classList.add('bg-white', 'text-primary', 'shadow-sm');
            } else {
                btn.classList.remove('bg-white', 'text-primary', 'shadow-sm');
                btn.classList.add('bg-white/20', 'text-white/80', 'border-white/40');
            }
        });
    }

    methodButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const method = btn.getAttribute('data-method-btn');
            setMethod(method);
        });
    });

    // init
    setMethod('qr');
</script>

</body>
</html>