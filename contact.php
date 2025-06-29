<?php
// contact.php
require_once 'includes/db_connect.php';
require_once 'includes/header.php'; // header.php includes config.php and starts session

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_contact'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $user_message = trim($_POST['message']); // Renamed to avoid conflict with $message var

    // Basic server-side validation
    if (empty($name) || empty($email) || empty($subject) || empty($user_message)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $message_type = "danger";
    } elseif (strlen($user_message) < 10) {
        $message = "Your message must be at least 10 characters long.";
        $message_type = "danger";
    } else {
        // Prepare and insert message into the database
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'new')");
        // 'ssss' for four string parameters
        $stmt->bind_param("ssss", $name, $email, $subject, $user_message);

        if ($stmt->execute()) {
            $message = "Your message has been sent successfully! We will get back to you soon.";
            $message_type = "success";
            // Clear form fields on success
            $_POST = array();
        } else {
            $message = "Sorry, there was an error sending your message: " . $stmt->error . ". Please try again later.";
            $message_type = "danger";
        }
        $stmt->close();
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header text-center text-white" style="background-color: #000000;">
                <h2>Contact Us</h2>
                <p class="mb-0">Have questions or need assistance? Reach out to us!</p>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <form action="<?php echo BASE_URL; ?>contact.php" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Your Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Your Message</label>
                        <textarea class="form-control" id="message" name="message" rows="6" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="submit_contact" class="btn btn-primary">Send Message</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
