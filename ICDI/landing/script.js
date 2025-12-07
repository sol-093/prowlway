/* ============================================
   PROWLWAY - ICDISG Archive Website Scripts
   ============================================
   
   TABLE OF CONTENTS:
   1. Mobile Menu Toggle
   2. Events Carousel/Slider
   3. Smooth Scroll Enhancement
   4. Utility Functions
   
   ============================================ */


/* ============================================
   1. MOBILE MENU TOGGLE
   Toggle navigation menu on mobile devices
   ============================================ */
(function initMobileMenu() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    // Exit if elements don't exist
    if (!mobileToggle || !mainNav) return;
    
    // Toggle menu visibility
    mobileToggle.addEventListener('click', function() {
        mainNav.classList.toggle('active');
        
        // Animate hamburger icon
        this.classList.toggle('active');
    });
    
    // Close menu when clicking nav links
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            mainNav.classList.remove('active');
            mobileToggle.classList.remove('active');
        });
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.main-nav') && 
            !event.target.closest('.mobile-menu-toggle') &&
            mainNav.classList.contains('active')) {
            mainNav.classList.remove('active');
            mobileToggle.classList.remove('active');
        }
    });
})();


/* ============================================
   2. EVENTS CAROUSEL/SLIDER
   Auto-rotating carousel for events section
   ============================================ */
(function initEventsCarousel() {
    const slides = document.querySelectorAll('.event-slide');
    const dots = document.querySelectorAll('.dot');
    
    // Exit if no slides found
    if (slides.length === 0) return;
    
    let currentSlide = 0;
    let autoplayInterval;
    
    /**
     * Show specific slide
     * @param {number} index - Slide index to show
     */
    function showSlide(index) {
        // Remove active class from all slides and dots
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        // Ensure index wraps around
        if (index >= slides.length) {
            currentSlide = 0;
        } else if (index < 0) {
            currentSlide = slides.length - 1;
        } else {
            currentSlide = index;
        }
        
        // Add active class to current slide and dot
        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) {
            dots[currentSlide].classList.add('active');
        }
    }
    
    /**
     * Go to next slide
     */
    function nextSlide() {
        showSlide(currentSlide + 1);
    }
    
    /**
     * Go to previous slide
     */
    function prevSlide() {
        showSlide(currentSlide - 1);
    }
    
    /**
     * Start autoplay
     * Automatically advance to next slide every 5 seconds
     */
    function startAutoplay() {
        autoplayInterval = setInterval(nextSlide, 5000);
    }
    
    /**
     * Stop autoplay
     */
    function stopAutoplay() {
        clearInterval(autoplayInterval);
    }
    
    /**
     * Reset autoplay
     * Useful when user manually changes slides
     */
    function resetAutoplay() {
        stopAutoplay();
        startAutoplay();
    }
    
    // Add click handlers to dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', function() {
            showSlide(index);
            resetAutoplay();
        });
    });
    
    // Add keyboard navigation (optional)
    document.addEventListener('keydown', function(event) {
        if (event.key === 'ArrowLeft') {
            prevSlide();
            resetAutoplay();
        } else if (event.key === 'ArrowRight') {
            nextSlide();
            resetAutoplay();
        }
    });
    
    // Pause autoplay when user hovers over carousel
    const carouselContainer = document.querySelector('.events-carousel');
    if (carouselContainer) {
        carouselContainer.addEventListener('mouseenter', stopAutoplay);
        carouselContainer.addEventListener('mouseleave', startAutoplay);
    }
    
    // Initialize carousel
    showSlide(0);
    startAutoplay();
})();


/* ============================================
   3. SMOOTH SCROLL ENHANCEMENT
   Add smooth scrolling behavior with offset
   ============================================ */
(function initSmoothScroll() {
    const navLinks = document.querySelectorAll('a[href^="#"]');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            const targetId = this.getAttribute('href');
            
            // Skip if it's just "#"
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                event.preventDefault();
                
                // Calculate offset (header height)
                const headerOffset = 80;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                // Smooth scroll to target
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
})();


/* ============================================
   4. UTILITY FUNCTIONS
   Additional helpful functions
   ============================================ */

/**
 * Add fade-in animation on scroll (Optional)
 * Uncomment to enable fade-in effect for cards
 */
/*
(function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe all cards
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
})();
*/

/**
 * Log page load time (For debugging/performance monitoring)
 */
window.addEventListener('load', function() {
    const loadTime = performance.now();
    console.log(`Page loaded in ${loadTime.toFixed(2)}ms`);
});


/**
 * Handle Read More button clicks
 * You can customize this to show modal or navigate to detail page
 */
(function initReadMoreButtons() {
    const readMoreButtons = document.querySelectorAll('.btn-read-more');
    
    readMoreButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Get announcement title
            const card = this.closest('.announcement-card');
            const title = card.querySelector('.announcement-title').textContent;
            
            // Example: Alert (replace with your own logic)
            alert(`Opening announcement: ${title}\n\nYou can replace this with a modal or navigate to a detail page.`);
            
            // Example: Navigate to detail page
            // window.location.href = '/announcement-detail.html?id=1';
            
            // Example: Open modal
            // showAnnouncementModal(title);
        });
    });
})();


/**
 * Handle document folder clicks
 * Add navigation or download functionality
 */
(function initDocumentFolders() {
    const folderItems = document.querySelectorAll('.folder-item');
    
    folderItems.forEach(folder => {
        folder.addEventListener('click', function() {
            const folderNumber = this.querySelector('.folder-number').textContent;
            const folderLabel = this.querySelector('.folder-label').textContent;
            
            // Example: Alert (replace with your own logic)
            console.log(`Opening folder: ${folderNumber} - ${folderLabel}`);
            
            // Example: Navigate to document list page
            // window.location.href = `/documents/${folderNumber}`;
        });
    });
})();


/* ============================================
   END OF SCRIPT
   ============================================ */
console.log('PROWLWAY scripts initialized successfully');
