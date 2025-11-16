<?php
session_start();

/* ---------------------------------------------
   INITIALIZE SETTINGS IF NOT EXISTS
---------------------------------------------- */
if (!isset($_SESSION['settings'])) {
    $_SESSION['settings'] = [
        'email_notifications'   => false,
        'shipping_updates'      => false,
        'promo_notifications'   => false,
        'login_alerts'          => false,
        'two_factor'            => false,
        'dark_mode'             => false,
    ];
}

$settings = $_SESSION['settings'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — Convenience Store</title>

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

<!-- ======================= SIDEBAR ======================= -->
<aside class="w-64 bg-sidebar p-4 sticky top-0 h-screen overflow-y-auto">
    <div class="bg-white border border-blue-300 rounded-xl p-4 shadow-sm flex flex-col h-full">

        <!-- LOGO -->
        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-600 to-cyan-400 text-white flex items-center justify-center">
                <img src="asset/2960679-2182.png">
            </div>
            <div>
                <div class="text-lg font-semibold leading-tight">
                    Convenience<br><span class="text-sm text-gray-500">Store</span>
                </div>
            </div>
        </div>

        <!-- MENU -->
        <nav class="flex-1">
                    <ul class="space-y-3">
                        <!--Tab Bar-->
                        <li><a href="HOME.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-primary">🏠</span><span class="text-sm font-medium">Home</span></a></li>
                        <li><a href="WISHLIST.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">❤️</span><span class="text-sm font-medium">Wishlist </span></a></li>
                        <li><a href="checkout.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💳</span><span class="text-sm font-medium">Checkout</span></a></li>
                        <li><a href="userpage.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">👤</span><span class="text-sm font-medium">Profile</span></a></li>
                        <li><a href="preach.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">📜</span><span class="text-sm font-medium">Preach History</span></a></li>
                        <li><a href="contact.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">💬</span><span class="text-sm font-medium">Contact us</span></a></li>
                        <li class="bg-red-50 rounded-lg"><a href="setting.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"><span class="w-9 h-9 flex items-center justify-center rounded-md bg-white border text-gray-600">⚙️</span><span class="text-sm font-medium">Setting</span></a></li>
                    </ul>
                </nav>

        <!-- CUSTOMER SERVICE -->
        <div class="mt-6">
            <div class="bg-gradient-to-br from-red-200 to-red-400 text-white rounded-2xl p-4 shadow-soft-lg relative overflow-hidden">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-2xl">＋</div>
                    <div>
                        <div class="text-sm font-semibold">Need Help</div>
                        <p class="text-xs mt-1 opacity-90">Lorem Ipsum is simply dummy text of the printing industry.</p>
                    </div>
                </div>

                <button class="mt-4 bg-white text-red-600 text-sm font-semibold px-3 py-2 rounded-md shadow">
                    Customer Service
                </button>

                <div class="absolute -right-6 -bottom-8 w-40 h-40 rounded-full bg-white/10 transform rotate-12"></div>
            </div>
        </div>

    </div>
</aside>

<!-- ======================= MAIN CONTENT ======================= -->
<div class="flex-1 p-10">
    <h1 class="text-3xl font-bold mb-6" style="font-family:'PT Serif'">Settings</h1>

    <div class="bg-white rounded-2xl shadow-card p-6 w-full max-w-3xl space-y-8">

<?php
/* ----------- TOGGLE COMPONENT WITH ANIMATION ------------ */
function toggle($label, $key, $state) {

    $isOn = $state ? "bg-primary" : "bg-gray-300";
    $knob = $state ? "translate-x-6" : "";

    echo "
    <div class='flex items-center justify-between py-4 border-b last:border-none'>
        <span class='text-gray-700 text-lg'>$label</span>

        <button 
            id='toggle-$key'
            class='relative w-12 h-6 rounded-full transition-colors duration-300 $isOn'
            onclick=\"toggleSwitch('$key')\">

            <span 
                class='absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-300 $knob'
                id='knob-$key'></span>
        </button>
    </div>";
}
?>

<!-- =================== ACCOUNT =================== -->
<section>
    <h2 class="text-xl font-semibold mb-3">Account</h2>

    <div class="space-y-4">
        <div class="flex justify-between py-3 border-b">
            <span>Email:</span>
            <span class="text-gray-600">example@gmail.com</span>
        </div>

        <div class="flex justify-between py-3 border-b">
            <span>Change Username</span>
            <a href="#" class="text-primary hover:underline">Edit</a>
        </div>

        <div class="flex justify-between py-3">
            <span>Change Password</span>
            <a href="#" class="text-primary hover:underline">Edit</a>
        </div>
    </div>
</section>

<!-- =================== NOTIFICATIONS =================== -->
<section>
    <h2 class="text-xl font-semibold mb-3">Notifications</h2>
    <?php
        toggle("Email Notifications", "email_notifications", $settings['email_notifications']);
        toggle("Shipping Status Updates", "shipping_updates", $settings['shipping_updates']);
        toggle("Promotions", "promo_notifications", $settings['promo_notifications']);
    ?>
</section>

<!-- =================== PRIVACY =================== -->
<section>
    <h2 class="text-xl font-semibold mb-3">Privacy & Security</h2>
    <?php
        toggle("Login Alerts", "login_alerts", $settings['login_alerts']);
        toggle("Two-Factor Authentication", "two_factor", $settings['two_factor']);
    ?>
</section>

<!-- =================== APPEARANCE =================== -->
<section>
    <h2 class="text-xl font-semibold mb-3">Appearance</h2>
    <?php toggle("Dark Mode", "dark_mode", $settings['dark_mode']); ?>
</section>

<!-- =================== APP INFO =================== -->
<section>
    <h2 class="text-xl font-semibold mb-3">App Info</h2>

    <div class="flex justify-between py-3 border-b">
        <span>Version</span>
        <span class="text-gray-600">1.0.0</span>
    </div>

    <div class="flex justify-between py-3 border-b">
        <span>Terms & Conditions</span>
        <a href="#" class="text-primary hover:underline">Open</a>
    </div>

    <div class="flex justify-between py-3">
        <span>Privacy Policy</span>
        <a href="#" class="text-primary hover:underline">Open</a>
    </div>
</section>

    </div>
</div>

</div>

<!-- ================= AJAX SCRIPT ================= -->
<script>
function toggleSwitch(settingKey) {

    const wrapper = document.getElementById("toggle-" + settingKey);
    const knob    = document.getElementById("knob-" + settingKey);

    const isOn = wrapper.classList.contains("bg-primary");

    // --- PLAY ANIMATION IMMEDIATELY ---
    if (isOn) {
        wrapper.classList.remove("bg-primary");
        wrapper.classList.add("bg-gray-300");
        knob.classList.remove("translate-x-6");
    } else {
        wrapper.classList.add("bg-primary");
        wrapper.classList.remove("bg-gray-300");
        knob.classList.add("translate-x-6");
    }

    // --- SAVE TO SESSION (AJAX) ---
    fetch("update_setting.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: `setting=${settingKey}&value=${isOn ? 0 : 1}`
    });
}
</script>

</body>
</html>
