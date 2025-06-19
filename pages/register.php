<?php
session_start();
include("../includes/db.php");

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);

    // Check if username or email already exists
    $checkQuery = "SELECT * FROM users WHERE username='$username' OR email='$email'";
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if (mysqli_num_rows($checkResult) > 0) {
        $message = "Username or email already exists. Please choose different ones.";
        $messageType = "danger";
    } else {
        $query = "INSERT INTO users (username, password, first_name, last_name, email, phone, location, role) 
                  VALUES ('$username', '$password', '$first_name', '$last_name', '$email', '$phone', '$location', 0)";
        
        if (mysqli_query($conn, $query)) {
            $message = "Registration successful! You can now log in.";
            $messageType = "success";
            
            // Redirect to login after 2 seconds
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 2000);
            </script>";
        } else {
            $message = "Registration failed. Please try again.";
            $messageType = "danger";
        }
    }
}

$pageTitle = "Register - KaziSellers";
$cssPath = "../assets/css/styles.css";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?php echo $cssPath; ?>">
</head>
<body>

<header>
    <div class="container clearfix">
        <div id="logo">
            <a href="../index.php" style="text-decoration: none; color: inherit;">
                <i class="fas fa-store"></i> KaziSellers
            </a>
        </div>
        <nav>
            <ul class="nav-links">
                <li><a href="../index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="container-fluid">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="form-container">
                    <h2 class="form-title">
                        <i class="fas fa-user-plus text-primary"></i> <span class="greeting-text">Welcome</span> to KaziSellers
                    </h2>
                    <p class="text-center text-muted mb-4">
                        Create your account and start buying & selling today
                    </p>

                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php if ($messageType == 'success'): ?>
                            <i class="fas fa-check-circle"></i>
                        <?php else: ?>
                            <i class="fas fa-exclamation-triangle"></i>
                        <?php endif; ?>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">
                                        <i class="fas fa-user"></i> First Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           placeholder="Your first name" required maxlength="50"
                                           value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name">
                                        <i class="fas fa-user"></i> Last Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           placeholder="Your last name" required maxlength="50"
                                           value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="username">
                                <i class="fas fa-at"></i> Username <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Choose a unique username" required maxlength="50"
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                            <small class="form-text text-muted">This will be your unique identifier on KaziSellers</small>
                        </div>

                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="your.email@example.com" required maxlength="100"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="phone">
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   placeholder="e.g., 083 123 4567" maxlength="20"
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                            <small class="form-text text-muted">Optional - helps with local meetups</small>
                        </div>

                        <div class="form-group">
                            <label for="location">
                                <i class="fas fa-map-marker-alt"></i> Location
                            </label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   placeholder="e.g., Cape Town, Johannesburg, Durban" maxlength="100"
                                   value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
                            <small class="form-text text-muted">Help others find items near them</small>
                        </div>

                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock"></i> Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Create a strong password" required minlength="6">
                            <small class="form-text text-muted">Minimum 6 characters</small>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i> Create My Account
                            </button>
                        </div>

                        <div class="text-center">
                            <p class="text-muted">
                                Already have an account? 
                                <a href="login.php" class="text-decoration-none">
                                    <strong>Sign in here</strong>
                                </a>
                            </p>
                        </div>

                        <div class="text-center mt-4">
                            <small class="text-muted">
                                By creating an account, you agree to our Terms of Service and Privacy Policy
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="mt-5 py-4" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); color: white;">
    <div class="container">
        <div class="text-center">
            <p class="mb-0 small">&copy; <?php echo date('Y'); ?> KaziSellers. Built by students, for students.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JavaScript -->
<script src="../assets/js/app.js"></script>

</body>
</html>
