// js/shopify-api.js

// Shopify API integration functions
class ShopifyAPI {
    constructor() {
        this.baseUrl = API_CONFIG.BASE_URL;
    }

    // Generic API call method
    async apiCall(endpoint, options = {}) {
        try {
            const url = `${this.baseUrl}${endpoint}`;
            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('API call failed:', error);
            throw error;
        }
    }

    // Get all products
    async getProducts() {
        return await this.apiCall(API_CONFIG.ENDPOINTS.PRODUCTS);
    }

    // Get single product
    async getProduct(id) {
        return await this.apiCall(`${API_CONFIG.ENDPOINTS.PRODUCTS}?id=${id}`);
    }

    // Upload design
    async uploadDesign(formData) {
        try {
            const response = await fetch(`${this.baseUrl}${API_CONFIG.ENDPOINTS.UPLOAD_DESIGN}`, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`Upload failed! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Upload failed:', error);
            throw error;
        }
    }
}

// Initialize Shopify API (optional global instance)
const shopifyAPI = new ShopifyAPI();
