<?php
// includes/config.php

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Default XAMPP/WAMP username
define('DB_PASSWORD', '');     // Default XAMPP/WAMP password (empty for XAMPP/WAMP)
define('DB_NAME', 'barber_booking');

// Business Hours Configuration
// Time should be in HH:MM format (24-hour)
define('SHOP_OPEN_TIME', '10:00'); // 10:00 AM
define('SHOP_CLOSE_TIME', '23:00'); // 11:00 PM (or 18:00 for 6 PM)
define('BOOKING_INTERVAL_MINUTES', 60); // E.g., 30 minutes per slot
define('DAYS_AHEAD_BOOKING_LIMIT', 90); // Max days in advance a user can book (e.g., 90 days)

// Base URL for redirects and asset linking
// IMPORTANT: Adjust this if your project is not directly under http://localhost/barber_booking/
// Example: if your project is at http://localhost/my_projects/barber_booking/
// then define('BASE_URL', '/my_projects/barber_booking/');
define('BASE_URL', '/barber_booking/');

// Phone Number Regex (Example: allows 7-20 digits, spaces, hyphens, parentheses)
// Adjust this regex based on your desired phone number format
define('PHONE_REGEX', '/^[0-9\s\-\(\)]{7,20}$/');

?>
