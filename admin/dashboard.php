<?php
require_once '../auth/auth_check.php';
check_admin(); // Ensure only logged-in users with 'admin' role can access this page
// admin/dashboard.php
require_once '../includes/db_connect.php';
require_once '../includes/header.php';


// Fetch counts for dashboard overview cards
$total_users = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetch_row()[0];
$total_bookings = $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0];
$total_services = $conn->query("SELECT COUNT(*) FROM services")->fetch_row()[0];
$total_barbers = $conn->query("SELECT COUNT(*) FROM barbers")->fetch_row()[0];
$new_contact_messages = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetch_row()[0];


// Fetch bookings for tabs
$all_bookings = [];
$upcoming_bookings = [];
$canceled_bookings = [];

// Removed s.duration_minutes from SELECT statement
$stmt_bookings = $conn->prepare("
    SELECT b.id, b.booking_number, b.date, b.time, b.status, b.notes,
           u.name AS user_name, u.email AS user_email,
           s.name AS service_name, s.price AS service_price,
           br.name AS barber_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN services s ON b.service_id = s.id
    JOIN barbers br ON b.barber_id = br.id
    ORDER BY b.date ASC, b.time ASC
");
$stmt_bookings->execute();
$result_bookings = $stmt_bookings->get_result();

if ($result_bookings->num_rows > 0) {
    while ($row = $result_bookings->fetch_assoc()) {
        $all_bookings[] = $row; // Store all for the "All Bookings" tab

        // For "Upcoming Bookings" tab (future date/time, and not cancelled/completed)
        $booking_datetime_str = $row['date'] . ' ' . $row['time'];
        if (strtotime($booking_datetime_str) > time() && ($row['status'] == 'pending' || $row['status'] == 'confirmed')) {
            $upcoming_bookings[] = $row;
        }

        // For "Canceled Bookings" tab
        if ($row['status'] == 'cancelled') {
            $canceled_bookings[] = $row;
        }
    }
}
$stmt_bookings->close();

// Removed the calculateEndTime function as it's no longer needed
// function calculateEndTime($start_date, $start_time, $duration_minutes) { ... }

?>

<div class="admin-dashboard-container"> <!-- Main wrapper for dashboard specific styling -->

    <h1 class="mb-4 dashboard-title-header">
        Admin Dashboard
        <a href="<?php echo BASE_URL; ?>admin/manage_bookings.php" class="btn btn-outline-primary admin-dashboard-button"><i class="fas fa-file-export me-1"></i> Manage All Bookings</a>
    </h1>

    <!-- Info Cards Section -->
    <div class="row info-cards-container">
        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
            <div class="dashboard-card shadow-sm">
                <div class="card-content">
                    <div class="card-title text-muted">TOTAL CLIENTS</div>
                    <div class="card-value"><?php echo $total_users; ?></div>
                </div>
                <div class="card-icon card-icon-blue">
                    <i class="fas fa-user-friends"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
            <div class="dashboard-card shadow-sm">
                <div class="card-content">
                    <div class="card-title text-muted">TOTAL SERVICES</div>
                    <div class="card-value"><?php echo $total_services; ?></div>
                </div>
                <div class="card-icon card-icon-green">
                    <i class="fas fa-cut"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
            <div class="dashboard-card shadow-sm">
                <div class="card-content">
                    <div class="card-title text-muted">EMPLOYEES</div>
                    <div class="card-value"><?php echo $total_barbers; ?></div>
                </div>
                <div class="card-icon card-icon-red">
                    <i class="fas fa-user-tie"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
            <div class="dashboard-card shadow-sm">
                <div class="card-content">
                    <div class="card-title text-muted">APPOINTMENTS</div>
                    <div class="card-value"><?php echo $total_bookings; ?></div>
                </div>
                <div class="card-icon card-icon-orange">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Bookings Section with Tabs (Left Column) -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm dashboard-card-large">
                <div class="card-header bg-white pt-3 pb-0 dashboard-tabs-header">
                    <ul class="nav nav-tabs card-header-tabs" id="myBookingTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" href="#upcoming" role="tab" aria-controls="upcoming" aria-selected="true">Upcoming Bookings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="all-tab" data-bs-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="false">All Bookings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="canceled-tab" data-bs-toggle="tab" href="#canceled" role="tab" aria-controls="canceled" aria-selected="false">Canceled Bookings</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="myBookingTabsContent">
                        <!-- Upcoming Bookings Tab -->
                        <div class="tab-pane fade show active" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
                            <?php if (empty($upcoming_bookings)): ?>
                                <p class="text-center py-4 text-muted">No upcoming bookings.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover dashboard-table">
                                        <thead>
                                            <tr>
                                                <th>Start Time</th>
                                                <th>Booked Services</th>
                                                <th>Client</th>
                                                <th>Employee</th>
                                                <th>Manage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($upcoming_bookings as $booking): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i A', strtotime($booking['date'] . ' ' . $booking['time']))); ?></td>
                                                    <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($booking['barber_name']); ?></td>
                                                    <td>
                                                        <a href="<?php echo BASE_URL; ?>admin/manage_bookings.php?filter_booking_number=<?php echo urlencode($booking['booking_number']); ?>" class="btn btn-sm btn-outline-secondary manage-button" title="Manage Booking"><i class="fas fa-calendar-alt"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- All Bookings Tab -->
                        <div class="tab-pane fade" id="all" role="tabpanel" aria-labelledby="all-tab">
                            <?php if (empty($all_bookings)): ?>
                                <p class="text-center py-4 text-muted">No bookings found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover dashboard-table">
                                        <thead>
                                            <tr>
                                                <th>Start Time</th>
                                                <th>Booked Services</th>
                                                <th>Client</th>
                                                <th>Employee</th>
                                                <th>Status</th>
                                                <th>Manage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($all_bookings as $booking): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i A', strtotime($booking['date'] . ' ' . $booking['time']))); ?></td>
                                                    <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($booking['barber_name']); ?></td>
                                                    <td>
                                                        <span class="status-badge
                                                            <?php
                                                                if ($booking['status'] == 'pending') echo 'status-pending';
                                                                else if ($booking['status'] == 'confirmed') echo 'status-confirmed';
                                                                else if ($booking['status'] == 'completed') echo 'status-completed';
                                                                else if ($booking['status'] == 'cancelled') echo 'status-cancelled';
                                                            ?>
                                                        "><?php echo ucfirst(htmlspecialchars($booking['status'])); ?></span>
                                                    </td>
                                                    <td>
                                                        <a href="<?php echo BASE_URL; ?>admin/manage_bookings.php?filter_booking_number=<?php echo urlencode($booking['booking_number']); ?>" class="btn btn-sm btn-outline-secondary manage-button" title="Manage Booking"><i class="fas fa-calendar-alt"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Canceled Bookings Tab -->
                        <div class="tab-pane fade" id="canceled" role="tabpanel" aria-labelledby="canceled-tab">
                            <?php if (empty($canceled_bookings)): ?>
                                <p class="text-center py-4 text-muted">No canceled bookings.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover dashboard-table">
                                        <thead>
                                            <tr>
                                                <th>Start Time</th>
                                                <th>Booked Services</th>
                                                <th>Client</th>
                                                <th>Employee</th>
                                                <th>Manage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($canceled_bookings as $booking): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i A', strtotime($booking['date'] . ' ' . $booking['time']))); ?></td>
                                                    <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                                                    <td><?php htmlspecialchars($booking['user_name']); ?></td>
                                                    <td><?php htmlspecialchars($booking['barber_name']); ?></td>
                                                    <td>
                                                        <a href="<?php echo BASE_URL; ?>admin/manage_bookings.php?filter_booking_number=<?php echo urlencode($booking['booking_number']); ?>" class="btn btn-sm btn-outline-secondary manage-button" title="Manage Booking"><i class="fas fa-calendar-alt"></i></a>
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

        <!-- Admin Quick Links Panel (Right Column) -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm dashboard-card-large">
                <div class="card-header bg-white dashboard-tabs-header">
                    <h5 class="card-title text-primary mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush admin-quick-links">
                        <a href="<?php echo BASE_URL; ?>admin/manage_bookings.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-calendar-check me-2"></i> Manage Bookings
                        </a>
                        <a href="<?php echo BASE_URL; ?>admin/manage_services.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-list me-2"></i> Manage Services
                        </a>
                        <a href="<?php echo BASE_URL; ?>admin/manage_barbers.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-tie me-2"></i> Manage Barbers
                        </a>
                        <a href="<?php echo BASE_URL; ?>admin/manage_users.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-users me-2"></i> Manage Clients
                        </a>
                        <a href="<?php echo BASE_URL; ?>admin/view_contact_messages.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-envelope me-2"></i> View Contact Messages
                            <?php if ($new_contact_messages > 0): ?>
                                <span class="badge bg-danger rounded-pill float-end"><?php echo $new_contact_messages; ?> New</span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div> <!-- End admin-dashboard-container -->

<?php
$conn->close();
require_once '../includes/footer.php';
?>
