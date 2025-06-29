<?php
// footer.php - Reusable footer component
require_once 'config.php'; // Include the config file for BASE_URL
?>
    </main> <!-- This closes the <main> tag opened in included pages -->
    <footer style="background-color: #000000 !important;" class="text-white text-center py-3 mt-4">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Hair.Kal's Barbershop. All rights reserved.</p>
            <div class="social-icons mt-2">
                <!-- Social Media Icons with placeholder links -->
                <a href="https://www.facebook.com/your-barbershop-page" target="_blank" class="text-white mx-2" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://twitter.com/your-barbershop-page" target="_blank" class="text-white mx-2" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="https://www.instagram.com/hairkals?igsh=aTNxMTkwZHlrdW1r" target="_blank" class="text-white mx-2" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="https://www.linkedin.com/your-barbershop-page" target="_blank" class="text-white mx-2" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </footer>
    <!-- Bootstrap JS (bundle includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>

</body>
</html>