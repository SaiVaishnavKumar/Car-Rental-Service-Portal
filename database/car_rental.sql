-- ========================================================
-- DriveEasy – Car Rental Service Portal
-- Database: car_rental
-- Compatible with XAMPP (Apache + MySQL / MariaDB + phpMyAdmin)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `car_rental` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `car_rental`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `cars`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `cars`
-- --------------------------------------------------------
CREATE TABLE `cars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `brand` VARCHAR(50) NOT NULL,
  `model` VARCHAR(50) NOT NULL,
  `year` INT NOT NULL,
  `registration_number` VARCHAR(50) NOT NULL UNIQUE,
  `price_per_day` DECIMAL(10,2) NOT NULL,
  `fuel_type` VARCHAR(30) NOT NULL,
  `transmission` VARCHAR(30) NOT NULL,
  `seats` INT NOT NULL,
  `mileage` VARCHAR(30) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `features` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `availability` ENUM('available', 'unavailable') NOT NULL DEFAULT 'available',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_cars_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `bookings`
-- --------------------------------------------------------
CREATE TABLE `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `car_id` INT NOT NULL,
  `pickup_date` DATE NOT NULL,
  `return_date` DATE NOT NULL,
  `pickup_location` VARCHAR(100) NOT NULL,
  `return_location` VARCHAR(100) NOT NULL,
  `number_of_days` INT NOT NULL,
  `price_per_day` DECIMAL(10,2) NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `booking_status` ENUM('Pending', 'Confirmed', 'Rejected', 'Active', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `payment_status` ENUM('Pending', 'Paid', 'Failed') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_bookings_car` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `payments`
-- --------------------------------------------------------
CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('UPI', 'Credit Card', 'Debit Card', 'Cash on Pickup') NOT NULL,
  `transaction_id` VARCHAR(100) NOT NULL,
  `payment_status` ENUM('Pending', 'Paid', 'Failed') NOT NULL DEFAULT 'Paid',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_payments_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `contact_messages`
-- --------------------------------------------------------
CREATE TABLE `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('unread', 'read') NOT NULL DEFAULT 'unread',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_messages_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Dumping data for table `users`
-- (Passwords hashed with BCRYPT: admin123, customer123)
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'Administrator', 'admin@driveeasy.com', '+91 98765 43210', '$2y$12$CtZI/Q0Pb/pcslOzIIM/uOBSEQ6X/oynQDVJJyXfIbVopUSfSQXoi', 'admin', 'active', NOW()),
(2, 'Rahul Sharma', 'customer@driveeasy.com', '+91 91234 56789', '$2y$12$4AuHxgwRI0m1oLU80Bwr/OihjCQ3/npVNi7G.iuLsKX4CvsYSavFG', 'customer', 'active', NOW()),
(3, 'Priya Patel', 'priya@example.com', '+91 99887 76655', '$2y$12$4AuHxgwRI0m1oLU80Bwr/OihjCQ3/npVNi7G.iuLsKX4CvsYSavFG', 'customer', 'active', NOW());

-- --------------------------------------------------------
-- Dumping data for table `categories`
-- --------------------------------------------------------
INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'SUV', 'Spacious, high ground clearance, powerful performance for long road trips and family vacations.', NOW()),
(2, 'Sedan', 'Comfortable, stylish, and fuel-efficient cars ideal for business travel and city commuting.', NOW()),
(3, 'Hatchback', 'Compact, nimble, and highly economical cars designed for everyday city cruising.', NOW()),
(4, 'Luxury', 'Premium vehicles featuring top-tier comfort, cutting-edge technology, and distinguished prestige.', NOW()),
(5, 'Electric', 'Eco-friendly, whisper-quiet electric vehicles with zero tailpipe emissions and instant torque.', NOW()),
(6, 'Sports', 'High performance, agile handling, and aggressive styling for driving enthusiasts.', NOW());

