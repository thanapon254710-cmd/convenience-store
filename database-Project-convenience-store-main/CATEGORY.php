<?php
session_start();
require_once("userconnect.php");

// Read selected category
$currentCategory = $_GET['category'] ?? '';
if ($currentCategory === '') {
    header("Location: HOME.php");
    exit;
}

// Fetch products in this category
$stmt = $mysqli->prepare("
    SELECT product_id, product_name, price, stock_qty, image_path, category
    FROM products
    WHERE category = ?
    ORDER BY product_name ASC
");
$stmt->bind_param("s", $currentCategory);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        "id"       => $row["product_id"],
        "name"     => $row["product_name"],
        "price"    => (float)$row["price"],
        "quantity" => (int)$row["stock_qty"],
        "category" => $row["category"],
        "image"    => $row["image_path"] ?: 'asset/default.png'
    ];
}

$wishlist = $_SESSION['wishlist'] ?? [];
$cart     = $_SESSION['cart'] ?? [];

$cartTotal = array_sum(array_map(function($i) {
    $qty = isset($i['quantity']) ? (int)$i['quantity'] : 1;
    return ((float)$i['price']) * $qty;
}, $cart));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($currentCategory) ?> — Category</title>

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
                        outline: '#e8e8ea',
                    },
                    fontFamily: {
                        body: ['"PT Serif"', 'Georgia', 'serif'],
                        ui: ['Inter', 'system-ui', 'Arial']
                    },
                    boxShadow: {
                        'card': '0 10px 20px rgba(9,18,40,0.06)',
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome (hearts) -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

</head>

<body class="min-h-screen bg-gray-50 text-gray-800">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-sidebar p-4 sticky top-0 h-screen overflow-y-auto">
        <div class="bg-white border border-blue-300 rounded-xl p-4 shadow-sm flex flex-col h-full">

            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-600 to-cyan-400 
                            text-white flex items-center justify-center text-lg font-bold">
                    <img src="asset/2960679-2182.png">
                </div>
                <div>
                    <div class="text-lg font-semibold">
                        Convenience<br>
                        <span class="text-sm text-gray-500">Store</span>
                    </div>
                </div>
            </div>

            <nav class="flex-1">
                <ul class="space-y-3">
                    <li><a href="HOME.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                        <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">🏠</span>
                        <span class="text-sm font-medium">Home</span></a></li>

                    <li><a href="WISHLIST.php"
                        class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                        <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">❤️</span>
                        <span class="text-sm font-medium">Wishlist</span></a></li>

                    <li><a href="checkout.php"
                        class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                        <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💳</span>
                        <span class="text-sm font-medium">Checkout</span></a></li>

                    <li><a href="userpage.php"
                        class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                        <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">👤</span>
                        <span class="text-sm font-medium">Profile</span></a></li>

                    <li><a href="preach.php"
                        class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                        <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">📜</span>
                        <span class="text-sm font-medium">Preach History</span></a></li>

                    <li><a href="contact.php"
                        class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                        <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💬</span>
                        <span class="text-sm font-medium">Contact us</span></a></li>

                    <li><a href="setting.php"
                        class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50">
                        <span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">⚙️</span>
                        <span class="text-sm font-medium">Setting</span></a></li>
                </ul>
            </nav>

        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="flex-1 p-8">
        <div class="max-w-screen-xl mx-auto">
            <div class="grid grid-cols-12 gap-6">

                <!-- LEFT SIDE: PRODUCTS -->
                <main class="col-span-12 lg:col-span-8">

                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h1 class="text-2xl font-bold" style="font-family:'PT Serif'">
                                Category: <?= htmlspecialchars($currentCategory) ?>
                            </h1>
                            <p class="text-xs text-gray-500">Showing all products in this category</p>
                        </div>

                        <a href="HOME.php" class="text-sm text-gray-500 hover:text-primary">
                            ← Back to Home
                        </a>
                    </div>

                    <!-- PRODUCT GRID -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">

                        <?php foreach ($products as $product):
                            $inWishlist = isset($wishlist[$product['id']]);
                        ?>
                        <div class="bg-white rounded-xl p-4 shadow-card flex flex-col">
                            
                            <!-- Image -->
                            <div class="w-full h-40 bg-gray-100 rounded-lg overflow-hidden">
                                <img src="<?= $product['image'] ?>" class="w-full h-full object-cover">
                            </div>

                            <!-- Name + Price -->
                            <div class="mt-3 flex-1">
                                <a href="Product_description.php?
                                    id=<?= $product['id'] ?>&
                                    name=<?= urlencode($product['name']) ?>&
                                    price=<?= $product['price'] ?>&
                                    category=<?= urlencode($product['category']) ?>&
                                    image=<?= urlencode($product['image']) ?>">
                                    <div class="text-sm font-semibold"><?= $product['name'] ?></div>
                                </a>

                                <div class="text-xs text-gray-500 mt-1">
                                    $<?= number_format($product['price'], 2) ?>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="mt-3 flex items-center justify-between">

                                <!-- Add to cart -->
                                <form action="ADDTOCART.php" method="POST">
                                    <input type="hidden" name="product_name"  value="<?= htmlspecialchars($product['name']) ?>">
                                    <input type="hidden" name="product_price" value="<?= $product['price'] ?>">
                                    <input type="hidden" name="product_image" value="<?= htmlspecialchars($product['image']) ?>">
                                    <input type="hidden" name="action_type"   value="add_to_cart">

                                    <button class="w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center hover:bg-red-200">
                                        +
                                    </button>
                                </form>

                                <!-- Wishlist -->
                                <form action="WISHLIST_ACTION.php" method="POST">
                                    <input type="hidden" name="product_id"    value="<?= $product['id'] ?>">
                                    <input type="hidden" name="product_name"  value="<?= htmlspecialchars($product['name']) ?>">
                                    <input type="hidden" name="product_price" value="<?= $product['price'] ?>">
                                    <input type="hidden" name="product_image" value="<?= htmlspecialchars($product['image']) ?>">

                                    <button name="action" value="toggle"
                                            class="w-8 h-8 rounded-full border flex items-center justify-center transition
                                            <?= $inWishlist ? 'bg-red-500 text-white' : 'text-gray-500 hover:bg-red-50 hover:text-red-600' ?>">
                                        <i class="<?= $inWishlist ? 'fas' : 'far' ?> fa-heart"></i>
                                    </button>
                                </form>

                            </div>

                        </div>
                        <?php endforeach; ?>

                    </div>

                </main>

                <!-- RIGHT SIDE: CART + CATEGORY -->
                <aside class="col-span-12 lg:col-span-4">
                    <div class="sticky top-6 space-y-6">

                        <!-- CART -->
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
                                        $qty = $item['quantity'] ?? 1;
                                        $itemname = htmlspecialchars($item['name']);
                                    ?>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="truncate pr-2">
                                            <?= $itemname ?><?= $qty > 1 ? " x{$qty}" : "" ?>
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <span>$<?= number_format($item['price'] * $qty, 2) ?></span>
                                            <a href="ADJUSTCART.php?one=<?= $index ?>"      class="text-s text-white/90 hover:text-white">[−]</a>
                                            <a href="ADJUSTCART.php?increase=<?= $index ?>" class="text-s text-white/90 hover:text-white">[＋]</a>
                                            <a href="ADJUSTCART.php?all=<?= $index ?>"      class="text-s text-white/90 hover:text-white">[×]</a>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <hr class="my-3 border-white/50">

                            <div class="flex items-center justify-between font-bold text-lg">
                                <span>Total:</span>
                                <span>$<?= number_format($cartTotal, 2) ?></span>
                            </div>

                            <a href="checkout.php"
                               class="block text-center mt-3 bg-white text-primary text-sm font-semibold py-2 rounded-md shadow hover:bg-gray-100">
                                Proceed to Checkout
                            </a>

                        </div>

                        <!-- CATEGORY LIST -->
                        <div class="bg-white rounded-2xl p-4 shadow-card">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-semibold">Category</h3>
                                <a href="HOME.php?category=all" class="text-xs text-gray-400">View All</a>
                            </div>

                            <div class="space-y-3 max-h-72 overflow-auto pr-2">
                                <?php
                                $catResult = $mysqli->query("SELECT DISTINCT(category) FROM products");

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

                                while ($row = $catResult->fetch_assoc()):
                                    $cateName = $row['category'];
                                    $img = $categoryImages[$cateName] ?? "asset/category/other.png";
                                ?>
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-md bg-gray-100">
                                        <img src="<?= $img ?>" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium">
                                       a    <a href="CATEGORY.php?category=<?= urlencode($cateName) ?>">
                                                <?= $cateName ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>

                    </div>
                </aside>

            </div>
        </div>
    </div>

</div>

</body>
</html>
