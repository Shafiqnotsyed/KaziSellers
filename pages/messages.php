<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$pageTitle = "Messages - KaziSellers";
$cssPath = "../assets/css/styles.css";
$isInPages = true;

$user_id = $_SESSION['user_id'];

// Send new message
if ($_POST['action'] ?? '' === 'send_message' && isset($_POST['product_id']) && isset($_POST['receiver_id']) && isset($_POST['message'])) {
    $product_id = (int)$_POST['product_id'];
    $receiver_id = (int)$_POST['receiver_id'];
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        $insertQuery = "INSERT INTO messages (product_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($stmt, "iiis", $product_id, $user_id, $receiver_id, $message);
        mysqli_stmt_execute($stmt);
    }
}

// Get conversations
$conversationsQuery = "SELECT p.id as product_id, p.title, p.price,
                      u1.username as other_user, u1.id as other_user_id,
                      m.message as last_message, m.created_at as last_message_time,
                      COUNT(CASE WHEN m.is_read = 0 AND m.receiver_id = ? THEN 1 END) as unread_count
                      FROM messages m
                      JOIN products p ON m.product_id = p.id
                      JOIN users u1 ON (CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END) = u1.id
                      WHERE m.sender_id = ? OR m.receiver_id = ?
                      GROUP BY p.id, u1.id
                      ORDER BY m.created_at DESC";

$stmt = mysqli_prepare($conn, $conversationsQuery);
mysqli_stmt_bind_param($stmt, "iiii", $user_id, $user_id, $user_id, $user_id);
mysqli_stmt_execute($stmt);
$conversations = mysqli_stmt_get_result($stmt);

// Get selected conversation
$selectedProductId = $_GET['product'] ?? 0;
$selectedMessages = null;
$selectedProduct = null;
$otherUser = null;

if ($selectedProductId) {
    // Get product details
    $productQuery = "SELECT p.*, u.username as seller_name, u.id as seller_id
                     FROM products p 
                     JOIN users u ON p.seller_id = u.id
                     WHERE p.id = ?";
    $stmt = mysqli_prepare($conn, $productQuery);
    mysqli_stmt_bind_param($stmt, "i", $selectedProductId);
    mysqli_stmt_execute($stmt);
    $selectedProduct = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    if ($selectedProduct) {
        // Get messages
        $messagesQuery = "SELECT m.*, u.username as sender_name 
                         FROM messages m
                         JOIN users u ON m.sender_id = u.id
                         WHERE m.product_id = ? AND (m.sender_id = ? OR m.receiver_id = ?)
                         ORDER BY m.created_at ASC";
        $stmt = mysqli_prepare($conn, $messagesQuery);
        mysqli_stmt_bind_param($stmt, "iii", $selectedProductId, $user_id, $user_id);
        mysqli_stmt_execute($stmt);
        $selectedMessages = mysqli_stmt_get_result($stmt);
        
// Get other user - either from existing conversation or from recipient_id parameter
        $otherUserId = isset($_GET['recipient_id']) ? (int)$_GET['recipient_id'] : (
            ($selectedProduct['seller_id'] == $user_id) ? 
            (mysqli_fetch_assoc(mysqli_query($conn, "SELECT DISTINCT sender_id FROM messages WHERE product_id = $selectedProductId AND sender_id != $user_id LIMIT 1"))['sender_id'] ?? 0) :
            $selectedProduct['seller_id']
        );
        
        if ($otherUserId) {
            $otherUserQuery = "SELECT * FROM users WHERE id = ?";
            $stmt = mysqli_prepare($conn, $otherUserQuery);
            mysqli_stmt_bind_param($stmt, "i", $otherUserId);
            mysqli_stmt_execute($stmt);
            $otherUser = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        }
        
        // Mark as read
        $updateQuery = "UPDATE messages SET is_read = 1 WHERE product_id = ? AND receiver_id = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "ii", $selectedProductId, $user_id);
        mysqli_stmt_execute($stmt);
    }
}

