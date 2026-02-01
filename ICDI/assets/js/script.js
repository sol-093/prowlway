/* ============================================
   PROWLWAY - ICDISG Archive Website
   Combined JavaScript File (All Scripts)
   ============================================
   
   TABLE OF CONTENTS:
   1. Static Data (for fallback/standalone mode)
   2. API Service
   3. Intro Animation
   4. Page Transition
   5. Data Loading (Announcements, Events, Documents)
   6. Mobile Menu Toggle
   7. Events Carousel/Slider
   8. Documents Page Functions
   9. Events Page Functions
   10. Admin Panel Functions
   11. Smooth Scroll & Utilities
   
   ============================================ */

// ============================================
// 1. STATIC DATA (Fallback for standalone mode)
// ============================================
if (typeof window.staticData === 'undefined') {
    window.staticData = {
        announcements: [
            {
                _id: 'ANN-001',
                title: 'TechCare: Free Printing Station',
                date: '2025-11-05T00:00:00Z',
                description: 'We know defense week demands courage, resilience, and long nights and we refuse to let financial barriers hold any student back...',
                fullContent: 'We know defense week demands courage, resilience, and long nights—and we refuse to let financial barriers hold any student back. TechCare sets up a free DIY printing station from November 24-29, complete with volunteers ready to assist. Bring your flash drives or upload files via the TechCare portal.',
            },
            {
                _id: 'ANN-002',
                title: 'Semester End Party',
                date: '2025-11-12T00:00:00Z',
                description: 'Join us for an evening of music and celebration as we wrap up another successful semester. Do not forget your ID!',
                fullContent: 'Celebrate the end of the semester with the entire college community. Expect live performers, showcase booths, and the annual student recognition ceremony. Entry is free but IDs are required at the gate.',
            },
            {
                _id: 'ANN-003',
                title: 'Library Maintenance',
                date: '2025-11-15T00:00:00Z',
                description: 'The main library will be closed for system upgrades this weekend. Online resources remain available 24/7.',
                fullContent: 'The main library receives a full systems upgrade from November 15-16. Physical spaces are closed, but librarians are online for chat consultations and all digital references remain accessible.',
            },
        ],
        events: [
            {
                id: 'EVT-001',
                title: 'Print It Yourself: Thesis Printing Week',
                eventDate: '2025-11-25T09:00:00Z',
                category: 'service',
                location: 'Feed Room, 3rd Floor',
                caption: 'Find us at the Feed Room on 3rd Floor',
                description: 'DIY printing kiosks plus TechCare volunteers on standby to help you conquer final submissions without the long queues.',
                summary: 'The Print It Yourself program keeps paper, toner, and staff ready during defense week so everyone submits on time.',
                imageUrl: (window.ASSETS_URL || '/assets') + '/images/diy-print.jpg',
                gallery: [],
            },
            {
                id: 'EVT-002',
                title: 'TechCare Service Orientation',
                eventDate: '2025-12-05T13:00:00Z',
                category: 'workshop',
                location: 'ICDISG Lab 2',
                caption: 'Learn how to run the TechCare stations',
                description: 'A hands-on session for new volunteers covering equipment maintenance, troubleshooting, and user assistance best practices.',
                summary: 'Participants leave ready to manage TechCare pop-ups with confidence.',
                imageUrl: (window.ASSETS_URL || '/assets') + '/images/techxellence.jpg',
                gallery: [],
            },
        ],
        documents: [
            {
                id: 'DOC-001',
                categoryNumber: '01',
                title: 'Office Accomplishment Report FY25',
                description: 'Summary of office programs and KPIs for the fiscal year.',
                fileName: 'Office-Accomplishment-Report.pdf',
                fileUrl: (window.ASSETS_URL || '/assets') + '/images/Doc1.docx',
                fileSize: 2359296,
                createdAt: '2025-10-12T00:00:00Z',
            },
        ],
    };
}

// ============================================
// 2. PHP API SERVICE (No Node.js)
// ============================================
// All data is loaded from static data or PHP endpoints
// No Node.js backend dependencies

// ============================================
// 2.1. FORM VALIDATION HELPERS
// ============================================
/**
 * Validate email address
 */
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validate academic year format (YYYY-YYYY)
 */
function validateAcademicYear(year) {
    const yearRegex = /^\d{4}-\d{4}$/;
    return yearRegex.test(year);
}

/**
 * Validate required field
 */
function validateRequired(value, fieldName) {
    if (!value || value.trim() === '') {
        return { valid: false, error: `${fieldName} is required` };
    }
    return { valid: true };
}

/**
 * Validate string length
 */
function validateLength(value, min, max, fieldName) {
    if (value.length < min) {
        return { valid: false, error: `${fieldName} must be at least ${min} characters` };
    }
    if (max && value.length > max) {
        return { valid: false, error: `${fieldName} must be no more than ${max} characters` };
    }
    return { valid: true };
}

/**
 * Show field error message
 */
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    // Remove existing error
    const existingError = field.parentElement.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Add error styling
    field.classList.add('border-red-500', 'border-2');
    field.classList.remove('border-gray-200');
    
    // Add error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error text-red-600 text-sm mt-1 font-semibold';
    errorDiv.textContent = message;
    field.parentElement.appendChild(errorDiv);
}

/**
 * Clear field error
 */
function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.classList.remove('border-red-500', 'border-2');
    field.classList.add('border-gray-200');
    
    const error = field.parentElement.querySelector('.field-error');
    if (error) {
        error.remove();
    }
}

/**
 * Validate document form
 */
function validateDocumentForm(formData) {
    const errors = [];
    const id = formData.get('id');
    const isUpdate = id && id !== '';
    
    // Title validation
    const title = formData.get('title') || '';
    const titleValidation = validateRequired(title, 'Title');
    if (!titleValidation.valid) {
        errors.push({ field: 'title', message: titleValidation.error });
    } else {
        const lengthValidation = validateLength(title, 3, 255, 'Title');
        if (!lengthValidation.valid) {
            errors.push({ field: 'title', message: lengthValidation.error });
        }
    }
    
    // Category validation
    const category = formData.get('category') || '';
    if (!category) {
        errors.push({ field: 'category', message: 'Category is required' });
    }
    
    // Subcategory validation (required)
    const subcategory = formData.get('subcategory') || '';
    if (!subcategory) {
        errors.push({ field: 'subcategory', message: 'Subcategory is required. Please select a category first to load subcategories.' });
    }
    
    // File validation (required for create)
    if (!isUpdate) {
        const file = formData.get('file');
        if (!file || !file.name) {
            errors.push({ field: 'file', message: 'PDF file upload is required' });
        }
    }
    
    // Academic year validation (if provided)
    const academicYear = formData.get('academic_year') || '';
    if (academicYear && !validateAcademicYear(academicYear)) {
        errors.push({ field: 'academic-year', message: 'Invalid academic year format. Use YYYY-YYYY (e.g., 2024-2025)' });
    }
    
    return errors;
}

/**
 * Validate announcement form
 */
function validateAnnouncementForm(formData) {
    const errors = [];
    
    const title = formData.get('title') || '';
    const titleValidation = validateRequired(title, 'Title');
    if (!titleValidation.valid) {
        errors.push({ field: 'ann-title', message: titleValidation.error });
    }
    
    const description = formData.get('description') || '';
    const descValidation = validateRequired(description, 'Description');
    if (!descValidation.valid) {
        errors.push({ field: 'ann-description', message: descValidation.error });
    }
    
    // Image is required for published or pinned announcements (strong announcements)
    const status = formData.get('status') || 'draft';
    const pinned = formData.get('pinned') === 'on' || formData.get('pinned') === '1';
    const isStrongAnnouncement = status === 'published' || pinned;
    
    if (isStrongAnnouncement) {
        const imageFile = formData.get('image');
        const oldImage = formData.get('old_image') || '';
        const hasImage = (imageFile && imageFile.name) || oldImage;
        
        if (!hasImage) {
            errors.push({ field: 'ann-image', message: 'Image is required for published or pinned announcements' });
        }
    }
    
    return errors;
}

/**
 * Validate event form
 */
function validateEventForm(formData) {
    const errors = [];
    const id = formData.get('id');
    const isUpdate = id && id !== '';
    
    const title = formData.get('title') || '';
    const titleValidation = validateRequired(title, 'Title');
    if (!titleValidation.valid) {
        errors.push({ field: 'evt-title', message: titleValidation.error });
    }
    
    const caption = formData.get('caption') || '';
    const captionValidation = validateRequired(caption, 'Caption');
    if (!captionValidation.valid) {
        errors.push({ field: 'evt-caption', message: captionValidation.error });
    }
    
    const date = formData.get('date') || '';
    const dateValidation = validateRequired(date, 'Event date');
    if (!dateValidation.valid) {
        errors.push({ field: 'evt-date', message: dateValidation.error });
    }
    
    // Image validation (required for create)
    if (!isUpdate) {
        const image = formData.get('image');
        if (!image || !image.name) {
            errors.push({ field: 'evt-image', message: 'Event image is required' });
        }
    }
    
    return errors;
}

// ============================================
// 3. INTRO ANIMATION
// ============================================
(function initIntroAnimation() {
    // Wait for DOM to be ready
    function startIntroAnimation() {
        console.log('[Intro Debug] Starting intro animation check...');
        
    // Check if animation was already shown (cookie check)
    const introShownCookie = document.cookie.split(';').find(c => c.trim().startsWith('prowlway_intro_shown='));
    if (introShownCookie && introShownCookie.split('=')[1] === '1') {
        console.log('[Intro Debug] Animation already shown (cookie found) - skipping');
        const mainContent = document.getElementById('mainContent');
        const introScreen = document.getElementById('introScreen');
        if (introScreen) {
            introScreen.style.display = 'none';
            introScreen.classList.add('hidden');
        }
        if (mainContent) {
            mainContent.style.opacity = '1';
            mainContent.style.visibility = 'visible';
            mainContent.style.display = 'block';
        }
        document.body.classList.remove('intro-active');
        return;
    }
        
    const introScreen = document.getElementById('introScreen');
    const introTextEl = document.getElementById('introText');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

        console.log('[Intro Debug] introScreen:', introScreen);
        console.log('[Intro Debug] introTextEl:', introTextEl);
        console.log('[Intro Debug] body.className:', body.className);
        console.log('[Intro Debug] body has intro-active:', body.classList.contains('intro-active'));

        // If elements don't exist, skip animation and show content immediately
        if (!introScreen || !introTextEl) {
            console.warn('[Intro Debug] Intro elements not found - showing content immediately');
            // Fallback: ensure content is visible if intro elements are missing
            if (mainContent) {
                mainContent.classList.add('show');
                mainContent.style.opacity = '1';
                mainContent.style.visibility = 'visible';
            }
            if (body) {
                body.classList.remove('intro-active');
                body.style.overflow = '';
            }
            return;
        }
        
        // Only proceed if body has intro-active class OR if intro screen exists
        // This ensures intro shows even if body class check fails
        const hasIntroActive = body.classList.contains('intro-active');
        console.log('[Intro Debug] Body has intro-active class:', hasIntroActive);
        
        if (!hasIntroActive && introScreen) {
            // If body doesn't have class but intro screen exists, add the class
            console.log('[Intro Debug] Adding intro-active class to body');
            body.classList.add('intro-active');
        }
        
        if (!hasIntroActive && !introScreen) {
            // No intro screen and no class - skip animation
            console.log('[Intro Debug] No intro screen and no intro-active class - showing content');
            if (mainContent) {
                mainContent.classList.add('show');
                mainContent.style.opacity = '1';
                mainContent.style.visibility = 'visible';
            }
            return;
        }
        
        console.log('[Intro Debug] All checks passed - starting animation');
        
        // Ensure body has intro-active class
        if (!body.classList.contains('intro-active')) {
            body.classList.add('intro-active');
            console.log('[Intro Debug] Added intro-active class to body');
        }
        
        // Ensure intro screen is visible and properly styled
        if (introScreen) {
            introScreen.style.display = 'flex';
            introScreen.style.opacity = '1';
            introScreen.style.visibility = 'visible';
            introScreen.classList.remove('hidden', 'fade-out');
            console.log('[Intro Debug] Ensured intro screen is visible');
            console.log('[Intro Debug] Intro screen computed display:', window.getComputedStyle(introScreen).display);
        }

    const INTRO_CONFIG = {
        text: "ICDISG PROWLWAY",
        letterDelay: 50,
        holdDuration: 500,
        fadeOutDuration: 500
    };

    const text = INTRO_CONFIG.text;
    const letters = text.split('');
    let revealedCount = 0;

        // Clear any existing content
        introTextEl.innerHTML = '';

    letters.forEach(letter => {
        const span = document.createElement('span');
        span.textContent = letter === ' ' ? '\u00A0' : letter;
        introTextEl.appendChild(span);
    });

    const spans = introTextEl.querySelectorAll('span');
        console.log('[Intro Debug] Created', spans.length, 'letter spans');

    function revealNextLetter() {
        if (revealedCount < spans.length) {
            spans[revealedCount].classList.add('revealed');
            revealedCount++;
            setTimeout(revealNextLetter, INTRO_CONFIG.letterDelay);
        } else {
                console.log('[Intro Debug] All letters revealed - waiting before fade out');
            setTimeout(fadeOutIntro, INTRO_CONFIG.holdDuration);
        }
    }

    function fadeOutIntro() {
            console.log('[Intro Debug] Fading out intro');
            if (!introScreen) {
                console.error('[Intro Debug] introScreen is null in fadeOutIntro');
                return;
            }
            
        // Set cookie to remember animation was shown (expires in 1 year)
        const expiryDate = new Date();
        expiryDate.setFullYear(expiryDate.getFullYear() + 1);
        document.cookie = 'prowlway_intro_shown=1; expires=' + expiryDate.toUTCString() + '; path=/; SameSite=Lax';
            
        introScreen.classList.add('fade-out');
        
        setTimeout(() => {
                console.log('[Intro Debug] Hiding intro screen and showing content');
                if (introScreen) {
            introScreen.classList.add('hidden');
                    introScreen.style.display = 'none';
                }
                if (body) {
                    body.classList.remove('intro-active');
                    body.style.overflow = '';
                }
                if (mainContent) {
                    mainContent.classList.add('show');
                    mainContent.style.opacity = '1';
                    mainContent.style.visibility = 'visible';
                    mainContent.style.pointerEvents = 'auto';
                }
        }, INTRO_CONFIG.fadeOutDuration);
    }
        
        console.log('[Intro Debug] Starting letter reveal animation in 200ms');

        // Safety timeout: if animation doesn't complete in 10 seconds, force show content
        // Animation should complete in ~2-3 seconds, so 10 seconds is a safe buffer
        setTimeout(() => {
            const intro = document.getElementById('introScreen');
            const content = document.getElementById('mainContent');
            const bodyEl = document.body;
            
            if (intro && !intro.classList.contains('hidden') && intro.offsetParent !== null) {
                console.warn('Intro animation timeout (10s) - forcing content display');
                intro.style.display = 'none';
                intro.classList.add('fade-out', 'hidden');
            }
            if (bodyEl && bodyEl.classList.contains('intro-active')) {
                bodyEl.classList.remove('intro-active');
                bodyEl.style.overflow = '';
            }
            if (content) {
                content.classList.add('show');
                content.style.opacity = '1';
                content.style.visibility = 'visible';
                content.style.pointerEvents = 'auto';
            }
        }, 10000);
        
        // Additional emergency timeout at 5 seconds
        setTimeout(() => {
            const intro = document.getElementById('introScreen');
            const content = document.getElementById('mainContent');
            const bodyEl = document.body;
            
            if (intro && intro.offsetParent !== null) {
                console.warn('Emergency timeout (5s) - forcing content display');
                intro.style.display = 'none';
                intro.classList.add('hidden', 'fade-out');
            }
            if (bodyEl) {
                bodyEl.classList.remove('intro-active');
                bodyEl.style.overflow = '';
            }
            if (content) {
                content.classList.add('show');
                content.style.opacity = '1';
                content.style.visibility = 'visible';
                content.style.pointerEvents = 'auto';
            }
        }, 5000);

    setTimeout(revealNextLetter, 200);
    }

    // Run when DOM is ready
    console.log('[Intro Debug] initIntroAnimation called, document.readyState:', document.readyState);
    if (document.readyState === 'loading') {
        console.log('[Intro Debug] DOM still loading - waiting for DOMContentLoaded');
        document.addEventListener('DOMContentLoaded', function() {
            console.log('[Intro Debug] DOMContentLoaded fired - starting animation');
            startIntroAnimation();
        });
    } else {
        // DOM is already ready
        console.log('[Intro Debug] DOM already ready - starting animation immediately');
        startIntroAnimation();
    }
})();

