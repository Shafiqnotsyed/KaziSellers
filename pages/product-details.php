<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header("Location: home.php");
    exit;
}

// Get product details
$query = "SELECT p.*, c.name AS category_name, u.username AS seller_name, u.first_name, u.last_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id
          LEFT JOIN users u ON p.seller_id = u.id
          WHERE p.id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: home.php");
    exit;
}

$product = mysqli_fetch_assoc($result);

// Update view count
if ($_SESSION['user_id'] != $product['seller_id']) {
    $updateViewQuery = "UPDATE products SET views = views + 1 WHERE id = ?";
    $updateStmt = mysqli_prepare($conn, $updateViewQuery);
    mysqli_stmt_bind_param($updateStmt, "i", $product_id);
    mysqli_stmt_execute($updateStmt);
}

// Get product images
$imageQuery = "SELECT image_path, is_primary FROM product_images WHERE product_id = ? ORDER BY is_primary DESC";
$imageStmt = mysqli_prepare($conn, $imageQuery);
mysqli_stmt_bind_param($imageStmt, "i", $product_id);
mysqli_stmt_execute($imageStmt);
$imageResult = mysqli_stmt_get_result($imageStmt);
$images = mysqli_fetch_all($imageResult, MYSQLI_ASSOC);

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rating'])) {
    $rating = (int)$_POST['rating'];
    $review = trim($_POST['review'] ?? '');
    
    if ($rating >= 1 && $rating <= 5 && $_SESSION['user_id'] != $product['seller_id']) {
        // Check if already rated
        $checkQuery = "SELECT id FROM product_ratings WHERE product_id = ? AND user_id = ?";
        $checkStmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, "ii", $product_id, $_SESSION['user_id']);
        mysqli_stmt_execute($checkStmt);
        $existingRating = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($existingRating) == 0) {
            $insertRatingQuery = "INSERT INTO product_ratings (product_id, user_id, rating, review) VALUES (?, ?, ?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertRatingQuery);
            mysqli_stmt_bind_param($insertStmt, "iiis", $product_id, $_SESSION['user_id'], $rating, $review);
            mysqli_stmt_execute($insertStmt);
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}

// Get ratings
$ratingsQuery = "SELECT pr.*, u.username, u.first_name, u.last_name 
                FROM product_ratings pr 
                JOIN users u ON pr.user_id = u.id 
                WHERE pr.product_id = ? 
                ORDER BY pr.created_at DESC";
$ratingsStmt = mysqli_prepare($conn, $ratingsQuery);
mysqli_stmt_bind_param($ratingsStmt, "i", $product_id);
mysqli_stmt_execute($ratingsStmt);
$ratingsResult = mysqli_stmt_get_result($ratingsStmt);

// Calculate average rating
$avgQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings FROM product_ratings WHERE product_id = ?";
$avgStmt = mysqli_prepare($conn, $avgQuery);
mysqli_stmt_bind_param($avgStmt, "i", $product_id);
mysqli_stmt_execute($avgStmt);
$avgResult = mysqli_fetch_assoc(mysqli_stmt_get_result($avgStmt));

$averageRating = round($avgResult['avg_rating'], 1);
$totalRatings = $avgResult['total_ratings'];

// Check if user has rated
$userRatedQuery = "SELECT id FROM product_ratings WHERE product_id = ? AND user_id = ?";
$userRatedStmt = mysqli_prepare($conn, $userRatedQuery);
mysqli_stmt_bind_param($userRatedStmt, "ii", $product_id, $_SESSION['user_id']);
mysqli_stmt_execute($userRatedStmt);
$userHasRated = mysqli_num_rows(mysqli_stmt_get_result($userRatedStmt)) > 0;

$pageTitle = $product['title'];
$cssPath = "../assets/css/styles.css";
$isInPages = true;

include("../components/header.php");
?>

