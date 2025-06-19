/**
 * KaziSellers Main JavaScript File
 * Enhanced user experience and performance optimizations
 */

// Prevent FOUC (Flash of Unstyled Content)
document.documentElement.style.visibility = 'hidden';
document.addEventListener('DOMContentLoaded', function() {
    document.documentElement.style.visibility = 'visible';
});

// Main application object
const KaziSellers = {
    // Initialize the application
    init: function() {
        this.setupLoadingStates();
        this.setupImageOptimization();
        this.setupFormValidations();
        this.setupSmoothScrolling();
        this.setupTooltips();
        this.setupAutoRefresh();
        this.setupLazyLoading();
        this.setupDropdownNavigation();
    },

    // Add loading states to forms and buttons
    setupLoadingStates: function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                    submitBtn.disabled = true;
                    
                    // Re-enable after timeout (fallback)
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 10000);
                }
            });
        });
    },

    // Optimize image loading and prevent layout shifts
    setupImageOptimization: function() {
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            // Add loading attribute for native lazy loading
            if (!img.hasAttribute('loading')) {
                img.setAttribute('loading', 'lazy');
            }
            
            // Handle image load errors
            img.addEventListener('error', function() {
                this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjBmMGYwIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==';
                this.alt = 'Image not available';
            });
        });

        // Image preview for file uploads
        const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                this.handleImagePreview(this);
            }.bind(this));
        });
    },

    // Handle image preview for uploads
    handleImagePreview: function(input) {
        const file = input.files[0];
        if (file) {
            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size too large. Please select an image under 5MB.');
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                let preview = document.getElementById('image-preview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.id = 'image-preview';
                    preview.className = 'img-fluid mt-3 rounded shadow-sm';
                    preview.style.maxHeight = '200px';
                    preview.style.width = 'auto';
                    input.parentNode.appendChild(preview);
                }
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    },

    // Enhanced form validations
    setupFormValidations: function() {
        // Price validation
        const priceInputs = document.querySelectorAll('input[name="price"]');
        priceInputs.forEach(input => {
            input.addEventListener('input', function() {
                const value = parseFloat(this.value);
                if (value < 0) {
                    this.setCustomValidity('Price must be a positive number');
                } else if (value > 1000000) {
                    this.setCustomValidity('Price seems too high. Please verify.');
                } else {
                    this.setCustomValidity('');
                }
            });
        });

        // Phone number validation
        const phoneInputs = document.querySelectorAll('input[type="tel"]');
        phoneInputs.forEach(input => {
            input.addEventListener('input', function() {
                const phone = this.value.replace(/\D/g, '');
                if (phone.length > 0 && (phone.length < 10 || phone.length > 11)) {
                    this.setCustomValidity('Please enter a valid phone number');
                } else {
                    this.setCustomValidity('');
                }
            });
        });

        // Password confirmation
        const confirmPassword = document.getElementById('confirm_password');
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                const newPassword = document.getElementById('new_password');
                if (newPassword && newPassword.value !== this.value) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    },

    // Smooth scrolling for anchor links
    setupSmoothScrolling: function() {
        const links = document.querySelectorAll('a[href^="#"]');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    },

    // Initialize Bootstrap tooltips
    setupTooltips: function() {
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    },

    // Auto-refresh for messages (if on messages page)
    setupAutoRefresh: function() {
        if (window.location.pathname.includes('messages.php') && window.location.search.includes('product=')) {
            const refreshInterval = setInterval(() => {
                // Only refresh if user hasn't typed in the message box recently
                const messageInput = document.querySelector('textarea[name="message"]');
                if (!messageInput || !messageInput.value.trim()) {
                    window.location.reload();
                }
            }, 30000); // Refresh every 30 seconds

            // Clear interval when leaving page
            window.addEventListener('beforeunload', () => {
                clearInterval(refreshInterval);
            });
        }
    },

    // Lazy loading for images and content
    setupLazyLoading: function() {
        if ('IntersectionObserver' in window) {
            const lazyElements = document.querySelectorAll('.lazy-load');
            const lazyObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const element = entry.target;
                        element.classList.add('fade-in');
                        lazyObserver.unobserve(element);
                    }
                });
            });

            lazyElements.forEach(el => lazyObserver.observe(el));
        }
    },

    // Setup dropdown navigation functionality
    setupDropdownNavigation: function() {
        const dropdowns = document.querySelectorAll('.nav-dropdown');
        
        dropdowns.forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            if (!toggle || !menu) return;

            // Initialize dropdown state
            let isHoverActive = false;
            let isClickActive = false;

            // Handle click events
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close other dropdowns
                dropdowns.forEach(otherDropdown => {
                    if (otherDropdown !== dropdown) {
                        otherDropdown.classList.remove('dropdown-open');
                        otherDropdown.querySelector('.dropdown-menu')?.classList.remove('show');
                    }
                });
                
                // Toggle current dropdown
                isClickActive = !dropdown.classList.contains('dropdown-open');
                dropdown.classList.toggle('dropdown-open');
                menu.classList.toggle('show');
                
                // Add explicit visibility for better control
                if (dropdown.classList.contains('dropdown-open')) {
                    menu.style.opacity = '1';
                    menu.style.visibility = 'visible';
                    menu.style.transform = 'translateY(0)';
                    menu.style.pointerEvents = 'auto';
                } else {
                    menu.style.opacity = '0';
                    menu.style.visibility = 'hidden';
                    menu.style.transform = 'translateY(-10px)';
                    menu.style.pointerEvents = 'none';
                }
            });

            // Handle hover events for desktop
            if (window.innerWidth > 768) {
                dropdown.addEventListener('mouseenter', function() {
                    if (!isClickActive) {
                        isHoverActive = true;
                        this.classList.add('dropdown-open');
                        menu.classList.add('show');
                        menu.style.opacity = '1';
                        menu.style.visibility = 'visible';
                        menu.style.transform = 'translateY(0)';
                        menu.style.pointerEvents = 'auto';
                    }
                });

                dropdown.addEventListener('mouseleave', function() {
                    if (isHoverActive && !isClickActive) {
                        isHoverActive = false;
                        this.classList.remove('dropdown-open');
                        menu.classList.remove('show');
                        menu.style.opacity = '0';
                        menu.style.visibility = 'hidden';
                        menu.style.transform = 'translateY(-10px)';
                        menu.style.pointerEvents = 'none';
                    }
                });

                // Ensure dropdown stays open when hovering over menu
                menu.addEventListener('mouseenter', function() {
                    if (isHoverActive) {
                        dropdown.classList.add('dropdown-open');
                        menu.classList.add('show');
                    }
                });
            }

            // Handle keyboard navigation
            toggle.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    dropdown.classList.toggle('dropdown-open');
                    menu.classList.toggle('show');
                } else if (e.key === 'Escape') {
                    dropdown.classList.remove('dropdown-open');
                    menu.classList.remove('show');
                    isClickActive = false;
                    isHoverActive = false;
                    toggle.focus();
                }
            });

            // Handle dropdown menu item navigation
            const menuItems = menu.querySelectorAll('a');
            menuItems.forEach((item, index) => {
                item.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        const nextItem = menuItems[index + 1];
                        if (nextItem) nextItem.focus();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        const prevItem = menuItems[index - 1];
                        if (prevItem) {
                            prevItem.focus();
                        } else {
                            toggle.focus();
                        }
                    } else if (e.key === 'Escape') {
                        dropdown.classList.remove('dropdown-open');
                        menu.classList.remove('show');
                        isClickActive = false;
                        isHoverActive = false;
                        toggle.focus();
                    }
                });

                // Handle menu item clicks
                item.addEventListener('click', function() {
                    dropdown.classList.remove('dropdown-open');
                    menu.classList.remove('show');
                    isClickActive = false;
                    isHoverActive = false;
                });
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            dropdowns.forEach(dropdown => {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('dropdown-open');
                    const menu = dropdown.querySelector('.dropdown-menu');
                    if (menu) {
                        menu.classList.remove('show');
                        menu.style.opacity = '0';
                        menu.style.visibility = 'hidden';
                        menu.style.transform = 'translateY(-10px)';
                        menu.style.pointerEvents = 'none';
                    }
                }
            });
        });

        // Close dropdowns on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdowns.forEach(dropdown => {
                    dropdown.classList.remove('dropdown-open');
                    const menu = dropdown.querySelector('.dropdown-menu');
                    if (menu) {
                        menu.classList.remove('show');
                    }
                });
            }
        });

        // Adjust dropdown behavior on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth <= 768) {
                // Remove hover events on mobile
                dropdowns.forEach(dropdown => {
                    dropdown.classList.remove('dropdown-open');
                    const menu = dropdown.querySelector('.dropdown-menu');
                    if (menu) {
                        menu.classList.remove('show');
                    }
                });
            }
        });

        // Debug function to check dropdown state
        window.debugDropdown = function() {
            dropdowns.forEach((dropdown, index) => {
                const menu = dropdown.querySelector('.dropdown-menu');
                console.log(`Dropdown ${index}:`, {
                    hasDropdownOpen: dropdown.classList.contains('dropdown-open'),
                    hasShow: menu.classList.contains('show'),
                    opacity: menu.style.opacity,
                    visibility: menu.style.visibility,
                    transform: menu.style.transform
                });
            });
        };
    },

    // Utility functions
    utils: {
        // Format currency
        formatCurrency: function(amount) {
            return new Intl.NumberFormat('en-ZA', {
                style: 'currency',
                currency: 'ZAR'
            }).format(amount);
        },

        // Format date
        formatDate: function(dateString) {
            return new Intl.DateTimeFormat('en-ZA').format(new Date(dateString));
        },

        // Debounce function for search
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Show notification
        showNotification: function(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(notification);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
    }
};