// ============================================
// 4. PAGE TRANSITION
// ============================================
(function initPageTransition() {
    document.addEventListener('DOMContentLoaded', function() {
        const navLinks = document.querySelectorAll('a[href]');
        
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // CRITICAL: Skip folder-item links, folder-card links, document-category-card links, and links with data-skip-js-handler
                if (this.classList.contains('folder-item') || 
                    this.classList.contains('folder-card') ||
                    this.classList.contains('document-category-card') ||
                    this.hasAttribute('data-skip-js-handler') ||
                    this.closest('.folder-item') ||
                    this.closest('.folder-card') ||
                    this.closest('.document-category-card') ||
                    document.body.classList.contains('documents-page')) {
                    return; // Let the link work naturally
                }
                
                const href = this.getAttribute('href');
                if (href && !href.startsWith('#') && !href.startsWith('http') && (href.endsWith('.html') || href.endsWith('.php'))) {
                    // Skip page transition for origin-page type pages (same behavior as organization.php)
                    if (href.includes('faculty.php') || 
                        href.includes('faculty-detail.php') ||
                        href.includes('admin-representative-detail.php') || 
                        href.includes('batch-detail.php') ||
                        href.includes('organization.php') ||
                        href.includes('batches.php') ||
                        href.includes('institute.php')) {
                        return; // Let the link work naturally without transition
                    }
                    
                    e.preventDefault();
                    
                    document.body.classList.add('page-transition-out');
                    
                    setTimeout(() => {
                        window.location.href = href;
                    }, 250);
                }
            });
        });
    });
})();

// ============================================
// 5. DATA LOADING (Announcements, Events, Documents)
// ============================================
const staticAnnouncements = window.staticData?.announcements || [];
const staticEvents = window.staticData?.events || [];
const staticDocuments = window.staticData?.documents || [];

// Load announcements (PHP/Static only - no Node.js)
function loadAnnouncements() {
    const scrollWrapper = document.querySelector('.announcements-panel .scroll-wrapper');
    if (!scrollWrapper) return;

    // Use static data only (no Node.js API)
    const data = staticAnnouncements.length ? staticAnnouncements : [];
    if (!data.length) return;

    scrollWrapper.innerHTML = '';
    data.forEach(announcement => {
        const card = createAnnouncementCard(announcement);
        scrollWrapper.appendChild(card);
    });
}

function createAnnouncementCard(announcement) {
    const card = document.createElement('div');
    card.className = 'announcement-card';
    
    const date = new Date(announcement.date);
    const formattedDate = date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: '2-digit' 
    });
    
    card.innerHTML = `
        <div class="announcement-title">${announcement.title}</div>
        <div class="announcement-date">${formattedDate}</div>
        <div class="card-divider"></div>
        <p class="announcement-desc">${announcement.description || ''}</p>
        <button class="btn-read-more" data-id="${announcement._id || announcement.id}">READ MORE ></button>
    `;
    
    return card;
}

// Load events (PHP/Static only - no Node.js)
function loadEvents() {
    const carouselTrack = document.querySelector('.carousel-track');
    const carouselDots = document.querySelector('.carousel-dots');
    if (!carouselTrack || !carouselDots) return;

    // Use static data only (no Node.js API)
    const data = staticEvents.length ? staticEvents.slice(0, 5) : [];
    if (!data.length) return;

    carouselTrack.innerHTML = '';
    carouselDots.innerHTML = '';

    data.forEach((event, index) => {
        const slide = createEventSlide(event, index === 0);
        carouselTrack.appendChild(slide);

        const dot = createCarouselDot(index, index === 0);
        carouselDots.appendChild(dot);
    });

    reinitializeCarousel();
}

function createEventSlide(event, isActive = false) {
    const slide = document.createElement('div');
    slide.className = isActive ? 'event-slide active' : 'event-slide';
    const imageSrc = event.imageUrl || (window.ASSETS_URL || '/assets') + '/images/diy-print.jpg';
    const caption = event.caption || event.title;
    
    slide.innerHTML = `
        <div class="event-image">
            <img src="${imageSrc}" alt="${event.title}">
        </div>
        <p class="event-caption">${caption}</p>
    `;
    
    return slide;
}

function createCarouselDot(index, isActive = false) {
    const dot = document.createElement('span');
    dot.className = isActive ? 'dot active' : 'dot';
    dot.setAttribute('data-slide', index);
    return dot;
}

// Load document statistics (PHP/Static only - no Node.js)
function loadDocumentStats() {
    const folderItems = document.querySelectorAll('.folder-item');
    if (!folderItems.length) return;

    // Use static data only (no Node.js API)
    if (!staticDocuments.length) return;

    const counts = staticDocuments.reduce((acc, doc) => {
        const key = doc.categoryNumber;
        acc[key] = (acc[key] || 0) + 1;
        return acc;
    }, {});

    folderItems.forEach(item => {
        const folderNumber = item.querySelector('.folder-number')?.textContent;
        const docsElement = item.querySelector('.folder-docs');
        if (folderNumber && docsElement) {
            const count = counts[folderNumber] || 0;
            docsElement.textContent = `${count} Doc${count === 1 ? '' : 's'}`;
        }
    });
}

// Initialize data loading
(function initDataLoading() {
    // Since data is now loaded from PHP, we only need to initialize carousel if events exist
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            // Carousel is initialized from PHP-rendered HTML
            initializeCarouselFunctionality();
        });
    } else {
        // Carousel is initialized from PHP-rendered HTML
        initializeCarouselFunctionality();
    }
})();

// ============================================
// 6. MOBILE MENU TOGGLE (Disabled - Using new sidebar)
// ============================================
// Old mobile menu toggle disabled - sidebar is now handled in header.php
// This prevents duplicate navigation menus
(function initMobileMenu() {
    // Disabled - sidebar functionality is now in header.php
    // Keeping function structure for compatibility but not executing
    return;
})();

// ============================================
// 7. EVENTS CAROUSEL/SLIDER
// ============================================
function initializeCarouselFunctionality() {
    const slides = document.querySelectorAll('.event-slide');
    const dots = document.querySelectorAll('.dot');
    
    if (slides.length === 0) return;
    if (slides[0].dataset.initialized === 'true') return; // Prevent duplicate init
    slides[0].dataset.initialized = 'true';
    
    let currentSlide = 0;
    let autoplayInterval;
    const autoplayDelay = 4000; // 4 seconds for smoother loop
    
    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        // Continuous loop - wrap around seamlessly
        if (index >= slides.length) {
            currentSlide = 0;
        } else if (index < 0) {
            currentSlide = slides.length - 1;
        } else {
            currentSlide = index;
        }
        
        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) {
            dots[currentSlide].classList.add('active');
        }
    }
    
    function nextSlide() {
        if (slides.length > 1) {
            showSlide(currentSlide + 1);
        }
    }
    
    function startAutoplay() {
        stopAutoplay();
        if (slides.length > 1) {
            autoplayInterval = setInterval(nextSlide, autoplayDelay);
        }
    }
    
    function stopAutoplay() {
        if (autoplayInterval) {
            clearInterval(autoplayInterval);
            autoplayInterval = null;
        }
    }
    
    function resetAutoplay() {
        stopAutoplay();
        startAutoplay();
    }
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', function() {
            showSlide(index);
            resetAutoplay();
        });
    });
    
    document.addEventListener('keydown', function(event) {
        if (event.key === 'ArrowLeft') {
            showSlide(currentSlide - 1);
            resetAutoplay();
        } else if (event.key === 'ArrowRight') {
            nextSlide();
            resetAutoplay();
        }
    });
    
    const carouselContainer = document.querySelector('.events-carousel');
    if (carouselContainer) {
        carouselContainer.addEventListener('mouseenter', function() {
            if (window.innerWidth >= 768) { // Desktop only
                stopAutoplay();
            }
        });
        carouselContainer.addEventListener('mouseleave', function() {
            if (window.innerWidth >= 768) { // Desktop only
                startAutoplay();
            }
        });
    }
    
    showSlide(0);
    startAutoplay();
    
    // Restart autoplay on window focus
    window.addEventListener('focus', function() {
        if (!autoplayInterval) {
            startAutoplay();
        }
    });
}

// Carousel is now initialized from PHP-rendered HTML, but keep this for compatibility
function reinitializeCarousel() {
    initializeCarouselFunctionality();
    initializeCarouselFunctionality();
}

(function initEventsCarousel() {
    initializeCarouselFunctionality();
})();

// ============================================
// 8. DOCUMENTS PAGE FUNCTIONS
// ============================================
let allDocuments = [];
let currentFolder = null;
let currentView = 'list';

const folders = [
    { id: '01', name: 'Offices Report', icon: '📊', categoryNumber: '01' },
    { id: '02', name: 'Executive Orders', icon: '⚖️', categoryNumber: '02' },
    { id: '03', name: 'Ordinances', icon: '📜', categoryNumber: '03' },
    { id: '04', name: 'Resolutions', icon: '📋', categoryNumber: '04' },
    { id: '05', name: 'Other', icon: '📑', categoryNumber: '05' }
];

function loadDocuments() {
    const container = document.getElementById('foldersGrid');
    if (!container) return;

    // Use static data only (no Node.js API)
    if (!staticDocuments.length) {
        if (container) {
            container.innerHTML = '<div class="loading">No documents available</div>';
        }
        return;
    }

    allDocuments = staticDocuments;
    displayFolders();
    displayDocuments(allDocuments);
}

function displayFolders() {
    const container = document.getElementById('foldersGrid');
    if (!container) return;
    
    const folderCounts = {};
    allDocuments.forEach(doc => {
        const cat = doc.categoryNumber;
        folderCounts[cat] = (folderCounts[cat] || 0) + 1;
    });

    const iconByCategory = {
        '01': 'OFFICE.png',
        '02': 'EXECUTIVE.png',
        '03': 'ORDINANCE.png',
        '04': 'RESOLUTION.png',
        '05': 'OTHER.png',
    };

    const html = folders.map(folder => {
        const iconFile = iconByCategory[folder.categoryNumber] || 'OTHER.png';
        const iconUrl = (window.ASSETS_URL || '/assets') + '/IMG/ICONS/' + iconFile;
        const count = folderCounts[folder.categoryNumber] || 0;
        const href = (window.PUBLIC_URL || '/public') + `/documents.php?category=${folder.categoryNumber}`;

        return `
        <a class="folder-card" href="${href}" data-skip-js-handler="true">
            <div class="folder-icon"><img src="${iconUrl}" alt="${folder.name}"></div>
            <div class="folder-info">
                <div class="folder-name">${folder.name}</div>
                <div class="folder-count">${count} document${count === 1 ? '' : 's'}</div>
            </div>
        </a>
        `;
    }).join('');

    container.innerHTML = html;
}

function openFolder(categoryNumber, folderName) {
    // On documents.php page, navigate to the filtered PHP page
    if (document.body.classList.contains('documents-page')) {
        window.location.href = (window.PUBLIC_URL || '/public') + `/documents.php?category=${categoryNumber}`;
        return;
    }
    
    currentFolder = { categoryNumber, name: folderName };
    const filtered = allDocuments.filter(doc => doc.categoryNumber === categoryNumber);
    
    const breadcrumb = document.getElementById('breadcrumb');
    if (breadcrumb) {
        breadcrumb.innerHTML = `
            <div class="breadcrumb-item">
                <a href="#" class="breadcrumb-link" onclick="goToRoot(event)">Documents</a>
            </div>
            <span class="breadcrumb-separator">›</span>
            <div class="breadcrumb-item">
                <span class="breadcrumb-current">${folderName}</span>
            </div>
        `;
    }

    const foldersSection = document.getElementById('foldersSection');
    if (foldersSection) foldersSection.classList.add('hidden');
    displayDocuments(filtered);
}

function goToRoot(e) {
    if (e) e.preventDefault();
    
    // Don't modify breadcrumb on documents.php page - it's handled by PHP
    if (document.body.classList.contains('documents-page')) {
        window.location.href = (window.PUBLIC_URL || '/public') + '/documents.php';
        return;
    }
    
    currentFolder = null;
    const breadcrumb = document.getElementById('breadcrumb');
    if (breadcrumb) {
        breadcrumb.innerHTML = `
            <div class="breadcrumb-item">
                <a href="#" class="breadcrumb-link" onclick="goToRoot(event)">Documents</a>
            </div>
        `;
    }
    const foldersSection = document.getElementById('foldersSection');
    if (foldersSection) foldersSection.classList.remove('hidden');
    displayDocuments(allDocuments);
}

function displayDocuments(documents) {
    if (currentView === 'list') {
        displayListView(documents);
    } else {
        displayGridView(documents);
    }
}

function displayListView(documents) {
    const container = document.getElementById('listView');
    if (!container) return;
    
    if (documents.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <p class="empty-text">No documents found</p>
            </div>
        `;
        return;
    }

    const html = documents.map(doc => {
        const size = formatFileSize(doc.fileSize || 0);
        const date = new Date(doc.createdAt).toLocaleDateString();
        
        return `
            <div class="document-item" onclick='viewDocument("${doc.fileUrl}")'>
                <div class="doc-icon">📄</div>
                <div class="doc-info">
                    <div class="doc-name">${doc.title}</div>
                    <div class="doc-meta">${doc.fileName || 'Document'} • ${date}</div>
                </div>
                <div class="doc-size">${size}</div>
                <div class="doc-actions">
                    <button class="action-btn" onclick='downloadDocument(event, "${doc.fileUrl}", "${doc.fileName}")'>↓ Download</button>
                </div>
            </div>
        `;
    }).join('');

    container.innerHTML = html;
}

function displayGridView(documents) {
    const container = document.getElementById('gridView');
    if (!container) return;
    
    if (documents.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <p class="empty-text">No documents found</p>
            </div>
        `;
        return;
    }

    const html = documents.map(doc => {
        const size = formatFileSize(doc.fileSize || 0);
        
        return `
            <div class="document-card" onclick='viewDocument("${doc.fileUrl}")'>
                <div class="doc-card-icon">📄</div>
                <div class="doc-card-name">${doc.title}</div>
                <div class="doc-card-meta">${size}</div>
            </div>
        `;
    }).join('');

    container.innerHTML = html;
}

function switchView(view, evt) {
    currentView = view;
    
    document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
    const trigger = evt?.currentTarget || evt?.target;
    if (trigger) {
        trigger.classList.add('active');
    }
    
    if (view === 'list') {
        const listView = document.getElementById('listView');
        const gridView = document.getElementById('gridView');
        if (listView) listView.classList.remove('hidden');
        if (gridView) gridView.classList.add('hidden');
    } else {
        const listView = document.getElementById('listView');
        const gridView = document.getElementById('gridView');
        if (listView) listView.classList.add('hidden');
        if (gridView) gridView.classList.remove('hidden');
    }
    
    displayDocuments(currentFolder ? 
        allDocuments.filter(doc => doc.categoryNumber === currentFolder.categoryNumber) : 
        allDocuments
    );
}

const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        const filtered = allDocuments.filter(doc => 
            doc.title.toLowerCase().includes(query) ||
            doc.description?.toLowerCase().includes(query) ||
            doc.fileName?.toLowerCase().includes(query)
        );
        
        if (currentFolder) {
            const folderFiltered = filtered.filter(doc => 
                doc.categoryNumber === currentFolder.categoryNumber
            );
            displayDocuments(folderFiltered);
        } else {
            displayDocuments(filtered);
        }
    });
}

function viewDocument(url) {
    const fullUrl = url.startsWith('http') ? url : url;
    window.open(fullUrl, '_blank');
}

