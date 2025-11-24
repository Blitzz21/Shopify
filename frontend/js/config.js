// js/config.js

// API Configuration
const API_CONFIG = {
    // Use relative path when on same origin
    BASE_URL: window.location.hostname === 'localhost' 
        ? 'http://localhost:8000'  // When using XAMPP
        : 'http://localhost/Shopify/backend/api/', // Fallback
    ENDPOINTS: {
        PRODUCTS: 'products.php',
        UPLOAD_DESIGN: 'upload-designs.php',
        DESIGNS: 'designs.php'
    }
};

// Shopify Configuration (if needed)
const SHOPIFY_CONFIG = {
    API_KEY: 'your_shopify_api_key',
    SHOP_DOMAIN: 'your-store.myshopify.com'
};

// Application Constants
const CONSTANTS = {
    MAX_FILE_SIZE: 10 * 1024 * 1024, // 10MB
    ALLOWED_FILE_TYPES: ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml'],
    MIN_DPI: 150
};

// Export configuration
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { API_CONFIG, SHOPIFY_CONFIG, CONSTANTS };
}