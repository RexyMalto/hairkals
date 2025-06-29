<?php
// admin/manage_barbers.php
require_once '../includes/db_connect.php';
require_once '../includes/header.php'; // header.php includes config.php and starts session
require_once '../auth/auth_check.php';

check_admin(); // Ensure only logged-in users with 'admin' role can access this page

$message = '';
$message_type = '';
$upload_dir = '../assets/img/barbers/'; // Directory to store barber images

// Create the upload directory if it doesn't exist
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); // 0777 grants full permissions (adjust as per server security needs)
}

// Handle Add/Edit Barber
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $bio = trim($_POST['bio']);
    $image_filename = null; // Default to null for no image

    // Handle image upload if a file was selected and there's no error
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); // Get file extension
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif']; // Allowed image extensions

        if (in_array($file_ext, $allowed_ext)) {
            // Generate a unique filename to prevent conflicts
            $image_filename = uniqid('barber_', true) . '.' . $file_ext;
            $destination = $upload_dir . $image_filename;
            if (!move_uploaded_file($file_tmp, $destination)) {
                $message = "Error uploading image. Please check directory permissions.";
                $message_type = "danger";
                $image_filename = null; // Reset if upload fails
            }
        } else {
            $message = "Invalid image file type. Only JPG, JPEG, PNG, GIF are allowed.";
            $message_type = "danger";
        }
    }

    if (isset($_POST['add_barber'])) {
        // Validation for adding a barber
        if (empty($name)) {
            $message = "Barber name is required.";
            $message_type = "danger";
        } else {
            // Only proceed if no image upload error occurred or no image was uploaded
            if ($message_type !== "danger" || $image_filename === null) {
                $stmt = $conn->prepare("INSERT INTO barbers (name, bio, image) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $bio, $image_filename); // 's' for string
                if ($stmt->execute()) {
                    $message = "Barber added successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error adding barber: " . $stmt->error;
                    $message_type = "danger";
                }
                $stmt->close();
            }
        }
    } elseif (isset($_POST['edit_barber'])) {
        $id = intval($_POST['barber_id']); // Ensure ID is integer
        $old_image = $_POST['old_image'] ?? null; // Get the existing image filename from hidden field

        // Validation for editing a barber
        if (empty($name)) {
            $message = "Barber name is required.";
            $message_type = "danger";
        } else {
            // Only proceed if no image upload error occurred or no new image was uploaded
            if ($message_type !== "danger" || ($image_filename === null && !isset($_FILES['image']['name']))) {
                $final_image_to_save = $old_image; // Assume old image is kept by default

                if ($image_filename) { // A new image was successfully uploaded
                    $final_image_to_save = $image_filename;
                    // Delete the old image file if a new one was uploaded and old one existed
                    if ($old_image && file_exists($upload_dir . $old_image)) {
                        unlink($upload_dir . $old_image);
                    }
                } elseif (isset($_POST['clear_image']) && $_POST['clear_image'] == 'yes') {
                    // User explicitly checked to clear the image
                    $final_image_to_save = null;
                    if ($old_image && file_exists($upload_dir . $old_image)) {
                        unlink($upload_dir . $old_image);
                    }
                }

                $stmt = $conn->prepare("UPDATE barbers SET name = ?, bio = ?, image = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $bio, $final_image_to_save, $id); // 's' for string, 'i' for integer
                if ($stmt->execute()) {
                    $message = "Barber updated successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error updating barber: " . $stmt->error;
                    $message_type = "danger";
                }
                $stmt->close();
            }
        }
    }
}

// Handle Delete Barber via GET request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']); // Ensure ID is integer

    // Get image filename before deleting the record from DB
    $stmt_img = $conn->prepare("SELECT image FROM barbers WHERE id = ?");
    $stmt_img->bind_param("i", $id);
    $stmt_img->execute();
    $result_img = $stmt_img->get_result();
    $barber_image = null;
    if ($result_img->num_rows > 0) {
        $barber_image = $result_img->fetch_assoc()['image'];
    }
    $stmt_img->close();

    // Check for associated bookings before deleting barber
    // This is a crucial step to maintain data integrity.
    $stmt_check_bookings = $conn->prepare("SELECT COUNT(*) AS booking_count FROM bookings WHERE barber_id = ?");
    $stmt_check_bookings->bind_param("i", $id);
    $stmt_check_bookings->execute();
    $result_check_bookings = $stmt_check_bookings->get_result();
    $booking_count = $result_check_bookings->fetch_assoc()['booking_count'];
    $stmt_check_bookings->close();

    if ($booking_count > 0) {
        $message = "Cannot delete barber: There are " . $booking_count . " associated bookings. Please reassign or delete bookings first.";
        $message_type = "danger";
    } else {
        $stmt = $conn->prepare("DELETE FROM barbers WHERE id = ?");
        $stmt->bind_param("i", $id); // 'i' for integer
        if ($stmt->execute()) {
            // Delete image file from server if it exists
            if ($barber_image && file_exists($upload_dir . $barber_image)) {
                unlink($upload_dir . $barber_image);
            }
            $message = "Barber deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting barber: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    }
    // Redirect to clear GET parameters and display message
    header("Location: " . BASE_URL . "admin/manage_barbers.php?message=" . urlencode($message) . "&type=" . urlencode($message_type));
    exit();
}

// Display messages from redirects (e.g., after delete)
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type']);
}

// Fetch all barbers for displaying the list
$barbers = [];
$result = $conn->query("SELECT id, name, bio, image FROM barbers ORDER BY name");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $barbers[] = $row;
    }
}