function downloadDocument(e, url, filename) {
    if (e) e.stopPropagation();
    
    const fullUrl = url.startsWith('http') ? url : url;
    
    const link = document.createElement('a');
    link.href = fullUrl;
    link.download = filename || 'document';
    link.target = '_blank';
    link.click();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Initialize documents page
if (document.getElementById('foldersGrid')) {
    document.addEventListener('DOMContentLoaded', function() {
        // IMPORTANT: documents.php is rendered by PHP; don't overwrite its markup
        if (document.body.classList.contains('documents-page')) return;
        loadDocuments();
    });
}

// ============================================
// 9. EVENTS PAGE FUNCTIONS
// ============================================
let allEvents = [];
let currentCategory = 'all';

function loadEventsPage() {
    const container = document.getElementById('eventsContainer');
    if (!container) return;

    // Use static data only (no Node.js API)
    if (!staticEvents.length) {
        if (container) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">📅</div>
                    <p class="empty-text">No events available at the moment</p>
                </div>
            `;
        }
        return;
    }

    allEvents = staticEvents;
    displayEventsPage(allEvents);
}

function displayEventsPage(events) {
    const container = document.getElementById('eventsContainer');
    if (!container) return;
    
    if (events.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">🔍</div>
                <p class="empty-text">No events found in this category</p>
            </div>
        `;
        return;
    }

    const html = `
        <div class="events-grid">
            ${events.map(event => createEventCard(event)).join('')}
        </div>
    `;
    
    container.innerHTML = html;
}

function createEventCard(event) {
    const eventDate = new Date(event.eventDate);
    const formattedDate = eventDate.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    const imageUrl = event.imageUrl || (window.ASSETS_URL || '/assets') + '/images/diy-print.jpg';
    
    return `
        <div class="event-card" onclick='openEventModal(${JSON.stringify(event).replace(/'/g, "\\'")})'> 
            <div class="event-image">
                <img src="${imageUrl}" alt="${event.title}">
            </div>
            <div class="event-content">
                <span class="event-category">${event.category}</span>
                <div class="event-info-block">
                    <div class="event-meta">
                        <h3 class="event-title">${event.title}</h3>
                        <div class="meta-item">
                            <span>📅</span>
                            <span>${formattedDate}</span>
                        </div>
                        ${event.location ? `
                            <div class="meta-item">
                                <span>📍</span>
                                <span>${event.location}</span>
                            </div>
                        ` : ''}
                    </div>
                    <button class="btn-view" aria-label="View details for ${event.title}">View Details</button>
                </div>
            </div>
        </div>
    `;
}

function openEventModal(event) {
    const modal = document.getElementById('eventModal');
    if (!modal) return;

    const eventDate = new Date(event.eventDate);
    const formattedDate = eventDate.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        weekday: 'long'
    });
    const shortDate = eventDate.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
    
    const imageUrl = event.imageUrl || (window.ASSETS_URL || '/assets') + '/images/diy-print.jpg';
    
    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    };
    
    const modalImage = document.getElementById('modalImage');
    if (modalImage) modalImage.src = imageUrl;
    
    setText('modalCategory', event.category);
    setText('modalCategoryDetail', event.category);
    setText('modalTitle', event.title);
    setText('modalTitleDetail', event.title);
    setText('modalDate', formattedDate);
    setText('modalDateShort', shortDate);
    setText('modalLocation', event.location || 'TBA');
    setText('modalCaption', event.caption || event.title);
    setText('modalDescription', event.description?.trim() ? event.description : 'No additional details provided.');
    setText('modalSummary', event.summary?.trim() ? event.summary : 'No dedicated summary provided for this event.');
    
    const galleryContainer = document.getElementById('modalGallery');
    if (galleryContainer) {
        const galleryList = Array.isArray(event.gallery) && event.gallery.length
            ? event.gallery
            : Array.from({ length: 3 }, (_, idx) => `https://via.placeholder.com/400x300/1f2933/ffffff?text=${encodeURIComponent(event.title)}+${idx + 1}`);
        galleryContainer.innerHTML = galleryList
            .map((src, index) => `
                <div class="gallery-item" data-label="Photo ${index + 1}">
                    <img src="${src}" alt="${event.title} gallery image ${index + 1}">
                </div>
            `)
            .join('');
    }
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('eventModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

function filterByCategory(category) {
    currentCategory = category;
    
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.category === category) {
            btn.classList.add('active');
        }
    });
    
    if (category === 'all') {
        displayEventsPage(allEvents);
    } else {
        const filtered = allEvents.filter(event => 
            event.category.toLowerCase() === category.toLowerCase()
        );
        displayEventsPage(filtered);
    }
}

// Initialize events page
if (document.getElementById('eventsContainer')) {
    document.addEventListener('DOMContentLoaded', () => {
        loadEventsPage();
        
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                filterByCategory(btn.dataset.category);
            });
        });
        
        const eventModal = document.getElementById('eventModal');
        if (eventModal) {
            eventModal.addEventListener('click', (e) => {
                if (e.target.id === 'eventModal') {
                    closeModal();
                }
            });
        }
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    });
}

// ============================================
// 10. ADMIN PANEL FUNCTIONS - Full CRUD
// ============================================

// Admin API Base URLs
const ADMIN_API = {
    announcements: window.ADMIN_URL + '/announcements.php',
    events: window.ADMIN_URL + '/events_handler.php',
    holidays: window.ADMIN_URL + '/holidays_handler.php',
    documents: window.ADMIN_URL + '/documents.php',
    inquiries: window.ADMIN_URL + '/inquiries_handler.php',
    sponsors: window.ADMIN_URL + '/sponsors_handler.php',
    subcategories: window.ADMIN_URL + '/subcategories_handler.php'
};

// Tab switching
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
        tab.classList.remove('block');
    });
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active', 'text-indigo-600', 'border-indigo-600');
        btn.classList.add('text-gray-600', 'border-transparent');
    });
    
    // Show selected tab
    const selectedTab = document.getElementById(tabName + '-tab');
    const selectedBtn = Array.from(document.querySelectorAll('.tab-button')).find(btn => 
        btn.textContent.toLowerCase().includes(tabName.toLowerCase())
    );
    
    if (selectedTab) {
        selectedTab.classList.remove('hidden');
        selectedTab.classList.add('block');
    }
    if (selectedBtn) {
        selectedBtn.classList.add('active', 'text-indigo-600', 'border-indigo-600');
        selectedBtn.classList.remove('text-gray-600', 'border-transparent');
    }
    
    // Load data for the tab
    if (tabName === 'announcements') {
        loadAnnouncementsList();
    } else if (tabName === 'events') {
        loadEventsList();
    } else if (tabName === 'calendar') {
        loadHolidaysList();
    } else if (tabName === 'documents') {
        loadDocumentsList();
        loadSubcategoriesList();
    } else if (tabName === 'inquiries') {
        loadInquiriesList();
    } else if (tabName === 'sponsors') {
        loadSponsorsList();
    }
}

// Form toggle
function toggleForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.classList.toggle('hidden');
        if (!form.classList.contains('hidden')) {
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
}

// ============================================
// ANNOUNCEMENTS CRUD
// ============================================

async function loadAnnouncementsList() {
    const container = document.getElementById('announcements-list');
    if (!container) return;
    
    container.innerHTML = '<p>Loading announcements...</p>';
    
    try {
        const response = await fetch(ADMIN_API.announcements);
        const result = await response.json();
        
        if (result.success && result.data) {
            displayAnnouncementsList(result.data);
        } else {
            container.innerHTML = '<p class="error">Error loading announcements</p>';
        }
    } catch (error) {
        container.innerHTML = '<p class="error">Failed to load announcements</p>';
        console.error('Error loading announcements:', error);
    }
}

