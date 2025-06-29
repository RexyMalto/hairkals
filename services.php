<?php
// services.php
require_once 'includes/db_connect.php';
require_once 'includes/header.php'; // header.php includes config.php and starts session

// Fetch all services from the database
$services = [];
$result = $conn->query("SELECT name, description, price FROM services ORDER BY name");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}
?>

<div class="card mb-4">
    <div class="card-header text-white" style="background-color: #000000;">
        <h2>Our Services</h2>
    </div>
    <div class="card-body">
        <?php if (empty($services)): ?>
            <p class="text-center">No services are currently listed. Please check back later!</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($services as $service): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($service['description']); ?></p>
                                <p class="card-text"><strong>Price: RM<?php echo htmlspecialchars(number_format($service['price'], 2)); ?></strong></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS (Required for navbar, modals) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Your universal JS -->
<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
