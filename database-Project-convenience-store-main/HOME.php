<?php
session_start();
require_once("userconnect.php");

$seeAll = isset($_GET['see']) && $_GET['see'] === 'all';
$Cateall = isset($_GET['category']) && $_GET['category'] === 'all';
$search = $_GET['search'] ?? '';

// --- PRODUCT DATA ARRAYS ---
// Only the most expensive 
$heroProduct = null;
$heroId = 0;

if ($search === '') {
    $heroq = "SELECT * FROM products 
            WHERE status != 'Inactive'
            ORDER BY price DESC
            LIMIT 1";

    $resultHero = $mysqli->query($heroq);

    if (!$resultHero){
        echo "Select failed. Error: " . $mysqli->error;
        return false;
    }

    while ($rowHero = $resultHero->fetch_assoc()) {
        $statusRaw = $rowHero['status']; 
        if ($statusRaw === 'Active') {
            $statusDisplay = 'In Stock';
        } elseif ($statusRaw === 'Out of Stock') {
            $statusDisplay = 'Out of Stock';
        }

        $heroProduct = [
            'id'            => (int)$rowHero['product_id'],
            'name'          => $rowHero['product_name'],
            'price'         => (float)$rowHero['price'],
            'quantity'      => $rowHero['stock_qty'],
            'detail'        => 'Rich, dark chocolate with sea salt flakes.',
            'category'      => $rowHero['category'],
            'status_raw'    => $statusRaw,
            'status_label'  => $statusDisplay,
            'image'         => $rowHero['image_path'] ?? 'asset/default.png'
        ];
    }
    $heroId = $heroProduct['id'];
}

// Select all products except the most expensive from DB
$q =  "SELECT * FROM products
       WHERE product_id != $heroId
       AND status != 'Inactive'";


// Add search filter
if (!empty($search)) {
    $searchSafe = $mysqli->real_escape_string($search);
    $q .= " AND product_name LIKE '%$searchSafe%'";
}

// Order results
$q .= " ORDER BY stock_qty DESC";

// Collect 16 popular products from DB
if (!$seeAll) {
    $q .= " LIMIT 16";
}

$result = $mysqli->query($q);

if (!$result){
    echo "Select failed. Error: " . $mysqli->error;
    return false;
}

$baseProducts = [];

while ($row = $result->fetch_assoc()) {
    $statusRaw1 = $row['status']; 
    if ($statusRaw1 === 'Active') {
        $statusDisplay1 = 'In Stock';
    } elseif ($statusRaw1 === 'Out of Stock') {
        $statusDisplay1 = 'Out of Stock';
    }

    $baseProducts[] = [
        'id'           => (int)$row["product_id"],
        'name'         => $row['product_name'],
        'price'        => (float)$row['price'],
        'quantity'     => (int)$row['stock_qty'],
        'category'     => $row['category'],
        'status_raw'   => $statusRaw1,
        'status_label' => $statusDisplay1,
        'image'        => $row['image_path'] ?? 'asset/default.png'
    ];
}

