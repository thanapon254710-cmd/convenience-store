<?php
session_start();
require_once("userconnect.php");

$id    = isset($_GET['id']) ? (int)$_GET['id'] : null;
$name  = $_GET['name']  ?? 'Unknown Product';
$price = $_GET['price'] ?? '0.00';
$category = $_GET['category'] ?? null;
$status = $_GET['status'] ?? null;
$image = $_GET['image'] ?? 'asset/default.png';

// --- WISHLIST (SESSION) ---
$wishlist = $_SESSION['wishlist'] ?? [];
$inWishlist = isset($wishlist[$id]);

if ($status === 'Active' || $status === 'In Stock') {
    $statusDisplay = 'In Stock';
    $statusIcon    = 'fas fa-check-circle text-green-500';
} elseif ($status === 'Out of Stock') {
    $statusDisplay = 'Out of Stock';
    $statusIcon    = 'fas fa-times-circle text-red-500';
}

// Fetch related products (same category, different name)
$relatedProducts = [];

if ($category) {
    $q = $mysqli->prepare("
        SELECT *
        FROM products
        WHERE product_id != ? AND category = ? AND status != 'Inactive'
        LIMIT 5
    ");
    $q->bind_param('is', $id, $category);
    $q->execute();
    $result = $q->get_result();
    
    $relatedProducts = [];

    while ($row = $result->fetch_assoc()) {
        $statusRaw = $row['status']; 
        if ($statusRaw === 'Active') {
            $statusDisplay = 'In Stock';
        } elseif ($statusRaw === 'Out of Stock') {
            $statusDisplay = 'Out of Stock';
        }

        $relatedProducts[] = [
            'id'            => (int)$row["product_id"],
            'name'          => $row['product_name'],
            'price'         => (float)$row['price'],
            'quantity'      => (int)$row['stock_qty'],
            'category'      => $row['category'],
            'status_raw'   => $statusRaw,
            'status_label' => $statusDisplay,   
            'image'         => $row['image_path'] ?? 'asset/default.png'
        ];
    }
    $q->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Number 1 Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Using the same primary/accent colors as the index.php dashboard
                        primary: '#b94a4a', 
                        accent: '#e86d6d',
                        'placeholder-bg': '#f7f7f7', 
                        'placeholder-fill': '#ccc',
                    },
                    // We need 'soft-lg' shadow definition from the main dashboard for consistency
                    boxShadow: {
                        'soft-lg': '0 14px 30px rgba(9,18,40,0.08)',
                    }
                }
            }
        }
    </script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* Existing CSS for abstract shapes */
        .shape {
            position: absolute;
            background-color: var(--tw-placeholder-fill);
            opacity: 0.7;
        }
        .shape-1 { 
            width: 100px;
            height: 100px;
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
            top: 10%;
            left: 20%;
            transform: rotate(180deg);
        }
        .shape-2 { 
            width: 80px;
            height: 80px;
            clip-path: polygon(
                50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%
            );
            bottom: 25%;
            left: 10%;
        }
        .shape-3 { 
            width: 120px;
            height: 120px;
            border-radius: 15px;
            bottom: 10%;
            right: 5%;
        }
    </style>
</head>
<body class="font-sans text-gray-800">

    <header class="border-b border-gray-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center py-4">
        
        <div class="flex items-center gap-x-1"> 
            <div class="w-12 h-12 rounded-md bg-gradient-to-br from-blue-600 to-cyan-400 text-white flex items-center justify-center text-lg font-bold">
            <img src="asset/2960679-2182.png">
        </div>  
            
            <div class="text-lg font-semibold">Convenience<br/><span class="text-sm text-gray-500">Store</span></div>
        </div>
        <div class="flex items-center space-x-4">
            <div class="relative flex items-center border border-gray-300 rounded-md pr-1">
                <input type="text" placeholder="Search Product" class="py-2 pl-3 pr-10 text-sm focus:outline-none rounded-l-md w-64">
            </div>
            
        </div>
    </div>
