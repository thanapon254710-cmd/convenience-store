<?php
session_start();
require_once("userconnect.php");

// --- PRODUCT DATA ARRAYS ---
// Define the Hero Product data
$heroProduct = [
    'id'     => 'hero_chocolate',
    'name'   => 'Premium Chocolate Bar',
    'price'  => 45.50, // Use numeric format
    'rating' => '5.0',
    'reviews'=> '200+',
    'detail' => 'Rich, dark chocolate with sea salt flakes.',
    'image'  => 'asset/chocolate.png' // Ensure this path is correct
];

// Define the Popular Product data 
$baseProducts = [
    [
        'name' => 'Organic Fresh Milk',
        'price' => 3.50,
        'rating' => '4.9',
        'image' => 'asset/milk.png'
    ],
    [
        'name' => 'Crunchy Potato Chips',
        'price' => 1.99,
        'rating' => '4.5',
        'image' => 'asset/chips.png'
    ],
    [
        'name' => 'Fresh Lettuce Head',
        'price' => 2.25,
        'rating' => '4.7',
        'image' => 'asset/lettuce.png'
    ],
    [
        'name' => 'Assorted Gummy Bears',
        'price' => 4.00,
        'rating' => '4.6',
        'image' => 'asset/gummy.png'
    ],
];

// --- EXTENDED PRODUCT LIST (For 16 items) ---
// give each card a UNIQUE id so hearts don’t affect each other
$popularProducts = [];
$counter = 1;
foreach (array_merge($baseProducts, $baseProducts, $baseProducts, $baseProducts) as $p) {
    $p['id'] = 'p' . $counter;
    $popularProducts[] = $p;
    $counter++;
}

// Calculate total cart price
$cart = $_SESSION['cart'] ?? [];
$cartTotal = array_sum(array_map(function($i) {
    $qty = isset($i['quantity']) ? (int)$i['quantity'] : 1;
    return ((float)$i['price']) * $qty;
}, $cart));