$popularProducts = $baseProducts;

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
    <title>Number 1 Shop</title>
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
                        <li class="bg-red-50 rounded-lg"><a href="HOME.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-primary">🏠</span><span class="text-sm font-medium">Home</span></a></li>
                        <li><a href="WISHLIST.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">❤️</span><span class="text-sm font-medium">Wishlist </span></a></li>
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
                                <h1 class="text-2xl font-bold" style="font-family: 'PT Serif', Georgia, serif;">Convenience Store</h1>
                                <div class="text-xs text-gray-500">No.1 Shop</div>
                            </div>
                            <!-- Search Bar -->
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div class="relative ">
                                        <form method="GET" action="HOME.php" class="relative">
                                            <input 
                                                type="text" 
                                                name="search"
                                                value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                                                placeholder="Search Product"
                                                class="py-2 pl-3 pr-10 text-sm focus:outline-none rounded-l-md w-64"
                                            >
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!$seeAll && $search === '' && $heroProduct): ?>
                        <!-- HERO PRODUCT (only show when NOT view=all) -->
                        <div class="bg-white rounded-2xl shadow-card p-6 mb-6 relative">
                            <div class="grid grid-cols-12 gap-6 items-center">
                                <div class="col-span-12 md:col-span-4 lg:col-span-3">
                                    <div class="img-placeholder w-full h-40 md:h-44 rounded-xl flex items-center justify-center bg-gray-100">
                                        <img src="<?= $heroProduct['image'] ?>" alt="<?= $heroProduct['name'] ?>" class="w-full h-full object-cover rounded-xl" />
                                    </div>
                                </div>

                                <div class="col-span-12 md:col-span-8 lg:col-span-9">
                                    <!-- heroProduct -->
                                    <div class="flex justify-between items-start flex-wrap">
                                        <div>
                                            <a href="Product_description.php?
                                                id=<?= $heroProduct['id'] ?>&
                                                name=<?= $heroProduct['name'] ?>&
                                                price=<?= $heroProduct['price'] ?>&
                                                category=<?= $heroProduct['category'] ?>&
                                                status=<?= $heroProduct['status_raw'] ?>&
                                                image=<?= $heroProduct['image'] ?>">

                                                <h3 class="text-lg font-semibold"><?= $heroProduct['name'] ?></h3>
                                            </a>
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
                                            <input type="hidden" name="product_id" value="<?= $heroProduct['id'] ?>">
                                            <input type="hidden" name="product_name" value="<?= htmlspecialchars($heroProduct['name']) ?>">
                                            <input type="hidden" name="product_price" value="<?= $heroProduct['price'] ?>">
                                            <input type="hidden" name="product_image" value="<?= htmlspecialchars($heroProduct['image']) ?>">
                                            <input type="hidden" name="action_type" value="add_to_cart">
                                            
                                            <button type="submit" class="flex items-center gap-2 border border-gray-200 rounded-md px-3 py-2 text-gray-700 hover:bg-gray-50 transition">
                                                <span class="text-sm"> Add to cart</span>
                                            </button>
                                        </form>

                                        <!-- Buy now -->
                                        <form action="ADDTOCART.php" method="POST">
                                            <input type="hidden" name="product_id" value="<?= $heroProduct['id'] ?>">
                                            <input type="hidden" name="product_name" value="<?= htmlspecialchars($heroProduct['name']) ?>">
                                            <input type="hidden" name="product_price" value="<?= $heroProduct['price'] ?>">
                                            <input type="hidden" name="product_image" value="<?= htmlspecialchars($heroProduct['image']) ?>">
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
                        <?php endif; ?>

                        <!-- Popular products -->
                        <section class="mb-8">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold">
                                    <?php
                                    if (!empty($search)) {
                                        $title = "Search results for: " . htmlspecialchars($search);
                                    } elseif ($seeAll) {
                                        $title = "All The Popular Product";
                                    } else {
                                        $title = "Explore The Popular Product";
                                    }
                                    ?>
                                    <?= $title ?>
                                </h2>
                                <?php if (!$seeAll): ?>
                                    <a href="HOME.php?see=all" class="text-sm text-gray-500">See all</a>
                                <?php else : ?>
                                    <a href="HOME.php" class="text-sm text-gray-500">Show Top 16</a>
                                <?php endif; ?>
                            </div>
                            <?php if (empty($popularProducts)): ?>
                                <p class="text-gray-500 text-sm">No products found for "<?= htmlspecialchars($search) ?>".</p> 
                            <?php endif; ?>
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
                                                <a href="Product_description.php?
                                                    id=<?= $product['id'] ?>&
                                                    name=<?= $product['name']  ?>&
                                                    price=<?= $product['price'] ?>&
                                                    category=<?= $product['category'] ?>&
                                                    status=<?= $product['status_raw'] ?>&
                                                    image=<?= $product['image'] ?>">

                                                    <!-- removed (1), (2), (3)... here -->
                                                    <?= $product['name'] ?>
                                                </a>
                                            </div> 
                                            <div class="text-xs text-gray-500">$<?= number_format($product['price'], 2) ?></div>
                                        </div>
                                        
                                        <div class="mt-3 flex items-center justify-center">
                                            <div class="flex items-center gap-20">
                                                <!-- Add to cart -->
                                                <form action="ADDTOCART.php" method="POST" class="inline-block">
                                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                    <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']) ?>">
                                                    <input type="hidden" name="product_price" value="<?= $product['price'] ?>">
                                                    <input type="hidden" name="product_image" value="<?= htmlspecialchars($product['image']) ?>">
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
                                    <h3 class="text-sm font-semibold">Category</h3>
                                    <?php if (!$Cateall):?>
                                        <a href="HOME.php?category=all" class="text-xs text-gray-400">View All</a>
                                    <?php else: ?>
                                        <a href="HOME.php" class="text-xs text-gray-400">View Less</a>
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