function displayAnnouncementsList(announcements, pagination = null) {
    const container = document.getElementById('announcements-list');
    if (!container) return;
    
    // Bulk selection state
    const selectedIds = new Set();
    
    if (announcements.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">No announcements found. Create your first announcement!</p>';
        return;
    }
    
    // Add bulk actions UI
    let html = `
        <div class="mb-4 flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="select-all-announcements" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500" onchange="toggleSelectAll('announcements', this.checked)">
                    <span class="text-sm font-medium text-gray-700">Select All</span>
                </label>
                <button onclick="archiveAllItems('announcements')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 text-sm font-medium border-2 border-gray-300">Archive All</button>
                <span id="selected-count-announcements" class="text-sm text-gray-600 hidden">0 selected</span>
            </div>
            <div id="bulk-actions-announcements" class="hidden flex gap-2">
                <button onclick="bulkAction('announcements', 'publish')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">Publish</button>
                <button onclick="bulkAction('announcements', 'archive')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 text-sm font-medium border-2 border-gray-300">Archive</button>
            </div>
        </div>
    `;
    
    html += announcements.map(ann => {
        const date = new Date(ann.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
        let imageUrl = '';
        if (ann.image) {
            // Use PUBLIC_URL if available, otherwise construct from BASE_URL
            let baseUrl = window.PUBLIC_URL;
            if (!baseUrl && window.BASE_URL) {
                baseUrl = window.BASE_URL + '/public';
            }
            if (!baseUrl) {
                // Fallback: try to detect from current location
                const pathParts = window.location.pathname.split('/');
                const icdiIndex = pathParts.indexOf('ICDI');
                if (icdiIndex >= 0) {
                    baseUrl = pathParts.slice(0, icdiIndex + 1).join('/') + '/public';
                } else {
                    baseUrl = '/ICDI/public';
                }
            }
            imageUrl = baseUrl + '/image.php?path=' + encodeURIComponent(ann.image);
        }
        
        return `
            <div class="bg-white border border-gray-200 rounded-xl p-4 mb-4 flex gap-4 items-start hover:shadow-md transition-shadow">
                <input type="checkbox" class="item-checkbox w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500 mt-1" value="${ann.id}" onchange="updateBulkActions('announcements')">
                ${imageUrl ? `<img src="${imageUrl}" alt="${ann.title}" class="w-24 h-24 object-cover rounded-lg flex-shrink-0 bg-gray-100" style="min-width: 96px; min-height: 96px;" onerror="console.error('Image failed to load:', this.src); this.style.display='none'">` : ''}
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-1">${ann.title} ${ann.pinned ? '📌' : ''}</h4>
                            <p class="text-sm text-gray-500">${date} • ${ann.category}</p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="editAnnouncement(${ann.id})" class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">Edit</button>
                            <button onclick="archiveAnnouncement(${ann.id})" class="px-3 py-1.5 text-xs font-semibold text-gray-800 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors border-2 border-gray-300">Archive</button>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">${ann.description || ''}</p>
                    <span class="inline-block px-2 py-1 text-xs font-medium rounded ${ann.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">${ann.status}</span>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = html;
}

function editAnnouncement(id) {
    fetch(ADMIN_API.announcements)
        .then(res => res.json())
        .then(result => {
            if (result.success && result.data) {
                const ann = result.data.find(a => a.id == id);
                if (ann) {
                    // Populate form
                    document.getElementById('ann-id').value = ann.id;
                    document.getElementById('ann-title').value = ann.title;
                    document.getElementById('ann-description').value = ann.description || '';
                    document.getElementById('ann-content').value = ann.content || '';
                    document.getElementById('ann-category').value = ann.category;
                    document.getElementById('ann-pinned').checked = ann.pinned == 1;
                    const annStatus = document.getElementById('ann-status');
                    if (annStatus) annStatus.value = ann.status || 'draft';
                    
                    // Populate meeting fields
                    const isMeeting = ann.is_meeting == 1;
                    document.getElementById('ann-is-meeting').checked = isMeeting;
                    if (isMeeting) {
                        if (ann.meeting_date) {
                            // Convert datetime to datetime-local format (YYYY-MM-DDTHH:mm)
                            const meetingDate = new Date(ann.meeting_date);
                            const localDate = new Date(meetingDate.getTime() - meetingDate.getTimezoneOffset() * 60000);
                            document.getElementById('ann-meeting-date').value = localDate.toISOString().slice(0, 16);
                        }
                        if (ann.meeting_end_date) {
                            const meetingEndDate = new Date(ann.meeting_end_date);
                            const localEndDate = new Date(meetingEndDate.getTime() - meetingEndDate.getTimezoneOffset() * 60000);
                            document.getElementById('ann-meeting-end-date').value = localEndDate.toISOString().slice(0, 16);
                        }
                        document.getElementById('ann-meeting-location').value = ann.meeting_location || '';
                        toggleMeetingFields(); // Show meeting fields
                    } else {
                        toggleMeetingFields(); // Hide meeting fields
                    }
                    
                    // Show image preview if exists
                    if (ann.image) {
                        const preview = document.getElementById('ann-image-preview');
                        const previewImg = document.getElementById('ann-image-preview-img');
                        const oldImage = document.getElementById('ann-old-image');
                        let baseUrl = window.PUBLIC_URL || (window.BASE_URL ? window.BASE_URL + '/public' : null);
                        if (!baseUrl) {
                            const pathParts = window.location.pathname.split('/');
                            const icdiIndex = pathParts.indexOf('ICDI');
                            baseUrl = icdiIndex >= 0 ? pathParts.slice(0, icdiIndex + 1).join('/') + '/public' : '/ICDI/public';
                        }
                        previewImg.src = baseUrl + '/image.php?path=' + encodeURIComponent(ann.image);
                        oldImage.value = ann.image;
                        preview.style.display = 'block';
                    }
                    
                    // Show PDF preview if exists
                    if (ann.pdf_file) {
                        const pdfPreview = document.getElementById('ann-pdf-preview');
                        const pdfFilename = document.getElementById('ann-pdf-filename');
                        const oldPdf = document.getElementById('ann-old-pdf');
                        pdfFilename.textContent = ann.pdf_file.split('/').pop() || 'PDF Document';
                        oldPdf.value = ann.pdf_file;
                        pdfPreview.style.display = 'block';
                    }
                    
                    // Update form title and button
                    document.getElementById('announcement-form-title').textContent = 'Edit Announcement';
                    document.getElementById('ann-submit-btn').textContent = 'Update Announcement';
                    
                    // Update image requirement indicator
                    toggleAnnouncementImageRequired();
                    
                    // Show form
                    toggleForm('announcement-form');
                }
            }
        });
}

function toggleMeetingFields() {
    const isMeeting = document.getElementById('ann-is-meeting').checked;
    const meetingFields = document.getElementById('ann-meeting-fields');
    if (isMeeting) {
        meetingFields.classList.remove('hidden');
        // Make meeting date required when checked
        document.getElementById('ann-meeting-date').required = true;
    } else {
        meetingFields.classList.add('hidden');
        document.getElementById('ann-meeting-date').required = false;
        // Clear meeting fields
        document.getElementById('ann-meeting-date').value = '';
        document.getElementById('ann-meeting-end-date').value = '';
        document.getElementById('ann-meeting-location').value = '';
    }
}

function cancelAnnouncementEdit() {
    // Reset form
    document.getElementById('announcementForm').reset();
    document.getElementById('ann-id').value = '';
    document.getElementById('ann-image-preview').style.display = 'none';
    document.getElementById('ann-old-image').value = '';
    document.getElementById('ann-pdf-preview').style.display = 'none';
    document.getElementById('ann-old-pdf').value = '';
    document.getElementById('announcement-form-title').textContent = 'Create Announcement';
    document.getElementById('ann-submit-btn').textContent = 'Create Announcement';
    // Hide meeting fields
    document.getElementById('ann-meeting-fields').classList.add('hidden');
    document.getElementById('ann-is-meeting').checked = false;
    // Reset image requirement indicator
    toggleAnnouncementImageRequired();
    toggleForm('announcement-form');
}

function resetAndShowAnnouncementForm() {
    // Reset form to create mode first (without toggling visibility)
    const form = document.getElementById('announcementForm');
    if (form) {
        form.reset();
    }
    
    // Explicitly clear all fields
    const annId = document.getElementById('ann-id');
    if (annId) annId.value = '';
    
    const annImagePreview = document.getElementById('ann-image-preview');
    if (annImagePreview) annImagePreview.style.display = 'none';
    
    const annOldImage = document.getElementById('ann-old-image');
    if (annOldImage) annOldImage.value = '';
    
    const annPdfPreview = document.getElementById('ann-pdf-preview');
    if (annPdfPreview) annPdfPreview.style.display = 'none';
    
    const annOldPdf = document.getElementById('ann-old-pdf');
    if (annOldPdf) annOldPdf.value = '';
    
    const formTitle = document.getElementById('announcement-form-title');
    if (formTitle) formTitle.textContent = 'Create Announcement';
    
    const submitBtn = document.getElementById('ann-submit-btn');
    if (submitBtn) submitBtn.textContent = 'Create Announcement';
    
    // Hide meeting fields
    const meetingFields = document.getElementById('ann-meeting-fields');
    if (meetingFields) meetingFields.classList.add('hidden');
    
    const isMeeting = document.getElementById('ann-is-meeting');
    if (isMeeting) isMeeting.checked = false;
    
    // Reset image requirement indicator
    toggleAnnouncementImageRequired();
    
    // Show the form
    const announcementForm = document.getElementById('announcement-form');
    if (announcementForm) {
        announcementForm.classList.remove('hidden');
        announcementForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

async function archiveAnnouncement(id) {
    if (!confirm('Are you sure you want to archive this announcement?')) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'archive');
        formData.append('id', id);
        if (window.CSRF_TOKEN) {
            formData.append('csrf_token', window.CSRF_TOKEN);
        }
        
        const response = await fetch(ADMIN_API.announcements, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Announcement archived successfully', 'success');
            loadAnnouncementsList();
        } else {
            showAlert(result.error || 'Error archiving announcement', 'error');
        }
    } catch (error) {
        showAlert('Failed to archive announcement', 'error');
        console.error('Error:', error);
    }
}

// Toggle image required indicator based on status and pinned checkbox
function toggleAnnouncementImageRequired() {
    const statusEl = document.getElementById('ann-status');
    const pinnedEl = document.getElementById('ann-pinned');
    const imageRequiredIndicator = document.getElementById('ann-image-required-indicator');
    const imageOptionalText = document.getElementById('ann-image-optional-text');
    const imageRequiredText = document.getElementById('ann-image-required-text');
    const imageInput = document.getElementById('ann-image');
    const oldImageInput = document.getElementById('ann-old-image');
    
    if (!statusEl || !pinnedEl || !imageRequiredIndicator) return;
    
    const isPublished = statusEl.value === 'published';
    const isPinned = pinnedEl.checked;
    const isStrongAnnouncement = isPublished || isPinned;
    const hasImage = (imageInput && imageInput.files.length > 0) || (oldImageInput && oldImageInput.value);
    
    if (isStrongAnnouncement) {
        imageRequiredIndicator.classList.remove('hidden');
        imageOptionalText.classList.add('hidden');
        imageRequiredText.classList.remove('hidden');
        if (imageInput) {
            imageInput.setAttribute('required', 'required');
        }
    } else {
        imageRequiredIndicator.classList.add('hidden');
        imageOptionalText.classList.remove('hidden');
        imageRequiredText.classList.add('hidden');
        if (imageInput) {
            imageInput.removeAttribute('required');
        }
    }
}

// Announcement form submission
document.addEventListener('DOMContentLoaded', function() {
    const annForm = document.getElementById('announcementForm');
    if (annForm) {
        const annStatusEl = document.getElementById('ann-status');
        const annPinnedEl = document.getElementById('ann-pinned');
        
        if (window.ADMIN_CAN_PUBLISH === false && annStatusEl) {
            annStatusEl.innerHTML = '<option value="draft">Draft</option><option value="pending_review">Pending Review</option>';
            annStatusEl.value = 'draft';
        }
        
        // Add event listeners to toggle image requirement
        if (annStatusEl) {
            annStatusEl.addEventListener('change', toggleAnnouncementImageRequired);
        }
        if (annPinnedEl) {
            annPinnedEl.addEventListener('change', toggleAnnouncementImageRequired);
        }
        
        // Also check when image is selected/removed
        const annImageEl = document.getElementById('ann-image');
        if (annImageEl) {
            annImageEl.addEventListener('change', function() {
                // Update old_image hidden field when new image is selected
                const oldImageEl = document.getElementById('ann-old-image');
                if (this.files.length > 0 && oldImageEl) {
                    // Keep old image value for validation purposes
                }
                toggleAnnouncementImageRequired();
            });
        }
        
        // Handle PDF file selection preview
        const annPdfEl = document.getElementById('ann-pdf');
        if (annPdfEl) {
            annPdfEl.addEventListener('change', function() {
                const pdfPreview = document.getElementById('ann-pdf-preview');
                const pdfFilename = document.getElementById('ann-pdf-filename');
                if (this.files.length > 0) {
                    pdfFilename.textContent = this.files[0].name;
                    pdfPreview.style.display = 'block';
                } else {
                    pdfPreview.style.display = 'none';
                }
            });
        }
        
        // Check on page load
        toggleAnnouncementImageRequired();
        annForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            if (window.CSRF_TOKEN) {
                formData.append('csrf_token', window.CSRF_TOKEN);
            }
            
            // Clear previous errors
            document.querySelectorAll('.field-error').forEach(el => el.remove());
            document.querySelectorAll('.border-red-500').forEach(el => {
                el.classList.remove('border-red-500', 'border-2');
                el.classList.add('border-gray-200');
            });
            
            // Validate form
            const validationErrors = validateAnnouncementForm(formData);
            if (validationErrors.length > 0) {
                validationErrors.forEach(error => {
                    showFieldError(error.field, error.message);
                });
                showAlert('Please fix the errors in the form', 'error');
                return;
            }
            
            const id = formData.get('id');
            const action = (id && id !== '' && id !== '0') ? 'update' : 'create';
            formData.append('action', action);
            
            // Set status - ensure it's always set
            const statusValue = annStatusEl ? annStatusEl.value : 'draft';
            // Remove existing status if present, then append new one
            if (formData.has('status')) {
                formData.delete('status');
            }
            formData.append('status', statusValue);
            
            // Debug: Log form data (for troubleshooting)
            console.log('Submitting announcement:', {
                action: action,
                id: id,
                status: statusValue,
                title: formData.get('title'),
                description: formData.get('description') ? 'present' : 'missing'
            });
            
            // Show loading state
            const submitBtn = document.getElementById('ann-submit-btn');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            
            try {
                const response = await fetch(ADMIN_API.announcements, {
                    method: 'POST',
                    body: formData
                });
                
                // Check if response is ok
                if (!response.ok) {
                    const errorText = await response.text();
                    let errorMessage = 'Error saving announcement';
                    try {
                        const errorJson = JSON.parse(errorText);
                        errorMessage = errorJson.error || errorJson.message || errorMessage;
                    } catch (e) {
                        errorMessage = errorText || `Server error: ${response.status} ${response.statusText}`;
                    }
                    showAlert(errorMessage, 'error');
                    console.error('Server error:', errorText);
                    return;
                }
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    this.reset();
                    cancelAnnouncementEdit();
                    loadAnnouncementsList();
                } else {
                    const errorMsg = result.error || result.message || 'Error saving announcement';
                    showAlert(errorMsg, 'error');
                    if (result.errors) {
                        result.errors.forEach(error => {
                            const fieldId = 'ann-' + error.field;
                            showFieldError(fieldId, error.message);
                        });
                    }
                }
            } catch (error) {
                const errorMsg = error.message || 'Failed to save announcement. Please check your connection and try again.';
                showAlert(errorMsg, 'error');
                console.error('Error:', error);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }
});

// ============================================
// EVENTS CRUD
// ============================================

async function loadEventsList() {
    const container = document.getElementById('events-list');
    if (!container) return;
    
    container.innerHTML = '<p>Loading events...</p>';
    
    try {
        const response = await fetch(ADMIN_API.events);
        const result = await response.json();
        
        if (result.success && result.data) {
            displayEventsList(result.data);
        } else {
            container.innerHTML = '<p class="error">Error loading events</p>';
        }
    } catch (error) {
        container.innerHTML = '<p class="error">Failed to load events</p>';
        console.error('Error loading events:', error);
    }
}

function displayEventsList(events, pagination = null) {
    const container = document.getElementById('events-list');
    if (!container) return;
    
    if (events.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">No events found. Create your first event!</p>';
        return;
    }
    
    // Add bulk actions UI
    let html = `
        <div class="mb-4 flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="select-all-events" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500" onchange="toggleSelectAll('events', this.checked)">
                    <span class="text-sm font-medium text-gray-700">Select All</span>
                </label>
                <button onclick="archiveAllItems('events')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 text-sm font-medium border-2 border-gray-300">Archive All</button>
                <span id="selected-count-events" class="text-sm text-gray-600 hidden">0 selected</span>
            </div>
            <div id="bulk-actions-events" class="hidden flex gap-2">
                <button onclick="bulkAction('events', 'publish')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">Publish</button>
                <button onclick="bulkAction('events', 'archive')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 text-sm font-medium border-2 border-gray-300">Archive</button>
            </div>
        </div>
    `;
    
    html += events.map(event => {
        // Format date range or single date
        const startDate = new Date(event.date);
        let date = startDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
        if (event.end_date && event.end_date !== event.date) {
            const endDate = new Date(event.end_date);
            const endDateStr = endDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            date = date + ' - ' + endDateStr;
        }
        let imageUrl = '';
        if (event.image) {
            // Use PUBLIC_URL if available, otherwise construct from BASE_URL
            let baseUrl = window.PUBLIC_URL;
            if (!baseUrl && window.BASE_URL) {
                baseUrl = window.BASE_URL + '/public';
            }
            if (!baseUrl) {
                // Fallback: try to detect from current location
                const pathParts = window.location.pathname.split('/');
                const icdiIndex = pathParts.indexOf('ICDI');
                if (icdiIndex >= 0) {
                    baseUrl = pathParts.slice(0, icdiIndex + 1).join('/') + '/public';
                } else {
                    baseUrl = '/ICDI/public';
                }
            }
            imageUrl = baseUrl + '/image.php?path=' + encodeURIComponent(event.image);
        }
        
        return `
            <div class="bg-white border border-gray-200 rounded-xl p-4 mb-4 flex gap-4 items-start hover:shadow-md transition-shadow">
                <input type="checkbox" class="item-checkbox w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500 mt-1" value="${event.id}" onchange="updateBulkActions('events')">
                ${imageUrl ? `<img src="${imageUrl}" alt="${event.title}" class="w-36 h-24 object-cover rounded-lg flex-shrink-0 bg-gray-100" style="min-width: 144px; min-height: 96px;" onerror="console.error('Image failed to load:', this.src); this.style.display='none'">` : ''}
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-1">${event.title}</h4>
                            <p class="text-sm text-gray-500">${date} • ${event.location || 'No location'} • ${event.category}</p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="editEvent(${event.id})" class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">Edit</button>
                            <button onclick="archiveEvent(${event.id})" class="px-3 py-1.5 text-xs font-semibold text-gray-800 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors border-2 border-gray-300">Archive</button>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">${event.caption || event.description || ''}</p>
                    <span class="inline-block px-2 py-1 text-xs font-medium rounded ${event.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">${event.status}</span>
                </div>
            </div>
        `;
    }).join('');
    
    // Add pagination
    if (pagination && pagination.total_pages > 1) {
        html += generatePagination('events', pagination);
    }
    
    container.innerHTML = html;
}

function editEvent(id) {
    fetch(ADMIN_API.events)
        .then(res => res.json())
        .then(result => {
            if (result.success && result.data) {
                const event = result.data.find(e => e.id == id);
                if (event) {
                    // Populate form
                    document.getElementById('evt-id').value = event.id;
                    document.getElementById('evt-title').value = event.title;
                    document.getElementById('evt-caption').value = event.caption || '';
                    document.getElementById('evt-description').value = event.description || '';
                    document.getElementById('evt-summary').value = event.summary || '';
                    document.getElementById('evt-category').value = event.category;
                    document.getElementById('evt-schedule-type').value = event.schedule_type || 'event';
                    document.getElementById('evt-date').value = event.date;
                    document.getElementById('evt-end-date').value = event.end_date || '';
                    document.getElementById('evt-location').value = event.location || '';
                    document.getElementById('evt-order').value = event.display_order || 0;
                    
                    // Show image preview if exists
                    if (event.image) {
                        const preview = document.getElementById('evt-image-preview');
                        const previewImg = document.getElementById('evt-image-preview-img');
                        const oldImage = document.getElementById('evt-old-image');
                        let baseUrl = window.PUBLIC_URL || (window.BASE_URL ? window.BASE_URL + '/public' : null);
                        if (!baseUrl) {
                            const pathParts = window.location.pathname.split('/');
                            const icdiIndex = pathParts.indexOf('ICDI');
                            baseUrl = icdiIndex >= 0 ? pathParts.slice(0, icdiIndex + 1).join('/') + '/public' : '/ICDI/public';
                        }
                        previewImg.src = baseUrl + '/image.php?path=' + encodeURIComponent(event.image);
                        oldImage.value = event.image;
                        preview.style.display = 'block';
                        document.getElementById('evt-image').required = false;
                    }
                    
                    // Show gallery preview if exists
                    if (event.gallery) {
                        const gallery = JSON.parse(event.gallery);
                        const oldGallery = document.getElementById('evt-old-gallery');
                        oldGallery.value = event.gallery;
                        displayGalleryPreview(gallery);
                    }
                    
                    // Update form title and button
                    document.getElementById('event-form-title').textContent = 'Edit Event';
                    document.getElementById('evt-submit-btn').textContent = 'Update Event';
                    
                    // Show form
                    toggleForm('event-form');
                }
            }
        });
}

function displayGalleryPreview(gallery) {
    const preview = document.getElementById('evt-gallery-preview');
    if (!preview) return;
    if (!Array.isArray(gallery) || gallery.length === 0) {
        preview.innerHTML = '';
        preview.style.display = 'none';
        return;
    }
    let baseUrl = window.PUBLIC_URL || (window.BASE_URL ? window.BASE_URL + '/public' : null);
    if (!baseUrl) {
        const pathParts = window.location.pathname.split('/');
        const icdiIndex = pathParts.indexOf('ICDI');
        baseUrl = icdiIndex >= 0 ? pathParts.slice(0, icdiIndex + 1).join('/') + '/public' : '/ICDI/public';
    }
    preview.innerHTML = gallery.map((img, index) => {
        const imgUrl = baseUrl + '/image.php?path=' + encodeURIComponent(img);
        return `<div class="relative rounded-lg overflow-hidden border-2 border-gray-200 bg-gray-50"><img src="${imgUrl}" alt="Gallery ${index + 1}" class="w-full h-24 object-cover"></div>`;
    }).join('');
    preview.style.display = 'grid';
}

// Live preview when user selects multiple gallery files (admin event form)
(function initGalleryFilePreview() {
    document.addEventListener('DOMContentLoaded', function() {
        const galleryInput = document.getElementById('evt-gallery');
        const preview = document.getElementById('evt-gallery-preview');
        if (!galleryInput || !preview) return;
        galleryInput.addEventListener('change', function() {
            const files = this.files;
            if (!files || files.length === 0) return;
            preview.innerHTML = '';
            preview.style.display = 'grid';
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (!file.type.startsWith('image/')) continue;
                const div = document.createElement('div');
                div.className = 'relative rounded-lg overflow-hidden border-2 border-gray-200 bg-gray-50';
                const img = document.createElement('img');
                img.alt = 'Gallery ' + (i + 1);
                img.className = 'w-full h-24 object-cover';
                div.appendChild(img);
                const reader = new FileReader();
                reader.onload = (function(el) { return function(e) { el.src = e.target.result; }; })(img);
                reader.readAsDataURL(file);
                preview.appendChild(div);
            }
        });
    });
})();

function cancelEventEdit() {
    // Reset form
    document.getElementById('eventForm').reset();
    document.getElementById('evt-id').value = '';
    document.getElementById('evt-image-preview').style.display = 'none';
    document.getElementById('evt-old-image').value = '';
    document.getElementById('evt-gallery-preview').innerHTML = '';
    document.getElementById('evt-gallery-preview').style.display = 'none';
    document.getElementById('evt-old-gallery').value = '';
    document.getElementById('evt-image').required = true;
    document.getElementById('event-form-title').textContent = 'Create Event';
    document.getElementById('evt-submit-btn').textContent = 'Create Event';
    toggleForm('event-form');
}

async function archiveEvent(id) {
    if (!confirm('Are you sure you want to archive this event?')) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'archive');
        formData.append('id', id);
        if (window.CSRF_TOKEN) {
            formData.append('csrf_token', window.CSRF_TOKEN);
        }
        
        const response = await fetch(ADMIN_API.events, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Event archived successfully', 'success');
            loadEventsList();
        } else {
            showAlert(result.error || 'Error archiving event', 'error');
        }
    } catch (error) {
        showAlert('Failed to archive event', 'error');
        console.error('Error:', error);
    }
}

// Event form submission
document.addEventListener('DOMContentLoaded', function() {
    const eventForm = document.getElementById('eventForm');
    if (eventForm) {
        const evtStatusEl = document.getElementById('evt-status');
        if (window.ADMIN_CAN_PUBLISH === false && evtStatusEl) {
            evtStatusEl.innerHTML = '<option value="draft">Draft</option><option value="pending_review">Pending Review</option>';
            evtStatusEl.value = 'draft';
        }
        eventForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            if (window.CSRF_TOKEN) {
                formData.append('csrf_token', window.CSRF_TOKEN);
            }
            
            // Clear previous errors
            document.querySelectorAll('.field-error').forEach(el => el.remove());
            document.querySelectorAll('.border-red-500').forEach(el => {
                el.classList.remove('border-red-500', 'border-2');
                el.classList.add('border-gray-200');
            });
            
            // Validate form
            const validationErrors = validateEventForm(formData);
            if (validationErrors.length > 0) {
                validationErrors.forEach(error => {
                    showFieldError(error.field, error.message);
                });
                showAlert('Please fix the errors in the form', 'error');
                return;
            }
            
            const id = formData.get('id');
            formData.append('action', id ? 'update' : 'create');
            const evtStatusEl = document.getElementById('evt-status');
            formData.set('status', evtStatusEl ? evtStatusEl.value : 'draft');
            
            // Explicitly append multiple gallery files (FormData from form can miss multiple in some browsers)
            const galleryInput = document.getElementById('evt-gallery');
            if (galleryInput && galleryInput.files && galleryInput.files.length > 0) {
                formData.delete('gallery[]');
                for (let i = 0; i < galleryInput.files.length; i++) {
                    formData.append('gallery[]', galleryInput.files[i]);
                }
            }
            
            // Show loading state
            const submitBtn = document.getElementById('evt-submit-btn');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            
            try {
                const response = await fetch(ADMIN_API.events, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    this.reset();
                    cancelEventEdit();
                    loadEventsList();
                } else {
                    showAlert(result.error || 'Error saving event', 'error');
                    if (result.errors) {
                        result.errors.forEach(error => {
                            const fieldId = 'evt-' + error.field;
                            showFieldError(fieldId, error.message);
                        });
                    }
                }
            } catch (error) {
                showAlert('Failed to save event', 'error');
                console.error('Error:', error);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }
    
    // Holiday form submission
    const holidayForm = document.getElementById('holidayForm');
    if (holidayForm) {
        holidayForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const typeEl = document.getElementById('hol-type');
            const typeLabelEl = document.getElementById('hol-type-label');
            if (typeEl && typeEl.value === 'school' && (!typeLabelEl || !typeLabelEl.value.trim())) {
                showAlert('Event type is required when Type is "School (enter type below)".', 'error');
                return;
            }
            const formData = new FormData(this);
            if (window.CSRF_TOKEN) {
                formData.append('csrf_token', window.CSRF_TOKEN);
            }
            const action = document.getElementById('hol-action').value || 'create';
            formData.set('action', action);
            try {
                const response = await fetch(ADMIN_API.holidays, { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    showAlert(result.message, 'success');
                    cancelHolidayEdit();
                    loadHolidaysList();
                } else {
                    showAlert(result.error || 'Error saving calendar entry', 'error');
                }
            } catch (error) {
                showAlert('Failed to save calendar entry', 'error');
            }
        });
    }
    // When type is school calendar, show "What event is it?" for the description field
    const holType = document.getElementById('hol-type');
    if (holType) {
        holType.addEventListener('change', function() {
            updateHolidayDescriptionLabel();
            updateHolidayTypeLabelVisibility();
        });
        updateHolidayDescriptionLabel();
        updateHolidayTypeLabelVisibility();
    }
});

// ============================================
// CALENDAR (HOLIDAYS) CRUD
// ============================================

async function loadHolidaysList() {
    const container = document.getElementById('holidays-list');
    if (!container) return;
    
    container.innerHTML = '<p class="text-gray-500 text-center py-8">Loading calendar...</p>';
    
    try {
        const response = await fetch(ADMIN_API.holidays);
        const result = await response.json();
        
        if (result.success && result.data) {
            displayHolidaysList(result.data);
        } else {
            container.innerHTML = '<p class="text-gray-500 text-center py-8">Calendar table not set up yet. Run migration_create_holidays.sql and seed_holidays_ph_dasma.sql.</p>';
        }
    } catch (error) {
        container.innerHTML = '<p class="error">Failed to load calendar.</p>';
        console.error('Error loading holidays:', error);
    }
}

const HOLIDAY_TYPE_LABELS = {
    regular: 'Regular', special_non_working: 'Special Non-Working', special_working: 'Special Working', dasma: 'Dasma',
    enrollment: 'Enrollment', start_of_school: 'Start of School', wellness_break: 'Wellness Break', christmas_break: 'Christmas Break', year_end: 'Year End', school_end: 'School End', school: 'School'
};

const HOLIDAY_SCHOOL_TYPES = ['enrollment', 'start_of_school', 'wellness_break', 'christmas_break', 'year_end', 'school_end', 'school'];

function updateHolidayDescriptionLabel() {
    const typeEl = document.getElementById('hol-type');
    const labelEl = document.getElementById('hol-description-label');
    const textareaEl = document.getElementById('hol-description');
    const hintEl = document.getElementById('hol-description-hint');
    if (!typeEl || !labelEl || !textareaEl) return;
    const isSchool = HOLIDAY_SCHOOL_TYPES.includes((typeEl.value || '').trim());
    if (isSchool) {
        labelEl.textContent = 'What event is it? (optional)';
        textareaEl.placeholder = 'e.g. Enrollment for Grade 11, Wellness break for all students, Christmas break (campus closed)';
        if (hintEl) hintEl.classList.remove('hidden');
    } else {
        labelEl.textContent = 'Description (optional)';
        textareaEl.placeholder = 'e.g. Details or notes';
        if (hintEl) hintEl.classList.add('hidden');
    }
}

function updateHolidayTypeLabelVisibility() {
    const typeEl = document.getElementById('hol-type');
    const wrap = document.getElementById('hol-type-label-wrap');
    const input = document.getElementById('hol-type-label');
    const labelEl = document.getElementById('hol-type-label-label');
    if (!typeEl || !wrap) return;
    const val = (typeEl.value || '').trim();
    const isSchoolType = HOLIDAY_SCHOOL_TYPES.includes(val);
    if (isSchoolType) {
        wrap.classList.remove('hidden');
        if (val === 'school') {
            labelEl.textContent = 'Event type *';
            if (input) input.required = true;
        } else {
            labelEl.textContent = 'Event type (optional – override label)';
            if (input) input.required = false;
        }
    } else {
        wrap.classList.add('hidden');
        if (input) input.required = false;
    }
}

const HOLIDAY_ONLY_TYPES = ['regular', 'special_non_working', 'special_working', 'dasma'];

function getMonthKey(dateStr) {
    if (!dateStr || dateStr.length < 7) return '';
    return dateStr.substring(0, 7);
}
function getMonthLabel(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr + 'T12:00:00');
    return d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
}

function renderHolidayCard(h) {
    const startDate = new Date(h.date + 'T12:00:00').toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    let dateRange = startDate;
    if (h.end_date && h.end_date !== h.date) {
        const endDate = new Date(h.end_date + 'T12:00:00').toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        dateRange = startDate + ' – ' + endDate;
    }
    const typeLabel = (h.type_label && h.type_label.trim()) ? h.type_label.trim() : (HOLIDAY_TYPE_LABELS[h.type] || h.type);
    const descSnippet = h.description ? (h.description.substring(0, 80) + (h.description.length > 80 ? '…' : '')) : '';
    return `
        <div class="bg-white border border-gray-200 rounded-xl p-4 mb-3 flex gap-4 items-start hover:shadow-md transition-shadow">
            <div class="flex-1 min-w-0">
                <h4 class="text-lg font-semibold text-gray-900 mb-1">${(h.name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</h4>
                <p class="text-sm text-gray-500">${dateRange} • ${h.region} • ${(typeLabel || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</p>
                ${descSnippet ? '<p class="text-sm text-gray-600 mt-2">' + descSnippet.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</p>' : ''}
            </div>
            <div class="flex gap-2 flex-shrink-0">
                <button onclick="editHoliday(${h.id})" class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">Edit</button>
                <button onclick="deleteHoliday(${h.id})" class="px-3 py-1.5 text-xs font-semibold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">Delete</button>
            </div>
        </div>
    `;
}

function displayHolidaysList(holidays) {
    const container = document.getElementById('holidays-list');
    if (!container) return;
    
    if (!holidays || holidays.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">No entries yet. Add calendar entries (school calendar or PH/Dasma holidays).</p>';
        return;
    }
    const sorted = [...holidays].sort((a, b) => (a.date || '').localeCompare(b.date || ''));
    const holidayOnly = sorted.filter(h => HOLIDAY_ONLY_TYPES.includes((h.type || '').trim()));
    const schoolPurpose = sorted.filter(h => HOLIDAY_SCHOOL_TYPES.includes((h.type || '').trim()));
    function groupByMonth(arr) {
        const byMonth = {};
        arr.forEach(h => {
            const key = getMonthKey(h.date) || 'unknown';
            if (!byMonth[key]) byMonth[key] = [];
            byMonth[key].push(h);
        });
        return Object.keys(byMonth).sort().map(key => ({ key, label: key === 'unknown' ? 'Other' : getMonthLabel(byMonth[key][0].date), items: byMonth[key] }));
    }
    const holidayByMonth = groupByMonth(holidayOnly);
    const schoolByMonth = groupByMonth(schoolPurpose);
    const menuEl = document.getElementById('holidays-list-menu');
    if (menuEl) {
        if (holidayOnly.length > 0 || schoolPurpose.length > 0) {
            menuEl.classList.remove('hidden');
            const linkHolidays = menuEl.querySelector('a[href="#calendar-section-holidays"]');
            const linkSchool = menuEl.querySelector('a[href="#calendar-section-school"]');
            if (linkHolidays) linkHolidays.style.display = holidayOnly.length > 0 ? '' : 'none';
            if (linkSchool) linkSchool.style.display = schoolPurpose.length > 0 ? '' : 'none';
        } else {
            menuEl.classList.add('hidden');
        }
    }
    let html = '';
    if (holidayOnly.length > 0) {
        html += '<section id="calendar-section-holidays" class="mb-8 scroll-mt-4"><h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b-2 border-amber-200">Holidays (PH / Dasma)</h3>';
        holidayByMonth.forEach(({ key, label, items }) => {
            html += '<div class="mb-5"><h4 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">' + (label || key).replace(/</g, '&lt;') + '</h4>';
            items.forEach(h => { html += renderHolidayCard(h); });
            html += '</div>';
        });
        html += '</section>';
    }
    if (schoolPurpose.length > 0) {
        html += '<section id="calendar-section-school" class="mb-4 scroll-mt-4"><h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b-2 border-indigo-200">School calendar</h3>';
        schoolByMonth.forEach(({ key, label, items }) => {
            html += '<div class="mb-5"><h4 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">' + (label || key).replace(/</g, '&lt;') + '</h4>';
            items.forEach(h => { html += renderHolidayCard(h); });
            html += '</div>';
        });
        html += '</section>';
    }
    if (holidayOnly.length === 0 && schoolPurpose.length === 0) {
        html = '<p class="text-gray-500 text-center py-8">No entries yet.</p>';
    }
    container.innerHTML = html;
    document.querySelectorAll('.calendar-list-jump').forEach(a => {
        a.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

function editHoliday(id) {
    fetch(ADMIN_API.holidays)
        .then(res => res.json())
        .then(result => {
            if (result.success && result.data) {
                const h = result.data.find(x => x.id == id);
                if (h) {
                    document.getElementById('hol-id').value = h.id;
                    document.getElementById('hol-action').value = 'update';
                    document.getElementById('hol-date').value = h.date || '';
                    document.getElementById('hol-end-date').value = h.end_date || '';
                    document.getElementById('hol-name').value = h.name || '';
                    document.getElementById('hol-type').value = h.type || 'regular';
                    document.getElementById('hol-type-label').value = h.type_label || '';
                    document.getElementById('hol-region').value = h.region || 'PH';
                    document.getElementById('hol-description').value = h.description || '';
                    updateHolidayDescriptionLabel();
                    updateHolidayTypeLabelVisibility();
                    document.getElementById('holiday-form-title').textContent = 'Edit Calendar Entry';
                    document.getElementById('hol-submit-btn').textContent = 'Update';
                    toggleForm('holiday-form');
                }
            }
        });
}

function cancelHolidayEdit() {
    document.getElementById('holidayForm').reset();
    document.getElementById('hol-id').value = '';
    document.getElementById('hol-action').value = 'create';
    document.getElementById('hol-end-date').value = '';
    const typeLabelInput = document.getElementById('hol-type-label');
    if (typeLabelInput) typeLabelInput.required = false;
    updateHolidayTypeLabelVisibility();
    document.getElementById('holiday-form-title').textContent = 'Add Calendar Entry';
    document.getElementById('hol-submit-btn').textContent = 'Add Calendar Entry';
    toggleForm('holiday-form');
}

async function deleteHoliday(id) {
    if (!confirm('Delete this entry from the calendar?')) return;
    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        if (window.CSRF_TOKEN) {
            formData.append('csrf_token', window.CSRF_TOKEN);
        }
        const response = await fetch(ADMIN_API.holidays, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            showAlert(result.message || 'Calendar entry deleted', 'success');
            loadHolidaysList();
        } else {
            showAlert(result.error || 'Error deleting calendar entry', 'error');
        }
    } catch (error) {
        showAlert('Failed to delete calendar entry', 'error');
    }
}

// ============================================
// DOCUMENTS CRUD
// ============================================

async function loadDocumentsList() {
    const container = document.getElementById('documents-list');
    if (!container) return;
    
    container.innerHTML = '<p>Loading documents...</p>';
    
    try {
        const response = await fetch(ADMIN_API.documents);
        const result = await response.json();
        
        if (result.success && result.data) {
            displayDocumentsList(result.data);
        } else {
            container.innerHTML = '<p class="error">Error loading documents</p>';
        }
    } catch (error) {
        container.innerHTML = '<p class="error">Failed to load documents</p>';
        console.error('Error loading documents:', error);
    }
}

function displayDocumentsList(documents, pagination = null) {
    const container = document.getElementById('documents-list');
    if (!container) return;
    
    if (documents.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">No documents found. Create your first document!</p>';
        return;
    }
    
    // Add bulk actions UI
    let html = `
        <div class="mb-4 flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="select-all-documents" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500" onchange="toggleSelectAll('documents', this.checked)">
                    <span class="text-sm font-medium text-gray-700">Select All</span>
                </label>
                <button onclick="archiveAllItems('documents')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 text-sm font-medium border-2 border-gray-300">Archive All</button>
                <span id="selected-count-documents" class="text-sm text-gray-600 hidden">0 selected</span>
            </div>
            <div id="bulk-actions-documents" class="hidden flex gap-2">
                <button onclick="bulkAction('documents', 'publish')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">Publish</button>
                <button onclick="bulkAction('documents', 'archive')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 text-sm font-medium border-2 border-gray-300">Archive</button>
            </div>
        </div>
    `;
    
    const categoryNames = {
        '01': 'OFFICES REPORT',
        '02': 'EXECUTIVE ORDER',
        '03': 'ORDINANCE',
        '04': 'RESOLUTION',
        '05': 'OTHER'
    };
    
    const documentsHtml = documents.map(doc => {
        const date = new Date(doc.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
        const fileSize = doc.file_size ? (doc.file_size / 1024).toFixed(2) + ' KB' : 'N/A';
        
        return `
            <div class="bg-white border border-gray-200 rounded-xl p-4 mb-4 flex gap-4 items-start hover:shadow-md transition-shadow">
                <input type="checkbox" class="item-checkbox w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500 mt-1" value="${doc.id}" onchange="updateBulkActions('documents')">
                <div class="text-5xl flex-shrink-0">📄</div>
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-1">${doc.title}</h4>
                            <p class="text-sm text-gray-500">${date} • ${categoryNames[doc.category] || doc.category} • ${fileSize}</p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="editDocument(${doc.id})" class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">Edit</button>
                            <button onclick="archiveDocument(${doc.id})" class="px-3 py-1.5 text-xs font-semibold text-gray-800 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors border-2 border-gray-300">Archive</button>
                        </div>
                    </div>
                    ${doc.description ? `<p class="text-sm text-gray-600 mb-2">${doc.description}</p>` : ''}
                    <span class="inline-block px-2 py-1 text-xs font-medium rounded ${doc.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">${doc.status}</span>
                </div>
            </div>
        `;
    }).join('');
    
    html += documentsHtml;
    
    // Add pagination
    if (pagination && pagination.total_pages > 1) {
        html += generatePagination('documents', pagination);
    }
    
    container.innerHTML = html;
}

// Dynamic subcategory loading from API
async function updateDocumentSubcategory() {
    const category = document.getElementById('doc-category').value;
    const subcategorySelect = document.getElementById('doc-subcategory');
    const documentTypeGroup = document.getElementById('doc-document-type-group');
    
    // Clear existing options
    subcategorySelect.innerHTML = '<option value="">Loading subcategories...</option>';
    subcategorySelect.disabled = true;
    
    if (!category) {
        subcategorySelect.innerHTML = '<option value="">Select subcategory...</option>';
        subcategorySelect.disabled = false;
        return;
    }
    
    // Show/hide document type group based on category
    if (category === '02') {
        documentTypeGroup.style.display = 'block';
    } else {
        documentTypeGroup.style.display = 'none';
    }
    
    try {
        // Fetch subcategories from API
        const response = await fetch(`${ADMIN_API.subcategories}?category=${category}`);
        const result = await response.json();
        
        subcategorySelect.innerHTML = '<option value="">Select subcategory...</option>';
        
        if (result.success && result.data && result.data.length > 0) {
            result.data.forEach(subcat => {
                const option = document.createElement('option');
                option.value = subcat.name;
                option.textContent = subcat.name;
                subcategorySelect.appendChild(option);
            });
        } else {
            // No subcategories found - show message
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No subcategories available. Create one first.';
            option.disabled = true;
            subcategorySelect.appendChild(option);
        }
        
        subcategorySelect.style.display = 'block';
        subcategorySelect.disabled = false;
    } catch (error) {
        console.error('Error loading subcategories:', error);
        subcategorySelect.innerHTML = '<option value="">Error loading subcategories</option>';
        subcategorySelect.disabled = false;
    }
}

async function editDocument(id) {
    try {
        const response = await fetch(ADMIN_API.documents);
        const result = await response.json();
        
        if (result.success && result.data) {
            const doc = result.data.find(d => d.id == id);
            if (doc) {
                // Populate form
                document.getElementById('doc-id').value = doc.id;
                document.getElementById('doc-title').value = doc.title;
                document.getElementById('doc-description').value = doc.description || '';
                document.getElementById('doc-category').value = doc.category;
                
                // Update subcategory dropdown based on category (wait for it to load)
                await updateDocumentSubcategory();
                
                // Wait a bit for dropdown to fully populate, then set subcategory value
                await new Promise(resolve => setTimeout(resolve, 100));
                
                // Populate subcategory after dropdown is loaded
                const subcategorySelect = document.getElementById('doc-subcategory');
                if (doc.subcategory && subcategorySelect) {
                    // Try to set the value
                    subcategorySelect.value = doc.subcategory;
                    
                    // If value didn't set (option might not exist yet), add it temporarily
                    if (subcategorySelect.value !== doc.subcategory) {
                        const option = document.createElement('option');
                        option.value = doc.subcategory;
                        option.textContent = doc.subcategory;
                        option.selected = true;
                        subcategorySelect.appendChild(option);
                    }
                }
                    if (doc.document_type) {
                        document.getElementById('doc-document-type').value = doc.document_type;
                    }
                    if (doc.series_year) {
                        document.getElementById('doc-series-year').value = doc.series_year;
                    }
                    if (doc.academic_year) {
                        document.getElementById('doc-academic-year').value = doc.academic_year;
                    }
                    
                    const docStatus = document.getElementById('doc-status');
                    if (docStatus) docStatus.value = doc.status || 'draft';
                    
                    // Store old file path
                    if (doc.file_path) {
                        const oldFileInput = document.createElement('input');
                        oldFileInput.type = 'hidden';
                        oldFileInput.id = 'doc-old-file-path';
                        oldFileInput.name = 'old_file_path';
                        oldFileInput.value = doc.file_path;
                        const form = document.getElementById('documentForm');
                        if (!form.querySelector('#doc-old-file-path')) {
                            form.appendChild(oldFileInput);
                        } else {
                            form.querySelector('#doc-old-file-path').value = doc.file_path;
                        }
                    }
                    
                    // Update form title and button
                    document.getElementById('document-form-title').textContent = 'Edit Document';
                    document.getElementById('doc-submit-btn').textContent = 'Update Document';
                    document.getElementById('doc-file').required = false;
                    
                    // Show form
                    toggleForm('document-form');
                } else {
                    showAlert('Document not found', 'error');
                }
            } else {
                showAlert('Error loading document', 'error');
            }
        } catch (error) {
            console.error('Error loading document:', error);
            showAlert('Failed to load document', 'error');
        }
}

function cancelDocumentEdit() {
    // Reset form
    document.getElementById('documentForm').reset();
    document.getElementById('doc-id').value = '';
    const oldFileInput = document.getElementById('doc-old-file-path');
    if (oldFileInput) oldFileInput.remove();
    document.getElementById('document-form-title').textContent = 'Create Document Entry';
    document.getElementById('doc-submit-btn').textContent = 'Create Document';
    document.getElementById('doc-file').required = true;
    toggleForm('document-form');
}

async function archiveDocument(id) {
    if (!confirm('Are you sure you want to archive this document?')) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'archive');
        formData.append('id', id);
        if (window.CSRF_TOKEN) {
            formData.append('csrf_token', window.CSRF_TOKEN);
        }
        
        const response = await fetch(ADMIN_API.documents, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Document archived successfully', 'success');
            loadDocumentsList();
        } else {
            showAlert(result.error || 'Error archiving document', 'error');
        }
    } catch (error) {
        showAlert('Failed to archive document', 'error');
        console.error('Error:', error);
    }
}

// Document form submission
document.addEventListener('DOMContentLoaded', function() {
    const docForm = document.getElementById('documentForm');
    if (docForm) {
        const docStatusEl = document.getElementById('doc-status');
        if (window.ADMIN_CAN_PUBLISH === false && docStatusEl) {
            docStatusEl.innerHTML = '<option value="draft">Draft</option><option value="pending_review">Pending Review</option>';
            docStatusEl.value = 'draft';
        }
        docForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            if (window.CSRF_TOKEN) {
                formData.append('csrf_token', window.CSRF_TOKEN);
            }
            
            // Clear previous errors
            document.querySelectorAll('.field-error').forEach(el => el.remove());
            document.querySelectorAll('.border-red-500').forEach(el => {
                el.classList.remove('border-red-500', 'border-2');
                el.classList.add('border-gray-200');
            });
            
            // Ensure subcategory is included in form data
            const subcategoryValue = document.getElementById('doc-subcategory')?.value || '';
            if (subcategoryValue) {
                formData.set('subcategory', subcategoryValue);
            }
            
            // Validate form
            const validationErrors = validateDocumentForm(formData);
            if (validationErrors.length > 0) {
                validationErrors.forEach(error => {
                    const fieldId = 'doc-' + error.field;
                    showFieldError(fieldId, error.message);
                });
                showAlert('Please fix the errors in the form', 'error');
                return;
            }
            
            const id = formData.get('id');
            formData.append('action', id ? 'update' : 'create');
            const docStatusEl = document.getElementById('doc-status');
            formData.set('status', docStatusEl ? docStatusEl.value : 'draft');
            
            // Show loading state
            const submitBtn = document.getElementById('doc-submit-btn');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            
            try {
                const response = await fetch(ADMIN_API.documents, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    cancelDocumentEdit();
                    loadDocumentsList();
                } else {
                    const errorMsg = result.error || 'Error saving document';
                    console.error('Document save error:', errorMsg, result);
                    showAlert(errorMsg, 'error');
                    // Re-enable submit button on error
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            } catch (error) {
                showAlert('Failed to save document', 'error');
                console.error('Error:', error);
                // Re-enable submit button on error
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }
    
    // Load data on page load if logged in
    const dashboardPage = document.getElementById('dashboardPage');
    if (dashboardPage && !dashboardPage.classList.contains('hidden')) {
        loadAnnouncementsList();
    }
});

// ============================================
// INQUIRIES (Contact form submissions)
// ============================================
async function loadInquiriesList() {
    const container = document.getElementById('inquiries-list');
    if (!container) return;
    container.innerHTML = '<p class="text-gray-500 text-center py-8">Loading inquiries...</p>';
    try {
        const response = await fetch(ADMIN_API.inquiries);
        const result = await response.json();
        if (result.success && result.data) {
            displayInquiriesList(result.data);
        } else {
            container.innerHTML = '<p class="text-gray-500 text-center py-8">No inquiries or error loading.</p>';
        }
    } catch (e) {
        container.innerHTML = '<p class="text-red-600 text-center py-8">Failed to load inquiries.</p>';
    }
}

function displayInquiriesList(inquiries) {
    const container = document.getElementById('inquiries-list');
    if (!container) return;
    const canRespond = window.ADMIN_CAN_PUBLISH === true;
    if (!inquiries || inquiries.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">No inquiries yet.</p>';
        return;
    }
    const html = inquiries.map(inq => {
        const date = new Date(inq.created_at).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' });
        const statusClass = inq.status === 'open' ? 'bg-amber-100 text-amber-800' : inq.status === 'closed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
        const respondBtn = canRespond ? `<button onclick="openRespondModal(${inq.id})" class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Respond</button>` : '';
        return `
            <div class="border border-gray-200 rounded-xl p-4 mb-4 bg-gray-50">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <strong class="text-gray-900">${escapeHtml(inq.name)}</strong>
                        <span class="text-gray-500 text-sm ml-2">${escapeHtml(inq.email)}</span>
                    </div>
                    <div class="flex gap-2 items-center">
                        <span class="px-2 py-1 text-xs font-medium rounded ${statusClass}">${inq.status}</span>
                        ${respondBtn}
                    </div>
                </div>
                ${inq.subject ? `<p class="text-sm text-gray-600 mb-1"><strong>Subject:</strong> ${escapeHtml(inq.subject)}</p>` : ''}
                <p class="text-sm text-gray-700 whitespace-pre-wrap">${escapeHtml(inq.message)}</p>
                <p class="text-xs text-gray-500 mt-2">${date}</p>
                ${inq.response_text ? `<div class="mt-3 pt-3 border-t border-gray-200"><p class="text-xs text-gray-600 font-semibold">Response:</p><p class="text-sm text-gray-700 whitespace-pre-wrap">${escapeHtml(inq.response_text)}</p></div>` : ''}
            </div>
        `;
    }).join('');
    
    // Add pagination
    if (pagination && pagination.total_pages > 1) {
        html += generatePagination('documents', pagination);
    }
    
    container.innerHTML = html;
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function openRespondModal(id) {
    const form = document.getElementById('inquiry-respond-form');
    const modal = document.getElementById('inquiry-respond-modal');
    const inpId = document.getElementById('inquiry-respond-id');
    if (!form || !modal || !inpId) return;
    inpId.value = id;
    modal.classList.remove('hidden');
    document.getElementById('inquiry-respond-text').value = '';
    document.getElementById('inquiry-respond-status').value = 'closed';
}

function closeInquiryRespondModal() {
    const modal = document.getElementById('inquiry-respond-modal');
    if (modal) modal.classList.add('hidden');
}

async function submitInquiryResponse(e) {
    e.preventDefault();
    const id = document.getElementById('inquiry-respond-id').value;
    const responseText = document.getElementById('inquiry-respond-text').value;
    const status = document.getElementById('inquiry-respond-status').value;
    const formData = new FormData();
    formData.append('action', 'respond');
    formData.append('id', id);
    formData.append('response_text', responseText);
    formData.append('status', status);
    if (window.CSRF_TOKEN) {
        formData.append('csrf_token', window.CSRF_TOKEN);
    }
    try {
        const response = await fetch(ADMIN_API.inquiries, { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            showAlert(result.message, 'success');
            closeInquiryRespondModal();
            loadInquiriesList();
        } else {
            showAlert(result.error || 'Error', 'error');
        }
    } catch (err) {
        showAlert('Failed to save response', 'error');
    }
}

// Alert function
function showAlert(message, type = 'success') {
    const alertDiv = document.getElementById('dashboardAlert');
    if (!alertDiv) return;
    
    alertDiv.innerHTML = `<div class="alert alert-${type}" style="padding: 1rem; margin-bottom: 1rem; border-radius: 8px; background: ${type === 'success' ? '#d4edda' : '#f8d7da'}; color: ${type === 'success' ? '#155724' : '#721c24'};">
        ${message}
    </div>`;
    
    setTimeout(() => {
        alertDiv.innerHTML = '';
    }, 5000);
}

// ============================================
// 11. SMOOTH SCROLL & UTILITIES
// ============================================
(function initSmoothScroll() {
    const navLinks = document.querySelectorAll('a[href^="#"]');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            const targetId = this.getAttribute('href');
            
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                event.preventDefault();
                
                const headerOffset = 80;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
})();

// Global: used by PHP-rendered announcement "Read more" buttons
function showAnnouncementModal(announcement) {
    console.log('[Announcement Modal] Function called with:', announcement);
    
    // Ensure function is available globally
    if (typeof window !== 'undefined') {
        window.showAnnouncementModal = showAnnouncementModal;
    }
    
    if (!announcement) {
        console.error('[Announcement Modal] No announcement data provided');
        alert('Announcement data is missing. Please try again.');
        return;
    }
    
    // Handle string JSON if passed as string
    if (typeof announcement === 'string') {
        try {
            announcement = JSON.parse(announcement);
            console.log('[Announcement Modal] Parsed JSON string:', announcement);
        } catch (e) {
            console.error('[Announcement Modal] Failed to parse JSON:', e);
            alert('Error loading announcement data.');
            return;
        }
    }
    
    // Get or create modal element
    let modal = document.getElementById('announcement-modal');
    if (!modal) {
        console.log('[Announcement Modal] Creating new modal element');
        // Create modal structure
        modal = document.createElement('div');
        modal.id = 'announcement-modal';
        modal.className = 'announcement-modal';
        modal.innerHTML = `
            <div class="announcement-modal-overlay" onclick="closeAnnouncementModal()"></div>
            <div class="announcement-modal-content">
                <button class="announcement-modal-close" onclick="closeAnnouncementModal()" aria-label="Close modal">×</button>
                <div class="announcement-modal-header">
                    <h2 class="announcement-modal-title" id="announcement-modal-title"></h2>
                    <div class="announcement-modal-date" id="announcement-modal-date"></div>
                    <div class="announcement-modal-meeting-info" id="announcement-modal-meeting-info" style="display: none;">
                        <div class="meeting-detail-item" id="meeting-date-time"></div>
                        <div class="meeting-detail-item" id="meeting-location"></div>
                        <div class="meeting-detail-item" id="meeting-purpose"></div>
                    </div>
                </div>
                <div class="announcement-modal-content-wrapper">
                    <div class="announcement-modal-left-panel">
                        <div class="announcement-modal-image" id="announcement-modal-image" style="display: none;">
                            <img id="announcement-modal-img" src="" alt="Announcement image">
                        </div>
                        <div class="announcement-modal-pdf" id="announcement-modal-pdf" style="display: none;">
                            <iframe id="announcement-modal-pdf-iframe" src="" title="PDF Document"></iframe>
                        </div>
                    </div>
                    <div class="announcement-modal-body">
                        <div class="announcement-modal-content-full" id="announcement-modal-content-full"></div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        console.log('[Announcement Modal] Modal element created and appended to body');
    } else {
        console.log('[Announcement Modal] Using existing modal element');
    }
    
    // Format date
    const date = announcement.created_at
        ? new Date(announcement.created_at).toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        })
        : '';
    
    // Populate modal content
    document.getElementById('announcement-modal-title').textContent = announcement.title || 'Announcement';
    document.getElementById('announcement-modal-date').textContent = date;
    
    // Handle meeting information - show if it has meeting fields (date or location)
    const hasMeetingInfo = announcement.meeting_date || announcement.meeting_location || announcement.is_meeting == 1 || announcement.is_meeting === true;
    const meetingInfoEl = document.getElementById('announcement-modal-meeting-info');
    
    if (hasMeetingInfo && (announcement.meeting_date || announcement.meeting_location)) {
        meetingInfoEl.style.display = 'block';
        
        // Meeting date and time
        const meetingDateEl = document.getElementById('meeting-date-time');
        if (announcement.meeting_date) {
            try {
                const meetingDate = new Date(announcement.meeting_date);
                const dateStr = meetingDate.toLocaleDateString('en-US', { 
                year: 'numeric',
                month: 'long',
                    day: 'numeric' 
                });
                const timeStr = meetingDate.toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit',
                    hour12: true 
                });
                
                let dateTimeText = `📅 Date & Time: ${dateStr} at ${timeStr}`;
                
                if (announcement.meeting_end_date) {
                    try {
                        const endDate = new Date(announcement.meeting_end_date);
                        const endTimeStr = endDate.toLocaleTimeString('en-US', { 
                            hour: 'numeric', 
                            minute: '2-digit',
                            hour12: true 
                        });
                        dateTimeText += ` - ${endTimeStr}`;
                    } catch (e) {
                        console.error('Error parsing end date:', e);
                    }
                }
                
                meetingDateEl.textContent = dateTimeText;
                meetingDateEl.style.display = 'block';
            } catch (e) {
                console.error('Error parsing meeting date:', e);
                meetingDateEl.style.display = 'none';
            }
        } else {
            meetingDateEl.style.display = 'none';
        }
        
        // Meeting location (always show if available, even if not marked as meeting)
        const meetingLocationEl = document.getElementById('meeting-location');
        if (announcement.meeting_location) {
            meetingLocationEl.textContent = `📍 Location: ${announcement.meeting_location}`;
            meetingLocationEl.style.display = 'block';
        } else {
            meetingLocationEl.style.display = 'none';
        }
        
        // Meeting purpose (what it's for) - use title
        const meetingPurposeEl = document.getElementById('meeting-purpose');
        const purpose = announcement.title || '';
        if (purpose.trim()) {
            meetingPurposeEl.textContent = `📋 What it's for: ${purpose}`;
            meetingPurposeEl.style.display = 'block';
        } else {
            meetingPurposeEl.style.display = 'none';
        }
    } else {
        meetingInfoEl.style.display = 'none';
    }
    
    // Handle image display
    const imageEl = document.getElementById('announcement-modal-image');
    const imgEl = document.getElementById('announcement-modal-img');
    const pdfEl = document.getElementById('announcement-modal-pdf');
    const pdfIframe = document.getElementById('announcement-modal-pdf-iframe');
    const contentWrapper = modal.querySelector('.announcement-modal-content-wrapper');
    const leftPanel = modal.querySelector('.announcement-modal-left-panel');
    
    if (announcement.image && announcement.image.trim()) {
        // Construct image URL using getImageUrl helper pattern
        let imageUrl = announcement.image;
        // If it's not already a full URL, construct it
        if (!imageUrl.startsWith('http') && !imageUrl.startsWith('//')) {
            const baseUrl = window.PUBLIC_URL || (window.BASE_URL ? window.BASE_URL + '/public' : '/ICDI/public');
            imageUrl = baseUrl + '/image.php?path=' + encodeURIComponent(announcement.image);
        }
        imgEl.src = imageUrl;
        imgEl.alt = announcement.title || 'Announcement image';
        imageEl.style.display = 'flex';
    } else {
        imageEl.style.display = 'none';
    }
    
    // Handle PDF display
    if (announcement.pdf_file && announcement.pdf_file.trim()) {
        let pdfUrl = announcement.pdf_file;
        // If it's not already a full URL, construct it
        if (!pdfUrl.startsWith('http') && !pdfUrl.startsWith('//')) {
            const baseUrl = window.PUBLIC_URL || (window.BASE_URL ? window.BASE_URL + '/public' : '/ICDI/public');
            // Use download endpoint for security
            pdfUrl = baseUrl.replace('/public', '') + '/public/download.php?path=' + encodeURIComponent(announcement.pdf_file);
        }
        pdfIframe.src = pdfUrl;
        pdfEl.style.display = 'flex';
        if (leftPanel) {
            leftPanel.style.display = 'flex';
        }
    } else {
        pdfEl.style.display = 'none';
        // Hide left panel if no image and no PDF
        if (!announcement.image && leftPanel) {
            leftPanel.style.display = 'none';
            const bodyEl = modal.querySelector('.announcement-modal-body');
            if (bodyEl) {
                bodyEl.style.width = '100%';
            }
        }
    }
    
    if (contentWrapper) {
        contentWrapper.style.display = 'flex';
    }
    
    // Use full content, fallback to description if content is empty
    const fullContent = announcement.content || announcement.description || '';
    
    const contentEl = document.getElementById('announcement-modal-content-full');
    if (fullContent && fullContent.trim()) {
        // Check if content contains HTML tags
        const hasHtmlTags = /<[a-z][\s\S]*>/i.test(fullContent);
        if (hasHtmlTags) {
            // If HTML is present, use innerHTML (content is trusted from database)
            contentEl.innerHTML = fullContent;
        } else {
            // Otherwise, preserve line breaks as <br> tags
            contentEl.innerHTML = fullContent.replace(/\n/g, '<br>');
        }
        contentEl.style.display = 'block';
    } else {
        contentEl.innerHTML = '<p style="color: var(--color-text-muted); font-style: italic;">No content available.</p>';
        contentEl.style.display = 'block';
    }
    
    // Show modal
    console.log('[Announcement Modal] Showing modal');
    console.log('[Announcement Modal] Modal element:', modal);
    console.log('[Announcement Modal] Modal classes before:', modal.className);
    
    // Remove any hidden classes first
    modal.classList.remove('hidden');
    
    // Add active class
    modal.classList.add('active');
    
    // Force inline styles to ensure visibility
    modal.style.display = 'flex';
    modal.style.visibility = 'visible';
    modal.style.opacity = '1';
    modal.style.zIndex = '10001';
    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.right = '0';
    modal.style.bottom = '0';
    
    document.body.style.overflow = 'hidden';
    
    console.log('[Announcement Modal] Modal classes after:', modal.className);
    console.log('[Announcement Modal] Modal computed display:', window.getComputedStyle(modal).display);
    
    // Force display in case CSS doesn't apply immediately
    setTimeout(() => {
        if (modal) {
            modal.style.display = 'flex';
            modal.style.visibility = 'visible';
            modal.style.opacity = '1';
            modal.style.zIndex = '10001';
            console.log('[Announcement Modal] Modal should now be visible');
            console.log('[Announcement Modal] Final computed display:', window.getComputedStyle(modal).display);
            
            // Verify modal is in DOM
            const modalInDom = document.getElementById('announcement-modal');
            if (modalInDom) {
                console.log('[Announcement Modal] ✓ Modal is in DOM');
            } else {
                console.error('[Announcement Modal] ✗ Modal is NOT in DOM');
            }
        }
    }, 50);
    
    // Close on Escape key
    const escapeHandler = (e) => {
        if (e.key === 'Escape') {
            closeAnnouncementModal();
            document.removeEventListener('keydown', escapeHandler);
        }
    };
    document.addEventListener('keydown', escapeHandler);
}

function closeAnnouncementModal() {
    console.log('[Announcement Modal] Closing modal');
    
    // Ensure function is available globally
    if (typeof window !== 'undefined') {
        window.closeAnnouncementModal = closeAnnouncementModal;
    }
    
    const modal = document.getElementById('announcement-modal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
        modal.style.visibility = 'hidden';
        modal.style.opacity = '0';
        document.body.style.overflow = '';
    }
}

// Read More button handler (for static/JS-rendered cards)
// REMOVED: This old handler used alert() and conflicted with the new modal implementation.
// The event delegation in ensureAnnouncementModalFunctions() now handles all announcement clicks.

// Document folder click handler
(function initDocumentFolders() {
    function setupHandler() {
        // Don't attach handler at all if we're on documents page
        if (document.body && document.body.classList.contains('documents-page')) {
            return;
        }
        
        document.addEventListener('click', function(e) {
            // Always check - if body class is documents-page, skip
            if (document.body.classList.contains('documents-page')) {
                return;
            }
            
            const folderItem = e.target.closest('.folder-item');
            if (!folderItem) return;

            // Skip if has skip attribute or is a link
            if (folderItem.hasAttribute('data-skip-js-handler') || 
                (folderItem.tagName === 'A' && folderItem.hasAttribute('href'))) {
                return;
            }

            // Only prevent default if we're handling it ourselves
            e.preventDefault();
            e.stopPropagation();

            const folderNumber = folderItem.querySelector('.folder-number')?.textContent;
            if (!folderNumber) return;

            const documents = staticDocuments.filter(doc => doc.categoryNumber === folderNumber);
            const category = folderItem.querySelector('.folder-title')?.textContent || 'Documents';

            if (documents.length === 0) {
                alert(`${category}\n\nNo documents found in this category.`);
            } else {
                window.location.href = (window.PUBLIC_URL || '/public') + `/documents.php?category=${folderNumber}`;
            }
        });
    }
    
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupHandler);
    } else {
        setupHandler();
    }
})();

// Ensure announcement modal functions are available globally on page load
// Also set up event delegation for announcement cards
(function ensureAnnouncementModalFunctions() {
    function init() {
        // Make functions globally available
        if (typeof window !== 'undefined') {
            window.showAnnouncementModal = showAnnouncementModal;
            window.closeAnnouncementModal = closeAnnouncementModal;
            console.log('[Announcement Modal] Functions registered globally');
            
            // Test that function is accessible
            if (typeof window.showAnnouncementModal === 'function') {
                console.log('[Announcement Modal] ✓ Function is accessible');
            } else {
                console.error('[Announcement Modal] ✗ Function is NOT accessible');
            }
        }
        
        // Set up event delegation for announcement cards and buttons
        // Use capture phase to ensure we catch events before other handlers
        document.addEventListener('click', function(e) {
            // Skip if card is inside a link (new page-based approach)
            const cardLink = e.target.closest('.announcement-card-link');
            if (cardLink) {
                return; // Let the link handle navigation
            }
            
            // Check if clicked element is a read more button or announcement card
            const readMoreBtn = e.target.closest('.btn-read-more');
            const announcementCard = e.target.closest('.announcement-card');
            
            if (readMoreBtn && readMoreBtn.hasAttribute('data-announcement')) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation(); // Prevent other handlers from running
                console.log('[Announcement Modal] Read More button clicked');
                
                try {
                    const announcementData = readMoreBtn.getAttribute('data-announcement');
                    const announcement = JSON.parse(announcementData);
                    console.log('[Announcement Modal] Parsed announcement data:', announcement);
                    
                    if (typeof showAnnouncementModal === 'function') {
                        showAnnouncementModal(announcement);
                    } else {
                        console.error('[Announcement Modal] Function not available, trying window.showAnnouncementModal');
                        if (typeof window.showAnnouncementModal === 'function') {
                            window.showAnnouncementModal(announcement);
                        } else {
                            console.error('[Announcement Modal] Function not loaded');
                        }
                    }
                } catch (error) {
                    console.error('[Announcement Modal] Error parsing announcement data:', error);
                }
            } else if (announcementCard && announcementCard.hasAttribute('data-announcement') && !readMoreBtn) {
                // Only trigger on card click if not clicking the button
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation(); // Prevent other handlers from running
                console.log('[Announcement Modal] Announcement card clicked');
                
                try {
                    const announcementData = announcementCard.getAttribute('data-announcement');
                    const announcement = JSON.parse(announcementData);
                    console.log('[Announcement Modal] Parsed announcement data:', announcement);
                    
                    if (typeof showAnnouncementModal === 'function') {
                        showAnnouncementModal(announcement);
                    } else if (typeof window.showAnnouncementModal === 'function') {
                        window.showAnnouncementModal(announcement);
                    } else {
                        console.error('[Announcement Modal] Function not available');
                    }
                } catch (error) {
                    console.error('[Announcement Modal] Error parsing announcement data:', error);
                }
            }
        }, true); // Use capture phase (true) to catch events before they bubble
        
        console.log('[Announcement Modal] Event delegation set up');
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

// Page load logging
// ============================================
// SPONSOR MANAGEMENT FUNCTIONS
// ============================================
async function loadSponsorsList() {
    const listEl = document.getElementById('sponsors-list');
    if (!listEl) return;
    
    try {
        const response = await fetch(ADMIN_API.sponsors);
        const result = await response.json();
        
        if (result.success && result.data) {
            displaySponsorsList(result.data);
        } else {
            listEl.innerHTML = '<p class="text-gray-500 text-center py-8">No sponsors found.</p>';
        }
    } catch (error) {
        console.error('Error loading sponsors:', error);
        listEl.innerHTML = '<p class="text-red-500 text-center py-8">Error loading sponsors.</p>';
    }
}

function displaySponsorsList(sponsors) {
    const listEl = document.getElementById('sponsors-list');
    if (!listEl) return;
    
    if (sponsors.length === 0) {
        listEl.innerHTML = '<p class="text-gray-500 text-center py-8">No sponsors found. Click "Add Sponsor" to create one.</p>';
        return;
    }
    
    let html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
    sponsors.forEach(sponsor => {
        const imageUrl = sponsor.image ? (window.PUBLIC_URL || window.BASE_URL + '/public') + '/image.php?path=' + encodeURIComponent(sponsor.image) : '';
        const activeClass = sponsor.active ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200';
        html += `
            <div class="bg-white border-2 ${activeClass} rounded-xl p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-900 mb-1">${escapeHtml(sponsor.title || 'Untitled')}</h4>
                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded ${sponsor.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'}">
                            ${sponsor.active ? 'Active' : 'Inactive'}
                        </span>
                    </div>
                </div>
                ${imageUrl ? `<img src="${imageUrl}" alt="${escapeHtml(sponsor.title)}" class="w-full h-32 object-cover rounded-lg mb-3">` : ''}
                ${sponsor.link_url ? `<p class="text-xs text-gray-600 mb-2 truncate">🔗 ${escapeHtml(sponsor.link_url)}</p>` : ''}
                <p class="text-xs text-gray-500 mb-3">Order: ${sponsor.display_order || 0}</p>
                <div class="flex gap-2">
                    <button onclick="editSponsor('${sponsor.id}')" class="flex-1 px-3 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700">Edit</button>
                    <button onclick="toggleSponsorActive('${sponsor.id}')" class="px-3 py-2 ${sponsor.active ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700'} text-white text-sm font-semibold rounded-lg">
                        ${sponsor.active ? 'Deactivate' : 'Activate'}
                    </button>
                    <button onclick="deleteSponsor('${sponsor.id}')" class="px-3 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700">Delete</button>
                </div>
            </div>
        `;
    });
    html += '</div>';
    listEl.innerHTML = html;
}

async function editSponsor(id) {
    try {
        const response = await fetch(ADMIN_API.sponsors);
        const result = await response.json();
        
        if (result.success && result.data) {
            const sponsor = result.data.find(s => s.id === id);
            if (sponsor) {
                document.getElementById('sponsor-id').value = sponsor.id;
                document.getElementById('sponsor-title').value = sponsor.title || '';
                document.getElementById('sponsor-link-url').value = sponsor.link_url || '';
                document.getElementById('sponsor-order').value = sponsor.display_order || 0;
                document.getElementById('sponsor-active').checked = sponsor.active == 1;
                document.getElementById('sponsor-old-image').value = sponsor.image || '';
                document.getElementById('sponsor-action').value = 'update';
                document.getElementById('sponsor-form-title').textContent = 'Edit Sponsor';
                document.getElementById('sponsor-submit-btn').textContent = 'Update Sponsor';
                document.getElementById('sponsor-image').required = false;
                
                // Show image preview
                const previewEl = document.getElementById('sponsor-image-preview');
                if (sponsor.image) {
                    const imageUrl = (window.PUBLIC_URL || window.BASE_URL + '/public') + '/image.php?path=' + encodeURIComponent(sponsor.image);
                    previewEl.innerHTML = `<img src="${imageUrl}" alt="Current image" class="max-w-xs max-h-48 rounded-xl border border-gray-200">`;
                }
                
                toggleForm('sponsor-form');
            }
        }
    } catch (error) {
        console.error('Error loading sponsor:', error);
        showAlert('Error loading sponsor', 'error');
    }
}

function cancelSponsorEdit() {
    document.getElementById('sponsorForm').reset();
    document.getElementById('sponsor-id').value = '';
    document.getElementById('sponsor-old-image').value = '';
    document.getElementById('sponsor-action').value = 'create';
    document.getElementById('sponsor-form-title').textContent = 'Add New Sponsor';
    document.getElementById('sponsor-submit-btn').textContent = 'Create Sponsor';
    document.getElementById('sponsor-image').required = true;
    document.getElementById('sponsor-image-preview').innerHTML = '';
    toggleForm('sponsor-form');
}

async function submitSponsor(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    if (window.CSRF_TOKEN) {
        formData.append('csrf_token', window.CSRF_TOKEN);
    }
    
    try {
        const response = await fetch(ADMIN_API.sponsors, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message || 'Sponsor saved successfully', 'success');
            cancelSponsorEdit();
            loadSponsorsList();
        } else {
            showAlert(result.error || 'Error saving sponsor', 'error');
        }
    } catch (error) {
        console.error('Error saving sponsor:', error);
        showAlert('Failed to save sponsor', 'error');
    }
}

async function deleteSponsor(id) {
    if (!confirm('Are you sure you want to delete this sponsor?')) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        if (window.CSRF_TOKEN) {
            formData.append('csrf_token', window.CSRF_TOKEN);
        }
        
        const response = await fetch(ADMIN_API.sponsors, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Sponsor deleted successfully', 'success');
            loadSponsorsList();
        } else {
            showAlert(result.error || 'Error deleting sponsor', 'error');
        }
    } catch (error) {
        console.error('Error deleting sponsor:', error);
        showAlert('Failed to delete sponsor', 'error');
    }
}

async function toggleSponsorActive(id) {
    try {
        const formData = new FormData();
        formData.append('action', 'toggle_active');
        formData.append('id', id);
        if (window.CSRF_TOKEN) {
            formData.append('csrf_token', window.CSRF_TOKEN);
        }
        
        const response = await fetch(ADMIN_API.sponsors, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Sponsor status updated', 'success');
            loadSponsorsList();
        } else {
            showAlert(result.error || 'Error updating sponsor', 'error');
        }
    } catch (error) {
        console.error('Error toggling sponsor:', error);
        showAlert('Failed to update sponsor', 'error');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ============================================
// SUBCATEGORY MANAGEMENT FUNCTIONS
// ============================================
let currentSubcategoryCategory = '';

async function loadSubcategoriesList(category = '') {
    const listEl = document.getElementById('subcategories-list');
    if (!listEl) return;
    
    currentSubcategoryCategory = category;
    
    try {
        const url = category 
            ? `${ADMIN_API.subcategories}?category=${category}&show_all=1`
            : `${ADMIN_API.subcategories}?show_all=1`;
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success && result.data) {
            displaySubcategoriesList(result.data, category);
        } else {
            listEl.innerHTML = '<p class="text-gray-500 text-center py-8">No subcategories found. Select a category filter or create one.</p>';
        }
    } catch (error) {
        console.error('Error loading subcategories:', error);
        listEl.innerHTML = '<p class="text-red-500 text-center py-8">Error loading subcategories.</p>';
    }
}

function displaySubcategoriesList(subcategories, filterCategory = '') {
    const listEl = document.getElementById('subcategories-list');
    if (!listEl) return;
    
    if (subcategories.length === 0) {
        listEl.innerHTML = '<p class="text-gray-500 text-center py-8">No subcategories found. Click "Add Subcategory" to create one.</p>';
        return;
    }
    
    const categoryNames = {
        '01': 'OFFICES REPORT',
        '02': 'EXECUTIVE ORDER',
        '03': 'ORDINANCE',
        '04': 'RESOLUTION',
        '05': 'OTHER'
    };
    
    // Group by category if no filter
    const grouped = {};
    if (!filterCategory) {
        subcategories.forEach(sub => {
            if (!grouped[sub.category]) grouped[sub.category] = [];
            grouped[sub.category].push(sub);
        });
    }
    
    let html = '';
    
    if (filterCategory) {
        // Show filtered list
        html = '<div class="mb-4"><h4 class="font-semibold text-gray-700">' + categoryNames[filterCategory] + ' Subcategories</h4></div>';
        html += '<div class="space-y-2">';
        subcategories.forEach(sub => {
            const activeClass = sub.status === 'active' ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200';
            html += `
                <div class="bg-white border-2 ${activeClass} rounded-xl p-4 flex items-center justify-between">
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-900">${escapeHtml(sub.name)}</h4>
                        <p class="text-xs text-gray-500 mt-1">Order: ${sub.display_order || 0}</p>
                    </div>
                    <div class="flex gap-2">
                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded ${sub.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'}">
                            ${sub.status === 'active' ? 'Active' : 'Inactive'}
                        </span>
                        <button onclick="editSubcategory(${sub.id})" class="px-3 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700">Edit</button>
                        <button onclick="toggleSubcategoryStatus(${sub.id})" class="px-3 py-2 ${sub.status === 'active' ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700'} text-white text-sm font-semibold rounded-lg">
                            ${sub.status === 'active' ? 'Deactivate' : 'Activate'}
                        </button>
                        <button onclick="deleteSubcategory(${sub.id})" class="px-3 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700">Delete</button>
                    </div>
                </div>
            `;
        });
        html += '</div>';
    } else {
        // Show grouped by category
        Object.keys(grouped).sort().forEach(cat => {
            html += `<div class="mb-6"><h4 class="font-semibold text-gray-700 mb-3">${categoryNames[cat]}</h4>`;
            html += '<div class="space-y-2">';
            grouped[cat].forEach(sub => {
                const activeClass = sub.status === 'active' ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200';
                html += `
                    <div class="bg-white border-2 ${activeClass} rounded-xl p-4 flex items-center justify-between">
                        <div class="flex-1">
                            <h4 class="font-bold text-gray-900">${escapeHtml(sub.name)}</h4>
                            <p class="text-xs text-gray-500 mt-1">Order: ${sub.display_order || 0}</p>
                        </div>
                        <div class="flex gap-2">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded ${sub.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'}">
                                ${sub.status === 'active' ? 'Active' : 'Inactive'}
                            </span>
                            <button onclick="editSubcategory(${sub.id})" class="px-3 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700">Edit</button>
                            <button onclick="toggleSubcategoryStatus(${sub.id})" class="px-3 py-2 ${sub.status === 'active' ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700'} text-white text-sm font-semibold rounded-lg">
                                ${sub.status === 'active' ? 'Deactivate' : 'Activate'}
                            </button>
                            <button onclick="deleteSubcategory(${sub.id})" class="px-3 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700">Delete</button>
                        </div>
                    </div>
                `;
            });
            html += '</div></div>';
        });
    }
    
    listEl.innerHTML = html;
}

async function editSubcategory(id) {
    try {
        const response = await fetch(`${ADMIN_API.subcategories}?show_all=1`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const subcat = result.data.find(s => s.id == id);
            if (subcat) {
                document.getElementById('subcat-id').value = subcat.id;
                document.getElementById('subcat-category').value = subcat.category;
                document.getElementById('subcat-name').value = subcat.name;
                document.getElementById('subcat-status').value = subcat.status;
                document.getElementById('subcat-order').value = subcat.display_order || 0;
                document.getElementById('subcat-action').value = 'update';
                document.getElementById('subcategory-form-title').textContent = 'Edit Subcategory';
                document.getElementById('subcat-submit-btn').textContent = 'Update Subcategory';
                
                toggleForm('subcategory-form');
            }
        }
    } catch (error) {
        console.error('Error loading subcategory:', error);
        showAlert('Error loading subcategory', 'error');
    }
}

function cancelSubcategoryEdit() {
    document.getElementById('subcategoryForm').reset();
    document.getElementById('subcat-id').value = '';
    document.getElementById('subcat-action').value = 'create';
    document.getElementById('subcategory-form-title').textContent = 'Add New Subcategory';
    document.getElementById('subcat-submit-btn').textContent = 'Create Subcategory';
    toggleForm('subcategory-form');
}

async function submitSubcategory(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    const action = document.getElementById('subcat-action').value;
    formData.append('action', action);
    
    if (window.CSRF_TOKEN) {
        formData.append('csrf_token', window.CSRF_TOKEN);
    }
    
    try {
        const response = await fetch(ADMIN_API.subcategories, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message || 'Subcategory saved successfully', 'success');
            cancelSubcategoryEdit();
            loadSubcategoriesList(currentSubcategoryCategory);
            // Reload subcategory dropdown if category matches
            const docCategory = document.getElementById('doc-category')?.value;
            if (docCategory && formData.get('category') === docCategory) {
                await updateDocumentSubcategory();
            }
        } else {
            showAlert(result.error || 'Error saving subcategory', 'error');
        }
    } catch (error) {
        console.error('Error saving subcategory:', error);
        showAlert('Failed to save subcategory', 'error');
    }
}

async function deleteSubcategory(id) {
    if (!confirm('Are you sure you want to delete this subcategory? This action cannot be undone.')) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        if (window.CSRF_TOKEN) {
            formData.append('csrf_token', window.CSRF_TOKEN);
        }
        
        const response = await fetch(ADMIN_API.subcategories, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Subcategory deleted successfully', 'success');
            loadSubcategoriesList(currentSubcategoryCategory);
        } else {
            showAlert(result.error || 'Error deleting subcategory', 'error');
        }
    } catch (error) {
        console.error('Error deleting subcategory:', error);
        showAlert('Failed to delete subcategory', 'error');
    }
}

