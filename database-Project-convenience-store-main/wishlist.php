<?php
session_start();

// --- WISHLIST FROM SESSION ---
$wishlist_items = isset($_SESSION['wishlist']) ? array_values($_SESSION['wishlist']) : [];

// --- CART LOGIC ---
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cartTotal = array_sum(array_map(function($i) {
    $qty = isset($i['quantity']) ? (int)$i['quantity'] : 1;
    return ((float)$i['price']) * $qty;
}, $cart));

// --- CATEGORY VIEW LOGIC (same style as HOME.php) ---
$Cateall = isset($_GET['category']) && $_GET['category'] === 'all';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>My Wishlist — Number 1 Shop</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="min-h-screen antialiased text-gray-800">

    <div class="flex min-h-screen">

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
                        <li class="bg-red-50 rounded-lg"><a href="WISHLIST.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-primary">❤️</span><span class="text-sm font-medium">Wishlist </span></a></li>
                        <li><a href="checkout.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💳</span><span class="text-sm font-medium">Checkout</span></a></li>
                        <li><a href="preach.php"   class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">📜</span><span class="text-sm font-medium">Preach History</span></a></li>
                        <li><a href="contact.php"  class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💬</span><span class="text-sm font-medium">Contact us</span></a></li>
                        <li><a href="setting.php"  class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">⚙️</span><span class="text-sm font-medium">Setting</span></a></li>
                        <li><a href="#" onclick="confirmLogout()" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50"> <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border">🚪</span><span class="text-sm font-medium">Logout</span></a></li>
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="flex-1 p-8">
            <div class="max-w-screen-xl mx-auto">
                <div class="grid grid-cols-12 gap-6">

                    <main class="col-span-12 lg:col-span-8">
                        
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h1 class="text-2xl font-bold flex items-center gap-3" style="font-family: 'PT Serif', Georgia, serif;">
                                    My Wishlist <i class="fa-solid fa-heart text-primary"></i>
                                </h1>
                                <div class="text-sm text-gray-500"><?= count($wishlist_items) ?> items currently saved.</div>
                            </div>
                            <a href="HOME.php" class="text-sm text-gray-500 hover:text-primary transition duration-150">
                                Browse Products &rarr;
                            </a>
                        </div>
                        
                        <div class="space-y-4">
                            <?php if (empty($wishlist_items)): ?>
                                <div class="bg-white p-6 rounded-2xl shadow-card text-center">
                                    <p class="text-gray-600 text-lg">Your wishlist is empty. Visit <a href="HOME.php" class="text-primary hover:underline">Home</a>!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($wishlist_items as $item): 
                                    $card_classes = $item['in_stock'] ? '' : 'opacity-60 grayscale'; 
                                ?>
                                    <div class="flex items-center bg-white p-4 rounded-2xl shadow-card hover:shadow-lg transition duration-200 <?= $card_classes ?>">
                                        
                                        <div class="w-24 h-24 bg-gray-100 flex items-center justify-center rounded-xl mr-5 overflow-hidden flex-shrink-0">
                                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="max-w-[80%] max-h-[80%] object-contain">
                                        </div>
                                        
                                        <div class="flex-grow min-w-0 pr-4">
                                            <h3 class="text-xl font-bold text-gray-800 truncate mb-1">
                                                <a href="Product_description.php?
                                                id=<?= $item['id'] ?>&
                                                name=<?= urlencode($item['name']) ?>&
                                                price=<?= $item['price'] ?>&
                                                image=<?= $item['image'] ?>
                                                " class="hover:text-primary transition">
                                                    <?= htmlspecialchars($item['name']) ?>
                                                </a>
                                            </h3>
                                            
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-semibold 
                                                    <?= $item['in_stock'] ? 'text-green-600' : 'text-red-500'; ?> ml-2"> 
                                                    <?= $item['in_stock'] ? 'In Stock' : 'Out of Stock'; ?>
                                                </p>
                                            </div>
                                            
                                            <p class="text-2xl font-extrabold text-primary mt-2">
                                                $<?= number_format($item['price'], 2) ?>
                                            </p>
                                        </div>

                                        <div class="flex flex-col space-y-2 ml-4 w-36 items-end flex-shrink-0">
                                            
                                            <!-- Add to cart from wishlist, return back to wishlist -->
                                            <form action="ADDTOCART.php" method="POST" class="w-full">
                                                <input type="hidden" name="product_name" value="<?= htmlspecialchars($item['name']) ?>">
                                                <input type="hidden" name="product_price" value="<?= $item['price'] ?>">
                                                <input type="hidden" name="action_type" value="add_to_cart">
                                                <input type="hidden" name="return" value="WISHLIST.php">
                                                
                                                <button type="submit" class="w-full flex items-center justify-center gap-1 bg-primary hover:bg-accent text-white font-semibold py-2 px-3 rounded-full text-sm transition duration-150">
                                                    <i class="fa-solid fa-cart-plus"></i> Add
                                                </button>
                                            </form>
                                            
                                            <!-- Remove from wishlist -->
                                            <form action="WISHLIST_ACTION.php" method="POST">
                                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['id']) ?>">
                                                <input type="hidden" name="product_name" value="<?= htmlspecialchars($item['name']) ?>">
                                                <button type="submit" name="action" value="remove" class="text-gray-500 hover:text-red-500 text-sm p-1 transition duration-150">
                                                    Remove from list
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
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
                                                <div class="flex items-center gap-2 flex-shrink-0">
                                                    <span>$<?= number_format($item['price'] * $qty, 2) ?></span>

                                                    <a href="ADJUSTCART.php?one=<?= $index ?>" class="text-s text-white/90 hover:text-white">[−]</a>
                                                    <a href="ADJUSTCART.php?increase=<?= $index ?>" class="text-s text-white/90 hover:text-white">[＋]</a>
                                                    <a href="ADJUSTCART.php?all=<?= $index ?>" class="text-s text-white/90 hover:text-white">[×]</a>
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
                                    <h3 class="text-sm font-semibold">Category</h3>
                                    <?php if (!$Cateall):?>
                                        <a href="wishlist.php?category=all" class="text-xs text-gray-400">View All</a>
                                    <?php else: ?>
                                        <a href="wishlist.php" class="text-xs text-gray-400">View Less</a>
                                    <?php endif; ?>
                                </div>

                                <div class="space-y-3 
                                    <?= $Cateall ? 'max-h-none overflow-visible' : 'max-h-72 overflow-auto' ?> 
                                pr-2">
                                    <?php
                                        $q1 = "SELECT DISTINCT(category) FROM products";

                                        if (!$Cateall) {
                                            $q1 .= " LIMIT 5";
                                        }

                                        $result1 = $mysqli->query($q1);

                                        if (!$result1){
                                            echo "Select failed. Error: " . $mysqli->error;
                                            return false;
                                        }
                                        
                                        $categoryImages = [
                                            "Beverage"       => "asset/category/beverage.png",
                                            "Snack"          => "asset/category/snack.png",
                                            "Instant Food"   => "asset/category/instant.png",
                                            "Dairy Product"  => "asset/category/dairy.png",
                                            "Frozen Food"    => "asset/category/frozen.png",
                                            "Personal Care"  => "asset/category/personal-care.png",
                                            "Household Item" => "asset/category/household.png",
                                            "Stationery"     => "asset/category/stationery.png",
                                            "Pet Supply"     => "asset/category/pet.png",
                                            "Other"          => "asset/category/other.png"
                                        ];


                                        while ($row1 = $result1->fetch_assoc()) {
                                            $cateName = $row1['category'];
                                            $img = $categoryImages[$cateName] ?? 'asset/category/other.png';

                                            echo " 
                                            <div class='flex items-center gap-3'>
                                                <div class='w-12 h-12 rounded-md bg-gray-100'>
                                                    <img src='$img' class='w-full h-full object-cover'>
                                                </div>
                                                <div class='flex-1'>
                                                    <div class='text-sm font-medium'><a href=\"CATEGORY.php?category=" . urlencode($cateName) . "\">{$cateName}</a></div>
                                                </div>
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
<?php ob_end_flush(); ?>
