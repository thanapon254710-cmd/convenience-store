-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 28, 2025 at 05:54 PM
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
-- Database: `convenience`
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
    FROM coupons
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
        UPDATE coupons
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
  `user_id` int(11) DEFAULT NULL,
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
  `user_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `role` enum('Customer','Staff','Admin') NOT NULL DEFAULT 'Customer',
  `points` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
-- Constraints for dumped tables
--

--
-- Constraints for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD CONSTRAINT `fk_orderdetails_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_orderdetails_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`coupon_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
