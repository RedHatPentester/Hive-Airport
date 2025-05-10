<?php
session_start();

include_once '../includes/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

// Mock products
$products = [
    ['id' => 1, 'name' => 'Lounge Access', 'price' => 50],
    ['id' => 2, 'name' => 'Fast Track Security', 'price' => 30],
    ['id' => 3, 'name' => 'Airport Meal Voucher', 'price' => 20],
    ['id' => 4, 'name' => 'Priority Boarding', 'price' => 40],
    ['id' => 5, 'name' => 'Extra Baggage Allowance', 'price' => 60],
    ['id' => 6, 'name' => 'Wi-Fi Access', 'price' => 10]
];

$cart = $_SESSION['cart'] ?? [];
$purchase_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $product_id = $_POST['product_id'];
        $product = array_filter($products, fn($p) => $p['id'] == $product_id);
        if ($product) {
            $product = array_values($product)[0];
            // Check if product already in cart, increment quantity
            $found = false;
            foreach ($cart as &$item) {
                if ($item['id'] == $product['id']) {
                    $item['quantity'] = ($item['quantity'] ?? 1) + 1;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $product['quantity'] = 1;
                $cart[] = $product;
            }
            $_SESSION['cart'] = $cart;
        }
    } elseif (isset($_POST['checkout'])) {
        $total = array_sum(array_map(fn($item) => $item['price'] * ($item['quantity'] ?? 1), $cart));
        $purchase_message = "Checkout successful! Total: $$total.";
        $cart = [];
        $_SESSION['cart'] = $cart;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Airport Store / Lounge Booking - Hive Airport</title>
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
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h1 {
            text-align: center;
            color: #444;
        }
        .purchase-message {
            text-align: center;
            font-size: 18px;
            color: green;
            margin-bottom: 20px;
        }
        .product-list {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .product-item {
            background: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 10px;
            text-align: center;
            width: 200px;
        }
        .product-item h3 {
            font-size: 18px;
            color: #0073e6;
        }
        .product-item p {
            font-size: 16px;
            color: #333;
        }
        .product-item button {
            padding: 10px 20px;
            font-size: 14px;
            background: #0073e6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .product-item button:hover {
            background: #005bb5;
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
        }
        .cart td {
            background: #f9f9f9;
        }
        .cart .checkout {
            margin-top: 20px;
            text-align: center;
        }
        .cart .checkout button {
            padding: 10px 20px;
            font-size: 16px;
            background: #0073e6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .cart .checkout button:hover {
            background: #005bb5;
        }
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background: #0073e6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .back-button:hover {
            background: #005bb5;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-button">&laquo; Back to Home</a>
        <h1>Airport Store / Lounge Booking</h1>

        <?php if ($purchase_message): ?>
            <p class="purchase-message"> <?php echo htmlspecialchars($purchase_message); ?> </p>
        <?php endif; ?>

        <div class="product-list">
            <?php foreach ($products as $product): ?>
                <div class="product-item">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p>Price: $<?php echo htmlspecialchars($product['price']); ?></p>
                    <form method="POST" action="store.php">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>" />
                        <button type="submit" name="add_to_cart">Add to Cart</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

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
                        <button type="submit" name="confirm_checkout">Proceed to Payment</button>
                    </form>
                </div>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
