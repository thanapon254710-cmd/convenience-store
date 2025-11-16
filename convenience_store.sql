-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 16, 2025 at 08:27 PM
-- Server version: 5.7.24
-- PHP Version: 8.2.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `convenience_store`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `process_payment` (IN `p_order_id` INT, IN `p_amount` DECIMAL(8,2), IN `p_method` VARCHAR(30))   BEGIN
    INSERT INTO payments (order_id, payment_date, amount_paid, method)
    VALUES (p_order_id, NOW(), p_amount, p_method);

    UPDATE orders
    SET status = 'Paid'
    WHERE order_id = p_order_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `redeem_coupon` (IN `p_coupon_code` VARCHAR(30), IN `p_order_id` INT)   BEGIN
    DECLARE v_discount DECIMAL(5,2);
    DECLARE v_min_purchase DECIMAL(8,2);
    DECLARE v_order_total DECIMAL(8,2);
    DECLARE v_expiry DATE;
    DECLARE v_status VARCHAR(20);
    DECLARE v_msg VARCHAR(50);

    -- Step 1: Fetch coupon details
    SELECT discount_percent, min_purchase, expiry_date, status
    INTO v_discount, v_min_purchase, v_expiry, v_status
    FROM coupon
    WHERE coupon_code = p_coupon_code
    Limit 1;

    -- Step 2: Fetch order total
    SELECT total_amount INTO v_order_total
    FROM orders
    WHERE order_id = p_order_id
    Limit 1;

    -- Step 3: Validate coupon
    IF v_discount IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid coupon code.';
    ELSEIF v_status <> 'Active' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Coupon is not active or already used.';
    ELSEIF v_expiry < CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Coupon has expired.';
    ELSEIF v_order_total < v_min_purchase THEN
        SET v_msg = CONCAT('Minimum purchase of ', v_min_purchase, ' required.');
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = v_message;
    ELSE
        -- Step 4: Apply discount
        UPDATE orders
        SET total_amount = floor(total_amount - (v_order_total * v_discount / 100))
        WHERE order_id = p_order_id;

        -- Step 5: Mark coupon as used
        UPDATE coupon
        SET status = 'Redeemed'
        WHERE coupon_code = p_coupon_code;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `coupon_id` int(11) NOT NULL,
  `coupon_code` varchar(30) DEFAULT NULL,
  `discount_percent` decimal(5,2) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `min_purchase` decimal(8,2) DEFAULT NULL,
  `status` enum('Active','Inactive','Expired','Redeemed') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `orderdetails`
--

