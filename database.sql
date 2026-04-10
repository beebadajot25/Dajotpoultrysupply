-- Database: dajot_poultry

CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL,
    `email` varchar(100) NOT NULL,
    `role` enum('admin','farmer') DEFAULT 'farmer',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);

-- Admin User (Password: admin123)
-- Hash needed in production, plain for demo setup if not using password_hash() immediately
INSERT INTO `users` (`username`, `password`, `email`, `role`) VALUES
('admin', 'admin123', 'admin@dajot.com', 'admin');

CREATE TABLE IF NOT EXISTS `products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `description` text,
    `price` decimal(10,2) NOT NULL,
    `price_unit` varchar(20) DEFAULT 'unit', -- e.g., per bird, per crate
    `image` varchar(255),
    `category` varchar(50),
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);

INSERT INTO `products` (`name`, `description`, `price`, `price_unit`, `category`, `image`) VALUES
('Live Broilers', 'Healthy, well-fed broilers suitable for meat.', 3500.00, 'bird', 'Broilers', 'assets/images/products/broilers.png'),
('Crates of Eggs', 'Farm fresh crates of eggs.', 2800.00, 'crate', 'Eggs', 'assets/images/products/eggs.png'),
('Processed Chicken', 'Cleaned, dressed, and frozen chicken.', 4000.00, 'kg', 'Processed', 'assets/images/products/processed.png'),
('Poultry Feed', 'Quality feed for Starter, Grower, Finisher.', 9500.00, 'bag', 'Feed', 'assets/images/products/feed.png');

CREATE TABLE IF NOT EXISTS `listings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `farmer_name` varchar(100) NOT NULL,
    `phone` varchar(20) NOT NULL,
    `whatsapp` varchar(20),
    `location` varchar(100) NOT NULL,
    `product_name` varchar(100) NOT NULL,
    `price` decimal(10,2) NOT NULL,
    `price_unit` varchar(20) DEFAULT 'unit',
    `status` enum('pending','approved','rejected') DEFAULT 'pending',
    `category` varchar(50),
    `image` varchar(255),
    `type` enum('marketplace','bulk') DEFAULT 'marketplace',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);

INSERT INTO `listings` (`farmer_name`, `phone`, `whatsapp`, `location`, `product_name`, `price`, `price_unit`, `status`, `category`, `image`, `type`) VALUES
('Ibrahim Farms', '08012345678', '08012345678', 'Ibadan, Oyo', '500 Broilers Ready for Market', 3200.00, 'bird', 'approved', 'Broilers', 'assets/images/products/broilers.png', 'marketplace'),
('AgroConnect', '08098765432', '08098765432', 'Abeokuta, Ogun', 'Point of Lay Birds', 4500.00, 'bird', 'approved', 'Layers', 'assets/images/products/layers.png', 'marketplace'),
('Mama Chioma', '07055555555', '07055555555', 'Lagos', 'Crates of Eggs (Large)', 2700.00, 'crate', 'approved', 'Eggs', 'assets/images/products/eggs.png', 'marketplace');
