<?php
// admin/manage_bookings.php
require_once '../includes/db_connect.php';
require_once '../includes/header.php'; // header.php includes config.php and starts session
require_once '../auth/auth_check.php';

check_admin(); // Ensure only logged-in users with 'admin' role can access this page

$message = '';
$message_type = '';

// Handle Booking Status Update via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $booking_id = intval($_POST['booking_id']); // Ensure integer
    $new_status = trim($_POST['new_status']);

    $allowed_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    if (!in_array($new_status, $allowed_statuses)) {
        $message = "Invalid status provided.";
        $message_type = "danger";
    } else {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $booking_id); // 's' for string, 'i' for integer
        if ($stmt->execute()) {
            $message = "Booking status updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating status: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    }
}

// Handle Delete Booking via GET request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']); // Ensure ID is integer

    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $id); // 'i' for integer
    if ($stmt->execute()) {
        $message = "Booking deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting booking: " . $stmt->error;
        $message_type = "danger";
    }
    $stmt->close();
    // Redirect to clear GET parameters and display message
    header("Location: " . BASE_URL . "admin/manage_bookings.php?message=" . urlencode($message) . "&type=" . urlencode($message_type));
    exit();
}

// Display messages from redirects (e.g., after status update or delete)
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type']);
}

// Fetch all barbers for the filter dropdown
$barbers_for_filter = [];
$result_barbers_filter = $conn->query("SELECT id, name FROM barbers ORDER BY name");
if ($result_barbers_filter->num_rows > 0) {
    while ($row = $result_barbers_filter->fetch_assoc()) {
        $barbers_for_filter[] = $row;
    }
}

// Fetch all users (role 'user') for the filter dropdown
$users_for_filter = [];
$result_users_filter = $conn->query("SELECT id, name, email FROM users WHERE role = 'user' ORDER BY name");
if ($result_users_filter->num_rows > 0) {
    while ($row = $result_users_filter->fetch_assoc()) {
        $users_for_filter[] = $row;
    }
}

// Build SQL Query for Bookings List with Filters
$sql = "
    SELECT b.id, b.booking_number, b.date, b.time, b.status, b.notes,
           u.name AS user_name, u.email AS user_email,
           s.name AS service_name, s.price AS service_price,
           br.name AS barber_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN services s ON b.service_id = s.id
    JOIN barbers br ON b.barber_id = br.id
    WHERE 1=1
";
$params = [];
$param_types = "";

// Apply Filters based on GET parameters
// Store values in dedicated variables before binding
$filter_date = $_GET['filter_date'] ?? '';
$filter_barber = $_GET['filter_barber'] ?? '';
$filter_user = $_GET['filter_user'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

if (!empty($filter_date)) {
    $sql .= " AND b.date = ?";
    $params[] = $filter_date; // $params will hold the values
    $param_types .= "s";
}
if (!empty($filter_barber)) {
    $sql .= " AND b.barber_id = ?";
    $params[] = intval($filter_barber); // intval returns a value, not a reference
    $param_types .= "i";
}
if (!empty($filter_user)) {
    $sql .= " AND b.user_id = ?";
    $params[] = intval($filter_user); // intval returns a value, not a reference
    $param_types .= "i";
}
if (!empty($filter_status)) {
    $sql .= " AND b.status = ?";
    $params[] = $filter_status;
    $param_types .= "s";
}

$sql .= " ORDER BY b.date DESC, b.time DESC"; // Order by date and time, newest first

$stmt_bookings = $conn->prepare($sql);

if ($params) {
    // Dynamically bind parameters (required for prepared statements with variable number of parameters)
    // Fix: Create an array of references for bind_param
    $bind_args = [];
    $bind_args[] = $param_types; // First argument is the type string
    foreach ($params as $key => $value) {
        $bind_args[] = &$params[$key]; // Pass each parameter by reference
    }

    // Use call_user_func_array with the array of references
    call_user_func_array([$stmt_bookings, 'bind_param'], $bind_args);
}
$stmt_bookings->execute();
$result_bookings = $stmt_bookings->get_result();

$all_bookings = [];
if ($result_bookings->num_rows > 0) {
    while ($row = $result_bookings->fetch_assoc()) {
        $all_bookings[] = $row;
    }
}
$stmt_bookings->close();

?>

<h1 class="mb-4 d-flex justify-content-between align-items-center">
    Manage Bookings
    <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
</h1>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h5>Filter Bookings</h5>
    </div>
    <div class="card-body">
        <form action="<?php echo BASE_URL; ?>admin/manage_bookings.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="filter_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="filter_date" name="filter_date" value="<?php echo htmlspecialchars($filter_date); ?>">
            </div>
            <div class="col-md-3">
                <label for="filter_barber" class="form-label">Barber</label>
                <select class="form-select" id="filter_barber" name="filter_barber">
                    <option value="">All Barbers</option>
                    <?php foreach ($barbers_for_filter as $barber): ?>
                        <option value="<?php echo htmlspecialchars($barber['id']); ?>" <?php echo ($filter_barber == $barber['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($barber['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_user" class="form-label">User</label>
                <select class="form-select" id="filter_user" name="filter_user">
                    <option value="">All Users</option>
                    <?php foreach ($users_for_filter as $user): ?>
                        <option value="<?php echo htmlspecialchars($user['id']); ?>" <?php echo ($filter_user == $user['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['name']) . " (" . htmlspecialchars($user['email']) . ")"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter_status" class="form-label">Status</label>
                <select class="form-select" id="filter_status" name="filter_status">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo ($filter_status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo ($filter_status == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="completed" <?php echo ($filter_status == 'completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo ($filter_status == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <a href="<?php echo BASE_URL; ?>admin/manage_bookings.php" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Bookings List -->
<div class="card">
    <div class="card-header text-white" style="background-color:rgb(46, 7, 110);">
        <h5>All Bookings</h5>
    </div>
    <div class="card-body">
        <?php if (empty($all_bookings)): ?>
            <p class="text-center">No bookings found with the current filters.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Booking No.</th>
                            <th>User</th>
                            <th>Service</th>
                            <th>Barber</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['booking_number']); ?></td>
                                <td><?php echo htmlspecialchars($booking['user_name']); ?><br><small><?php echo htmlspecialchars($booking['user_email']); ?></small></td>
                                <td><?php echo htmlspecialchars($booking['service_name']); ?><br><small>RM<?php echo htmlspecialchars(number_format($booking['service_price'], 2)); ?></small></td>
                                <td><?php echo htmlspecialchars($booking['barber_name']); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($booking['date']))); ?></td>
                                <td><?php echo htmlspecialchars(date('h:i A', strtotime($booking['time']))); ?></td>
                                <td>
                                    <form action="<?php echo BASE_URL; ?>admin/manage_bookings.php" method="POST" class="d-flex align-items-center">
                                        <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['id']); ?>">
                                        <select name="new_status" class="form-select form-select-sm me-2">
                                            <option value="pending" <?php echo ($booking['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo ($booking['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="completed" <?php echo ($booking['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo ($booking['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-outline-primary">Update</button>
                                    </form>
                                </td>
                                <td><?php echo htmlspecialchars($booking['notes'] ?: 'N/A'); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>admin/manage_bookings.php?action=delete&id=<?php echo htmlspecialchars($booking['id']); ?>" class="btn btn-sm btn-danger mt-1" onclick="return confirm('Are you sure you want to delete this booking?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
require_once '../includes/footer.php';
?>
