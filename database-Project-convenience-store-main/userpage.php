<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>UserProfile</title>
        <link rel="stylesheet" href="css/userp.css">
    </head>
    <body>
        
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
        <nav class="flex-1">
            <ul class="space-y-3">
                <!--Tab Bar-->
                <li><a href="HOME.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">🏠</span><span class="text-sm font-medium">Home</span></a></li>
                <!-- <li><a href="#" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">🔎</span><span class="text-sm font-medium">Explore</span></a></li> -->
                <li><a href="#" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">❤️</span><span class="text-sm font-medium">Wishlist </span></a></li>
                <li><a href="cart.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">🛒</span><span class="text-sm font-medium">Cart</span></a></li>
                <!-- <li><a href="#" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💳  </span><span class="text-sm font-medium">Selling</span></a></li> -->
                <li><a href="userpage.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">👤</span><span class="text-sm font-medium">Profile</span></a></li>
                <li><a href="#" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">📜</span><span class="text-sm font-medium">Preach History</span></a></li>
                <li><a href="#" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💬</span><span class="text-sm font-medium">Contact us</span></a></li>
                <li><a href="#" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">⚙️</span><span class="text-sm font-medium">Setting</span></a></li>
            </ul>
        </nav>
        
        <!--
        <div class="pagebar">
            <p>This is pagebar</p>
            <a href="HOME.php"><p>Home</p></a>
            <p>Search</p>
            <a href="cart.php"><p>Cart</p></a>
            <a href="userpage.php"><p>UserProfile</p></a>
        </div>
        -->
        <section class="content">
            <div class="rightside">
                <div class="topright">
                    <div id = "namedate">
                        <p style="font-size: 20px;"><b>Welcome, Name</b></p>
                        <p>Date:</p>
                    </div>
                    <input type="text" id="search" name="search" placeholder="Search">
                    <input type="image" id="noti" value="noti" src="">
                    <input type="image" id="profile" value="profile" src="">
                </div>
                <div class="botright">
                    <div class="userpro">
                        <div class="nameemail">
                            <div id="ne">
                                <img src="asset/account.png" alt="profilepicture" style="width: 75px; height: 75px; margin-top: 1.5%;">
                                <div id = "text1">
                                    <p>Name: </p>
                                    <p>Email: </p>
                                </div>
                            </div>
                            <input type="button" id="edit" value="Edit profile" style="justify-content: end; margin-top: 20px;">
                        </div>
                        <div class="infoinput">
                            <div class="fullnick">
                                <div id = "fullname">
                                    <p>Full Name</p>
                                    <input type="text" id="fulln" name="fullname" placeholder="YourFirstName">
                                </div>
                                <div id = "nickname">
                                    <p>NickName</p>
                                    <input type="text" id="nickn" name="nickname" placeholder="YourNickName">
                                </div>
                            </div>
                        </div>
                        <div class="gendercountry">
                            <div id = "gender">
                                <p>Gender</p>
                                <select>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div id = "country">
                                <p>Country</p>
                                <input type="text" id="country" name="country" placeholder="YourCountry">
                            </div>
                        </div>
                        <div class="langtime">
                            <div id = "language">
                                <p>Language</p>
                                <select>
                                    <option value="eng">English</option>
                                    <option value="th">Thai</option>
                                </select>
                            </div>
                            <div id = "timezone">
                                <p>Time Zone</p>
                                <select>
                                    <option value="eng">UTC+00:00</option>
                                    <option value="th">UTC+07:00</option>
                                </select>
                            </div>
                        </div>
                        <div class="addemail">
                            <p style="font-size: 18px;"><b>My Email Address</b></p>
                            <div class="ownedemail">
                                <h3>my owned email</h3>
                            </div>
                            <div id="add">
                                <input type="button" id="addemail" value="+Add Email Address">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </body>
</html>