include("../components/header.php");
?>

<div class="container my-4">
    <div class="row">
        <!-- Conversations List -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4>Conversations</h4>
                </div>
                <div class="card-body p-0">
                    <?php if (mysqli_num_rows($conversations) > 0): ?>
                        <?php while ($conv = mysqli_fetch_assoc($conversations)): ?>
                        <div class="border-bottom p-3 <?php echo $selectedProductId == $conv['product_id'] ? 'bg-light' : ''; ?>">
                            <a href="?product=<?php echo $conv['product_id']; ?>" class="text-decoration-none">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars(substr($conv['title'], 0, 30)); ?></h6>
                                        <small class="text-muted">with <?php echo htmlspecialchars($conv['other_user']); ?></small>
                                        <p class="mb-1 small"><?php echo htmlspecialchars(substr($conv['last_message'], 0, 40)) . '...'; ?></p>
                                    </div>
                                    <div class="text-end">
                                        <small><?php echo date('M j', strtotime($conv['last_message_time'])); ?></small>
                                        <?php if ($conv['unread_count'] > 0): ?>
                                        <br><span class="badge bg-danger"><?php echo $conv['unread_count']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                    <div class="p-4 text-center">
                        <p>No conversations yet</p>
                        <small>Start chatting by contacting sellers!</small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="col-lg-8">
            <?php if ($selectedProduct): ?>
            <div class="card">
                <!-- Chat Header -->
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5><?php echo htmlspecialchars($selectedProduct['title']); ?></h5>
                            <small>R<?php echo number_format($selectedProduct['price'], 2); ?></small>
                            <?php if ($otherUser): ?>
                            <small class="text-muted">• Chatting with <?php echo htmlspecialchars($otherUser['username']); ?></small>
                            <?php endif; ?>
                        </div>
                        <a href="product-details.php?id=<?php echo $selectedProduct['id']; ?>" class="btn btn-outline-primary btn-sm">
                            View Item
                        </a>
                    </div>
                </div>

                <!-- Messages -->
                <div class="card-body" style="height: 400px; overflow-y: auto;">
                    <?php if (mysqli_num_rows($selectedMessages) > 0): ?>
                        <?php while ($message = mysqli_fetch_assoc($selectedMessages)): ?>
                        <div class="mb-3">
                            <div class="<?php echo $message['sender_id'] == $user_id ? 'text-end' : 'text-start'; ?>">
                                <div class="d-inline-block p-2 rounded <?php echo $message['sender_id'] == $user_id ? 'bg-primary text-white' : 'bg-light'; ?>" style="max-width: 70%;">
                                    <?php if ($message['sender_id'] != $user_id): ?>
                                    <small class="fw-bold"><?php echo htmlspecialchars($message['sender_name']); ?></small><br>
                                    <?php endif; ?>
                                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                    <br><small class="opacity-75"><?php echo date('M j, g:i A', strtotime($message['created_at'])); ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <p>No messages yet. Start the conversation!</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Message Input -->
                <div class="card-footer">
                    <form method="POST" class="d-flex gap-2">
                        <input type="hidden" name="action" value="send_message">
                        <input type="hidden" name="product_id" value="<?php echo $selectedProduct['id']; ?>">
                        <input type="hidden" name="receiver_id" value="<?php echo $otherUser['id'] ?? 0; ?>">
                        
                        <textarea name="message" class="form-control" rows="2" placeholder="Type your message..." required></textarea>
                        <button type="submit" class="btn btn-primary">Send</button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <!-- No conversation selected -->
            <div class="card">
                <div class="card-body text-center py-5">
                    <h4>Select a conversation</h4>
                    <p>Choose a conversation from the left to start chatting</p>
                    <a href="home.php" class="btn btn-primary">Browse Items</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include("../components/footer.php"); ?>
