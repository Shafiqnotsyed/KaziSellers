<?php
session_start();

// Simple admin check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../pages/login.php');
    exit();
}

include '../includes/db.php';

$message = '';

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $message = "User deleted successfully!";
    } else {
        $message = "Error deleting user.";
    }
}

// Get all users
$users = $conn->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Admin Panel</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Admin Styles -->
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-cogs"></i> Admin Panel</h1>
        <div>
            Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
            <span class="admin-badge">ADMIN</span>
            <a href="admin_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <nav class="nav">
        <ul>
            <li><a href="admin.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="admin_users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="admin_products.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="admin_categories.php"><i class="fas fa-tags"></i> Categories</a></li>
            <li><a href="admin_messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="content-box">
            <h2><i class="fas fa-users"></i> Manage Users</h2>
            
            <?php if ($message): ?>
                <div class="message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="searchInput"><i class="fas fa-search"></i> Search Users:</label>
                <input type="text" id="searchInput" placeholder="Search users by username or email..." onkeyup="searchUsers()">
            </div>
            
            <table id="usersTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-user"></i> Username</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-calendar"></i> Joined Date</th>
                        <th><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users->num_rows > 0): ?>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="?delete=<?php echo $user['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete user \'<?php echo htmlspecialchars($user['username']); ?>\'? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-state">
                                <i class="fas fa-users"></i>
                                <p>No users found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function searchUsers() {
            var input = document.getElementById("searchInput");
            var filter = input.value.toLowerCase();
            var table = document.getElementById("usersTable");
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var username = rows[i].getElementsByTagName("td")[1];
                var email = rows[i].getElementsByTagName("td")[2];
                
                if (username && email) {
                    var usernameText = username.textContent || username.innerText;
                    var emailText = email.textContent || email.innerText;
                    
                    if (usernameText.toLowerCase().indexOf(filter) > -1 || 
                        emailText.toLowerCase().indexOf(filter) > -1) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</body>
</html>
