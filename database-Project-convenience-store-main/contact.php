<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us — Convenience Store</title>

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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="min-h-screen antialiased text-gray-800">

<div class="flex min-h-screen">

    <!-- =============== SIDEBAR =============== -->
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
                    <li><a href="checkout.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💳</span><span class="text-sm font-medium">Checkout</span></a></li>
                    <li><a href="userpage.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">👤</span><span class="text-sm font-medium">Profile</span></a></li>
                    <li><a href="preach.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">📜</span><span class="text-sm font-medium">Preach History</span></a></li>
                    <li class="bg-red-50 rounded-lg"><a href="contact.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💬</span><span class="text-sm font-medium">Contact us</span></a></li>
                    <li><a href="setting.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">⚙️</span><span class="text-sm font-medium">Setting</span></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- ================= MAIN CONTENT ================= -->
    <div class="flex-1 p-10">

        <h1 class="text-3xl font-bold mb-4" style="font-family:'PT Serif'">Contact Us</h1>

        <p class="text-gray-600 mb-8 text-lg max-w-2xl">
            We’d love to hear from you. Reach out to us anytime through our social platforms or support channels below.
        </p>

        <div class="bg-white rounded-2xl shadow-card p-8 w-full max-w-2xl">

            <h2 class="text-xl font-semibold mb-4">Our Socials</h2>

            <div class="space-y-4 text-gray-700 text-lg">

                <div class="flex items-center gap-3">
                    <i class="fa-brands fa-instagram text-pink-500 text-xl"></i>
                    Instagram: <span class="text-gray-600">@conveniencestore</span>
                </div>

                <div class="flex items-center gap-3">
                    <i class="fa-brands fa-facebook text-blue-600 text-xl"></i>
                    Facebook: <span class="text-gray-600">Convenience Store Official</span>
                </div>

                <div class="flex items-center gap-3">
                    <i class="fa-brands fa-line text-green-500 text-xl"></i>
                    Line: <span class="text-gray-600">@storehelp</span>
                </div>

                <div class="flex items-center gap-3">
                    <i class="fa-brands fa-discord text-indigo-500 text-xl"></i>
                    Discord: <span class="text-gray-600">StoreSupport#2025</span>
                </div>

            </div>

            <hr class="my-6">

            <h2 class="text-xl font-semibold mb-3">Support Contacts</h2>

            <div class="space-y-3 text-lg text-gray-700">
                <div><i class="fa-solid fa-envelope text-primary mr-2"></i> Email: support@conveniencestore.com</div>
                <div><i class="fa-solid fa-phone text-primary mr-2"></i> Hotline: +66 02-123-4567</div>
                <div><i class="fa-solid fa-clock text-primary mr-2"></i> Mon–Fri, 9:00 AM – 6:00 PM</div>
            </div>

        </div>

    </div>

</div>
</body>
</html>