</header>

    <main class="container mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <div class="text-sm text-gray-500 mb-6">
            <a href="HOME.php" class="hover:text-primary">Home</a> / 
            <a href="CATEGORY.php?category=<?= urlencode($category) ?>" class="hover:text-primary"><?= $category ?></a> / 
            <span class="font-semibold"><?= $name ?></span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <div class="lg:col-span-2 flex flex-col md:flex-row gap-8">
                
                <div class="flex-1 min-h-[400px] rounded-lg overflow-hidden flex justify-center items-center">
                    <img src="<?= htmlspecialchars($image) ?>" class="w-full h-full object-contain">
                </div>

                <div class="flex-1 pt-4">
                    <h2 class="text-3xl font-medium mb-4"><?= htmlspecialchars($name) ?></h2>
                    
                    <div class="pb-4 border-b border-gray-200 mb-4">
                        <span class="text-xl font-bold">Price</span>
                        <span class="ml-4 text-xl">$<?= number_format($price, 2) ?></span>
                    </div>
                    
                    <div class="text-gray-800 font-medium mb-6">
                        <i class="<?= $statusIcon ?> mr-2"></i> <?= $statusDisplay ?>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <!-- add to cart -->
                        <form action="ADDTOCART.php" method="POST" class="inline-block">
                            <input type="hidden" name="product_name"  value="<?= htmlspecialchars($name) ?>">
                            <input type="hidden" name="product_price" value="<?= $price ?>">
                            <input type="hidden" name="product_image" value="<?= htmlspecialchars($image) ?>">
                            <input type="hidden" name="action_type"   value="add_to_cart">

                            <button class="bg-primary text-white font-bold px-6 py-2.5 rounded-md uppercase hover:bg-red-700 transition duration-150">
                                ADD TO CART
                            </button>
                        </form>

                        <!-- wishlist -->
                        <form action="WISHLIST_ACTION.php" method="POST" class="inline-block">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($id) ?>">
                            <input type="hidden" name="product_name" value="<?= htmlspecialchars($name) ?>">
                            <input type="hidden" name="product_price" value="<?= $price ?>">
                            <input type="hidden" name="product_image" value="<?= htmlspecialchars($image) ?>">

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

            <aside class="lg:col-span-1 pt-4">
                <div class="bg-gradient-to-br from-red-200 to-red-400 text-white rounded-2xl p-5 shadow-soft-lg">
                    <h3 class="text-xl font-semibold mb-4 border-b border-white/50 pb-3">Your Cart</h3>
                    
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

                    <div class="flex justify-between font-bold pt-2">
                        <span>Sub Total:</span>
                        <span class="text-white">Price</span>
                    </div>

                    <div class="flex space-x-3 mt-5">
                        <button class="flex-1 py-3 text-sm font-bold bg-primary text-white rounded-md uppercase hover:bg-red-700 transition duration-150">
                            <a href="cart.php">CHECKOUT</a>
                        </button>
                    </div>
                </div>
            </aside>
        </div>

        <section class="mt-16 pb-12">
            <h2 class="text-xl font-medium mb-6">RELATED PRODUCTS</h2>

            <?php if (!empty($relatedProducts)): ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    <?php foreach ($relatedProducts as $rp): ?>
                        <div class="text-center">
                            <div class="bg-placeholder-bg h-48 rounded-lg mb-3 relative flex justify-center items-center overflow-hidden">
                                <img src="<?= htmlspecialchars($rp['image'] ?? 'asset/default.png') ?>"
                                     alt="<?= htmlspecialchars($rp['name']) ?>"
                                     class="w-full h-full object-contain">
                            </div>
                            <a href="Product_description.php?
                                id=<?= $rp['id'] ?>&
                                name=<?= urlencode($rp['name']) ?>&
                                price=<?= $rp['price'] ?>&
                                category=<?= urlencode($rp['category']) ?>&
                                status=<?= urlencode($rp['status_raw']) ?>&
                                image=<?= urlencode($rp['image']) ?>">

                                <h3 class="font-bold text-gray-800"><?= htmlspecialchars($rp['name']) ?></h3>
                            </a>
                            <p class="text-sm text-gray-600">
                                $<?= number_format($rp['price'], 2) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-sm">No related products in this category yet.</p>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>
