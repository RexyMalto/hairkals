<?php
ob_start(); // ← Buffer output to prevent header already sent error
require_once 'includes/db_connect.php';
require_once 'includes/header.php'; // header.php outputs HTML!

$message = '';
$message_type = '';

// Check if there's a registration success message from redirect
if (isset($_GET['registration']) && $_GET['registration'] == 'success') {
    $message = "Registration successful! Please log in.";
    $message_type = "success";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = "Please enter both email and password.";
        $message_type = "danger";
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            // Verify password using password_verify() against the hashed password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start session and store user info
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on user role
                if ($user['role'] == 'admin') {
                    header("Location: " . BASE_URL . "admin/dashboard.php");
                } else {
                    header("Location: " . BASE_URL . "user/dashboard.php");
                }
                exit();
            } else {
                $message = "Invalid email or password.";
                $message_type = "danger";
            }
        } else {
            $message = "Invalid email or password."; // User not found
            $message_type = "danger";
        }
        $stmt->close();
    }
}
?>

<style>
.login-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-overlay {
  position: absolute;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.6);
  top: 0;
  left: 0;
}

.modal-content {
  position: relative;
  background: #fff;
  border-radius: 10px;
  max-width: 500px;
  width: 90%;
  padding: 20px;
  animation: fadeInUp 0.4s ease-out;
  z-index: 999;
}

.close-btn {
  position: absolute;
  top: 12px;
  right: 15px;
  font-size: 20px;
  cursor: pointer;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(40px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

<!-- Login Modal Container -->
<!-- Login Modal Structure -->
<div id="loginModal" class="login-modal d-none">
  <div class="modal-overlay"></div>
  <div class="modal-content">
    <span class="close-btn">&times;</span>
    <div class="card shadow">
      <div class="card-header text-center text-white" style="background-color: #000;">
        <h4>Login to Your Account</h4>
      </div>
      <div class="card-body">
        <?php if ($message): ?>
          <div class="alert alert-<?php echo $message_type; ?>" role="alert">
            <?php echo $message; ?>
          </div>
        <?php endif; ?>
        <form action="<?php echo BASE_URL; ?>login.php" method="POST">
          <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email"
                   required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Login</button>
          </div>
          <p class="mt-3 text-center">
            Don't have an account? <a href="<?php echo BASE_URL; ?>register.php">Register here</a>
          </p>
        </form>
      </div>
    </div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('loginModal');
  const closeBtn = document.querySelector('.close-btn');
  const overlay = document.querySelector('.modal-overlay');

  // Show modal if ?showLogin=1 or if there's an error message
  const showLogin = new URLSearchParams(window.location.search).get('showLogin');
  if (showLogin === '1' || <?php echo $message ? 'true' : 'false'; ?>) {
    modal.classList.remove('d-none');
  }

  closeBtn.addEventListener('click', () => modal.classList.add('d-none'));
  overlay.addEventListener('click', () => modal.classList.add('d-none'));
});
</script>


<?php
$conn->close();
require_once 'includes/footer.php';
ob_end_flush(); // ← End output buffering
?>