<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white p-3 rounded">
            <li class="breadcrumb-item"><a href="home.php">Home</a></li>
            <li class="breadcrumb-item"><a href="categories.php"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['title']); ?></li>
        </ol>
    </nav>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4">
            <?php if (!empty($images)): ?>
                <!-- Main Image -->
                <div class="mb-3">
                    <?php 
                    $mainImagePath = 'https://via.placeholder.com/600x400?text=No+Image';
                    if (!empty($images[0]['image_path'])) {
                        $imagePath = $images[0]['image_path'];
                        if (str_starts_with($imagePath, 'uploads/')) {
                            $mainImagePath = '../' . $imagePath;
                        } else {
                            $mainImagePath = '../uploads/' . $imagePath;
                        }
                    }
                    ?>
                    <img id="mainImage" src="<?php echo htmlspecialchars($mainImagePath); ?>" 
                         class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['title']); ?>"
                         style="width: 100%; height: 400px; object-fit: cover;"
                         onerror="this.src='https://via.placeholder.com/600x400?text=No+Image'">
                </div>
                
                <!-- Thumbnail Images -->
                <?php if (count($images) > 1): ?>
                <div class="row">
                    <?php foreach ($images as $image): ?>
                    <div class="col-3 mb-2">
                        <?php 
                        $thumbPath = 'https://via.placeholder.com/100x100?text=No+Image';
                        if (!empty($image['image_path'])) {
                            if (str_starts_with($image['image_path'], 'uploads/')) {
                                $thumbPath = '../' . $image['image_path'];
                            } else {
                                $thumbPath = '../uploads/' . $image['image_path'];
                            }
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($thumbPath); ?>" 
                             class="img-thumbnail" 
                             style="height: 80px; object-fit: cover; cursor: pointer;"
                             onclick="document.getElementById('mainImage').src=this.src"
                             onerror="this.src='https://via.placeholder.com/100x100?text=No+Image'">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <img src="https://via.placeholder.com/600x400?text=No+Image" 
                     class="img-fluid rounded" style="width: 100%; height: 400px; object-fit: cover;">
            <?php endif; ?>
        </div>

        <!-- Product Details -->
        <div class="col-lg-6">
            <h1><?php echo htmlspecialchars($product['title']); ?></h1>
            <h2 class="text-success">R<?php echo number_format($product['price'], 2); ?></h2>
            
            <!-- Status -->
            <div class="mb-3">
                <?php if ($product['availability'] === 'available'): ?>
                    <span class="badge bg-success">Available</span>
                <?php elseif ($product['availability'] === 'reserved'): ?>
                    <span class="badge bg-warning">Reserved</span>
                <?php else: ?>
                    <span class="badge bg-danger">Sold</span>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <strong>Category:</strong><br>
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </div>
                        <div class="col-6">
                            <strong>Condition:</strong><br>
                            <?php echo ucfirst(str_replace('_', ' ', $product['condition_type'])); ?>
                        </div>
                        <div class="col-6 mt-2">
                            <strong>Views:</strong><br>
                            <?php echo number_format($product['views']); ?>
                        </div>
                        <div class="col-6 mt-2">
                            <strong>Listed:</strong><br>
                            <?php echo date('M d, Y', strtotime($product['created_at'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <?php if ($_SESSION['user_id'] != $product['seller_id'] && $product['availability'] === 'available'): ?>
            <div class="mb-4">
<a href="messages.php?product=<?php echo $product['id']; ?>&recipient_id=<?php echo $product['seller_id']; ?>" 
   class="btn btn-primary btn-lg w-100 mb-2">Contact Seller</a>
                
                <div class="row">
                    <div class="col-6">
                        <form method="POST" action="wishlist.php">
                            <input type="hidden" name="action" value="add_to_wishlist">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="btn btn-outline-danger w-100">Add to Wishlist</button>
                        </form>
                    </div>
                    <div class="col-6">
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="action" value="add_to_cart">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="btn btn-outline-primary w-100">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php elseif ($_SESSION['user_id'] == $product['seller_id']): ?>
            <div class="mb-4">
                <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-warning btn-lg w-100">Edit Listing</a>
            </div>
            <?php endif; ?>

            <!-- Seller Info -->
            <div class="card">
                <div class="card-header">
                    <h5>Seller Information</h5>
                </div>
                <div class="card-body">
                    <h6><?php echo htmlspecialchars($product['first_name'] . ' ' . $product['last_name']); ?></h6>
                    <p class="text-muted">@<?php echo htmlspecialchars($product['seller_name']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Description</h5>
        </div>
        <div class="card-body">
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
    </div>

    <!-- Ratings Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Product Ratings
            <?php if ($totalRatings > 0): ?>
                <small class="text-muted">(<?php echo $averageRating; ?>/5 from <?php echo $totalRatings; ?> review<?php echo $totalRatings != 1 ? 's' : ''; ?>)</small>
            <?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
                    <!-- Average Rating -->
                    <?php if ($totalRatings > 0): ?>
                    <div class="text-center mb-4">
                        <div class="mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="<?php echo $i <= $averageRating ? 'text-warning' : 'text-muted'; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <strong><?php echo $averageRating; ?> out of 5</strong>
                    </div>
                    <?php endif; ?>

                    <!-- Rating Form -->
                    <?php if ($_SESSION['user_id'] != $product['seller_id'] && !$userHasRated): ?>
                    <form method="POST" class="mb-4">
                        <h6>Rate this product:</h6>
                        <div class="mb-3">
                            <label>Your Rating:</label><br>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" required>
                            <label for="star<?php echo $i; ?>">★</label>
                            <?php endfor; ?>
                        </div>
                        <div class="mb-3">
                            <label>Review (Optional):</label>
                            <textarea name="review" class="form-control" rows="3" maxlength="500"></textarea>
                        </div>
                        <button type="submit" name="submit_rating" class="btn btn-primary">Submit Rating</button>
                    </form>
                    <?php elseif ($_SESSION['user_id'] == $product['seller_id']): ?>
                    <p class="text-muted">You cannot rate your own product.</p>
                    <?php elseif ($userHasRated): ?>
                    <p class="text-success">Thank you! You have already rated this product.</p>
                    <?php endif; ?>

                    <!-- Existing Ratings -->
                    <?php if ($totalRatings > 0): ?>
                    <h6>Customer Reviews:</h6>
                    <?php while ($rating = mysqli_fetch_assoc($ratingsResult)): ?>
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between">
                            <strong><?php echo htmlspecialchars($rating['first_name'] . ' ' . $rating['last_name']); ?></strong>
                            <div>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="<?php echo $i <= $rating['rating'] ? 'text-warning' : 'text-muted'; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php if (!empty($rating['review'])): ?>
                        <p class="mt-2"><?php echo nl2br(htmlspecialchars($rating['review'])); ?></p>
                        <?php endif; ?>
                        <small class="text-muted"><?php echo date('M j, Y', strtotime($rating['created_at'])); ?></small>
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <p class="text-center text-muted">No ratings yet. Be the first to rate this product!</p>
                    <?php endif; ?>
        </div>
    </div>
</div>

<style>
input[type="radio"] {
    display: none;
}

input[type="radio"] + label {
    font-size: 2em;
    color: #ddd;
    cursor: pointer;
}

input[type="radio"]:checked + label,
input[type="radio"]:checked + label ~ label {
    color: #ffc107;
}

label:hover,
label:hover ~ label {
    color: #ffc107;
}
</style>

<?php include("../components/footer.php"); ?>
