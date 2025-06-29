<?php
// user/profile.php
require_once '../includes/db_connect.php';
require_once '../includes/header.php';
require_once '../auth/auth_check.php';

check_user();

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Fetch current user data
$user_data = [];
$stmt = $conn->prepare("SELECT name, email, phone, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
} else {
    $message = "User data not found. Please log in again.";
    $message_type = "danger";
}
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email)) {
        $message = "Name and Email are required.";
        $message_type = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $message_type = "danger";
    } elseif (!empty($phone) && !preg_match("/^[0-9\s\-\(\)]+$/", $phone)) {
        $message = "Invalid phone number format.";
        $message_type = "danger";
    } elseif (!empty($password) && $password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "danger";
    } elseif (!empty($password) && strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
        $message_type = "danger";
    } else {
        $stmt_check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt_check_email->bind_param("si", $email, $user_id);
        $stmt_check_email->execute();
        $stmt_check_email->store_result();

        if ($stmt_check_email->num_rows > 0) {
            $message = "Email already taken.";
            $message_type = "danger";
        } else {
            $update_sql = "UPDATE users SET name = ?, email = ?, phone = ?";
            $params = [$name, $email, $phone];
            $types = "sss";

            // Handle password
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_sql .= ", password = ?";
                $params[] = $hashed_password;
                $types .= "s";
            }

            // Handle profile image upload
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
                $img_dir = "../assets/img/";
                $img_name = basename($_FILES['profile_image']['name']);
                $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($img_ext, $allowed)) {
                    $new_img_name = "user_" . $user_id . "_" . time() . "." . $img_ext;
                    $img_path = $img_dir . $new_img_name;

                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $img_path)) {
                        $update_sql .= ", profile_image = ?";
                        $params[] = $new_img_name;
                        $types .= "s";
                    }
                }
            }

            $update_sql .= " WHERE id = ?";
            $params[] = $user_id;
            $types .= "i";

            $stmt = $conn->prepare($update_sql);

            $bind_names[] = $types;
            foreach ($params as $key => $value) {
                ${"param$key"} = $value;
                $bind_names[] = &${"param$key"};
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_names);

            if ($stmt->execute()) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $message = "Profile updated successfully!";
                $message_type = "success";

                // Refresh data
                $stmt_fetch = $conn->prepare("SELECT name, email, phone, profile_image FROM users WHERE id = ?");
                $stmt_fetch->bind_param("i", $user_id);
                $stmt_fetch->execute();
                $result_fetch = $stmt_fetch->get_result();
                $user_data = $result_fetch->fetch_assoc();
                $stmt_fetch->close();
            } else {
                $message = "Error updating profile: " . $stmt->error;
                $message_type = "danger";
            }

            $stmt->close();
        }

        $stmt_check_email->close();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">Your Profile</h1>
    <a href="<?php echo BASE_URL; ?>user/dashboard.php" class="btn btn-secondary">
        ← Back to Dashboard
    </a>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header text-white" style="background-color: #000000;">
        <h5>Update Profile Information</h5>
    </div>
    <div class="card-body">
        <form action="<?php echo BASE_URL; ?>user/profile.php" method="POST" enctype="multipart/form-data">
            <?php
            $profile_image = $user_data['profile_image'] ?? '';
            $profile_image_path = $profile_image ? BASE_URL . 'assets/img/' . $profile_image : BASE_URL . 'assets/img/default.png';
            ?>
            <div class="mb-3 text-center">
                <img src="<?php echo $profile_image_path; ?>" alt="Profile Image" class="img-thumbnail" width="150">
            </div>
            <div class="mb-3">
                <label for="profile_image" class="form-label">Change Profile Image</label>
                <input type="file" class="form-control" name="profile_image" id="profile_image" accept="image/*">
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" name="name" id="name" value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="text" class="form-control" name="phone" id="phone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" placeholder="e.g., 012-3456789">
            </div>
            <hr>
            <p class="text-muted">Leave password fields blank if you don't want to change it.</p>
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" name="password" id="password">
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" name="confirm_password" id="confirm_password">
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>

<?php
$conn->close();
require_once '../includes/footer.php';
?>
