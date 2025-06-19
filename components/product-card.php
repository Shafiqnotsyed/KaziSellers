<?php
/**
 * Product Card Component
 * Displays a product in a consistent card format
 * 
 * Expected variables:
 * $product - array with product data (id, title, price, image, location, etc.)
 */

if (!isset($product)) {
    return;
}

// FIXED: Get primary image or use default - Improved path handling
$primaryImage = 'https://via.placeholder.com/300x200?text=No+Image';
if (!empty($product['image'])) {
    $imagePath = $product['image'];
    
    // Handle different path formats
    if (str_starts_with($imagePath, 'http')) {
        // Already a full URL
        $primaryImage = $imagePath;
    } else {
        // Local file - ensure proper path construction
        if (str_starts_with($imagePath, 'uploads/')) {
            // Already has uploads/ prefix
            $primaryImage = (isset($isInPages) ? '../' : '') . $imagePath;
        } else {
            // Add uploads/ prefix and handle page context
            $primaryImage = (isset($isInPages) ? '../' : '') . 'uploads/' . $imagePath;
        }
    }
}

// Format price
$formattedPrice = 'R' . number_format($product['price'], 2);

// Truncate description
$description = isset($product['short_description']) ? $product['short_description'] : '';
if (strlen($description) > 100) {
    $description = substr($description, 0, 100) . '...';
}

// Format date
$timeAgo = '';
if (isset($product['created_at'])) {
    $createdAt = new DateTime($product['created_at']);
    $now = new DateTime();
    $interval = $now->diff($createdAt);
    
    if ($interval->days == 0) {
        $timeAgo = 'Today';
    } elseif ($interval->days == 1) {
        $timeAgo = 'Yesterday';
    } elseif ($interval->days < 7) {
        $timeAgo = $interval->days . ' days ago';
    } else {
        $timeAgo = $createdAt->format('M d, Y');
    }
}
?>

<div class="col">
    <div class="card product-card h-100">
        <div class="position-relative">
            <img src="<?php echo htmlspecialchars($primaryImage); ?>" 
                 class="card-img-top product-image" 
                 alt="<?php echo htmlspecialchars($product['title']); ?>"
                 onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
            
            <?php if (isset($product['condition_type']) && $product['condition_type'] !== 'good'): ?>
            <span class="badge bg-info position-absolute top-0 end-0 m-2">
                <?php echo ucfirst(str_replace('_', ' ', $product['condition_type'])); ?>
            </span>
            <?php endif; ?>
            
            <?php if (isset($product['availability']) && $product['availability'] !== 'available'): ?>
            <span class="badge bg-warning position-absolute top-0 start-0 m-2">
                <?php echo ucfirst($product['availability']); ?>
            </span>
            <?php endif; ?>
        </div>
        
        <div class="card-body d-flex flex-column">
            <h5 class="card-title product-title">
                <?php echo htmlspecialchars($product['title']); ?>
            </h5>
            
            <?php if (!empty($description)): ?>
            <p class="card-text product-description text-muted">
                <?php echo htmlspecialchars($description); ?>
            </p>
            <?php endif; ?>
            
            <div class="product-price fw-bold text-primary fs-4">
                <?php echo $formattedPrice; ?>
            </div>
            
            <?php if (!empty($product['location'])): ?>
            <p class="product-location text-muted small mb-2">
                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($product['location']); ?>
            </p>
            <?php endif; ?>
            
            <?php if (!empty($timeAgo)): ?>
            <p class="text-muted small mb-3">
                <i class="fas fa-clock"></i> <?php echo $timeAgo; ?>
            </p>
            <?php endif; ?>
            
            <div class="mt-auto">
                <div class="d-flex gap-2 mb-2">
                    <a href="<?php echo isset($isInPages) ? '' : 'pages/'; ?>product-details.php?id=<?php echo $product['id']; ?>" 
                       class="btn btn-primary flex-fill">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                    
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $product['seller_id']): ?>
                    <a href="<?php echo isset($isInPages) ? '' : 'pages/'; ?>edit-product.php?id=<?php echo $product['id']; ?>" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-edit"></i>
                    </a>
                    <?php endif; ?>
                </div>
                
                <!-- ADDED: Simple Add to Cart Button -->
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $product['seller_id'] && 
                          isset($product['availability']) && $product['availability'] === 'available'): ?>
                <form method="POST" action="<?php echo isset($isInPages) ? '../' : ''; ?>cart.php" class="w-100">
                    <input type="hidden" name="action" value="add_to_cart">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-outline-success w-100 btn-sm">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