?>

<h1 class="mb-4 d-flex justify-content-between align-items-center">
    Manage Barbers
    <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
</h1>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><?php echo (isset($_GET['action']) && $_GET['action'] == 'edit') ? 'Edit Barber' : 'Add New Barber'; ?></h5>
            </div>
            <div class="card-body">
                <?php
                // Pre-populate form if editing
                $current_barber = ['id' => '', 'name' => '', 'bio' => '', 'image' => ''];
                if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
                    $edit_id = intval($_GET['id']);
                    $stmt_edit = $conn->prepare("SELECT id, name, bio, image FROM barbers WHERE id = ?");
                    $stmt_edit->bind_param("i", $edit_id);
                    $stmt_edit->execute();
                    $result_edit = $stmt_edit->get_result();
                    if ($result_edit->num_rows == 1) {
                        $current_barber = $result_edit->fetch_assoc();
                    } else {
                        // If ID not found, redirect to clear edit state
                        header("Location: " . BASE_URL . "admin/manage_barbers.php?message=" . urlencode("Barber not found for editing.") . "&type=danger");
                        exit();
                    }
                    $stmt_edit->close();
                }
                ?>
                <form action="<?php echo BASE_URL; ?>admin/manage_barbers.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="barber_id" value="<?php echo htmlspecialchars($current_barber['id']); ?>">
                    <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($current_barber['image']); ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label">Barber Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($current_barber['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio / Experience (Optional)</label>
                        <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo htmlspecialchars($current_barber['bio']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Barber Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <?php if ($current_barber['image']): ?>
                            <div class="mt-2">
                                Current Image: <br>
                                <img src="<?php echo BASE_URL; ?>assets/img/barbers/<?php echo htmlspecialchars($current_barber['image']); ?>" alt="Barber Image" style="max-width: 100px; height: auto; border-radius: 0.5rem; margin-top: 5px;">
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" value="yes" id="clear_image" name="clear_image">
                                    <label class="form-check-label" for="clear_image">
                                        Clear current image
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                        <small class="form-text text-muted">Upload a new image or check "Clear current image" to remove it. Max file size depends on server config.</small>
                    </div>
                    <?php if ((isset($_GET['action']) && $_GET['action'] == 'edit')): ?>
                        <button type="submit" name="edit_barber" class="btn btn-primary">Update Barber</button>
                        <a href="<?php echo BASE_URL; ?>admin/manage_barbers.php" class="btn btn-secondary">Cancel Edit</a>
                    <?php else: ?>
                        <button type="submit" name="add_barber" class="btn btn-success">Add Barber</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5>All Barbers</h5>
            </div>
            <div class="card-body">
                <?php if (empty($barbers)): ?>
                    <p class="text-center">No barbers found. Add a new barber using the form.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Bio Summary</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($barbers as $barber): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($barber['id']); ?></td>
                                        <td>
                                            <?php if ($barber['image']): ?>
                                                <img src="<?php echo BASE_URL; ?>assets/img/barbers/<?php echo htmlspecialchars($barber['image']); ?>" alt="<?php echo htmlspecialchars($barber['name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                            <?php else: ?>
                                                <img src="https://placehold.co/50x50/cccccc/333333?text=No+Img" alt="No Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($barber['name']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($barber['bio'], 0, 70)) . (strlen($barber['bio']) > 70 ? '...' : ''); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>admin/manage_barbers.php?action=edit&id=<?php echo htmlspecialchars($barber['id']); ?>" class="btn btn-sm btn-primary me-1">Edit</a>
                                            <a href="<?php echo BASE_URL; ?>admin/manage_barbers.php?action=delete&id=<?php echo htmlspecialchars($barber['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this barber? This will also delete any associated bookings.');">Delete</a>
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
