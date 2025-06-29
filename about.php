<?php
// about.php
require_once 'includes/header.php'; // header.php includes config.php and starts session
?>

<div class="card mb-4">
    <div class="card-header text-white" style="background-color: #000000;">
        <h2>About Hair.Kal's Barbershop</h2>
    </div>
    <div class="card-body">
        <p>Welcome to Hair.Kal's Barbershop, your premier destination for exceptional grooming services. Established with a passion for precision and style, we are dedicated to providing a comfortable and modern barbershop experience.</p>
        <p>Our team of highly skilled and experienced barbers are masters of their craft, specializing in a wide range of services from classic haircuts and modern fades to meticulous beard trims and luxurious hot towel shaves. We believe that a great haircut is more than just a service – it's an art form that enhances your confidence and reflects your personal style.</p>
        <p>At Hair.Kal's Barbershop, we pride ourselves on creating a welcoming atmosphere where you can relax, unwind, and leave looking and feeling your best. We use only the finest products and maintain the highest standards of hygiene and professionalism.</p>
        <p>Book an appointment with us today and discover the Hair.Kal Barbershop difference!</p>
        <p>Opening Hours : 10.00 AM - 10.00 PM</p>
    </div>
</div>

<div class="container mb-5">
    <h3 class="text-center mb-4">Meet Our Barbers</h3>
    <div class="row g-4 justify-content-center">
        <!-- Barber 1 -->
        <div class="col-md-4">
            <div class="card h-100 shadow">
                <img src="assets/img/barbers/sam1.png" class="card-img-top" alt="Barber 1">
                <div class="card-body text-center">
                    <h5 class="card-title">Sam Rascals</h5>
                    <p class="card-text">Sam Rascals (real name Samuel Bentham) is a UK barber. Born in 1992, he’s known for viral haircuts, bold style, and teaching barbers how to grow online.</p>
                </div>
            </div>
        </div>

        <!-- Barber 2 -->
        <div class="col-md-4">
            <div class="card h-100 shadow">
                <img src="assets/img/barbers/alsanawi1.png" class="card-img-top" alt="Barber 2">
                <div class="card-body text-center">
                    <h5 class="card-title">Ahmed Alsanawi</h5>
                    <p class="card-text">Ahmed Alsanawi is a UK-based celebrity barber. Born in 1991, he’s known for cutting top footballers’ hair, his sharp fades, and building a strong brand through style and social media.</p>
                </div>
            </div>
        </div>

        <!-- Barber 3 -->
        <div class="col-md-4">
            <div class="card h-100 shadow">
                <img src="assets/img/barbers/vic1.png" class="card-img-top" alt="Barber 3">
                <div class="card-body text-center">
                    <h5 class="card-title">Vic Blends</h5>
                    <p class="card-text">Vic Blends (real name Victor Fontanez) is an American barber and influencer known for giving free street haircuts with deep convos. Born in 2000, working with celebs and giving back through barber education.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
