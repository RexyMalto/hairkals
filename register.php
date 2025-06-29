<?php
// register.php
require_once 'includes/db_connect.php';
require_once 'includes/header.php'; // header.php includes config.php and starts session

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']); // Phone is optional, but if provided, validate
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Input validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $message_type = "danger";
    } elseif (!empty($phone) && !preg_match(PHONE_REGEX, $phone)) { // Validate phone if not empty
        $message = "Invalid phone number format. Please use 7-20 digits, spaces, hyphens, or parentheses.";
        $message_type = "danger";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "danger";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $message_type = "danger";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Email already registered. Please use a different email or login.";
            $message_type = "danger";
        } else {
            // Hash password before storing
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user into database with 'user' role
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'user')");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);

            if ($stmt->execute()) {
                // Redirect to login page with success message
                header("Location: " . BASE_URL . "login.php?registration=success");
                exit();
            } else {
                $message = "Error during registration: " . $stmt->error;
                $message_type = "danger";
            }
        }
        $stmt->close();
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header text-center text-white" style="background-color: #000000;">
                <h4>User Registration</h4>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <form action="<?php echo BASE_URL; ?>register.php" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number (Optional)</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="e.g., 012-3456789">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                    <p class="mt-3 text-center">
                        Already have an account? <a href="<?php echo BASE_URL; ?>login.php">Login here</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