async function toggleSubcategoryStatus(id) {
    try {
        const formData = new FormData();
        formData.append('action', 'toggle_status');
        formData.append('id', id);
        if (window.CSRF_TOKEN) {
            formData.append('csrf_token', window.CSRF_TOKEN);
        }
        
        const response = await fetch(ADMIN_API.subcategories, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Subcategory status updated', 'success');
            loadSubcategoriesList(currentSubcategoryCategory);
        } else {
            showAlert(result.error || 'Error updating subcategory', 'error');
        }
    } catch (error) {
        console.error('Error toggling subcategory:', error);
        showAlert('Failed to update subcategory', 'error');
    }
}

// Attach subcategory form submit handler
document.addEventListener('DOMContentLoaded', function() {
    const subcatForm = document.getElementById('subcategoryForm');
    if (subcatForm) {
        subcatForm.addEventListener('submit', submitSubcategory);
    }
    
    // Filter subcategories by category
    const subcatCategoryFilter = document.getElementById('subcat-category');
    if (subcatCategoryFilter) {
        subcatCategoryFilter.addEventListener('change', function() {
            if (this.value) {
                loadSubcategoriesList(this.value);
            } else {
                loadSubcategoriesList();
            }
        });
    }
});

window.addEventListener('load', function() {
    const loadTime = performance.now();
    console.log(`PROWLWAY page loaded in ${loadTime.toFixed(2)}ms`);
    
    // Verify announcement modal functions are available
    if (typeof showAnnouncementModal === 'function') {
        console.log('[Announcement Modal] ✓ Function available after page load');
    } else {
        console.error('[Announcement Modal] ✗ Function NOT available after page load');
    }
});

