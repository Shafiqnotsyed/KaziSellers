<?php
/**
 * Simple Image Upload Handler for KaziSellers
 * Handles product image uploads with basic security validation
 */

function uploadProductImage($file, $productId = null) {
    $uploadDir = '../uploads/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB max file size
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file upload
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload failed'];
    }
    
    // Check file size
    if ($file['size'] > $maxFileSize) {
        return ['success' => false, 'error' => 'File size too large (max 5MB)'];
    }
    
    // Check file type
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, and GIF allowed'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('product_') . '_' . time() . '.' . $extension;
    $filePath = $uploadDir . $fileName;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return [
            'success' => true,
            'filename' => $fileName,
            'path' => 'uploads/' . $fileName,
            'full_path' => $filePath
        ];
    } else {
        return ['success' => false, 'error' => 'Failed to move uploaded file'];
    }
}

function deleteProductImage($imagePath) {
    $fullPath = '../' . $imagePath;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

function validateImageFile($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return 'File upload failed';
    }
    
    if ($file['size'] > $maxFileSize) {
        return 'File size too large (max 5MB)';
    }
    
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        return 'Invalid file type. Only JPG, PNG, and GIF allowed';
    }
    
    return true;
}
?>
