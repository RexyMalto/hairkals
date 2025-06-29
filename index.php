<?php
// index.php
require_once 'includes/db_connect.php';
require_once 'includes/header.php';
?>

<marquee behavior="scroll" direction="left" scrollamount="6">
    💈 Welcome to Hair.Kal's Barbershop — Where Confidence Gets a Cut! ✂️
</marquee>

<!-- Hero Slider Section -->
<div class="container-fluid p-0 mb-5 hero-slider">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="assets/img/barbers/ps1.png" class="d-block w-100" alt="Barbershop Interior" style="height: 70vh; object-fit: cover;">
                <div class="carousel-caption text-start">
                    <div class="caption-background">
                        <h1>Hair.Kal's Barbershop</h1>
                        <p>Where style meets precision</p>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets/img/barbers/ps2.png" class="d-block w-100" alt="Barber at Work" style="height: 70vh; object-fit: cover;">
                <div class="carousel-caption text-center">
                    <div class="caption-background">
                        <h1>Expert Barbers</h1>
                        <p>Master craftsmen of your perfect look</p>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets/img/barbers/ps3.png" class="d-block w-100" alt="Happy Client" style="height: 70vh; object-fit: cover;">
                <div class="carousel-caption text-end">
                    <div class="caption-background">
                        <h1>Your Best Look</h1>
                        <p>Guaranteed satisfaction every visit</p>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>

<!-- Welcome Section -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <h2 class="display-6 fw-bold mb-4">Welcome to Hair.Kal's Barbershop</h2>
            <div class="h-100 p-4 bg-white border rounded-4 shadow-sm mx-auto" style="max-width: 6000px;">
                <p class="lead mb-4">
                    What brings you here? Probably you need a haircut. You're in the right place — we don't just cut hair, we craft confidence. Our skilled barbers know exactly how to match your style with precision and care.
                </p>
                <p class="mb-5">
                    Whether it's a clean fade, a sharp beard line, or a bold new look, we've got your back. Step in, sit down, and let us bring out your best self.
                </p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo BASE_URL; ?>user/dashboard.php" class="btn btn-primary btn-lg px-4">Book Now!</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>login.php?showLogin=1" class="btn btn-primary btn-lg px-4">Book Now!</a>
                    <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<!-- Services and Barbers Section -->
<div class="container py-5">
    <div class="row g-4 align-items-stretch">
        <!-- Services Box -->
        <div class="col-md-6">
            <div class="h-100 p-4 bg-white border rounded-4 shadow-sm">
                <h2 class="mb-3">Our Services</h2>
                <p class="mb-4">From classic cuts to modern styles, beard trims, and hot towel shaves, our experienced barbers have you covered.</p>

                <div class="row text-center mb-4">
                    <div class="col-4">
                        <img src="assets/img/barbers/service1.png" alt="Haircut" class="img-fluid rounded mb-2" style="height: 100px; object-fit: cover;">
                        <p class="mb-0">Haircut</p>
                    </div>
                    <div class="col-4">
                        <img src="assets/img/barbers/service2.png" alt="Shave" class="img-fluid rounded mb-2" style="height: 100px; object-fit: cover;">
                        <p class="mb-0">Shave</p>
                    </div>
                    <div class="col-4">
                        <img src="assets/img/barbers/service3.png" alt="Hair Colouring" class="img-fluid rounded mb-2" style="height: 100px; object-fit: cover;">
                        <p class="mb-0">Hair Colouring</p>
                    </div>
                </div>

                <a href="<?php echo BASE_URL; ?>services.php" class="btn btn-outline-secondary">View All Services</a>
            </div>
        </div>

        <!-- Barbers Box -->
        <div class="col-md-6">
            <div class="h-100 p-4 bg-white border rounded-4 shadow-sm">
                <h2 class="mb-3">Meet Our Barbers</h2>
                <p class="mb-4">Our team of skilled professionals is dedicated to providing you with the best grooming experience. Choose your favorite!</p>

                <div class="row text-center mb-4">
                    <div class="col-4">
                        <img src="assets/img/barbers/sam.png" alt="Sam Rascal" class="img-fluid rounded-circle mb-2" style="width: 100px; height: 100px; object-fit: cover;">
                        <p class="mb-0">Sam Rascal</p>
                    </div>
                    <div class="col-4">
                        <img src="assets/img/barbers/alsanawi.png" alt="Ahmed Alsanawi" class="img-fluid rounded-circle mb-2" style="width: 100px; height: 100px; object-fit: cover;">
                        <p class="mb-0">Ahmed Alsanawi</p>
                    </div>
                    <div class="col-4">
                        <img src="assets/img/barbers/vic.png" alt="Vic Blends" class="img-fluid rounded-circle mb-2" style="width: 100px; height: 100px; object-fit: cover;">
                        <p class="mb-0">Vic Blends</p>
                    </div>
                </div>

                <a href="<?php echo BASE_URL; ?>about.php" class="btn btn-outline-secondary">About Our Team</a>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>