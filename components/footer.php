</main>

<footer class="mt-5 py-4" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); color: white;">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-store"></i> KaziSellers</h5>
                <p class="mb-2">South Africa's premier student marketplace</p>
                <p class="small">Connecting students across the country for safe, easy trading</p>
            </div>
            <div class="col-md-3">
                <h6>Quick Links</h6>
                <ul class="list-unstyled">
                    <?php if (isset($isInPages) && $isInPages): ?>
                        <li><a href="../index.php" class="text-light text-decoration-none">Home</a></li>
                        <li><a href="../categories.php" class="text-light text-decoration-none">Categories</a></li>
                        <li><a href="../register.php" class="text-light text-decoration-none">Join Us</a></li>
                    <?php else: ?>
                        <li><a href="index.php" class="text-light text-decoration-none">Home</a></li>
                        <li><a href="categories.php" class="text-light text-decoration-none">Categories</a></li>
                        <li><a href="register.php" class="text-light text-decoration-none">Join Us</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-3">
                <h6>Support</h6>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-light text-decoration-none">Help Center</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Safety Tips</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Contact Us</a></li>
                </ul>
            </div>
        </div>
        <hr class="my-4" style="border-color: rgba(255, 255, 255, 0.2);">
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="mb-0 small">&copy; <?php echo date('Y'); ?> KaziSellers. Built by students, for students.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <small class="text-muted">
                    <i class="fas fa-heart text-danger"></i> Made with love in South Africa
                </small>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JavaScript -->
<script src="<?php echo isset($cssPath) ? str_replace('css/styles.css', 'js/app.js', $cssPath) : '../assets/js/app.js'; ?>"></script>

</body>
</html>