// Search functionality
const SearchModule = {
    init: function() {
        const searchInput = document.querySelector('#search-input');
        if (searchInput) {
            searchInput.addEventListener('input', 
                KaziSellers.utils.debounce(this.performSearch.bind(this), 300)
            );
        }
    },

    performSearch: function(event) {
        const query = event.target.value.trim();
        if (query.length > 2) {
            // Implement search functionality here
            console.log('Searching for:', query);
        }
    }
};

// Performance monitoring
const PerformanceMonitor = {
    init: function() {
        // Monitor page load performance
        window.addEventListener('load', () => {
            if ('performance' in window) {
                const loadTime = performance.now();
                if (loadTime > 3000) {
                    console.warn('Page load time is slow:', loadTime + 'ms');
                }
            }
        });

        // Monitor memory usage (if available)
        if ('memory' in performance) {
            setInterval(() => {
                const memory = performance.memory;
                if (memory.usedJSHeapSize > 50 * 1024 * 1024) { // 50MB threshold
                    console.warn('High memory usage detected');
                }
            }, 30000);
        }
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    KaziSellers.init();
    SearchModule.init();
    PerformanceMonitor.init();
});

// Prevent double form submissions
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.dataset.submitted === 'true') {
        e.preventDefault();
        return false;
    }
    form.dataset.submitted = 'true';
    
    // Reset after 5 seconds
    setTimeout(() => {
        form.dataset.submitted = 'false';
    }, 5000);
});

