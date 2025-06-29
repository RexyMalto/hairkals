<?php
// admin/manage_services.php
require_once '../includes/db_connect.php';
require_once '../includes/header.php'; // header.php includes config.php and starts session
require_once '../auth/auth_check.php';

check_admin(); // Ensure only logged-in users with 'admin' role can access this page

$message = '';
$message_type = '';

// Handle Add/Edit Service
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_service'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price_input = $_POST['price'];

        // Server-side validation for name and price
        if (empty($name)) {
            $message = "Service name is required.";
            $message_type = "danger";
        } elseif (!is_numeric($price_input) || floatval($price_input) <= 0) {
            $message = "Price must be a positive number.";
            $message_type = "danger";
        } else {
            $price = floatval($price_input);
            // Original INSERT query without duration_minutes
            $stmt = $conn->prepare("INSERT INTO services (name, description, price) VALUES (?, ?, ?)");
            $stmt->bind_param("ssd", $name, $description, $price);
            if ($stmt->execute()) {
                $message = "Service added successfully!";
                $message_type = "success";
            } else {
                $message = "Error adding service: " . $stmt->error;
                $message_type = "danger";
            }
            $stmt->close();
        }
    } elseif (isset($_POST['edit_service'])) {
        $id = intval($_POST['service_id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price_input = $_POST['price'];

        // Server-side validation for name and price
        if (empty($name)) {
            $message = "Service name is required.";
            $message_type = "danger";
        } elseif (!is_numeric($price_input) || floatval($price_input) <= 0) {
            $message = "Price must be a positive number.";
            $message_type = "danger";
        } else {
            $price = floatval($price_input);
            // Original UPDATE query without duration_minutes
            $stmt = $conn->prepare("UPDATE services SET name = ?, description = ?, price = ? WHERE id = ?");
            $stmt->bind_param("ssdi", $name, $description, $price, $id);
            if ($stmt->execute()) {
                $message = "Service updated successfully!";
                $message_type = "success";
            } else {
                $message = "Error updating service: " . $stmt->error;
                $message_type = "danger";
            }
            $stmt->close();
        }
    }
}

// Handle Delete Service via GET request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Service deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting service: " . $stmt->error;
        $message_type = "danger";
    }
    $stmt->close();
    header("Location: " . BASE_URL . "admin/manage_services.php?message=" . urlencode($message) . "&type=" . urlencode($message_type));
    exit();
}

// Display messages from redirects
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type']);
}

// Fetch all services for displaying the list
$services = [];
// Original SELECT query without duration_minutes
$result = $conn->query("SELECT id, name, description, price FROM services ORDER BY name");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

?>

<h1 class="mb-4 d-flex justify-content-between align-items-center">
    Manage Services
    <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
</h1>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Add/Edit Service Form -->
    <div class="col-md-5 mb-4">
        <div class="card">
            <div class="card-header text-white" style="background-color:rgb(103, 61, 172);">
                <h5><?php echo (isset($_GET['action']) && $_GET['action'] == 'edit') ? 'Edit Service' : 'Add New Service'; ?></h5>
            </div>
            <div class="card-body">
                <?php
                // Pre-populate form if editing
                $current_service = ['id' => '', 'name' => '', 'description' => '', 'price' => '']; // Original current_service without duration
                if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
                    $edit_id = intval($_GET['id']);
                    // Original SELECT query without duration_minutes
                    $stmt_edit = $conn->prepare("SELECT id, name, description, price FROM services WHERE id = ?");
                    $stmt_edit->bind_param("i", $edit_id);
                    $stmt_edit->execute();
                    $result_edit = $stmt_edit->get_result();
                    if ($result_edit->num_rows == 1) {
                        $current_service = $result_edit->fetch_assoc();
                    } else {
                        header("Location: " . BASE_URL . "admin/manage_services.php?message=" . urlencode("Service not found for editing.") . "&type=danger");
                        exit();
                    }
                    $stmt_edit->close();
                }
                ?>
                <form action="<?php echo BASE_URL; ?>admin/manage_services.php" method="POST">
                    <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($current_service['id']); ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Service Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($current_service['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($current_service['description']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price (RM)</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0.01" value="<?php echo htmlspecialchars($current_service['price']); ?>" required>
                    </div>
                    <?php if ((isset($_GET['action']) && $_GET['action'] == 'edit')): ?>
                        <button type="submit" name="edit_service" class="btn btn-primary">Update Service</button>
                        <a href="<?php echo BASE_URL; ?>admin/manage_services.php" class="btn btn-secondary">Cancel Edit</a>
                    <?php else: ?>
                        <button type="submit" name="add_service" class="btn btn-success">Add Service</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Services List -->
    <div class="col-md-7 mb-4">
        <div class="card">
            <div class="card-header text-white" style="background-color:rgb(46, 7, 110);">
                <h5>All Services</h5>
            </div>
            <div class="card-body">
                <?php if (empty($services)): ?>
                    <p class="text-center">No services found. Add a new service using the form.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($services as $service): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($service['id']); ?></td>
                                        <td><?php echo htmlspecialchars($service['name']); ?></td>
                                        <td>RM<?php echo htmlspecialchars(number_format($service['price'], 2)); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>admin/manage_services.php?action=edit&id=<?php echo htmlspecialchars($service['id']); ?>" class="btn btn-sm btn-primary me-1">Edit</a>
                                            <a href="<?php echo BASE_URL; ?>admin/manage_services.php?action=delete&id=<?php echo htmlspecialchars($service['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this service? This will also delete any associated bookings due to database foreign key cascade rules.');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once '../includes/footer.php';
?>
