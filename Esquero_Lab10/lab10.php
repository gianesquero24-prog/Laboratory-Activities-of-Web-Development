<?php
    // =========================================================
    // TODO 1: SECURE DATABASE CONNECTION (XAMPP / MySQL)
    // =========================================================
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'PizzaDB'; // Make sure this database exists
    
    $conn = mysqli_connect($host, $user, $password, $dbname);
    
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    // Set charset to prevent encoding issues
    mysqli_set_charset($conn, "utf8");

    // =========================================================
    // TODO 2: HANDLE POST REQUESTS (ALL CRUD OPERATIONS)
    // =========================================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // You can refresh the page by using header("Location: " . $_SERVER['PHP_SELF']); exit; after each operation to see changes immediately.
        
        // ---  PIZZA ADMIN ---
        if (isset($_POST['add_pizza'])) {
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $price = floatval($_POST['price']);
            $query = "INSERT INTO pizzas (name, price) VALUES ('$name', $price)";
            mysqli_query($conn, $query);
            header("Location: " . $_SERVER['PHP_SELF']); exit;
        }
        if (isset($_POST['update_pizza'])) {
            $id = intval($_POST['item_id']);
            $new_price = floatval($_POST['new_price']);
            $query = "UPDATE pizzas SET price = $new_price WHERE id = $id";
            mysqli_query($conn, $query);
            header("Location: " . $_SERVER['PHP_SELF']); exit;
        }
        if (isset($_POST['delete_pizza'])) {
            $id = intval($_POST['item_id']);
            $query = "DELETE FROM pizzas WHERE id = $id";
            mysqli_query($conn, $query);
            header("Location: " . $_SERVER['PHP_SELF']); exit;
        }

        // ---  TOPPINGS ADMIN ---
        if (isset($_POST['add_topping'])) {
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $price = floatval($_POST['price']);
            $query = "INSERT INTO toppings (name, price) VALUES ('$name', $price)";
            mysqli_query($conn, $query);
            header("Location: " . $_SERVER['PHP_SELF']); exit;
        }
        if (isset($_POST['update_topping'])) {
            $id = intval($_POST['item_id']);
            $new_price = floatval($_POST['new_price']);
            $query = "UPDATE toppings SET price = $new_price WHERE id = $id";
            mysqli_query($conn, $query);
            header("Location: " . $_SERVER['PHP_SELF']); exit;
        }
        if (isset($_POST['delete_topping'])) {
            $id = intval($_POST['item_id']);
            $query = "DELETE FROM toppings WHERE id = $id";
            mysqli_query($conn, $query);
            header("Location: " . $_SERVER['PHP_SELF']); exit;
        }

        // --- 🛒 ORDERING SYSTEM ---
        if (isset($_POST['create_order'])) {
            $customer = mysqli_real_escape_string($conn, $_POST['customer']);
            $pizza_id = intval($_POST['pizza_id']);
            $qty = intval($_POST['qty']);
            
            // 1. Fetch pizza price
            $pizza_query = "SELECT price FROM pizzas WHERE id = $pizza_id";
            $pizza_result = mysqli_query($conn, $pizza_query);
            $pizza = mysqli_fetch_assoc($pizza_result);
            $pizza_price = $pizza['price'];
            
            // 2. Calculate toppings total
            $toppings_total = 0;
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'topping_') === 0) {
                    $topping_id = intval(str_replace('topping_', '', $key));
                    $topping_query = "SELECT price FROM toppings WHERE id = $topping_id";
                    $topping_result = mysqli_query($conn, $topping_query);
                    if ($topping = mysqli_fetch_assoc($topping_result)) {
                        $toppings_total += $topping['price'];
                    }
                }
            }
            
            // 3. Calculate grand total
            $grand_total = ($pizza_price + $toppings_total) * $qty;
            
            // 4. Insert order
            $query = "INSERT INTO orders (customer, pizza_id, toppings, quantity, total, status) VALUES (
                '$customer', $pizza_id, '$toppings_total', $qty, $grand_total, 'Pending')";
            mysqli_query($conn, $query);
            header("Location: " . $_SERVER['PHP_SELF']); exit;
        }

        // --- 📋 MANAGE ORDERS ---
        if (isset($_POST['update_status'])) {
            $id = intval($_POST['order_id']);
            $query = "UPDATE orders SET status = 'Completed' WHERE id = $id";
            mysqli_query($conn, $query);
            header("Location: " . $_SERVER['PHP_SELF']); exit;
        }
        if (isset($_POST['delete_order'])) {
            $id = intval($_POST['order_id']);
            $query = "DELETE FROM orders WHERE id = $id";
            mysqli_query($conn, $query);
            header("Location: " . $_SERVER['PHP_SELF']); exit;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🍕 Pizza Master Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #FF6B6B 0%, #FFA500 100%); min-height: 100vh; padding: 40px 20px; color: #333;}
        .container { max-width: 1200px; margin: 0 auto; }
        header { text-align: center; color: white; margin-bottom: 40px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
        h1 { font-size: 3em; margin-bottom: 10px; }
        
        .grid-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;}
        .full-width { grid-column: 1 / -1; }
        @media(max-width: 800px) { .grid-layout { grid-template-columns: 1fr; } }
        
        .card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
        .card h2 { color: #FF6B6B; border-bottom: 3px solid #FFA500; padding-bottom: 10px; margin-bottom: 20px; }
        
        .form-group { display: flex; gap: 10px; margin-bottom: 20px; align-items: flex-end; }
        .form-stack { display: flex; flex-direction: column; gap: 8px; margin-bottom: 15px; }
        input[type="text"], input[type="number"] { padding: 10px; border: 2px solid #FF6B6B; border-radius: 8px; width: 100%; }
        
        .radio-group, .checkbox-group { display: flex; flex-direction: column; gap: 10px; }
        .selection-item { display: flex; align-items: center; padding: 10px; border-radius: 8px; cursor: pointer; background: #fff5f5;}
        .selection-item:hover { background-color: #ffe8e8; }
        .selection-item input { margin-right: 10px; width: 18px; height: 18px; accent-color: #FF6B6B; }
        .price { color: #FFA500; font-weight: bold; }
        
        button { padding: 10px 15px; background: #FF6B6B; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
        button:hover { background: #FFA500; }
        .btn-large { width: 100%; padding: 15px; font-size: 1.1em; }
        .btn-update { background: #4CAF50; padding: 6px 12px; font-size: 0.9em; }
        .btn-delete { background: #f44336; padding: 6px 12px; font-size: 0.9em; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ecf0f1; }
        th { background-color: #FFF5E6; color: #FF6B6B; }
        .price-input { width: 90px !important; padding: 6px !important; margin-right: 5px; border: 1px solid #ccc !important;}
        
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8em; font-weight: bold; color: white; }
        .bg-pending { background-color: #FFA500; }
        .bg-completed { background-color: #4CAF50; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🍕 Pizza Master Dashboard</h1>
            <p>Admin Menu Management & Live Ordering System</p>
        </header>

        <div class="grid-layout">
            
            <div class="card">
                <h2>⚙️ Manage Pizzas</h2>
                <form method="post" class="form-group">
                    <div style="flex: 2;"><input type="text" name="name" placeholder="New Pizza Name" required></div>
                    <div style="flex: 1;"><input type="number" name="price" step="0.01" min="0" placeholder="Price" required></div>
                    <button type="submit" name="add_pizza">Add</button>
                </form>
                <table>
                    <thead>
                        <tr><th>Name</th><th>Price</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php
                            // TODO 3: Read from 'pizzas' table using mysqli_query and mysqli_fetch_assoc
                            $result = mysqli_query($conn, "SELECT * FROM pizzas ORDER BY id DESC");
                            while ($pizza = mysqli_fetch_assoc($result)) {
                                echo "<tr>
                                    <td><strong>" . htmlspecialchars($pizza['name']) . "</strong></td>
                                    <td>
                                        <form method='post' style='display:flex;'>
                                            <input type='hidden' name='item_id' value='" . $pizza['id'] . "'>
                                            <input type='number' name='new_price' value='" . $pizza['price'] . "' step='0.01' class='price-input' required>
                                            <button type='submit' name='update_pizza' class='btn-update'>Save</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method='post' style='display:inline;'>
                                            <input type='hidden' name='item_id' value='" . $pizza['id'] . "'>
                                            <button type='submit' name='delete_pizza' class='btn-delete' onclick='return confirm(\"Delete this pizza?\")'>✖</button>
                                        </form>
                                    </td>
                                </tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h2>⚙️ Manage Toppings</h2>
                <form method="post" class="form-group">
                    <div style="flex: 2;"><input type="text" name="name" placeholder="New Topping Name" required></div>
                    <div style="flex: 1;"><input type="number" name="price" step="0.01" min="0" placeholder="Price" required></div>
                    <button type="submit" name="add_topping">Add</button>
                </form>
                <table>
                    <thead>
                        <tr><th>Name</th><th>Price</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php
                            // TODO 4: Read from 'toppings' table and generate rows dynamically
                            $result = mysqli_query($conn, "SELECT * FROM toppings ORDER BY id DESC");
                            while ($topping = mysqli_fetch_assoc($result)) {
                                echo "<tr>
                                    <td><strong>" . htmlspecialchars($topping['name']) . "</strong></td>
                                    <td>
                                        <form method='post' style='display:flex;'>
                                            <input type='hidden' name='item_id' value='" . $topping['id'] . "'>
                                            <input type='number' name='new_price' value='" . $topping['price'] . "' step='0.01' class='price-input' required>
                                            <button type='submit' name='update_topping' class='btn-update'>Save</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method='post' style='display:inline;'>
                                            <input type='hidden' name='item_id' value='" . $topping['id'] . "'>
                                            <button type='submit' name='delete_topping' class='btn-delete' onclick='return confirm(\"Delete this topping?\")'>✖</button>
                                        </form>
                                    </td>
                                </tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="max-width: 800px; margin: 0 auto 30px auto;">
            <h2>🛒 Place New Order</h2>
            <form method="post">
                <div class="form-stack">
                    <label><strong>Customer Name</strong></label>
                    <input type="text" name="customer" required>
                </div>

                <div class="grid-layout" style="gap: 20px; margin-bottom: 0;">
                    
                    <div class="form-stack">
                        <label><strong>Select Pizza</strong></label>
                        <div class="radio-group">
                            <?php 
                                // TODO 5: Fetch Pizzas from DB to generate radio buttons
                                $result = mysqli_query($conn, "SELECT * FROM pizzas ORDER BY name");
                                while ($pizza = mysqli_fetch_assoc($result)) {
                                    echo "<label class='selection-item'>
                                        <input type='radio' name='pizza_id' value='" . $pizza['id'] . "' required>
                                        " . htmlspecialchars($pizza['name']) . " <span class='price'>$" . number_format($pizza['price'], 2) . "</span>
                                    </label>";
                                }
                            ?>
                        </div>
                    </div>

                    <div class="form-stack">
                        <label><strong>Select Toppings</strong></label>
                        <div class="checkbox-group">
                            <?php 
                                // TODO 6: Fetch Toppings from DB to generate checkboxes
                                $result = mysqli_query($conn, "SELECT * FROM toppings ORDER BY name");
                                while ($topping = mysqli_fetch_assoc($result)) {
                                    echo "<label class='selection-item'>
                                        <input type='checkbox' name='topping_" . $topping['id'] . "' value='1'>
                                        " . htmlspecialchars($topping['name']) . " <span class='price'>$" . number_format($topping['price'], 2) . "</span>
                                    </label>";
                                }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="form-stack" style="margin-top: 15px;">
                    <label><strong>Quantity</strong></label>
                    <input type="number" name="qty" min="1" value="1" required>
                </div>

                <button type="submit" name="create_order" class="btn-large">🚀 Submit Order</button>
            </form>
        </div>

        <div class="card full-width">
            <h2>📋 Live Kitchen Orders</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th><th>Customer</th><th>Order Details</th><th>Total</th><th>Status</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            // TODO 7: Read from 'orders' table and display live kitchen orders
                            $result = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC");
                            while ($order = mysqli_fetch_assoc($result)) {
                                $status_class = $order['status'] == 'Pending' ? 'bg-pending' : 'bg-completed';
                                echo "<tr>
                                    <td><strong>#" . $order['id'] . "</strong></td>
                                    <td>" . htmlspecialchars($order['customer']) . "</td>
                                    <td>
                                        Pizza #" . $order['pizza_id'] . " + Toppings: $" . $order['toppings'] . " × " . $order['quantity'] . "
                                    </td>
                                    <td><strong>$" . number_format($order['total'], 2) . "</strong></td>
                                    <td><span class='badge " . $status_class . "'>" . $order['status'] . "</span></td>
                                    <td>
                                        " . ($order['status'] == 'Pending' ? "
                                            <form method='post' style='display:inline;'>
                                                <input type='hidden' name='order_id' value='" . $order['id'] . "'>
                                                <button type='submit' name='update_status' class='btn-update'>✔ Complete</button>
                                            </form>
                                        " : "") . "
                                        <form method='post' style='display:inline;'>
                                            <input type='hidden' name='order_id' value='" . $order['id'] . "'>
                                            <button type='submit' name='delete_order' class='btn-delete' onclick='return confirm(\"Delete this order?\")'>✖</button>
                                        </form>
                                    </td>
                                </tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php
    // Close connection
    mysqli_close($conn);