// Dashboard-specific functionality
const DashboardModule = {
    init: function() {
        this.setupStatsAnimation();
        this.setupActionCardInteractions();
        this.setupCategoryCardAnimations();
        this.setupRealTimeUpdates();
        this.setupQuickActions();
    },

    // Animate statistics cards on load
    setupStatsAnimation: function() {
        const statsCards = document.querySelectorAll('.stats-card');
        if (statsCards.length > 0) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '0';
                            entry.target.style.transform = 'translateY(20px)';
                            entry.target.style.transition = 'all 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
                            
                            requestAnimationFrame(() => {
                                entry.target.style.opacity = '1';
                                entry.target.style.transform = 'translateY(0)';
                            });
                            
                            // Animate numbers
                            this.animateNumber(entry.target);
                        }, index * 100);
                        
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            statsCards.forEach(card => observer.observe(card));
        }
    },

    // Animate numbers in stats cards
    animateNumber: function(card) {
        const numberElement = card.querySelector('.stats-number');
        if (!numberElement) return;

        const finalValue = numberElement.textContent.replace(/[^\d.-]/g, '');
        const numericValue = parseFloat(finalValue);
        
        if (isNaN(numericValue)) return;

        const duration = 1500;
        const steps = 60;
        const increment = numericValue / steps;
        let current = 0;
        let step = 0;

        const timer = setInterval(() => {
            current += increment;
            step++;
            
            if (step >= steps) {
                current = numericValue;
                clearInterval(timer);
            }

            // Format the number appropriately
            const prefix = numberElement.textContent.charAt(0) === 'R' ? 'R' : '';
            const suffix = numberElement.textContent.includes(',') ? 
                Math.round(current).toLocaleString() : Math.round(current).toString();
            
            numberElement.textContent = prefix + suffix;
        }, duration / steps);
    },

    // Setup action card hover effects and clicks
    setupActionCardInteractions: function() {
        const actionCards = document.querySelectorAll('.action-card');
        actionCards.forEach(card => {
            // Add click feedback
            card.addEventListener('click', function(e) {
                if (this.href) {
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                }
            });

            // Add keyboard accessibility
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });
    },

    // Setup category card animations
    setupCategoryCardAnimations: function() {
        const categoryCards = document.querySelectorAll('.modern-category-card');
        if (categoryCards.length > 0) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '0';
                            entry.target.style.transform = 'translateY(30px) scale(0.9)';
                            entry.target.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
                            
                            requestAnimationFrame(() => {
                                entry.target.style.opacity = '1';
                                entry.target.style.transform = 'translateY(0) scale(1)';
                            });
                        }, index * 100);
                        
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            categoryCards.forEach(card => observer.observe(card));
        }
    },

    // Setup real-time updates for dashboard data
    setupRealTimeUpdates: function() {
        // Only run on dashboard/home page
        if (window.location.pathname.includes('home.php')) {
            // Update message counts every 30 seconds
            setInterval(() => {
                this.updateMessageCount();
            }, 30000);

            // Update stats every 5 minutes
            setInterval(() => {
                this.updateDashboardStats();
            }, 300000);
        }
    },

    // Update unread message count
    updateMessageCount: async function() {
        try {
            const response = await fetch('../api/get-message-count.php');
            if (response.ok) {
                const data = await response.json();
                const messageElements = document.querySelectorAll('.stats-info .stats-number');
                const notificationDots = document.querySelectorAll('.notification-dot');
                
                messageElements.forEach(el => {
                    if (el.textContent !== data.unread_count.toString()) {
                        el.textContent = data.unread_count;
                        el.parentElement.style.animation = 'pulse 0.5s ease-in-out';
                    }
                });

                notificationDots.forEach(dot => {
                    if (data.unread_count > 0) {
                        dot.textContent = data.unread_count;
                        dot.style.display = 'flex';
                    } else {
                        dot.style.display = 'none';
                    }
                });
            }
        } catch (error) {
            console.log('Could not update message count:', error);
        }
    },

    // Update dashboard statistics
    updateDashboardStats: async function() {
        try {
            const response = await fetch('../api/get-dashboard-stats.php');
            if (response.ok) {
                const data = await response.json();
                // Update stats if changed
                this.updateStatIfChanged('.stats-primary .stats-number', data.active_listings);
                this.updateStatIfChanged('.stats-success .stats-number', data.sold_items);
                this.updateStatIfChanged('.stats-warning .stats-number', 'R' + data.total_earnings.toLocaleString());
            }
        } catch (error) {
            console.log('Could not update dashboard stats:', error);
        }
    },

    // Helper to update stat if changed
    updateStatIfChanged: function(selector, newValue) {
        const element = document.querySelector(selector);
        if (element && element.textContent !== newValue.toString()) {
            element.style.transition = 'all 0.3s ease';
            element.style.transform = 'scale(1.1)';
            element.style.color = '#28a745';
            
            setTimeout(() => {
                element.textContent = newValue;
                element.style.transform = 'scale(1)';
                element.style.color = '';
            }, 150);
        }
    },

    // Setup quick action shortcuts
    setupQuickActions: function() {
        // Keyboard shortcuts for quick actions
        document.addEventListener('keydown', (e) => {
            // Only work if not in input field
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case '1':
                        e.preventDefault();
                        this.navigateToAction('sell.php');
                        break;
                    case '2':
                        e.preventDefault();
                        this.navigateToAction('my-listings.php');
                        break;
                    case '3':
                        e.preventDefault();
                        this.navigateToAction('sales.php');
                        break;
                    case '4':
                        e.preventDefault();
                        this.navigateToAction('messages.php');
                        break;
                }
            }
        });

        // Add keyboard shortcut hints
        this.addKeyboardHints();
    },

    // Navigate to action with visual feedback
    navigateToAction: function(page) {
        const actionCard = document.querySelector(`a[href="${page}"]`);
        if (actionCard) {
            actionCard.style.transform = 'scale(0.95)';
            actionCard.style.transition = 'transform 0.1s ease';
            
            setTimeout(() => {
                window.location.href = page;
            }, 100);
        }
    },

    // Add keyboard shortcut hints to action cards
    addKeyboardHints: function() {
        const shortcuts = ['Ctrl+1', 'Ctrl+2', 'Ctrl+3', 'Ctrl+4'];
        const actionCards = document.querySelectorAll('.action-card');
        
        actionCards.forEach((card, index) => {
            if (shortcuts[index]) {
                const hint = document.createElement('small');
                hint.className = 'keyboard-hint';
                hint.textContent = shortcuts[index];
                hint.style.cssText = `
                    position: absolute;
                    top: 8px;
                    right: 8px;
                    background: rgba(0,0,0,0.1);
                    padding: 2px 6px;
                    border-radius: 4px;
                    font-size: 0.7rem;
                    opacity: 0.7;
                `;
                card.appendChild(hint);
            }
        });
    }
};

