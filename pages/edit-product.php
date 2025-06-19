<?php
session_start();
include("../includes/db.php");
include("../includes/upload_handler.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header("Location: my-listings.php");
    exit;
}

// Get product details
$query = "SELECT * FROM products WHERE id = ? AND seller_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $product_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: my-listings.php");
    exit;
}

$product = mysqli_fetch_assoc($result);

// Get existing images
$imageQuery = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC";
$imageStmt = mysqli_prepare($conn, $imageQuery);
mysqli_stmt_bind_param($imageStmt, "i", $product_id);
mysqli_stmt_execute($imageStmt);
$imageResult = mysqli_stmt_get_result($imageStmt);
$existingImages = mysqli_fetch_all($imageResult, MYSQLI_ASSOC);

$pageTitle = "Edit Product";
$cssPath = "../assets/css/styles.css";
$isInPages = true;

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'delete_image') {
        // Delete image
        $image_id = (int)$_POST['image_id'];
        $deleteQuery = "DELETE FROM product_images WHERE id = ? AND product_id = ?";
        $deleteStmt = mysqli_prepare($conn, $deleteQuery);
        mysqli_stmt_bind_param($deleteStmt, "ii", $image_id, $product_id);
        
        if (mysqli_stmt_execute($deleteStmt)) {
            $message = "Image deleted successfully!";
            $messageType = "success";
        }
    } else {
        // Update product
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $price = floatval($_POST['price']);
        $category_id = intval($_POST['category_id']);
        $condition_type = mysqli_real_escape_string($conn, $_POST['condition_type']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        $availability = mysqli_real_escape_string($conn, $_POST['availability']);
        
        if (empty($title) || empty($description) || $price <= 0) {
            $message = "Please fill in all required fields.";
            $messageType = "danger";
        } else {
            $updateQuery = "UPDATE products SET title = ?, description = ?, price = ?, category_id = ?, condition_type = ?, location = ?, availability = ? WHERE id = ? AND seller_id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "ssdiissii", $title, $description, $price, $category_id, $condition_type, $location, $availability, $product_id, $_SESSION['user_id']);
            
            if (mysqli_stmt_execute($updateStmt)) {
                // Handle new images
                if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                    $uploadCount = 0;
                    foreach ($_FILES['images']['name'] as $key => $name) {
                        if ($_FILES['images']['error'][$key] == 0 && $uploadCount < 3) {
                            $file = [
                                'name' => $_FILES['images']['name'][$key],
                                'tmp_name' => $_FILES['images']['tmp_name'][$key],
                                'error' => $_FILES['images']['error'][$key],
                                'size' => $_FILES['images']['size'][$key]
                            ];
                            
                            $uploadResult = uploadProductImage($file, $product_id);
                            if ($uploadResult['success']) {
                                $imagePath = $uploadResult['path'];
                                $isPrimary = (count($existingImages) == 0 && $uploadCount == 0) ? 1 : 0;
                                
                                $imageInsertQuery = "INSERT INTO product_images (product_id, image_path, is_primary, upload_order) VALUES (?, ?, ?, ?)";
                                $imageInsertStmt = mysqli_prepare($conn, $imageInsertQuery);
                                mysqli_stmt_bind_param($imageInsertStmt, "isii", $product_id, $imagePath, $isPrimary, $uploadCount);
                                mysqli_stmt_execute($imageInsertStmt);
                                $uploadCount++;
                            }
                        }
                    }
                }
                
                $message = "Product updated successfully!";
                $messageType = "success";
                
                // Refresh data
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "ii", $product_id, $_SESSION['user_id']);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $product = mysqli_fetch_assoc($result);
                
                $imageStmt = mysqli_prepare($conn, $imageQuery);
                mysqli_stmt_bind_param($imageStmt, "i", $product_id);
                mysqli_stmt_execute($imageStmt);
                $imageResult = mysqli_stmt_get_result($imageStmt);
                $existingImages = mysqli_fetch_all($imageResult, MYSQLI_ASSOC);
            } else {
                $message = "Error updating product.";
                $messageType = "danger";
            }
        }
    }
}

// Get categories
$categoryQuery = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$categories = mysqli_query($conn, $categoryQuery);

include("../components/header.php");
?>

<div class="container my-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3>Edit Product</h3>
                    <a href="my-listings.php" class="btn btn-secondary btn-sm float-end">Back to Listings</a>
                </div>
                
                <div class="card-body">
                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Current Images -->
                    <?php if (!empty($existingImages)): ?>
                    <div class="mb-4">
                        <h5>Current Images</h5>
                        <div class="row">
                            <?php foreach ($existingImages as $image): ?>
                            <div class="col-md-3 mb-2">
                                <div class="position-relative">
                                    <img src="../uploads/<?php echo htmlspecialchars($image['image_path']); ?>" 
                                         class="img-thumbnail" style="width: 100%; height: 120px; object-fit: cover;">
                                    
                                    <?php if ($image['is_primary']): ?>
                                    <span class="badge bg-success position-absolute top-0 start-0">Primary</span>
                                    <?php endif; ?>
                                    
                                    <form method="POST" class="position-absolute top-0 end-0" 
                                          onsubmit="return confirm('Delete this image?')">
                                        <input type="hidden" name="action" value="delete_image">
                                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">×</button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Edit Form -->
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label>Product Title *</label>
                                    <input type="text" class="form-control" name="title" 
                                           value="<?php echo htmlspecialchars($product['title']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label>Price (R) *</label>
                                    <input type="number" class="form-control" name="price" 
                                           value="<?php echo $product['price']; ?>" step="0.01" min="0.01" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label>Category *</label>
                                    <select class="form-control" name="category_id" required>
                                        <option value="">Select category</option>
                                        <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo ($category['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label>Condition</label>
                                    <select class="form-control" name="condition_type">
                                        <option value="new" <?php echo ($product['condition_type'] == 'new') ? 'selected' : ''; ?>>New</option>
                                        <option value="like_new" <?php echo ($product['condition_type'] == 'like_new') ? 'selected' : ''; ?>>Like New</option>
                                        <option value="good" <?php echo ($product['condition_type'] == 'good') ? 'selected' : ''; ?>>Good</option>
                                        <option value="fair" <?php echo ($product['condition_type'] == 'fair') ? 'selected' : ''; ?>>Fair</option>
                                        <option value="poor" <?php echo ($product['condition_type'] == 'poor') ? 'selected' : ''; ?>>Poor</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label>Status</label>
                                    <select class="form-control" name="availability">
                                        <option value="available" <?php echo ($product['availability'] == 'available') ? 'selected' : ''; ?>>Available</option>
                                        <option value="reserved" <?php echo ($product['availability'] == 'reserved') ? 'selected' : ''; ?>>Reserved</option>
                                        <option value="sold" <?php echo ($product['availability'] == 'sold') ? 'selected' : ''; ?>>Sold</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Description *</label>
                            <textarea class="form-control" name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label>Location</label>
                            <input type="text" class="form-control" name="location" 
                                   value="<?php echo htmlspecialchars($product['location']); ?>">
                        </div>

                        <div class="mb-3">
                            <label>Add More Images</label>
                            <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
                            <small class="text-muted">You can upload up to 3 more images</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="my-listings.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../components/footer.php"); ?>
