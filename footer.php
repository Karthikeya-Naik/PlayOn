<!-- Footer -->
<footer class="bg-dark text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold mb-3">PlayOn</h5>
                <p>Your ultimate destination for booking box cricket venues in Telangana.</p>
                <div class="mt-4 social-icons">
                    <a href="#" class="text-white me-2" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white me-2" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-2" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="col-md-2 mb-4">
                <h5 class="fw-bold mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="index.php" class="text-white" style="text-decoration:none;"><i class="fas fa-angle-right me-2"></i>Home</a></li>
                    <li class="mb-2"><a href="venues.php" class="text-white" style="text-decoration:none;"><i class="fas fa-angle-right me-2"></i>Venues</a></li>
                    <li class="mb-2"><a href="about.php" class="text-white" style="text-decoration:none;"><i class="fas fa-angle-right me-2"></i>About Us</a></li>
                    <li class="mb-2"><a href="contact.php" class="text-white" style="text-decoration:none;"><i class="fas fa-angle-right me-2"></i>Contact</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5 class="fw-bold mb-3">Contact Us</h5>
                <ul class="list-unstyled">
                    <li class="mb-3"><i class="fas fa-map-marker-alt me-2"></i> Hyderabad, Telangana</li>
                    <li class="mb-3"><i class="fas fa-phone me-2"></i> +91 987-654-3210</li>
                    <li class="mb-3"><i class="fas fa-envelope me-2"></i> info@playon.com</li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5 class="fw-bold mb-3">Newsletter</h5>
                <p>Subscribe to get updates on new venues and special offers</p>
                <form class="newsletter-form">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Your Email" aria-label="Your Email" required>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
                <div class="newsletter-success-message d-none mt-2 text-success">
                    <i class="fas fa-check-circle me-1"></i> Thank you for subscribing!
                </div>
            </div>
        </div>
        <hr class="my-4">
        <div class="text-center">
            <p class="mb-0">&copy; 2025 PlayOn. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Newsletter form handling with animation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const newsletterForm = document.querySelector('.newsletter-form');
    const successMessage = document.querySelector('.newsletter-success-message');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simulate form submission
            const submitButton = this.querySelector('button[type="submit"]');
            const originalContent = submitButton.innerHTML;
            
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            submitButton.disabled = true;
            
            // Simulate API call delay
            setTimeout(function() {
                newsletterForm.reset();
                submitButton.innerHTML = originalContent;
                submitButton.disabled = false;
                
                // Show success message with animation
                successMessage.classList.remove('d-none');
                successMessage.style.animation = 'fadeIn 0.5s ease';
                
                // Hide message after 3 seconds
                setTimeout(function() {
                    successMessage.style.animation = 'fadeOut 0.5s ease';
                    setTimeout(function() {
                        successMessage.classList.add('d-none');
                    }, 500);
                }, 3000);
            }, 1000);
        });
    }
});

// Add animations
document.head.insertAdjacentHTML('beforeend', '
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    </style>
');
</script>