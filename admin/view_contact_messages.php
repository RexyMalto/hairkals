<?php
// admin/view_contact_messages.php
require_once '../includes/db_connect.php';
require_once '../includes/header.php'; // header.php includes config.php and starts session
require_once '../auth/auth_check.php';

check_admin(); // Ensure only logged-in users with 'admin' role can access this page

$message = '';
$message_type = '';

// Handle updating message status (e.g., mark as read)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_message_status'])) {
    $message_id = intval($_POST['message_id']);
    $new_status = trim($_POST['new_status']);

    if (!in_array($new_status, ['new', 'read'])) {
        $message = "Invalid status provided.";
        $message_type = "danger";
    } else {
        $stmt = $conn->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $message_id);
        if ($stmt->execute()) {
            $message = "Message status updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating message status: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    }
}

// Handle deleting a message
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $message_id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    if ($stmt->execute()) {
        $message = "Message deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting message: " . $stmt->error;
        $message_type = "danger";
    }
    $stmt->close();
    // Redirect to clear GET parameters and display message
    header("Location: " . BASE_URL . "admin/view_contact_messages.php?message=" . urlencode($message) . "&type=" . urlencode($message_type));
    exit();
}

// Display messages from redirects
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type']);
}

// Fetch all contact messages
$contact_messages = [];
$result = $conn->query("SELECT id, name, email, subject, message, sent_at, status FROM contact_messages ORDER BY sent_at DESC");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $contact_messages[] = $row;
    }
}
?>

<h1 class="mb-4 d-flex justify-content-between align-items-center">
    Contact Messages
    <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
</h1>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5>All Customer Inquiries</h5>
    </div>
    <div class="card-body">
        <?php if (empty($contact_messages)): ?>
            <p class="text-center">No contact messages received yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Sent At</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contact_messages as $msg): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($msg['id']); ?></td>
                                <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                                <td>
                                    <!-- Display full message in a modal or expandable content if needed for long messages -->
                                    <?php echo htmlspecialchars(substr($msg['message'], 0, 100)) . (strlen($msg['message']) > 100 ? '...' : ''); ?>
                                    <?php if (strlen($msg['message']) > 100): ?>
                                        <button type="button" class="btn btn-sm btn-link p-0" data-bs-toggle="modal" data-bs-target="#messageModal<?php echo $msg['id']; ?>">Read More</button>

                                        <!-- Modal for full message -->
                                        <div class="modal fade" id="messageModal<?php echo $msg['id']; ?>" tabindex="-1" aria-labelledby="messageModalLabel<?php echo $msg['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title" id="messageModalLabel<?php echo $msg['id']; ?>">Message from <?php echo htmlspecialchars($msg['name']); ?></h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($msg['email']); ?></p>
                                                        <p><strong>Subject:</strong> <?php echo htmlspecialchars($msg['subject']); ?></p>
                                                        <hr>
                                                        <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                                        <small class="text-muted">Sent at: <?php echo htmlspecialchars(date('M d, Y H:i', strtotime($msg['sent_at']))); ?></small>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($msg['sent_at']))); ?></td>
                                <td>
                                    <form action="<?php echo BASE_URL; ?>admin/view_contact_messages.php" method="POST" class="d-flex align-items-center">
                                        <input type="hidden" name="message_id" value="<?php echo htmlspecialchars($msg['id']); ?>">
                                        <select name="new_status" class="form-select form-select-sm me-2">
                                            <option value="new" <?php echo ($msg['status'] == 'new') ? 'selected' : ''; ?>>New</option>
                                            <option value="read" <?php echo ($msg['status'] == 'read') ? 'selected' : ''; ?>>Read</option>
                                        </select>
                                        <button type="submit" name="update_message_status" class="btn btn-sm btn-outline-primary">Update</button>
                                    </form>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>admin/view_contact_messages.php?action=delete&id=<?php echo htmlspecialchars($msg['id']); ?>" class="btn btn-sm btn-danger mt-1" onclick="return confirm('Are you sure you want to delete this message?');">Delete</a>
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