// --- WISHLIST (SESSION) ---
$wishlist = $_SESSION['wishlist'] ?? [];
?>
<!Doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Number 1 Shop — Mock</title>
    <link rel="stylesheet" href="css/style.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Tailwind theme extension - Clean JavaScript for stability
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#b94a4a',
                        accent: '#e86d6d',
                        sidebar: '#f4f7fb', // Use a light background for the fixed bar
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
<body class="min-h-screen antialiased text-gray-800">

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
                        <li class="bg-red-50 rounded-lg"><a href="HOME.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-primary">🏠</span><span class="text-sm font-medium">Home</span></a></li>
                        <li><a href="WISHLIST.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">❤️</span><span class="text-sm font-medium">Wishlist </span></a></li>
                        <li><a href="checkout.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💳</span><span class="text-sm font-medium">Checkout</span></a></li>
                        <li><a href="userpage.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">👤</span><span class="text-sm font-medium">Profile</span></a></li>
                        <li><a href="preach.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">📜</span><span class="text-sm font-medium">Preach History</span></a></li>
                        <li><a href="contact.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💬</span><span class="text-sm font-medium">Contact us</span></a></li>
                        <li><a href="setting.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">⚙️</span><span class="text-sm font-medium">Setting</span></a></li>
                    </ul>
                </nav>
                <div class="mt-4"></div>
                <div class="mt-6">
                    <div class="bg-gradient-to-br from-red-200 to-red-400 text-white rounded-2xl p-4 shadow-soft-lg relative overflow-hidden">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-2xl">＋</div>
                            <div>
                                <div class="text-sm font-semibold">Need Help</div>
                                <p class="text-xs mt-1 opacity-90">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                            </div>
                        </div>
                        <button class="mt-4 bg-white text-red-600 text-sm font-semibold px-3 py-2 rounded-md shadow">Customer Service</button>
                        <div class="absolute -right-6 -bottom-8 w-40 h-40 rounded-full bg-white/10 transform rotate-12"></div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex-1 p-8">
            <div class="max-w-screen-xl mx-auto">
                
                <div class="grid grid-cols-12 gap-6">

                    <main class="col-span-12 lg:col-span-8">
                        
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h1 class="text-2xl font-bold" style="font-family: 'PT Serif', Georgia, serif;">Convenience Store</h1>
                                <div class="text-xs text-gray-500">No.1 Shop</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div class="relative ">
                                        <input type="text" placeholder="Search Product" class="py-2 pl-3 pr-10 text-sm focus:outline-none rounded-l-md w-64">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-card p-6 mb-6 relative">
                            <div class="grid grid-cols-12 gap-6 items-center">
                                <div class="col-span-12 md:col-span-4 lg:col-span-3">
                                    <div class="img-placeholder w-full h-40 md:h-44 rounded-xl flex items-center justify-center bg-gray-100">
                                        <img src="<?= $heroProduct['image'] ?>" alt="<?= $heroProduct['name'] ?>" class="w-full h-full object-cover rounded-xl" />
                                    </div>
                                </div>

                                <div class="col-span-12 md:col-span-8 lg:col-span-9">
                                    
                                    <div class="flex justify-between items-start flex-wrap">
                                        <div>
                                            <a href="Product_description.php"><h3 class="text-lg font-semibold"><?= $heroProduct['name'] ?></h3></a>
                                            <div class="flex items-center gap-2 mt-2">
                                                <div class="flex -space-x-1">
                                                    <?php for ($i = 0; $i < floor($heroProduct['rating']); $i++): ?><span class="text-yellow-400">★</span><?php endfor; ?>
                                                </div>
                                                <div class="text-xs text-gray-400">(<?= $heroProduct['reviews'] ?> Reviews)</div>
                                            </div>
                                            <p class="mt-3 text-sm text-gray-600"><?= $heroProduct['detail'] ?></p>
                                        </div>

                                        <div class="text-right ml-4 mt-2 md:mt-0">
                                            <div class="text-sm text-gray-400">Price</div>
                                            <div class="text-xl font-semibold text-red-600 mt-2">$<?= number_format($heroProduct['price'], 2) ?></div>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex items-center gap-3">
                                        <!-- Add to cart -->
                                        <form id="hero-add-form" action="ADDTOCART.php" method="POST">
                                            <input type="hidden" name="product_name" value="<?= htmlspecialchars($heroProduct['name']) ?>">
                                            <input type="hidden" name="product_price" value="<?= $heroProduct['price'] ?>">
                                            <input type="hidden" name="action_type" value="add_to_cart">
                                            
                                            <button type="submit" class="flex items-center gap-2 border border-gray-200 rounded-md px-3 py-2 text-gray-700 hover:bg-gray-50 transition">
                                                <span class="text-sm"> Add to cart</span>
                                            </button>
                                        </form>

                                        <!-- Buy now -->
                                        <form action="ADDTOCART.php" method="POST">
                                            <input type="hidden" name="product_name" value="<?= htmlspecialchars($heroProduct['name']) ?>">
                                            <input type="hidden" name="product_price" value="<?= $heroProduct['price'] ?>">
                                            <input type="hidden" name="action_type" value="buy_now">
                                            
                                            <button type="submit" name="buy_now" class="bg-primary text-white px-4 py-2 rounded-md text-sm font-semibold shadow hover:bg-accent transition">
                                                Buy Now
                                            </button>
                                        </form>

                                        <!-- Wishlist heart (hero) -->
                                        <?php $heroInWishlist = isset($wishlist[$heroProduct['id']]); ?>
                                        <form action="WISHLIST_ACTION.php" method="POST" class="inline-block">
                                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($heroProduct['id']) ?>">
                                            <input type="hidden" name="product_name" value="<?= htmlspecialchars($heroProduct['name']) ?>">
                                            <input type="hidden" name="product_price" value="<?= $heroProduct['price'] ?>">
                                            <input type="hidden" name="product_image" value="<?= htmlspecialchars($heroProduct['image']) ?>">
                                            <input type="hidden" name="product_rating" value="<?= htmlspecialchars($heroProduct['rating']) ?>">

                                            <button type="submit" name="action" value="toggle"
                                                class="ml-2 w-10 h-10 flex items-center justify-center border rounded-lg transition <?=
                                                    $heroInWishlist ? 'bg-red-500 text-white hover:bg-red-600' : 'text-gray-600 hover:bg-red-50 hover:text-red-600'
                                                ?>">
                                                <i class="<?= $heroInWishlist ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                                            </button>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Popular products -->
                        <section class="mb-8">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold">Explore The Popular Product</h2>
                                <a href="#" class="text-sm text-gray-500">See all</a>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                                
                                <?php foreach ($popularProducts as $index => $product): 
                                    $inWishlist = isset($wishlist[$product['id']]);
                                ?>
                                    <div class="bg-white rounded-xl p-4 shadow-card flex flex-col">
                                        <div class="img-placeholder w-full h-36 rounded-lg mb-3 bg-gray-100">
                                            <img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>" class="w-full h-full object-cover rounded-lg" />
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-sm font-medium">
                                                <a href="Product_description.php">
                                                    <?= $product['name'] . " (" . ($index + 1) . ")" ?>
                                                </a>
                                            </div> 
                                            <div class="text-xs text-gray-500">$<?= number_format($product['price'], 2) ?></div>
                                        </div>
                                        <div class="mt-3 flex items-center justify-between">
                                            <div class="text-xs text-green-500">★ <?= $product['rating'] ?></div>
                                            
                                            <div class="flex items-center gap-2">
                                                <!-- Add to cart -->
                                                <form action="ADDTOCART.php" method="POST" class="inline-block">
                                                    <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']) ?>">
                                                    <input type="hidden" name="product_price" value="<?= $product['price'] ?>">
                                                    <input type="hidden" name="action_type" value="add_to_cart">

                                                    <button type="submit" class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center hover:bg-red-200 transition">
                                                        ＋
                                                    </button>
                                                </form>

                                                <!-- Wishlist heart -->
                                                <form action="WISHLIST_ACTION.php" method="POST" class="inline-block">
                                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                                                    <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']) ?>">
                                                    <input type="hidden" name="product_price" value="<?= $product['price'] ?>">
                                                    <input type="hidden" name="product_image" value="<?= htmlspecialchars($product['image']) ?>">
                                                    <input type="hidden" name="product_rating" value="<?= htmlspecialchars($product['rating']) ?>">

                                                    <button type="submit" name="action" value="toggle"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full border text-xs transition <?=
                                                            $inWishlist ? 'bg-red-500 text-white hover:bg-red-600' : 'text-gray-500 hover:bg-red-50 hover:text-red-600'
                                                        ?>">
                                                        <i class="<?= $inWishlist ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                            </div>
                        </section>

                        <section class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-white rounded-xl p-4 shadow-card flex items-center gap-4">
                                <div class="w-14 h-14 img-placeholder rounded-lg bg-gray-100"></div>
                                <div>
                                    <div class="text-sm font-semibold">Popular top 10 Product</div>
                                    <div class="text-xs text-gray-500">Price・Orders & reviews</div>
                                </div>
                            </div>
                            <div class="bg-white rounded-xl p-4 shadow-card flex items-center gap-4">
                                <div class="w-14 h-14 img-placeholder rounded-lg bg-gray-100"></div>
                                <div>
                                    <div class="text-sm font-semibold">Newest Sellers</div>
                                    <div class="text-xs text-gray-500">Price・Orders & reviews</div>
                                </div>
                            </div>
                        </section>
                        
                    </main>

                    <!-- CART SIDEBAR -->
                    <aside class="col-span-12 lg:col-span-4">
                        <div class="sticky top-6 space-y-6"> 
                            <div class="bg-gradient-to-br from-red-200 to-red-400 text-white rounded-2xl p-4 shadow-soft-lg">
                                <div class="flex items-center justify-between mb-2">
                                <div class="text-lg font-semibold">Shopping Cart</div>
                                <a href="CLEARCART.php" class="text-xs text-white/90 hover:text-white">[Delete All]</a>
                            </div>
                                <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                                    <?php if (empty($_SESSION['cart'])): ?>
                                        <p class="text-sm opacity-90">Your cart is empty.</p>
                                    <?php else: ?>
                                        <?php foreach ($_SESSION['cart'] as $index => $item): 
                                            $qty = isset($item['quantity']) ? $item['quantity'] : 1;
                                            $itemname = htmlspecialchars($item['name']);
                                        ?>
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="truncate pr-2"><?= $itemname ?><?= $qty > 1 ? " x{$qty}" : "" ?></span>
                                                <div class="flex items-center gap-2">
                                                    <span>$<?= number_format($item['price'] * $qty, 2) ?></span>
                                                    <a href="ADJUSTCART.php?one=<?= $index ?>" 
                                                        class="text-s text-white/90 hover:text-white leading-none">[−]</a>
                                                    <a href="ADJUSTCART.php?increase=<?= $index ?>" 
                                                        class="text-s text-white/90 hover:text-white leading-none">[＋]</a>
                                                    <a href="ADJUSTCART.php?all=<?= $index ?>" 
                                                        class="text-s text-white/90 hover:text-white leading-none">[×]</a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <hr class="my-3 border-white/50" />
                                <div class="flex items-center justify-between font-bold text-lg">
                                    <span>Total:</span>
                                    <span>$<?= number_format($cartTotal, 2) ?></span>
                                </div>

                                <a href="checkout.php" class="block text-center mt-3 bg-white text-primary text-sm font-semibold px-3 py-2 rounded-md shadow hover:bg-gray-100 transition">
                                    Proceed to Checkout
                                </a>
                            </div>


                            <div class="bg-white rounded-2xl p-4 shadow-card">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-semibold">Daily Deals</h3>
                                    <a href="#" class="text-xs text-gray-400">View All</a>
                                </div>

                                <div class="space-y-3 max-h-72 overflow-auto pr-2">
                                <?php
                                    $products = [
                                        ["name" => "Product 1", "details" => "Details", "price" => 45.5],
                                        ["name" => "Product 2", "details" => "Details", "price" => 45.5],
                                        ["name" => "Product 3", "details" => "Details", "price" => 45.5],
                                        ["name" => "Product 4", "details" => "Details", "price" => 45.5],
                                        ["name" => "Product 5", "details" => "Details", "price" => 45.5],
                                    ];

                                    foreach ($products as $p) {
                                        echo " 
                                        <div class='flex items-center gap-3'>
                                            <div class='w-12 h-12 img-placeholder rounded-md bg-gray-100'></div>
                                            <div class='flex-1'>
                                                <div class='text-sm font-medium'><a href=\"Product_description.php\">{$p['name']}</a></div>
                                                <div class='text-xs text-gray-400'>{$p['details']}</div>
                                            </div>
                                            <div class='text-sm text-gray-500'>\${$p['price']}</div>
                                        </div>";
                                    }
                                    
                                ?>

                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
     <script src="script.js"></script>
</body>
</html>
