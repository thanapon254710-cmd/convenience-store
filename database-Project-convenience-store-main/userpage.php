<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Profile</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
        // Tailwind theme extension - Identical to HOME.php for consistent styling
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
                            <li><a href="checkout.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💳</span><span class="text-sm font-medium">Checkout</span></a></li>
                            <li class="bg-red-50 rounded-lg"><a href="userpage.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">👤</span><span class="text-sm font-medium">Profile</span></a></li>
                            <li><a href="preach.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">📜</span><span class="text-sm font-medium">Preach History</span></a></li>
                            <li><a href="contact.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💬</span><span class="text-sm font-medium">Contact us</span></a></li>
                            <li><a href="setting.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">⚙️</span><span class="text-sm font-medium">Setting</span></a></li>
                        </ul>
                    </nav>
                </div>
            </aside>
            
            <div class="flex-1 p-8">
                <div class="max-w-screen-xl mx-auto">
                    
                    <div class="flex items-center justify-between p-4 bg-white rounded-xl shadow-card mb-6">
                        <div class="flex flex-col">
                            <h1 class="text-2xl font-bold">Welcome, Name</h1>
                            <p class="text-sm text-gray-500">Date:</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <input type="text" placeholder="Search" class="py-2 pl-3 pr-10 text-sm focus:outline-none rounded-l-md w-48 border border-gray-200">
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200">
                                    <i class="fas fa-bell text-gray-600"></i>
                                </button>
                                <button class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200">
                                    <i class="fas fa-user-circle text-gray-600"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-card p-8">
                        <div class="flex items-start justify-between border-b pb-4 mb-6">
                            <div class="flex items-center gap-6">
                                <img src="asset/account.png" alt="profilepicture" class="w-20 h-20 rounded-full object-cover border-2 border-primary">
                                <div>
                                    <p class="text-lg font-semibold">Name: [User Name]</p>
                                    <p class="text-gray-600">Email: [User Email]</p>
                                </div>
                            </div>
                            <input type="button" value="Edit profile" class="bg-accent text-white px-4 py-2 rounded-md text-sm font-semibold shadow hover:bg-primary transition cursor-pointer">
                        </div>

                        <div class="space-y-6">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm font-medium text-gray-700 mb-1">Full Name</p>
                                    <input type="text" id="fulln" name="fullname" placeholder="YourFirstName" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:outline-none focus:border-primary">
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700 mb-1">NickName</p>
                                    <input type="text" id="nickn" name="nickname" placeholder="YourNickName" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:outline-none focus:border-primary">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm font-medium text-gray-700 mb-1">Gender</p>
                                    <select class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:outline-none focus:border-primary">
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700 mb-1">Country</p>
                                    <input type="text" id="country" name="country" placeholder="YourCountry" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:outline-none focus:border-primary">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm font-medium text-gray-700 mb-1">Language</p>
                                    <select class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:outline-none focus:border-primary">
                                        <option value="eng">English</option>
                                        <option value="th">Thai</option>
                                    </select>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700 mb-1">Time Zone</p>
                                    <select class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:outline-none focus:border-primary">
                                        <option value="eng">UTC+00:00</option>
                                        <option value="th">UTC+07:00</option>
                                    </select>
                                </div>
                            </div>

                            <div class="pt-4 border-t">
                                <p class="text-base font-semibold text-gray-800 mb-3">My Email Address</p>
                                <div class="flex items-center justify-between bg-soft p-3 rounded-lg border border-outline">
                                    <h3 class="text-sm">my owned email</h3>
                                </div>
                                <div class="mt-4">
                                    <input type="button" value="+Add Email Address" class="bg-red-50 text-primary px-4 py-2 rounded-md text-sm font-semibold shadow hover:bg-red-100 transition cursor-pointer">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            </div>

    </body>
</html>