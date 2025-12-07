# PROWLWAY - ICDISG Archive Website

A modern, responsive static website for the ICDISG Archive platform.

## 📁 Project Structure

```
landing/
├── index.html       # Main HTML file
├── styles.css       # All CSS styles
├── script.js        # JavaScript functionality
└── README.md        # This file
```

## 🚀 Quick Start

### Option 1: Open Directly in Browser
Simply double-click `index.html` to open in your default browser.

### Option 2: Use a Local Server (Recommended)
Using a local server prevents CORS issues and provides better development experience.

**Using Python:**
```powershell
cd landing
python -m http.server 8000
```
Then open: http://localhost:8000

**Using Node.js (with npx):**
```powershell
cd landing
npx serve
```

**Using VS Code Live Server Extension:**
1. Install "Live Server" extension
2. Right-click `index.html`
3. Select "Open with Live Server"

## 📱 Features

- ✅ Fully responsive design (mobile, tablet, desktop)
- ✅ Sticky navigation header
- ✅ Auto-rotating events carousel
- ✅ Mobile menu toggle
- ✅ Smooth scroll navigation
- ✅ Hover effects and transitions
- ✅ Clean, commented code for easy customization

## 🎨 Customization Guide

### Colors
Edit CSS variables in `styles.css` (lines 20-32):

```css
:root {
    --color-bg-dark: #0f1112;        /* Dark background */
    --color-card-bg: #909398;        /* Card background */
    --color-accent-blue: #2f7bfe;    /* Button color */
    /* ... more colors ... */
}
```

### Spacing
Adjust spacing variables in `styles.css` (lines 34-38):

```css
:root {
    --spacing-xs: 8px;
    --spacing-sm: 12px;
    --spacing-md: 18px;
    --spacing-lg: 24px;
    --spacing-xl: 32px;
}
```

### Navigation Links
Edit navigation in `index.html` (lines 28-34):

```html
<nav class="main-nav">
    <a href="#home" class="nav-link">Home</a>
    <a href="#institute" class="nav-link">Institute</a>
    <!-- Add more links here -->
</nav>
```

### Announcements
Update announcements in `index.html` (lines 67-75):

```html
<div class="announcement-card">
    <h3 class="announcement-title">YOUR TITLE HERE</h3>
    <p class="announcement-desc">Your description...</p>
    <p class="announcement-date">Nov 7 — Nov 14</p>
    <button class="btn-read-more">Read More</button>
</div>
```

### Events Carousel
Add/edit event slides in `index.html` (lines 123-145):

```html
<div class="event-slide active">
    <div class="event-image">
        <img src="your-image.jpg" alt="Event Name">
    </div>
    <p class="event-caption">Your event description</p>
</div>
```

Don't forget to add corresponding dots:
```html
<span class="dot active" data-slide="0"></span>
```

### Document Folders
Edit document folders in `index.html` (lines 108-119):

```html
<div class="folder-item">
    <div class="folder-number">05</div>
    <div class="folder-label">YOUR LABEL</div>
</div>
```

### Footer Content
Update footer text in `index.html` (lines 172-195):

```html
<p class="footer-description">
    Your organization description here...
</p>
```

Update Tech Care links in `index.html` (lines 202-207):

```html
<ul class="techcare-links">
    <li><a href="#your-link">Your Service</a></li>
</ul>
```

## 🔧 JavaScript Functionality

### Carousel Settings
Edit autoplay interval in `script.js` (line 102):

```javascript
// Change 5000 to desired milliseconds (5000 = 5 seconds)
autoplayInterval = setInterval(nextSlide, 5000);
```

### Mobile Menu
Mobile menu automatically shows on screens < 768px.
Customize breakpoint in `styles.css` (line 598).

### Read More Button
Customize button action in `script.js` (lines 250-267):

```javascript
button.addEventListener('click', function() {
    // Add your custom action here
    window.location.href = '/announcement-detail.html';
});
```

### Document Folder Clicks
Customize folder action in `script.js` (lines 274-288):

```javascript
folder.addEventListener('click', function() {
    // Add your custom action here
    window.location.href = `/documents/${folderNumber}`;
});
```

## 📐 Responsive Breakpoints

| Device | Width | Grid Layout |
|--------|-------|-------------|
| Desktop | > 1024px | 3-column grid |
| Tablet | 768px - 1024px | 1-column grid |
| Mobile Landscape | 600px - 768px | 1-column grid |
| Mobile Portrait | < 600px | 1-column grid |

## 🎯 Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## 🐛 Common Issues & Solutions

### Issue: Images not loading
**Solution:** Make sure image paths are correct. Use relative paths:
```html
<img src="./images/your-image.jpg" alt="Description">
```

### Issue: Mobile menu not working
**Solution:** Ensure `script.js` is loaded after the HTML elements:
```html
<script src="script.js"></script> <!-- At end of body -->
```

### Issue: Carousel not auto-playing
**Solution:** Check browser console for errors. Ensure all slide elements exist.

### Issue: Styles not applying
**Solution:** 
1. Check that `styles.css` is linked correctly in HTML
2. Clear browser cache (Ctrl + F5)
3. Ensure no typos in class names

## 📝 Code Comments

All code is heavily commented to help you understand and modify:

- **HTML:** Section headers, element purposes
- **CSS:** Variable definitions, section organization
- **JavaScript:** Function descriptions, parameters, usage

## 🔄 Making Changes

1. **Edit HTML** for content changes
2. **Edit CSS** for styling changes
3. **Edit JavaScript** for functionality changes
4. **Test** in browser (refresh with Ctrl + F5 to clear cache)
5. **Deploy** when satisfied

## 📦 Adding New Sections

To add a new section, follow this template in `index.html`:

```html
<!-- ========================================
     YOUR SECTION NAME
     Description of what this section does
     ======================================== -->
<section class="your-section">
    <h2>Your Section Title</h2>
    <div class="your-content">
        <!-- Your content here -->
    </div>
</section>
```

Then add styles in `styles.css`:

```css
/* ============================================
   YOUR SECTION
   ============================================ */
.your-section {
    padding: var(--spacing-xl);
    /* Your styles */
}
```

## 🎨 Using Real Images

Replace placeholder images with your own:

1. Create an `images/` folder in the landing directory
2. Add your images (PNG, JPG, WebP)
3. Update image paths in HTML:

```html
<!-- Before -->
<img src="https://via.placeholder.com/..." alt="...">

<!-- After -->
<img src="./images/your-image.jpg" alt="Description">
```

## 🚢 Deployment

### GitHub Pages
1. Upload to GitHub repository
2. Go to Settings > Pages
3. Select branch and folder
4. Your site will be live at `https://username.github.io/repo-name`

### Netlify
1. Drag and drop the `landing` folder to Netlify
2. Site will be live instantly

### Traditional Hosting
1. Upload all files via FTP
2. Ensure `index.html` is in the root directory

## 📞 Support

For questions or issues:
- Review code comments in each file
- Check browser console (F12) for errors
- Validate HTML: https://validator.w3.org/
- Validate CSS: https://jigsaw.w3.org/css-validator/

## 📄 License

This project is free to use and modify for your needs.

---

**Created:** November 2025  
**Version:** 1.0  
**Status:** Production Ready ✅