// Bulk selection and actions
function toggleSelectAll(type, checked) {
    const checkboxes = document.querySelectorAll(`#${type}-list .item-checkbox:not(#select-all-${type})`);
    checkboxes.forEach(cb => {
        cb.checked = checked;
    });
    updateBulkActions(type);
}

function updateBulkActions(type) {
    const checkboxes = document.querySelectorAll(`#${type}-list .item-checkbox:checked`);
    const selectedCount = checkboxes.length;
    const bulkActions = document.getElementById(`bulk-actions-${type}`);
    const selectedCountEl = document.getElementById(`selected-count-${type}`);
    const selectAllCheckbox = document.getElementById(`select-all-${type}`);
    
    if (selectedCountEl) {
        if (selectedCount > 0) {
            selectedCountEl.textContent = `${selectedCount} selected`;
            selectedCountEl.classList.remove('hidden');
        } else {
            selectedCountEl.classList.add('hidden');
        }
    }
    
    if (bulkActions) {
        if (selectedCount > 0) {
            bulkActions.classList.remove('hidden');
        } else {
            bulkActions.classList.add('hidden');
        }
    }
    
    if (selectAllCheckbox) {
        const allCheckboxes = document.querySelectorAll(`#${type}-list .item-checkbox:not(#select-all-${type})`);
        selectAllCheckbox.checked = allCheckboxes.length > 0 && selectedCount === allCheckboxes.length;
    }
}

