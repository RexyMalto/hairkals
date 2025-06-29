<?php
// includes/header.php

require_once 'config.php'; // Include the new config file first

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hair.Kal's Barbershop - <?php echo ucfirst(str_replace('_', ' ', basename($_SERVER['PHP_SELF'], '.php'))); ?></title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>assets/img/barbers/logo9.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
	<!-- ✅ Bootstrap Icons CSS (add this line) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Pass BASE_URL to JavaScript for AJAX calls -->
    <script>
        const BASE_URL_JS = '<?php echo BASE_URL; ?>';
    </script>
    <style>
        /* Compact navbar styling */
        .navbar {
        height: auto; /* Let it grow naturally */
        min-height: 150px; /* Optional: ensures space */
        padding-top: 10px;
        padding-bottom: 10px;
        }

        .logo-img {
        height: auto;
        max-height: 200px; /* Increase this for larger logo */
        object-fit: contain;
        transition: all 0.3s ease;
        }

        .logo-img:hover {
            transform: scale(1.05);
        }
        .navbar-nav .nav-link {
            padding: 0.4rem 0.8rem;
            font-size: 1.2rem;
            font-weight: 500;
            position: relative;
        }
        .navbar-nav .nav-link:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #fff;
            transition: width 0.3s;
        }
        .navbar-nav .nav-link:hover:after {
            width: 100%;
        }
        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-top: 0.3rem;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-black">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>index.php">
            <img src="<?php echo BASE_URL; ?>assets/img/barbers/logo7.png" alt="Hair.Kal Barbershop Logo" class="logo-img">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Collapsible content -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-start align-items-lg-center flex-column flex-lg-row text-start text-lg-center">
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>about.php">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>services.php">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>contact.php">Contact Us</a></li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <?php if ($_SESSION['role'] === 'user'): ?>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>user/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>user/profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <?php elseif ($_SESSION['role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/dashboard.php"><i class="fas fa-cog me-2"></i>Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>login.php?showLogin=1" id="loginNavTrigger">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm ms-lg-2 mt-2 mt-lg-0" href="<?php echo BASE_URL; ?>register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-3">