-- --------------------------------------------------------
-- Dumping data for table `cars`
-- --------------------------------------------------------
INSERT INTO `cars` (`id`, `category_id`, `brand`, `model`, `year`, `registration_number`, `price_per_day`, `fuel_type`, `transmission`, `seats`, `mileage`, `description`, `features`, `image`, `availability`, `created_at`) VALUES
(1, 1, 'Toyota', 'Fortuner 4x4', 2024, 'DL-01-AB-1001', 4500.00, 'Diesel', 'Automatic', 7, '12 km/l', 'The undisputed king of rugged roads and highway presence. Built with durable 4x4 powertrain and luxurious 7-seater cabin.', 'Air Conditioning, Bluetooth, GPS Navigation, 4x4 Drive, Leather Seats, Reverse Camera, USB Charger, Apple CarPlay, Cruise Control', 'fortuner.jpg', 'available', NOW()),
(2, 1, 'Hyundai', 'Creta SX(O)', 2023, 'MH-02-CD-2002', 2800.00, 'Petrol', 'Automatic', 5, '17 km/l', 'One of India\'s most loved compact SUVs offering panoramic sunroof, ventilated seats, and smooth drive quality.', 'Air Conditioning, Panoramic Sunroof, Bluetooth, GPS Navigation, Ventilated Seats, Touchscreen Display, Wireless Charging', 'creta.jpg', 'available', NOW()),
(3, 1, 'Mahindra', 'Thar 4WD', 2023, 'KA-03-EF-3003', 3200.00, 'Diesel', 'Manual', 4, '13 km/l', 'Iconic off-roader designed for thrilling road trips, rough terrains, and outdoor camping adventures.', 'Air Conditioning, Convertible Hardtop, 4WD Mode, Touchscreen Infotainment, Adventure Dials, Heavy Duty Suspension', 'thar.jpg', 'available', NOW()),
(4, 2, 'Honda', 'City ZX', 2024, 'DL-04-GH-4004', 2200.00, 'Petrol', 'Automatic', 5, '18 km/l', 'The benchmark Japanese executive sedan offering peerless rear seat legroom, refined engine, and ADAS safety.', 'Air Conditioning, Sunroof, ADAS Safety Suite, Bluetooth, Leather Seats, Rear AC Vents, Push Button Start', 'city.jpg', 'available', NOW()),
(5, 2, 'Hyundai', 'Verna Turbo', 2023, 'TN-05-IJ-5005', 2500.00, 'Petrol', 'Automatic', 5, '19 km/l', 'Futuristic exterior lines paired with a turbocharged 1.5L motor and dual 10.25-inch panoramic screens.', 'Air Conditioning, Heated & Cooled Seats, Dual 10.25 screens, Bose 8-Speaker Audio, Wireless Charger, Cruise Control', 'verna.jpg', 'available', NOW()),
(6, 1, 'Kia', 'Seltos GTX+', 2024, 'HR-06-KL-6006', 2900.00, 'Diesel', 'Automatic', 5, '18 km/l', 'A feature-loaded modern SUV boasting 360-degree cameras, HUD heads up display, and sporty GT-line looks.', 'Air Conditioning, 360-degree Camera, Heads Up Display, Ambient Lighting, Bluetooth, Power Driver Seat, ESP', 'seltos.jpg', 'available', NOW()),
(7, 3, 'Tata', 'Nexon Creative', 2024, 'UP-07-MN-7007', 2000.00, 'Petrol', 'Manual', 5, '17 km/l', 'Award-winning 5-star GNCAP safety rated crossover with comfortable driving ergonomics and great ground clearance.', 'Air Conditioning, 6 Airbags Standard, Touchscreen Infotainment, Digital Cluster, Blind Spot Monitor, Harman Sound', 'nexon.jpg', 'available', NOW()),
(8, 3, 'Maruti', 'Swift ZXi', 2024, 'MH-08-OP-8008', 1500.00, 'Petrol', 'Manual', 5, '24 km/l', 'Peppy, ultra-reliable, and class-leading fuel economy. Perfect for budget-friendly road trips and daily urban errands.', 'Air Conditioning, SmartPlay Pro+, Keyless Entry, Reverse Camera, LED Projectors, USB Ports, Cruise Control', 'swift.jpg', 'available', NOW()),
(9, 1, 'Toyota', 'Innova Hycross', 2024, 'KA-09-QR-9009', 4200.00, 'Hybrid', 'Automatic', 7, '21 km/l', 'Ultimate long-distance family cruiser with ottoman captain chairs, super silent hybrid engine, and exceptional reliability.', 'Air Conditioning, Ottoman Captain Seats, Hybrid Drive, Sunroof, Multi-Zone Climate, ADAS, Premium JBL Audio', 'innova.jpg', 'available', NOW()),
(10, 1, 'MG', 'Hector Sharp Pro', 2023, 'DL-10-ST-1010', 3000.00, 'Petrol', 'Automatic', 5, '14 km/l', 'Internet inside vehicle featuring an enormous 14-inch HD portrait screen, panoramic skylight, and voice commands.', 'Air Conditioning, 14-inch Touchscreen, Infinity Sound System, Electric Tailgate, 360 Camera, Panoramic Sunroof', 'hector.jpg', 'available', NOW()),
(11, 4, 'BMW', '3 Series Gran Limousine', 2024, 'DL-11-UV-1111', 7500.00, 'Petrol', 'Automatic', 5, '15 km/l', 'Unmatched German engineering, extended rear wheelbase, and pure driving dynamics wrapped in executive luxury.', 'Air Conditioning, Curved Dual Display, Panorama Glass Roof, Vernasca Leather, Harman Kardon Surround, Ambient Lights', 'bmw-3.jpg', 'available', NOW()),
(12, 5, 'Tata', 'Nexon EV Empowered', 2024, 'MH-12-WX-1212', 2600.00, 'Electric', 'Automatic', 5, '465 km/charge', 'Clean zero-emission mobility with rapid DC fast charging, V2V vehicle-to-vehicle charging, and arcade games.', 'Air Conditioning, Fast Charging 10-80% in 56 mins, Vehicle-to-Load, JBL Audio with Cinematic Modes, Smart Digital Shifter', 'nexon-ev.jpg', 'available', NOW());

