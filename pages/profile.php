<?php
session_start();
include("../includes/db.php");

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$pageTitle = "My Profile - KaziSellers";
$cssPath = "../assets/css/styles.css";
$isInPages = true;

$user_id = $_SESSION['user_id'];

// Handle profile update
if (($_POST['action'] ?? '') === 'update_profile' && 
    isset($_POST['first_name'], $_POST['last_name'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    
    if (!empty($first_name) && !empty($last_name)) {
        $updateQuery = "UPDATE users SET first_name = ?, last_name = ?, phone = ?, location = ?, bio = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "sssssi", $first_name, $last_name, $phone, $location, $bio, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Profile updated successfully!";
        } else {
            $error_message = "Failed to update profile.";
        }
    } else {
        $error_message = "First name and last name are required.";
    }
}

// Handle password change
if (($_POST['action'] ?? '') === 'change_password' && 
    isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password === $confirm_password) {
        // Verify current password
        $verifyQuery = "SELECT password FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $verifyQuery);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Password changed successfully!";
            } else {
                $error_message = "Failed to change password.";
            }
        } else {
            $error_message = "Current password is incorrect.";
        }
    } else {
        $error_message = "New passwords do not match.";
    }
}

// Get user profile
$userQuery = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get user statistics
$statsQuery = "SELECT 
               COUNT(*) as total_listings,
               COUNT(CASE WHEN availability = 'available' THEN 1 END) as active_listings,
               COUNT(CASE WHEN availability = 'sold' THEN 1 END) as sold_listings,
               SUM(CASE WHEN availability = 'sold' THEN price ELSE 0 END) as total_earnings,
               SUM(views) as total_views
               FROM products 
               WHERE seller_id = ?";
$stmt = mysqli_prepare($conn, $statsQuery);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get recent activity
$activityQuery = "SELECT p.title, p.created_at, p.availability, p.price
                  FROM products p 
                  WHERE p.seller_id = ?
                  ORDER BY p.created_at DESC 
                  LIMIT 5";
$stmt = mysqli_prepare($conn, $activityQuery);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$recentActivity = mysqli_stmt_get_result($stmt);

include("../components/header.php");
?>

<div class="container my-4">
    <!-- Success/Error Messages -->
    <?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Profile Overview -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 100px; height: 100px;">
                            <i class="fas fa-user fa-3x text-white"></i>
                        </div>
                    </div>
                    
                    <h4 class="mb-1"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                    <p class="text-muted mb-3">@<?php echo htmlspecialchars($user['username']); ?></p>
                    
                    <?php if ($user['bio']): ?>
                    <p class="small text-muted mb-3"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                    <?php endif; ?>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <h5 class="text-primary mb-0"><?php echo $stats['total_listings']; ?></h5>
                            <small class="text-muted">Listings</small>
                        </div>
                        <div class="col-6">
                            <h5 class="text-success mb-0"><?php echo $stats['sold_listings']; ?></h5>
                            <small class="text-muted">Sold</small>
                        </div>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="d-flex justify-content-center">
                        <div class="text-center me-3">
                            <div class="d-flex align-items-center text-muted">
                                <i class="fas fa-star text-warning me-1"></i>
                                <span><?php echo number_format($user['rating'], 1); ?></span>
                                <small class="ms-1">(<?php echo $user['total_ratings']; ?>)</small>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="d-flex align-items-center text-muted">
                                <i class="fas fa-eye me-1"></i>
                                <span><?php echo number_format($stats['total_views']); ?></span>
                                <small class="ms-1">views</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie text-primary"></i> Quick Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Active Listings</small>
                        <span class="badge bg-success"><?php echo $stats['active_listings']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Total Earnings</small>
                        <span class="text-success fw-bold">R<?php echo number_format($stats['total_earnings'], 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Member Since</small>
                        <small class="text-muted"><?php echo date('M Y', strtotime($user['created_at'])); ?></small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Last Active</small>
                        <small class="text-muted"><?php echo date('M j, Y', strtotime($user['updated_at'])); ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Settings -->
        <div class="col-lg-8">
            <!-- Profile Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit text-primary"></i> Profile Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                <small class="text-muted">Username cannot be changed</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                       value="<?php echo htmlspecialchars($user['location']); ?>" 
                                       placeholder="e.g., Cape Town, Western Cape">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3" 
                                      placeholder="Tell other users about yourself..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
                            <small class="text-muted">This will be visible to other users</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-lock text-warning"></i> Change Password
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" 
                                   name="current_password" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" 
                                       name="new_password" minlength="6" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" minlength="6" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history text-info"></i> Recent Activity
                    </h5>
                    <a href="my-listings.php" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-list"></i> View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($recentActivity) > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php while ($activity = mysqli_fetch_assoc($recentActivity)): ?>
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($activity['title']); ?></h6>
                                    <small class="text-muted">
                                        Listed on <?php echo date('M j, Y', strtotime($activity['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="text-success fw-bold">R<?php echo number_format($activity['price'], 2); ?></span>
                                    <br>
                                    <span class="badge bg-<?php 
                                        echo $activity['availability'] === 'available' ? 'success' : 
                                            ($activity['availability'] === 'sold' ? 'danger' : 'warning'); 
                                    ?> small">
                                        <?php echo ucfirst($activity['availability']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                        <p>No activity yet</p>
                        <a href="sell.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Your First Listing
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs text-secondary"></i> Account Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="my-listings.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-list fa-2x d-block mb-2"></i>
                                <strong>My Listings</strong>
                                <small class="d-block text-muted">Manage your items</small>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="sales.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-chart-line fa-2x d-block mb-2"></i>
                                <strong>Sales Dashboard</strong>
                                <small class="d-block text-muted">View your performance</small>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="messages.php" class="btn btn-outline-info w-100">
                                <i class="fas fa-comments fa-2x d-block mb-2"></i>
                                <strong>Messages</strong>
                                <small class="d-block text-muted">Chat with buyers</small>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="../logout.php" class="btn btn-outline-danger w-100" 
                               onclick="return confirm('Are you sure you want to logout?')">
                                <i class="fas fa-sign-out-alt fa-2x d-block mb-2"></i>
                                <strong>Logout</strong>
                                <small class="d-block text-muted">Sign out securely</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include("../components/footer.php"); ?>