async function bulkAction(type, action) {
    const checkboxes = document.querySelectorAll(`#${type}-list .item-checkbox:checked`);
    if (checkboxes.length === 0) {
        showAlert('Please select at least one item', 'error');
        return;
    }
    
    const ids = Array.from(checkboxes).map(cb => cb.value);
    const actionText = action === 'publish' ? 'publish' : 'archive';
    if (!confirm(`Are you sure you want to ${actionText} ${ids.length} item(s)?`)) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'bulk');
        formData.append('bulk_action', action);
        ids.forEach(id => formData.append('ids[]', id));
        if (window.CSRF_TOKEN) {
            formData.append('csrf_token', window.CSRF_TOKEN);
        }
        
        const apiEndpoint = ADMIN_API[type] || ADMIN_API.announcements;
        const response = await fetch(apiEndpoint, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(`${ids.length} item(s) ${actionText}ed successfully`, 'success');
            if (type === 'announcements') loadAnnouncementsList();
            else if (type === 'events') loadEventsList();
            else if (type === 'documents') loadDocumentsList();
        } else {
            showAlert(result.error || `Error ${actionText}ing items`, 'error');
        }
    } catch (error) {
        showAlert(`Failed to ${actionText} items`, 'error');
        console.error('Error:', error);
    }
}

