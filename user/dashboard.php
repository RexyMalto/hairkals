<?php
require_once '../auth/auth_check.php';
check_user(); // Ensure only logged-in users with 'admin' role can access this page
// admin/dashboard.php
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $message_type = $_SESSION['flash_type'];
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

// ✅ FETCH PROFILE IMAGE
$profile_image = 'default.png'; // Default fallback
$stmt_img = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt_img->bind_param("i", $user_id);
$stmt_img->execute();
$result_img = $stmt_img->get_result();
if ($row_img = $result_img->fetch_assoc()) {
    if (!empty($row_img['profile_image'])) {
        $profile_image = $row_img['profile_image'];
    }
}
$stmt_img->close();
// --- Helper function to generate all possible time slots for a day ---
function generateAllPossibleTimeSlots($open_time_str, $close_time_str, $interval_minutes) {
    $slots = [];
    $current_time = strtotime($open_time_str);
    $close_time = strtotime($close_time_str);

    while ($current_time < $close_time) {
        $slots[] = date('H:i', $current_time);
        $current_time = strtotime("+" . $interval_minutes . " minutes", $current_time);
    }
    return $slots;
}

// Generate all possible time slots for the day based on config
$all_possible_time_slots = generateAllPossibleTimeSlots(SHOP_OPEN_TIME, SHOP_CLOSE_TIME, BOOKING_INTERVAL_MINUTES);

// --- Fetch Services for Dropdown ---
$services = [];
$result_services = $conn->query("SELECT id, name, price FROM services ORDER BY name");
if ($result_services === false) { // Added error check for query
    error_log("Error fetching services: " . $conn->error);
    $message = "Error loading services. Please try again later.";
    $message_type = "danger";
} else {
    if ($result_services->num_rows > 0) {
        while ($row = $result_services->fetch_assoc()) {
            $services[] = $row;
        }
    }
}

// --- Fetch Barbers for Dropdown ---
$barbers = [];
$result_barbers = $conn->query("SELECT id, name FROM barbers ORDER BY name");
if ($result_barbers === false) { // Added error check for query
    error_log("Error fetching barbers: " . $conn->error);
    $message = "Error loading barbers. Please try again later.";
    $message_type = "danger";
} else {
    if ($result_barbers->num_rows > 0) {
        while ($row = $result_barbers->fetch_assoc()) {
            $barbers[] = $row;
        }
    }
}

// Determine selected values for sticky form (on initial load or after POST submission)
$selected_service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
$selected_barber_id = isset($_POST['barber_id']) ? intval($_POST['barber_id']) : 0;
$selected_booking_date = isset($_POST['booking_date']) ? trim($_POST['booking_date']) : '';
$selected_booking_time = isset($_POST['booking_time']) ? trim($_POST['booking_time']) : ''; // Capture selected time

// --- Logic to populate available time slots for the form (after "Check Availability" or a failed booking attempt) ---
$available_time_slots_for_display = [];
// This block runs if "Check Availability" was clicked OR if "Book Now" was clicked and led to a validation error
if (isset($_POST['check_availability']) || (isset($_POST['make_booking']) && $message_type == 'danger')) {
    // Only proceed if barber and date are selected
    if ($selected_barber_id > 0 && !empty($selected_booking_date)) {

        // Get all booked slots for the selected barber and date
        $booked_slots = [];
        $stmt_booked_slots = $conn->prepare("SELECT time FROM bookings WHERE barber_id = ? AND date = ? AND status IN ('pending', 'confirmed')");
        if ($stmt_booked_slots === false) {
            $message = "Database prepare error for fetching booked slots: " . $conn->error;
            $message_type = "danger";
        } else {
            $stmt_booked_slots->bind_param("is", $selected_barber_id, $selected_booking_date);
            $stmt_booked_slots->execute();
            $result_booked_slots = $stmt_booked_slots->get_result();
            while ($row = $result_booked_slots->fetch_assoc()) {
                $booked_slots[] = $row['time'];
            }
            $stmt_booked_slots->close();
        }

        // Iterate through all possible slots and mark availability
        $current_time_for_comparison = time(); // Current server time
        // Note: $selected_date_timestamp is not directly used for past/future, but helps conceptualize.
        // The comparison for 'past' is done against current time and the slot's time.

        foreach ($all_possible_time_slots as $slot) {
            $is_booked = in_array($slot, $booked_slots);
            
            // Calculate the full timestamp for the slot on the selected date
            $slot_datetime_str = $selected_booking_date . ' ' . $slot;
            $slot_timestamp = strtotime($slot_datetime_str);

            // Check if the slot is in the past compared to current real-time
            $is_past_realtime = ($slot_timestamp < $current_time_for_comparison);

            $available_time_slots_for_display[] = [
                'time' => $slot,
                'display' => date('h:i A', strtotime($slot)),
                'booked' => $is_booked,
                'past_realtime' => $is_past_realtime,
                'available' => !($is_booked || $is_past_realtime)
            ];
        }
    }
}


