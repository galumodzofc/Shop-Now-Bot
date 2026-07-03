<?php
// ===== YAHI 3 LINE CHANGE KARNA ZARURI HAI =====
$BOT_TOKEN = "8704875344:AAGfZNSXsFylhvfoWoCCim4jqiWiYXsXebk"; // BotFather se naya token le
$ADMIN_ID = 8154859186; // @userinfobot se apni ID nikal ke daal
$SUPPORT_USERNAME = "@GaluModzOwner"; // @ ke saath
// ===============================================

define('API_URL', 'https://api.telegram.org/bot'.$BOT_TOKEN.'/');
$update = json_decode(file_get_contents("php://input"), true);

// Products save karne ke liye file
$products_file = "products.json";
if (!file_exists($products_file)) file_put_contents($products_file, "[]");
$products = json_decode(file_get_contents($products_file), true);

// MESSAGE AAYA
if (isset($update["message"])) {
    $chat_id = $update["message"]["chat"]["id"];
    $user_id = $update["message"]["from"]["id"];
    $text = $update["message"]["text"];
    $name = $update["message"]["from"]["first_name"];

    // /START
    if ($text == "/start") {
        $msg = "❓ <b>Shop Now</b> : all key purchase & instantly delivery\n";
        $msg.= "📦 <b>My Orders</b> : check all key purchase history\n";
        $msg.= "👤 <b>Profile</b> : check your account information\n";
        $msg.= "📖 <b>How to Use</b> : view tutorial and work this bot\n";
        $msg.= "💬 <b>Support</b> : bot problem fixed for support admin\n";
        $msg.= "💰 <b>Balance</b> : check wallet & add money";

        $keyboard = [
            [['text' => '🛒 Shop Now', 'callback_data' => 'shop']],
            [['text' => '📦 My Orders', 'callback_data' => 'orders'], ['text' => '👤 Profile', 'callback_data' => 'profile']],
            [['text' => '📖 How to Use', 'callback_data' => 'howto'], ['text' => '💬 Support', 'url' => 'https://t.me/'.str_replace('@', '', $GaluModzOwner)]],
            [['text' => '💰 Balance', 'callback_data' => 'balance']]
        ];

        if ($user_id == $ADMIN_ID) {
            $keyboard[] = [['text' => '⚙️ Admin Panel', 'callback_data' => 'admin']];
        }

        sendMsg($chat_id, $msg, $keyboard);
    }

    // ADMIN PRODUCT ADD KAREGA
    elseif (strpos($text, "/add ") === 0 && $user_id == $8154859186) {
        $data = str_replace("/add ", "", $text);
        $parts = explode("|", $data);
        if (count($parts) == 2) {
            $products[] = ['name' => trim($parts[0]), 'price' => trim($parts[1])];
            file_put_contents($products_file, json_encode($products));
            sendMsg($chat_id, "✅ Product Added!\n\n<b>Name:</b> {$parts[0]}\n<b>Price:</b> ₹{$parts[1]}");
        } else {
            sendMsg($chat_id, "❌ Format: /add Product Name|Price\nExample: /add Netflix|199");
        }
    }

    // NORMAL BANDA /add KARE TO BLOCK
    elseif (strpos($text, "/add") === 0 && $user_id!= $8154859186) {
        sendMsg($chat_id, "⛔ Sirf Admin hi product add kar sakta hai!");
    }
}

// BUTTON CLICK
elseif (isset($update["callback_query"])) {
    $chat_id = $update["callback_query"]["message"]["chat"]["id"];
    $user_id = $update["callback_query"]["from"]["id"];
    $data = $update["callback_query"]["data"];
    $name = $update["callback_query"]["from"]["first_name"];

    apiRequest("answerCallbackQuery", ['callback_query_id' => $update["callback_query"]["id"]]);

    if ($data == 'shop') {
        if (empty($products)) {
            $txt = "🛒 <b>Shop Now</b>\n\nAbhi koi product nahi hai. Admin jaldi add karega!";
        } else {
            $txt = "🛒 <b>Shop Now</b>\n━━━━━━━━━━━━━━━\n\n";
            foreach ($products as $i => $p) {
                $txt.= ($i+1).". <b>{$p['name']}</b> - ₹{$p['price']}\n";
            }
            $txt.= "\nBuy karne ke liye $SUPPORT_USERNAME pe contact karo";
        }
        sendMsg($chat_id, $txt);
    }

    elseif ($data == 'admin' && $user_id == $8154859186) {
        $txt = "⚙️ <b>Admin Panel</b>\n\nProduct add karne ka command:\n\n";
        $txt.= "<code>/add Product Name|Price</code>\n\n";
        $txt.= "<b>Example:</b>\n<code>/add Netflix 1 Month|199</code>\n";
        $txt.= "<code>/add Spotify 3 Month|299</code>\n\n";
        $txt.= "Total Products: ".count($products);
        sendMsg($chat_id, $txt);
    }

    elseif ($data == 'admin' && $user_id!= $ADMIN_ID) {
        sendMsg($chat_id, "⛔ Access Denied! Ye sirf Admin ke liye hai.");
    }

    elseif ($data == 'orders') sendMsg($chat_id, "📦 <b>My Orders</b>\n\nAbhi koi order nahi hai $name");
    elseif ($data == 'profile') sendMsg($chat_id, "👤 <b>Your Profile</b>\n\nName: $name\nUser ID: <code>$chat_id</code>\nBalance: ₹0");
    elseif ($data == 'howto') sendMsg($chat_id, "📖 <b>How to Use</b>\n\n1. Shop Now se product dekho\n2. Support pe msg karke buy karo\n3. Instant delivery milegi");
    elseif ($data == 'balance') sendMsg($chat_id, "💰 <b>Your Balance</b>\n\nCurrent Balance: <b>₹0</b>\n\nAdd karne ke liye $SUPPORT_USERNAME pe msg karo");
}

function sendMsg($chat_id, $text, $keyboard = null) {
    $params = ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML'];
    if ($keyboard) $params['reply_markup'] = json_encode(['inline_keyboard' => $keyboard]);
    apiRequest("sendMessage", $params);
}

function apiRequest($method, $params) {
    file_get_contents(API_URL.$method."?".http_build_query($params));
}
?>