// Welcome message enhancements
const WelcomeModule = {
    init: function() {
        this.setupDynamicGreeting();
        this.setupWelcomeAnimation();
    },

    setupDynamicGreeting: function() {
        const greetingElement = document.querySelector('.greeting-text');
        if (greetingElement) {
            const hour = new Date().getHours();
            let greeting = 'Good ';
            let emoji = '';

            if (hour < 12) {
                greeting += 'Morning';
                emoji = '🌅';
            } else if (hour < 17) {
                greeting += 'Afternoon';
                emoji = '☀️';
            } else {
                greeting += 'Evening';
                emoji = '🌆';
            }

            greetingElement.innerHTML = greeting + ' <span style="margin-left: 8px;">' + emoji + '</span>';
        }
    },

    setupWelcomeAnimation: function() {
        const welcomeSection = document.querySelector('.welcome-section');
        if (welcomeSection) {
            welcomeSection.style.opacity = '0';
            welcomeSection.style.transform = 'translateY(-20px)';
            
            setTimeout(() => {
                welcomeSection.style.transition = 'all 0.8s cubic-bezier(0.16, 1, 0.3, 1)';
                welcomeSection.style.opacity = '1';
                welcomeSection.style.transform = 'translateY(0)';
            }, 200);
        }
    }
};