// --- Handle New Booking Submission ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['make_booking'])) {
    $service_id = intval($_POST['service_id']);
    $barber_id = intval($_POST['barber_id']);
    $booking_date = trim($_POST['booking_date']);
    $booking_time = trim($_POST['booking_time']);
    $notes = trim($_POST['notes']);

    // --- Server-Side Validation ---
    // Make sure booking_time is not empty
    if (empty($service_id) || empty($barber_id) || empty($booking_date) || empty($booking_time)) {
        $message = "Please fill in all required booking fields (Service, Barber, Date, and Time).";
        $message_type = "danger";
    }
    elseif ($service_id <= 0 || !in_array($service_id, array_column($services, 'id'))) {
        $message = "Invalid service selected. Please try again.";
        $message_type = "danger";
    }
    elseif ($barber_id <= 0 || !in_array($barber_id, array_column($barbers, 'id'))) {
        $message = "Invalid barber selected. Please try again.";
        $message_type = "danger";
    }
    elseif (!strtotime($booking_date) || $booking_date < date('Y-m-d')) { // Date cannot be in the past
        $message = "Booking date must be a valid date and not in the past.";
        $message_type = "danger";
    }
    elseif ($booking_date > date('Y-m-d', strtotime('+' . DAYS_AHEAD_BOOKING_LIMIT . ' days'))) {
        $message = "Bookings can only be made up to " . DAYS_AHEAD_BOOKING_LIMIT . " days in advance.";
        $message_type = "danger";
    }
    elseif (!preg_match("/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/", $booking_time)) {
        $message = "Invalid time format. Please use HH:MM.";
        $message_type = "danger";
    }
    // New: Check if the selected slot is actually in the real-time past
    elseif (($booking_date == date('Y-m-d') && strtotime($booking_time) < time())) { // Using time() for real-time comparison
        $message = "The selected time slot is in the past. Please choose a future time.";
        $message_type = "danger";
    }
    elseif (!in_array($booking_time, $all_possible_time_slots)) { // Ensure it's a valid interval slot
         $message = "Invalid time slot. Please select a valid time from the available options.";
         $message_type = "danger";
    }
    else {
        // --- Booking Conflict Check (still performed server-side) ---
        $stmt_conflict = $conn->prepare("SELECT id FROM bookings WHERE barber_id = ? AND date = ? AND time = ? AND status IN ('pending', 'confirmed')");
        if ($stmt_conflict === false) {
             $message = "Database prepare error for conflict check: " . $conn->error;
             $message_type = "danger";
        } else {
            $stmt_conflict->bind_param("iss", $barber_id, $booking_date, $booking_time);
            $stmt_conflict->execute();
            $stmt_conflict->store_result();

            if ($stmt_conflict->num_rows > 0) {
                $_SESSION['flash_message'] = "This time slot is no longer available for the selected barber. Please choose another time or barber.";
                $_SESSION['flash_type'] = "danger";
                header("Location: dashboard.php");
                exit;
            } else {
                // All validations passed, proceed with booking insertion
                $date_part = date('Ymd', strtotime($booking_date));
                $random_part = strtoupper(substr(str_shuffle(MD5(microtime())), 0, 4));
                $booking_number = "BB" . $date_part . $random_part;

                $unique_check_attempts = 0;
                do {
                    $stmt_check_num = $conn->prepare("SELECT id FROM bookings WHERE booking_number = ?");
                    if ($stmt_check_num === false) {
                        $message = "Database prepare error for booking number check: " . $conn->error;
                        $message_type = "danger";
                        break;
                    }
                    $stmt_check_num->bind_param("s", $booking_number);
                    $stmt_check_num->execute();
                    $stmt_check_num->store_result();
                    if ($stmt_check_num->num_rows > 0) {
                        $random_part = strtoupper(substr(str_shuffle(MD5(microtime() . rand(0, 999))), 0, 4));
                        $booking_number = "BB" . $date_part . $random_part;
                    }
                    $unique_check_attempts++;
                } while ($stmt_check_num->num_rows > 0 && $unique_check_attempts < 5);
                $stmt_check_num->close();

                if ($unique_check_attempts >= 5 || $message_type == "danger") {
                    if (empty($message)) {
                        $message = "Could not generate a unique booking number after several attempts. Please try again.";
                        $message_type = "danger";
                    }
                } else {
                    // Insert the new booking into the database
                    $stmt = $conn->prepare("INSERT INTO bookings (user_id, service_id, barber_id, date, time, booking_number, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
                    if ($stmt === false) {
                        $message = "Database prepare error for inserting booking: " . $conn->error;
                        $message_type = "danger";
                    } else {
                        $stmt->bind_param("iiissss", $user_id, $service_id, $barber_id, $booking_date, $booking_time, $booking_number, $notes);

                        if ($stmt->execute()) {
                            $message = "Booking placed successfully! Your booking number is: <strong>" . htmlspecialchars($booking_number) . "</strong>";
                            $message_type = "success";
                            // Reset selected values after successful booking, so form starts fresh
                            $selected_service_id = 0; // Reset these variables
                            $selected_barber_id = 0;
                            $selected_booking_date = '';
                            $selected_booking_time = '';
                            $_POST = array(); // Clear original POST data
                            $available_time_slots_for_display = []; // Clear displayed slots
                        } else {
                            $message = "Error placing booking: " . $stmt->error;
                            $message_type = "danger";
                        }
                        $stmt->close();
                    }
                }
            }
            $stmt_conflict->close();
        }
    }
}