async function archiveAllItems(type) {
    const checkboxes = document.querySelectorAll(`#${type}-list .item-checkbox:not(#select-all-${type})`);
    if (checkboxes.length === 0) {
        showAlert('No items to archive', 'error');
        return;
    }
    
    if (!confirm(`Are you sure you want to archive all ${checkboxes.length} item(s)?`)) return;
    
    const ids = Array.from(checkboxes).map(cb => cb.value);
    
    try {
        const formData = new FormData();
        formData.append('action', 'bulk');
        formData.append('bulk_action', 'archive');
        ids.forEach(id => formData.append('ids[]', id));
        if (window.CSRF_TOKEN) {
            formData.append('csrf_token', window.CSRF_TOKEN);
        }
        
        const apiEndpoint = ADMIN_API[type] || ADMIN_API.announcements;
        const response = await fetch(apiEndpoint, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(`All ${ids.length} item(s) archived successfully`, 'success');
            if (type === 'announcements') loadAnnouncementsList();
            else if (type === 'events') loadEventsList();
            else if (type === 'documents') loadDocumentsList();
        } else {
            showAlert(result.error || 'Error archiving items', 'error');
        }
    } catch (error) {
        showAlert('Failed to archive items', 'error');
        console.error('Error:', error);
    }
}

console.log('PROWLWAY scripts initialized successfully');