// Enhanced search functionality for dashboard
const DashboardSearch = {
    init: function() {
        this.setupQuickSearch();
        this.setupCategoryFilter();
    },

    setupQuickSearch: function() {
        // Add search functionality if search box exists
        const searchBox = document.querySelector('#dashboard-search');
        if (searchBox) {
            searchBox.addEventListener('input', 
                KaziSellers.utils.debounce(this.performQuickSearch.bind(this), 300)
            );
        }
    },

    performQuickSearch: function(event) {
        const query = event.target.value.toLowerCase().trim();
        const productCards = document.querySelectorAll('.product-card, .mini-product-card');
        
        productCards.forEach(card => {
            const title = card.querySelector('.product-title, .mini-product-title');
            if (title) {
                const text = title.textContent.toLowerCase();
                const match = text.includes(query) || query === '';
                
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = match ? '1' : '0.3';
                card.style.transform = match ? 'scale(1)' : 'scale(0.95)';
            }
        });
    },

    setupCategoryFilter: function() {
        const categoryCards = document.querySelectorAll('.modern-category-card');
        categoryCards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Add visual feedback for category selection
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
    }
};

// Initialize dashboard modules when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize dashboard modules on appropriate pages
    if (window.location.pathname.includes('home.php') || 
        window.location.pathname.includes('dashboard')) {
        DashboardModule.init();
        WelcomeModule.init();
        DashboardSearch.init();
    }
});

// Export for global access
window.KaziSellers = KaziSellers;
window.DashboardModule = DashboardModule;
