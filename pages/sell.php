<?php
session_start();
include("../includes/db.php");
include("../includes/upload_handler.php");

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$pageTitle = "Sell Something - KaziSellers";
$cssPath = "../assets/css/styles.css";
$isInPages = true;

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $short_description = mysqli_real_escape_string($conn, $_POST['short_description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $condition_type = mysqli_real_escape_string($conn, $_POST['condition_type']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $seller_id = $_SESSION['user_id'];
    
    // Validate required fields
    if (empty($title) || empty($description) || $price <= 0 || empty($category_id)) {
        $message = "Please fill in all required fields and ensure price is greater than 0.";
        $messageType = "danger";
    } else {
        // Insert product
        $insertQuery = "INSERT INTO products (seller_id, category_id, title, description, short_description, price, condition_type, location) 
                       VALUES ('$seller_id', '$category_id', '$title', '$description', '$short_description', '$price', '$condition_type', '$location')";
        
        if (mysqli_query($conn, $insertQuery)) {
            $productId = mysqli_insert_id($conn);
            
            // Handle image uploads
            $imageUploaded = false;
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $uploadCount = 0;
                $totalFiles = count($_FILES['images']['name']);
                
                for ($i = 0; $i < $totalFiles && $i < 5; $i++) { // Max 5 images
                    if ($_FILES['images']['error'][$i] == 0) {
                        $file = [
                            'name' => $_FILES['images']['name'][$i],
                            'tmp_name' => $_FILES['images']['tmp_name'][$i],
                            'error' => $_FILES['images']['error'][$i],
                            'size' => $_FILES['images']['size'][$i]
                        ];
                        
                        $uploadResult = uploadProductImage($file, $productId);
                        if ($uploadResult['success']) {
                            // Insert image record
                            $imagePath = mysqli_real_escape_string($conn, $uploadResult['path']);
                            $isPrimary = ($uploadCount == 0) ? 1 : 0; // First image is primary
                            
                            $imageQuery = "INSERT INTO product_images (product_id, image_path, is_primary, upload_order) 
                                         VALUES ('$productId', '$imagePath', '$isPrimary', '$uploadCount')";
                            mysqli_query($conn, $imageQuery);
                            $uploadCount++;
                            $imageUploaded = true;
                        }
                    }
                }
            }
            
            $message = "Product listed successfully! " . ($imageUploaded ? "Images uploaded." : "No images were uploaded.");
            $messageType = "success";
            
            // Redirect to product details after 2 seconds
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'product-details.php?id=$productId';
                }, 2000);
            </script>";
        } else {
            $message = "Error creating product listing. Please try again.";
            $messageType = "danger";
        }
    }
}

// Get categories for dropdown
$categoryQuery = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$categories = mysqli_query($conn, $categoryQuery);

include("../components/header.php");
?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-container">
                <h2 class="form-title">
                    <i class="fas fa-plus-circle text-primary"></i> List Your Item
                </h2>
                <p class="text-center text-muted mb-4">
                    Fill in the details below to list your item for sale
                </p>

                <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title">Product Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       placeholder="e.g., iPhone 12 Pro Max 128GB" required maxlength="100">
                                <small class="form-text text-muted">Be specific and descriptive</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="price">Price (R) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="price" name="price" 
                                       placeholder="0.00" step="0.01" min="0.01" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_id">Category <span class="text-danger">*</span></label>
                                <select class="form-control" id="category_id" name="category_id" required>
                                    <option value="">Select a category</option>
                                    <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="condition_type">Condition</label>
                                <select class="form-control" id="condition_type" name="condition_type">
                                    <option value="new">Brand New</option>
                                    <option value="like_new">Like New</option>
                                    <option value="good" selected>Good Condition</option>
                                    <option value="fair">Fair Condition</option>
                                    <option value="poor">Poor Condition</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="short_description">Short Description</label>
                        <input type="text" class="form-control" id="short_description" name="short_description" 
                               placeholder="Brief summary for search results" maxlength="200">
                        <small class="form-text text-muted">This appears in search results (optional)</small>
                    </div>

                    <div class="form-group">
                        <label for="description">Full Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="5" 
                                  placeholder="Describe your item in detail - condition, features, reason for selling, etc." required></textarea>
                        <small class="form-text text-muted">Include all relevant details to help buyers make decisions</small>
                    </div>

                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" class="form-control" id="location" name="location" 
                               placeholder="e.g., Cape Town, Johannesburg, Durban" maxlength="100">
                        <small class="form-text text-muted">Help buyers find items near them</small>
                    </div>

                    <div class="form-group">
                        <label for="images">Product Images</label>
                        <input type="file" class="form-control" id="images" name="images[]" 
                               accept="image/jpeg,image/png,image/gif" multiple>
                        <small class="form-text text-muted">
                            Upload up to 5 images (JPEG, PNG, GIF). Max 5MB each. First image will be the main photo.
                        </small>
                        <div id="image-preview-container" class="mt-2"></div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="home.php" class="btn btn-secondary me-md-2">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> List My Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced image preview for multiple files
document.getElementById('images').addEventListener('change', function() {
    const container = document.getElementById('image-preview-container');
    container.innerHTML = '';
    
    const files = this.files;
    if (files.length > 5) {
        alert('Maximum 5 images allowed');
        return;
    }
    
    for (let i = 0; i < Math.min(files.length, 5); i++) {
        const file = files[i];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'd-inline-block me-2 mb-2 position-relative';
                div.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                    ${i === 0 ? '<small class="badge bg-primary position-absolute top-0 start-0">Main</small>' : ''}
                `;
                container.appendChild(div);
            };
            reader.readAsDataURL(file);
        }
    }
});
</script>

<?php include("../components/footer.php"); ?>
