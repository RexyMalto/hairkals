<?php
// admin/manage_users.php
require_once '../includes/db_connect.php';
require_once '../includes/header.php'; // header.php includes config.php and starts session
require_once '../auth/auth_check.php';

check_admin(); // Ensure only logged-in users with 'admin' role can access this page

$message = '';
$message_type = '';

// Handle User Edit (Name or Email only for 'user' role)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $user_id = intval($_POST['user_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    // Server-side validation
    if (empty($name) || empty($email)) {
        $message = "Name and Email are required.";
        $message_type = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $message_type = "danger";
    } else {
        // Check for duplicate email for other users (excluding current user)
        $stmt_check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt_check_email->bind_param("si", $email, $user_id);
        $stmt_check_email->execute();
        $stmt_check_email->store_result();

        if ($stmt_check_email->num_rows > 0) {
            $message = "Error: Email already exists for another user.";
            $message_type = "danger";
        } else {
            // Update only name and email for users with 'user' role
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ? AND role = 'user'");
            $stmt->bind_param("ssi", $name, $email, $user_id);
            if ($stmt->execute()) {
                $message = "User updated successfully!";
                $message_type = "success";
            } else {
                $message = "Error updating user: " . $stmt->error;
                $message_type = "danger";
            }
            $stmt->close();
        }
        $stmt_check_email->close();
    }
}

// Handle User Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Important: Prevent deleting the currently logged-in admin
    if ($id == $_SESSION['user_id']) {
        $message = "You cannot delete your own admin account.";
        $message_type = "danger";
    } else {
        // First, get the role of the user to be deleted
        $stmt_get_role = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt_get_role->bind_param("i", $id);
        $stmt_get_role->execute();
        $result_role = $stmt_get_role->get_result();
        $user_to_delete_role = '';
        if ($result_role->num_rows > 0) {
            $user_to_delete_role = $result_role->fetch_assoc()['role'];
        }
        $stmt_get_role->close();

        if ($user_to_delete_role == 'admin') {
            // Prevent deleting other admin accounts for safety, unless specific logic is added
            $message = "You cannot delete another administrator's account directly from this panel for security reasons.";
            $message_type = "danger";
        } else {
            // If it's a regular user, proceed with deletion
            // Bookings associated with this user should ideally cascade delete if foreign keys are set up with ON DELETE CASCADE
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "User deleted successfully!";
                $message_type = "success";
            } else {
                $message = "Error deleting user: " . $stmt->error;
                $message_type = "danger";
            }
            $stmt->close();
        }
    }
    header("Location: " . BASE_URL . "admin/manage_users.php?message=" . urlencode($message) . "&type=" . urlencode($message_type));
    exit();
}


// Display messages from redirects
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type']);
}

// Fetch all users (excluding current admin for self-deletion safety)
$users = [];
$stmt = $conn->prepare("SELECT id, name, email, role FROM users ORDER BY role DESC, name ASC");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
$stmt->close();

?>

<h1 class="mb-4 d-flex justify-content-between align-items-center">
    Manage Users
    <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
</h1>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<!-- User List -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5>All Users</h5>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
            <p class="text-center">No users found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><span class="badge bg-<?php echo ($user['role'] == 'admin') ? 'danger' : 'success'; ?>"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></span></td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): // Cannot edit/delete self for security reasons here ?>
                                        <form action="<?php echo BASE_URL; ?>admin/manage_users.php" method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="form-control" placeholder="Name" required>
                                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control ms-1" placeholder="Email" required>
                                                <button type="submit" name="edit_user" class="btn btn-sm btn-primary ms-1">Update</button>
                                            </div>
                                        </form>
                                        <a href="<?php echo BASE_URL; ?>admin/manage_users.php?action=delete&id=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-sm btn-danger mt-1" onclick="return confirm('Are you sure you want to delete this user? All their bookings will also be deleted.');">Delete</a>
                                    <?php else: ?>
                                        <span class="text-muted">Current Admin</span>
                                    <?php endif; ?>
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
