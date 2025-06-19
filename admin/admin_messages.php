<?php
session_start();

// Simple admin check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../pages/login.php');
    exit();
}

include '../includes/db.php';

$message = '';

// Handle message deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $message_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    if ($stmt->execute()) {
        $message = "Message deleted successfully!";
    } else {
        $message = "Error deleting message.";
    }
}

// Get all messages with user info
$messages = $conn->query("
    SELECT m.id, m.message, m.created_at, 
           sender.username as sender_name, 
           receiver.username as receiver_name
    FROM messages m 
    JOIN users sender ON m.sender_id = sender.id 
    JOIN users receiver ON m.receiver_id = receiver.id 
    ORDER BY m.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Messages - Admin Panel</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Admin Styles -->
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .message-text {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .user-badge {
            background: #667eea;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .message-text {
                max-width: 150px;
            }
        }
    </style>
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
            <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="admin_products.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="admin_categories.php"><i class="fas fa-tags"></i> Categories</a></li>
            <li><a href="admin_messages.php" class="active"><i class="fas fa-envelope"></i> Messages</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="content-box">
            <h2><i class="fas fa-envelope"></i> Manage Messages</h2>
            
            <?php if ($message): ?>
                <div class="message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="searchInput"><i class="fas fa-search"></i> Search Messages:</label>
                <input type="text" id="searchInput" placeholder="Search messages by sender or content..." onkeyup="searchMessages()">
            </div>
            
            <table id="messagesTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-user"></i> From</th>
                        <th><i class="fas fa-user"></i> To</th>
                        <th><i class="fas fa-comment"></i> Message</th>
                        <th><i class="fas fa-calendar"></i> Date</th>
                        <th><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($messages->num_rows > 0): ?>
                        <?php while ($msg = $messages->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $msg['id']; ?></td>
                                <td>
                                    <span class="user-badge"><?php echo htmlspecialchars($msg['sender_name']); ?></span>
                                </td>
                                <td>
                                    <span class="user-badge"><?php echo htmlspecialchars($msg['receiver_name']); ?></span>
                                </td>
                                <td>
                                    <div class="message-text" title="<?php echo htmlspecialchars($msg['message']); ?>">
                                        <?php echo htmlspecialchars($msg['message']); ?>
                                    </div>
                                </td>
                                <td><?php echo date('M j, Y H:i', strtotime($msg['created_at'])); ?></td>
                                <td>
                                    <a href="?delete=<?php echo $msg['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this message? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-envelope-open"></i>
                                <p>No messages found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function searchMessages() {
            var input = document.getElementById("searchInput");
            var filter = input.value.toLowerCase();
            var table = document.getElementById("messagesTable");
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var sender = rows[i].getElementsByTagName("td")[1];
                var message = rows[i].getElementsByTagName("td")[3];
                
                if (sender && message) {
                    var senderText = sender.textContent || sender.innerText;
                    var messageText = message.textContent || message.innerText;
                    
                    if (senderText.toLowerCase().indexOf(filter) > -1 || 
                        messageText.toLowerCase().indexOf(filter) > -1) {
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