-- --------------------------------------------------------
-- Dumping data for table `bookings`
-- --------------------------------------------------------
INSERT INTO `bookings` (`id`, `user_id`, `car_id`, `pickup_date`, `return_date`, `pickup_location`, `return_location`, `number_of_days`, `price_per_day`, `total_amount`, `booking_status`, `payment_status`, `created_at`) VALUES
(1, 2, 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'Airport Terminal 3', 'Airport Terminal 3', 3, 4500.00, 13500.00, 'Confirmed', 'Paid', NOW()),
(2, 2, 4, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Downtown Central Station', 'Downtown Central Station', 3, 2200.00, 6600.00, 'Completed', 'Paid', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(3, 3, 2, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'City Center Mall', 'City Center Mall', 3, 2800.00, 8400.00, 'Pending', 'Pending', NOW());

-- --------------------------------------------------------
-- Dumping data for table `payments`
-- --------------------------------------------------------
INSERT INTO `payments` (`id`, `booking_id`, `user_id`, `amount`, `payment_method`, `transaction_id`, `payment_status`, `created_at`) VALUES
(1, 1, 2, 13500.00, 'UPI', 'TXN-UPI-98471203', 'Paid', NOW()),
(2, 2, 2, 6600.00, 'Credit Card', 'TXN-CC-58219402', 'Paid', DATE_SUB(NOW(), INTERVAL 6 DAY));

-- --------------------------------------------------------
-- Dumping data for table `contact_messages`
-- --------------------------------------------------------
INSERT INTO `contact_messages` (`id`, `user_id`, `name`, `email`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 2, 'Rahul Sharma', 'customer@driveeasy.com', 'Inquiry regarding outstation interstate permits', 'Hello DriveEasy team, do your cars come with all-India commercial tourist permits for cross-state travel from Delhi to Himachal Pradesh?', 'read', NOW()),
(2, NULL, 'Ananya Sen', 'ananya.sen@example.com', 'Corporate monthly car rental inquiry', 'Hi, we are looking to book 3 executive sedans on a monthly contract for our visiting delegates next month. Please share corporate price plans.', 'unread', NOW());
