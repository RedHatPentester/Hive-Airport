<?php
session_start();

include_once '../includes/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];
$cart = $_SESSION['cart'] ?? [];
$checkout_message = "";

$valid_cards = [
    ['number' => '4111111111111111', 'expiry' => '12/25', 'cvv' => '123'],
    ['number' => '5500000000000004', 'expiry' => '11/24', 'cvv' => '456'],
    ['number' => '340000000000009',  'expiry' => '10/23', 'cvv' => '789'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_checkout'])) {
    // Vulnerable: No validation or authentication on user_id
    $user_id = $_POST['user_id'] ?? null;

    $card_number = $_POST['card_number'] ?? '';
    $card_expiry = $_POST['card_expiry'] ?? '';
    $card_cvv = $_POST['card_cvv'] ?? '';

    $card_valid = false;
    foreach ($valid_cards as $card) {
        // Vulnerable: weak comparison, case insensitive expiry check
        if ($card['number'] === $card_number &&
            strcasecmp($card['expiry'], $card_expiry) === 0 &&
            $card['cvv'] === $card_cvv) {
            $card_valid = true;
            break;
        }
    }

    if (!empty($cart) && $user_id && $card_valid) {
        $total = 0;
        foreach ($cart as $item) {
            // Vulnerable: trusting client-side price in session cart
            $total += $item['price'] * ($item['quantity'] ?? 1);
        }

        // Vulnerable: No transaction or order validation
        $order_id = rand(1000, 9999); // Random order ID for demonstration
        $checkout_message = "Checkout successful! Your order ID is $order_id. Total: $$total.";

        // Vulnerable: No database insertion, no order tracking
        // Clear the cart after checkout
        $cart = [];
        $_SESSION['cart'] = $cart;
    } else {
        $checkout_message = "Your cart is empty, user ID missing, or invalid card details.";
    }
}

// Fetch user id from username (vulnerable to IDOR if user_id is manipulated)
$stmt = $conn->prepare("SELECT id FROM customers WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout - Hive Airport</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: url('../assets/image1.jpeg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h1 {
            text-align: center;
            color: #444;
            font-size: 28px;
        }
        .checkout-message {
            text-align: center;
            font-size: 18px;
            color: green;
            margin-bottom: 20px;
        }
        .cart {
            margin-top: 30px;
        }
        .cart table {
            width: 100%;
            border-collapse: collapse;
        }
        .cart th, .cart td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        .cart th {
            background: #0073e6;
            color: white;
            font-size: 16px;
        }
        .cart td {
            background: #f9f9f9;
            font-size: 14px;
        }
        .checkout {
            margin-top: 20px;
            text-align: center;
        }
        .checkout button {
            padding: 12px 25px;
            font-size: 16px;
            background: #0073e6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .checkout button:hover {
            background: #005bb5;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Checkout</h1>

        <?php if ($checkout_message): ?>
            <p class="checkout-message" style="color: <?php echo strpos($checkout_message, 'successful') !== false ? 'green' : 'red'; ?>;">
                <?php echo htmlspecialchars($checkout_message); ?>
            </p>
        <?php endif; ?>

        <a href="store.php" style="display: inline-block; margin-bottom: 20px; color: #0073e6; text-decoration: underline;">&laquo; Back to Store</a>

        <div class="cart">
            <h2>Your Cart</h2>
            <?php if (!empty($cart)): ?>
                <table>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                    </tr>
                    <?php foreach ($cart as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>$<?php echo htmlspecialchars($item['price']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity'] ?? 1); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="checkout">
                    <form method="POST" action="checkout.php">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <label>Card Number:<br />
                            <input type="text" name="card_number" required>
                        </label><br /><br />
                        <label>Expiry Date (MM/YY):<br />
                            <input type="text" name="card_expiry" required>
                        </label><br /><br />
                        <label>CVV:<br />
                            <input type="text" name="card_cvv" required>
                        </label><br /><br />
                        <button type="submit" name="confirm_checkout">Confirm Checkout</button>
                    </form>
                </div>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