// --- Fetch User's Bookings History ---
$user_bookings = [];
$stmt_bookings = $conn->prepare("
    SELECT b.booking_number, b.date, b.time, b.status, b.notes,
           s.name AS service_name, s.price AS service_price,
           br.name AS barber_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN services s ON b.service_id = s.id
    JOIN barbers br ON b.barber_id = br.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
if ($stmt_bookings === false) {
    error_log("Failed to prepare bookings history statement: " . $conn->error);
} else {
    $stmt_bookings->bind_param("i", $user_id);
    $stmt_bookings->execute();
    $result_bookings = $stmt_bookings->get_result();

    if ($result_bookings->num_rows > 0) {
        while ($row = $result_bookings->fetch_assoc()) {
            $user_bookings[] = $row;
        }
    }
    $stmt_bookings->close();
}

?>

<div class="container py-5">
    <div class="row align-items-center mb-5">
        <div class="col-md-3 text-center">
            <a href="<?php echo BASE_URL; ?>user/profile.php" title="Edit Profile">
                <img src="<?php echo BASE_URL . 'assets/img/' . $profile_image; ?>" 
                     class="rounded-circle shadow" 
                     style="width: 120px; height: 120px; object-fit: cover;">
            </a>
        </div>
        <div class="col-md-9">
            <h3 class="mb-1">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
            <p class="text-muted">Manage your bookings and view your history here.<br>Please choose whether you'd like to make a new booking or view your booking history.</p>
        </div>
    </div>
</div>

<div class="container">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

 <div class="row">
    <!-- Make a New Booking -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header text-white" style="background-color:rgb(46, 7, 110);">
                <h5 class="mb-0">
                    <button class="btn btn-link text-white text-decoration-none p-0" type="button"
                         data-bs-toggle="collapse" data-bs-target="#bookingForm"
                        aria-controls="bookingForm">
                        <i class="bi bi-chevron-down me-1"></i> Make a New Booking
                    </button>
                    </h5>
                </div>
                <div class="collapse <?php echo (isset($_POST['check_availability']) || isset($_POST['make_booking'])) ? 'show' : ''; ?>" id="bookingForm">
                    <div class="card-body">
                        <form action="<?php echo BASE_URL; ?>user/dashboard.php" method="POST">
                            <div class="mb-3">
                                <label for="service_id" class="form-label">Service</label>
                                <select class="form-select" id="service_id" name="service_id" required>
                                    <option value="">Select a Service</option>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?php echo htmlspecialchars($service['id']); ?>" <?php echo ($selected_service_id == $service['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($service['name']) . " - RM" . htmlspecialchars(number_format($service['price'], 2)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="barber_id" class="form-label">Select Barber</label>
                                <select class="form-select" id="barber_id" name="barber_id" required>
                                    <option value="">Choose your Barber</option>
                                    <?php foreach ($barbers as $barber): ?>
                                        <option value="<?php echo htmlspecialchars($barber['id']); ?>" <?php echo ($selected_barber_id == $barber['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($barber['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="booking_date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="booking_date" name="booking_date"
                                    min="<?php echo date('Y-m-d'); ?>"
                                    max="<?php echo date('Y-m-d', strtotime('+' . DAYS_AHEAD_BOOKING_LIMIT . ' days')); ?>"
                                    value="<?php echo htmlspecialchars($selected_booking_date); ?>" required>
                            </div>

                            <div class="mb-3">
                                    <button type="submit" name="check_availability" class="btn text-white" style="background-color:rgb(108, 67, 223);">Select & View Times"</button>
                            </div>

                            <div class="mb-3">
                                <label for="booking_time" class="form-label">Time</label>
                                <select class="form-select" id="booking_time" name="booking_time">
                                    <?php if (empty($available_time_slots_for_display)): ?>
                                        <option value="">Select Barber and Date, then Check Availability</option>
                                    <?php else: ?>
                                        <option value="">-- Select Time --</option>
                                        <?php
                                        $found_available_slot = false;
                                        foreach ($available_time_slots_for_display as $slot):
                                            $disabled_attr = '';
                                            $display_text = $slot['display'];
                                            if (!$slot['available']) {
                                                $disabled_attr = 'disabled';
                                                if ($slot['booked']) $display_text .= ' (Booked)';
                                                elseif ($slot['past_realtime']) $display_text .= ' (Past)';
                                            } else {
                                                $found_available_slot = true;
                                            }
                                            $selected_attr = ($selected_booking_time == $slot['time'] && $slot['available']) ? 'selected' : '';
                                        ?>
                                            <option value="<?php echo htmlspecialchars($slot['time']); ?>" <?php echo $disabled_attr; ?> <?php echo $selected_attr; ?>>
                                                <?php echo htmlspecialchars($display_text); ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <?php if (!$found_available_slot): ?>
                                            <option value="" disabled>No available slots for this selection.</option>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                            </div>

                            <button type="submit" name="make_booking" class="btn text-white" style="background-color:rgb(7, 114, 15);">Book Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking History Section -->
        <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">
                    <button class="btn btn-link text-white text-decoration-none p-0" type="button"
                         data-bs-toggle="collapse" data-bs-target="#bookingHistory"
                         aria-controls="bookingHistory">
                        <i class="bi bi-chevron-down me-1"></i> Your Booking History
                    </button>
                    </h5>
                </div>
                <div class="collapse" id="bookingHistory">
                    <div class="card-body">
                        <?php if (empty($user_bookings)): ?>
                            <p class="text-center">You have no past bookings. Make one now!</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Booking No.</th>
                                            <th>Service</th>
                                            <th>Barber</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($user_bookings as $booking): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($booking['booking_number']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['barber_name']); ?></td>
                                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($booking['date']))); ?></td>
                                                <td><?php echo htmlspecialchars(date('h:i A', strtotime($booking['time']))); ?></td>
                                                <td>
                                                    <span class="badge
                                                        <?php
                                                        echo match($booking['status']) {
                                                            'pending' => 'bg-warning text-dark',
                                                            'confirmed' => 'bg-primary',
                                                            'completed' => 'bg-success',
                                                            'cancelled' => 'bg-danger',
                                                            default => 'bg-secondary'
                                                        };
                                                        ?>">
                                                        <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                                                    </span>
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
    </div>
</div>


<?php
$conn->close();
require_once '../includes/footer.php';
?>
