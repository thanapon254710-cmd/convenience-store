<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Shopping Cart</title>
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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>
    <body class="min-h-screen antialiased text-gray-800 bg-soft">

        <div class="flex min-h-screen">

            <aside class="w-64 bg-sidebar p-4 sticky top-0 h-screen overflow-y-auto">
                <div class="bg-white border border-blue-300 rounded-xl p-4 shadow-sm flex flex-col h-full">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-600 to-cyan-400 text-white flex items-center justify-center text-lg font-bold"><img src="asset/2960679-2182.png"></div>
                        <div>
                            <div class="text-lg font-semibold">Convenience<br/><span class="text-sm text-gray-500">Store</span></div>
                        </div>
                    </div>

                    <nav class="flex-1">
                        <ul class="space-y-3">
                            <!--Tab Bar-->
                            <li><a href="HOME.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-primary">🏠</span><span class="text-sm font-medium">Home</span></a></li>
                            <li><a href="WISHLIST.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">❤️</span><span class="text-sm font-medium">Wishlist </span></a></li>
                            <li class="bg-red-50 rounded-lg"><a href="checkout.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💳</span><span class="text-sm font-medium">Checkout</span></a></li>
                            <li><a href="userpage.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">👤</span><span class="text-sm font-medium">Profile</span></a></li>
                            <li><a href="preach.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">📜</span><span class="text-sm font-medium">Preach History</span></a></li>
                            <li><a href="contact.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💬</span><span class="text-sm font-medium">Contact us</span></a></li>
                            <li><a href="setting.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">⚙️</span><span class="text-sm font-medium">Setting</span></a></li>
                        </ul>
                    </nav>
                </div>
            </aside>
            
            <div class="flex-1 p-8">
                <div class="max-w-screen-xl mx-auto">
                    
                    <div class="mb-8">
                        <a href="HOME.php" class="text-lg text-gray-500 font-medium hover:text-primary transition flex items-center">
                            <i class="fas fa-chevron-left mr-3 text-sm"></i>
                            Shopping Continue
                        </a>
                    </div>

                    <div class="flex gap-8">
                        
                        <div class="flex-grow">
                            <div class="bg-white rounded-2xl shadow-card p-6">
                                <?php
                                    $cart = $_SESSION['cart'] ?? [];
                                    $totalQty = 0;

                                    if (isset($_SESSION['buy_now_item'])) {
                                        $totalQty = 1;
                                    } elseif (!empty($cart)) {
                                        $totalQty = array_sum(array_column($cart, 'quantity'));
                                    }
                                ?>

                                <h2 class="text-xl font-bold mb-1">Your Shopping Cart</h2>
                                <p class="text-sm text-gray-600 mb-6">You have <?= $totalQty ?> item(s) in your cart</p>
                                    
                                <div class="grid grid-cols-4 font-semibold text-sm text-gray-500 pb-2 border-b border-outline mb-4">
                                    <p class="col-span-2">Product Details</p>
                                    <p class="text-center">Quantity</p>
                                    <p class="text-right">Price</p>
                                </div>
                                    <?php 
                                        // default subtotal
                                        $subprice = 0;

                                        // CASE 1: Buy Now flow
                                        if (isset($_SESSION['buy_now_item'])) {
                                            $item = $_SESSION['buy_now_item'];
                                            $itemname = htmlspecialchars($item["name"]);
                                            $subprice = $item["price"];
                                            $qty = 1;

                                            // optional: clear it so refresh doesn't duplicate
                                            unset($_SESSION['buy_now_item']);
                                    ?>
                                        <div class="grid grid-cols-4 items-center py-4 border-b border-soft last:border-b-0">
                                            <div class="flex items-center gap-4 col-span-2">
                                                <img src="asset/example-product-1.png" alt="Product Image" class="w-16 h-16 object-cover rounded-lg border border-outline">
                                                <div>
                                                    <p class="font-medium"><?= $itemname ?></p>
                                                </div>
                                            </div>
                                            <div class="text-center flex justify-center items-center">
                                                <span class="truncate pr-2"><?= $qty ?></span>                                        
                                            </div>
                                            <div class="text-right flex justify-end items-center gap-3">
                                                <p class="font-semibold text-gray-800">$<?= number_format($subprice,2) ?></p>
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
                                        <div class="grid grid-cols-4 items-center py-4 border-b border-soft last:border-b-0">
                                            <div class="flex items-center gap-4 col-span-2">
                                                <img src="<?= $img ?>" alt="Product Image" class="w-16 h-16 object-cover rounded-lg border border-outline">
                                                <div>
                                                    <p class="font-medium"><?= $itemname ?></p>
                                                </div>
                                            </div>
                                            <div class="text-center flex justify-center items-center">
                                                <span class="truncate pr-2"><?= $qty ?></span>                                        
                                            </div>
                                            <div class="text-right flex justify-end items-center gap-3">
                                                <p class="font-semibold text-gray-800">$<?= number_format($price * $qty,2) ?></p>
                                            </div>
                                        </div>
                                    <?php 
                                        endforeach; 
                                    }
                                    ?>
                            </div>
                        </div>

                        <div class="w-96 flex-shrink-0 space-y-6">

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
                                            $minimum = 100;
                                            $shipping = ($subprice > $minimum || $subprice == 0) ? 0 : 3.50;
                                            $tax = number_format(($subprice + $shipping)*0.07, 2);
                                            $total = number_format($subprice + $shipping + $tax, 2)
                                        ?>
                                        <? if ($subprice > $minimum): ?>
                                            <p class="font-medium">Free</p>
                                        <? elseif ($subprice == 0): ?>
                                            <p class="font-medium">$0.00</p>
                                        <? else: ?>
                                            <p class="font-medium">$3.50</p>
                                        <? endif; ?>
                                    </div>
                                    <div class="flex justify-between">
                                        <p class="text-gray-600">Tax (7%)</p>
                                        <p class="font-medium">$<?= $tax ?></p>
                                    </div>
                                    <div class="flex justify-between font-bold text-lg border-t pt-3 mt-3">
                                        <p>Total (Tax incl.)</p>
                                        <p class="text-primary">$<?= $total ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-br from-accent to-primary text-white rounded-2xl shadow-card p-6">
                                <p class="font-bold text-xl mb-4">Card Details</p>
                                
                                <div class="flex justify-between items-center mb-4 border-b border-white/30 pb-3">
                                    <p class="text-sm opacity-80">Payment Method</p>
                                    
                                    <div class="flex items-center gap-2">
                                        <img src="asset/mastercard.png" alt="Mastercard" class="w-8 h-auto bg-white rounded p-1 border border-white transition hover:scale-105 shadow-md">
                                        <img src="asset/Visa Payment Card.png" alt="Visa" class="w-8 h-auto bg-white rounded p-1 border border-white transition hover:scale-105 shadow-md">
                                        <img src="asset/Rupay-Logo.png" alt="RuPay" class="w-8 h-auto bg-white rounded p-1 border border-white transition hover:scale-105 shadow-md">
                                    </div>
                                </div>

                                <form class="space-y-4">
                                    <div>
                                        <label for="nameoncard" class="block text-xs font-medium mb-1 opacity-80">Name on Card</label>
                                        <input type="text" id="nameoncard" name="nameoncard" placeholder="Enter name on card" class="w-full py-2 px-3 rounded-md border border-white/50 bg-white/20 text-white placeholder-white/70 focus:outline-none focus:ring-1 focus:ring-white">
                                    </div>

                                    <div>
                                        <label for="cardnumber" class="block text-xs font-medium mb-1 opacity-80">Card Number</label>
                                        <input type="text" id="cardnumber" name="cardnumber" placeholder="XXXX XXXX XXXX XXXX" class="w-full py-2 px-3 rounded-md border border-white/50 bg-white/20 text-white placeholder-white/70 focus:outline-none focus:ring-1 focus:ring-white">
                                    </div>

                                    <div class="flex gap-4">
                                        <div class="flex-1">
                                            <label for="exdate" class="block text-xs font-medium mb-1 opacity-80">Expiration Date</label>
                                            <input type="text" id="exdate" name="exdate" placeholder="MM/YYYY" class="w-full py-2 px-3 rounded-md border border-white/50 bg-white/20 text-white placeholder-white/70 focus:outline-none focus:ring-1 focus:ring-white">
                                        </div>
                                        <div class="flex-1">
                                            <label for="cvv" class="block text-xs font-medium mb-1 opacity-80">CVV</label>
                                            <input type="text" id="cvv" name="cvv" placeholder="CVV" class="w-full py-2 px-3 rounded-md border border-white/50 bg-white/20 text-white placeholder-white/70 focus:outline-none focus:ring-1 focus:ring-white">
                                        </div>
                                    </div>
                                
                                    <button id="paynow" class="w-full py-3 mt-6 font-semibold rounded-md shadow-lg transition
                                        <?= $subprice == 0 ? 'bg-white/60 text-primary/50 cursor-not-allowed' : 'bg-white text-primary hover:bg-gray-100' ?>"
                                        <?= $subprice == 0 ? 'disabled' : '' ?>>
                                        Pay Now <?= $total ?>
                                    </button>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>