CREATE TABLE `orderdetails` (
  `detail_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `subtotal` decimal(8,2) DEFAULT NULL,
  `discount_applied` decimal(6,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Triggers `orderdetails`
--
DELIMITER $$
CREATE TRIGGER `update_stock_after_order` BEFORE INSERT ON `orderdetails` FOR EACH ROW BEGIN
    DECLARE current_stock INT;

    SELECT stock_qty INTO current_stock
    FROM products
    WHERE product_id = NEW.product_id
    FOR UPDATE;

    IF current_stock < NEW.quantity THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Insufficient stock for this product.';
    ELSE
        UPDATE products
        SET stock_qty = stock_qty - NEW.quantity
        WHERE product_id = NEW.product_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `total_amount` decimal(8,2) DEFAULT NULL,
  `payment_type` varchar(20) DEFAULT NULL,
  `status` enum('Pending','Processing','Packed','Delivered','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `coupon_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `amount_paid` decimal(8,2) DEFAULT NULL,
  `method` enum('Cash','Credit Card','QR Payment') NOT NULL DEFAULT 'Cash',
  `transaction_code` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Triggers `payments`
--
DELIMITER $$
CREATE TRIGGER `add_reward_points_after_order` AFTER INSERT ON `payments` FOR EACH ROW BEGIN
    DECLARE customer_id INT;

    SELECT user_id INTO customer_id
    FROM orders
    WHERE order_id = NEW.order_id;

    UPDATE users
    SET points = FLOOR(points + (NEW.amount_paid / 10))
    WHERE user_id = customer_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `category` enum('Beverage','Snack','Instant Food','Dairy Product','Frozen Food','Personal Care','Household Item','Stationery','Pet Supply','Other') NOT NULL DEFAULT 'Other',
  `price` decimal(6,2) DEFAULT NULL,
  `stock_qty` int(11) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('Active','Inactive','Out of Stock') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category`, `price`, `stock_qty`, `expiry_date`, `status`) VALUES
(1, 'Cola Can 325ml', 'Beverage', '18.00', 120, '2025-12-10', 'Active'),
(2, 'Orange Juice 250ml', 'Beverage', '25.00', 80, '2025-11-05', 'Active'),
(3, 'Mineral Water 500ml', 'Beverage', '12.00', 150, '2026-01-15', 'Active'),
(4, 'Iced Tea Lemon 300ml', 'Beverage', '22.00', 90, '2025-10-20', 'Active'),
(5, 'Potato Chips Classic', 'Snack', '35.00', 60, '2025-09-01', 'Active'),
(6, 'Chocolate Wafer Bar', 'Snack', '20.00', 100, '2025-08-15', 'Active'),
(7, 'Seaweed Snack Original', 'Snack', '15.00', 70, '2025-07-30', 'Active'),
(8, 'Mixed Nuts 50g', 'Snack', '30.00', 50, '2025-10-05', 'Active'),
(9, 'Instant Noodles Chicken', 'Instant Food', '15.00', 200, '2026-03-10', 'Active'),
(10, 'Microwave Fried Rice', 'Instant Food', '55.00', 50, '2025-12-20', 'Active'),
(11, 'Canned Tuna 185g', 'Instant Food', '45.00', 40, '2027-04-01', 'Active'),
(12, 'Canned Soup Creamy Corn', 'Instant Food', '38.00', 30, '2026-02-14', 'Active'),
(13, 'Fresh Milk 1L', 'Dairy Product', '48.00', 60, '2025-06-10', 'Active'),
(14, 'Yogurt Strawberry Cup', 'Dairy Product', '25.00', 80, '2025-05-30', 'Active'),
(15, 'Cheddar Cheese Slice', 'Dairy Product', '75.00', 40, '2025-09-12', 'Active'),
(16, 'Butter Salted 200g', 'Dairy Product', '90.00', 25, '2025-11-18', 'Active'),
(17, 'Frozen Dumplings 12pcs', 'Frozen Food', '85.00', 35, '2026-06-01', 'Active'),
(18, 'Chicken Nuggets 500g', 'Frozen Food', '120.00', 20, '2026-05-12', 'Active'),
(19, 'Frozen Sausage 300g', 'Frozen Food', '70.00', 40, '2026-03-22', 'Active'),
(20, 'Fish Fillet Frozen 400g', 'Frozen Food', '110.00', 25, '2026-04-08', 'Active'),
(21, 'Shampoo 200ml', 'Personal Care', '65.00', 50, NULL, 'Active'),
(22, 'Body Wash 250ml', 'Personal Care', '55.00', 40, NULL, 'Active'),
(23, 'Toothpaste 160g', 'Personal Care', '45.00', 70, NULL, 'Active'),
(24, 'Deodorant Roll-On', 'Personal Care', '60.00', 30, NULL, 'Active'),
(25, 'Dishwashing Liquid 500ml', 'Household Item', '38.00', 40, NULL, 'Active'),
(26, 'Laundry Detergent 1kg', 'Household Item', '98.00', 30, NULL, 'Active'),
(27, 'Trash Bags Medium (20pcs)', 'Household Item', '45.00', 55, NULL, 'Active'),
(28, 'Paper Towels 2 Rolls', 'Household Item', '55.00', 45, NULL, 'Active'),
(29, 'Blue Ballpoint Pen', 'Stationery', '10.00', 200, NULL, 'Active'),
(30, 'A4 Notebook 80 pages', 'Stationery', '25.00', 100, NULL, 'Active'),
(31, 'Highlighter Set (3pcs)', 'Stationery', '40.00', 50, NULL, 'Active'),
(32, 'Glue Stick 15g', 'Stationery', '15.00', 80, NULL, 'Active'),
(33, 'Dog Food 1kg', 'Pet Supply', '120.00', 20, '2026-08-01', 'Active'),
(34, 'Cat Food 500g', 'Pet Supply', '75.00', 25, '2026-05-10', 'Active'),
(35, 'Pet Shampoo 250ml', 'Pet Supply', '60.00', 15, NULL, 'Active'),
(36, 'Cat Litter 5kg', 'Pet Supply', '90.00', 18, NULL, 'Active'),
(37, 'Umbrella Foldable', 'Other', '120.00', 12, NULL, 'Active'),
(38, 'Face Mask Pack (10pcs)', 'Other', '25.00', 200, NULL, 'Active'),
(39, 'Lighter Standard', 'Other', '10.00', 150, NULL, 'Active'),
(40, 'Reusable Tote Bag', 'Other', '30.00', 60, NULL, 'Active');

--
-- Triggers `products`
--
DELIMITER $$
CREATE TRIGGER `prevent_product_delete` BEFORE DELETE ON `products` FOR EACH ROW BEGIN
    IF EXISTS (SELECT 1 FROM orderdetails WHERE product_id = OLD.product_id) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Cannot delete a product that is linked to existing orders.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varbinary(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `role` enum('Customer','Staff','Admin') NOT NULL DEFAULT 'Customer',
  `points` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `phone_number`, `role`, `points`) VALUES
(1, 'Admin', 0x2cbe29039f1c00476c7c27903763fea1, '', '', 'Admin', 0),
(2, 'Staff', 0xf3709a0d9f27e4ec36cb8b7d5557065a, '', '', 'Staff', 0),
(3, 'Staff2', 0xf3709a0d9f27e4ec36cb8b7d5557065a, '', '', 'Staff', 0),
(4, 'Alice', 0xcef9b1fcff8bdcee130714ebe591fdf6, '', '', 'Customer', 0),
(5, 'test', 0xf220c96a890ebb043d3dc942e8fc73ef, '', '', 'Customer', 0),
(6, 'Bob', 0x0490cc6f1323d377a508e93e2b810976, '', '', 'Customer', 0);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `encrypt_password_after_insert` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
    SET NEW.password = AES_ENCRYPT(NEW.password, SHA1('password'));
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`coupon_id`);

--
-- Indexes for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `fk_orderdetails_order` (`order_id`),
  ADD KEY `fk_orderdetails_product` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_orders_user` (`user_id`),
  ADD KEY `fk_orders_coupon` (`coupon_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fk_payments_order` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orderdetails`
--
ALTER TABLE `orderdetails`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD CONSTRAINT `fk_orderdetails_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orderdetails_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`coupon_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
