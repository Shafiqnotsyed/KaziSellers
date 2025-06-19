<?php
session_start();
include("../includes/db.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        // Set common session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['user_role'] = $user['role'];
        
        // Check if user is admin (role = 1)
        if ($user['role'] == 1) {
            // Admin user - set admin session variables and redirect to admin dashboard
            $_SESSION['is_admin'] = true;
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $user['username'];
            header("Location: ../admin/admin.php");
            exit;
        } else {
            // Regular user - redirect to normal user dashboard
            $_SESSION['is_admin'] = false;
            header("Location: home.php");
            exit;
        }
    } else {
        $error_message = "Invalid email or password!";
    }
}

$pageTitle = "Login - KaziSellers";
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
                <li><a href="register.php"><i class="fas fa-user-plus"></i> Register</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="container-fluid">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="form-container">
                    <h2 class="form-title">
                        <i class="fas fa-sign-in-alt text-primary"></i> <span class="greeting-text">Welcome</span> Back
                    </h2>
                    <p class="text-center text-muted mb-4">
                        Sign in to your KaziSellers account
                    </p>

                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="Enter your email address" required 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Enter your password" required>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Sign In
                            </button>
                        </div>

                        <div class="text-center">
                            <p class="text-muted">
                                Don't have an account? 
                                <a href="register.php" class="text-decoration-none">
                                    <strong>Register here</strong>
                                </a>
                            </p>
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
