<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Shopping Cart</title>
        <link rel="stylesheet" href="css/cart.css">
    </head>
    <body style="background-color: ffffff;">

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
            <div class="leftcart">
                <div class="middlep">
                    <div class="topleft">
                        <input type="image" id="back" src="asset/left-arrow.png"  style="padding-left: 1%;"/>
                        <p style="font-family: Arial; margin: 0;font-size: 22px;"><b>Shopping Continue</b></p>
                    </div>
                    <hr style="width: 100%; margin-bottom: 1.5rem; margin-right: 0;">
                    <p><b>Shopping cart</b></p>
                    <p>You have ... item in your cart</p>
                    <form action="ShippingCart.php" method="post">
                        <!--php-->
                    </form>
                    <div class="botleft">
                        <p style="font-family: Arial; margin-left: 28px;">Item</p>
                    </div>
                </div>
            </div>
            <div class="rightcart">
                <div class="redbox">
                    <p style="padding-left: 10px; font-size: 19px;"><b>Card Details</b></p>
                    <p style="padding-left: 10px; font-size: 12px;">Cart type</p>

                    <div class="paymentmethod">                        
                        <input type="image" id="mastercard" value="mastercard" src="asset/mastercard.png">         
                        <input type="image" id="visa" value="visa" src="asset/Visa Payment Card.png">
                        <input type="image" id="paypal" value="rupay" src="asset/Rupay-Logo.png">
                        <input type="button" id="seeall" value="see all" src="asset/Rupay-Logo.png">
                    </div>

                    <p style="padding-left: 10px; font-size: 12px; padding-top: 15px;">Name on Card</p>
                    <input type="text" id="nameoncard" name="nameoncard" placeholder="Enter name on card" style="margin-left: 10px; width: 95%; height: 30px; border-radius: 5px; border: none; background-color: #c03636; caret-color: white;">
                    <p style="padding-left: 10px; font-size: 12px; padding-top: 15px;">Card Number</p>
                    <input type="text" id="cardnumber" name="cardnumber" placeholder="Card Number" style="margin-left: 10px; width: 95%; height: 30px; border-radius: 5px; border: none; background-color: #c03636; caret-color: white;">

                    <div class="excvv">
                        <div class="ex">
                            <p style="padding-left: 10px; font-size: 12px; padding-top: 15px;">Expiration date</p>
                            <input type="text" id="exdate" name="exdate" placeholder="mm/yy" style="margin-left: 10px; width: 95%; height: 30px; border-radius: 5px; border: none; background-color: #c03636; caret-color: white;">
                        </div>
                        <div class="cvv">
                            <p style="padding-left: 10px; font-size: 12px; padding-top: 15px;">CVV</p>
                            <input type="text" id="cvv" name="cvv" placeholder="CVV" style="margin-left: 10px; width: 95%; height: 30px; border-radius: 5px; border: none; background-color: #c03636; caret-color: white;">
                        </div>
                    </div>
                    <hr style="width: 90%; margin-top: 20px; margin-left: auto; margin-right: auto;">
                    <p style="padding-left: 10px; font-size: 12px;">Subtotal:</p>
                    <p style="padding-left: 10px; font-size: 12px;">Shipping:</p>
                    <p style="padding-left: 10px; font-size: 12px;">Total(Tax incl.):</p>
                    <input type="button" id="paynow" value="Pay Now" style="width: 90%; height: 40px; font-size: 16px; border: none; border-radius: 5px; margin-top: 10px; margin-bottom: 10px; margin-left: auto; margin-right: auto; display: block;">
                </div>
            </div>
        </section>
    </body>
</html>
