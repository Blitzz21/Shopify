// js/main.js

// Main application class
class CustomPrintApp {
    constructor() {
        this.cart = [];
        this.products = [];        // used as "templates"
        this.currentProduct = null;
        this.currentDesign = null; // current uploaded design info
        this.uploadInProgress = false;
        this.selectedQuality = 'premium';
        this.previewMode = 'template'; // 'template' or 'mockup'

        this.shopifyAPI = new ShopifyAPI();
        
        this.init();
    }

    // Initialize the application
    async init() {
        console.log('🚀 Initializing Custom Print App...');
        
        // Load templates (based on base product)
        await this.loadProducts();
        
        // Initialize event listeners
        this.initEventListeners();
        
        // Update cart display
        this.updateCartDisplay();
        
        console.log('✅ App initialized successfully');
    }

    // Load products from API (with sample T-shirt fallback)
    // Then convert into multiple templates
    async loadProducts() {
        const productGrid = document.getElementById('product-grid');
        
        // Show loading state
        if (productGrid) {
            productGrid.innerHTML = `
                <div class="col-span-full flex flex-col items-center justify-center py-16">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mb-4"></div>
                    <p class="text-gray-600 text-lg">Loading templates...</p>
                </div>
            `;
        }

        let baseProduct = null;

        try {
            const response = await this.shopifyAPI.getProducts();
            
            if (response && response.success && response.data && response.data.products && response.data.products.length > 0) {
                // Take the first product as base T-shirt
                baseProduct = response.data.products[0];
            } else {
                throw new Error('No products returned from API');
            }
        } catch (error) {
            console.warn('Error loading products, using sample base T-shirt instead:', error);

            // Fallback: sample base T-shirt product
            baseProduct = {
                id: 999,
                name: 'Premium T-Shirt',
                description: 'Soft 100% cotton unisex T-shirt with a smooth print surface.',
                base_price: 19.99,
                print_area_width: 12,    // inches
                print_area_height: 16,   // inches
                min_dpi: 150,
                max_file_size: 10485760, // 10MB
                product_type: 'tshirt'
            };
        }

        // Build templates from the base T-shirt product
        this.products = this.buildTemplatesFromBaseProduct(baseProduct);

        // Render template cards
        this.renderProducts();
    }

    // Build multiple design templates based on one base product
    buildTemplatesFromBaseProduct(base) {
        const basePrice = base.base_price || 19.99;
        const printW = base.print_area_width || 12;
        const printH = base.print_area_height || 16;
        const minDpi = base.min_dpi || 150;
        const maxFileSize = base.max_file_size || 10485760;

        return [
            {
                ...base,
                id: 1,
                template_key: 'front-logo',
                name: 'Lorem ipsum',
                description: 'Clean left chest logo on a premium T-shirt.',
                base_price: basePrice,
                print_area_width: printW,
                print_area_height: printH,
                min_dpi: minDpi,
                max_file_size: maxFileSize,
                image_url: 'img/templates/t-shirt design.webp',     // card image
                mockup_url: 'img/mock-ups/sample1-mockup.png'   // on T-shirt
            },

            {
                ...base,
                id: 2,
                template_key: 'front-logo',
                name: 'Lorem ipsum',
                description: 'Clean left chest logo on a premium T-shirt.',
                base_price: basePrice,
                print_area_width: printW,
                print_area_height: printH,
                min_dpi: minDpi,
                max_file_size: maxFileSize,
                image_url: 'img/templates/t-shirt design2.svg',     // card image
                mockup_url: 'img/mock-ups/sample2-mockup.png'   // on T-shirt
            },

            {
                ...base,
                id: 1,
                template_key: 'front-logo',
                name: 'Minimal Front Logo',
                description: 'Clean left chest logo on a premium T-shirt.',
                base_price: basePrice,
                print_area_width: printW,
                print_area_height: printH,
                min_dpi: minDpi,
                max_file_size: maxFileSize,
                image_url: 'img/templates/t-shirt design.webp',     // card image
                mockup_url: 'img/mock-ups/sample1-mockup.png'   // on T-shirt
            },

            {
                ...base,
                id: 1,
                template_key: 'front-logo',
                name: 'Minimal Front Logo',
                description: 'Clean left chest logo on a premium T-shirt.',
                base_price: basePrice,
                print_area_width: printW,
                print_area_height: printH,
                min_dpi: minDpi,
                max_file_size: maxFileSize,
                image_url: 'img/templates/t-shirt design.webp',     // card image
                mockup_url: 'img/mock-ups/sample1-mockup.png'   // on T-shirt
            },

            {
                ...base,
                id: 1,
                template_key: 'front-logo',
                name: 'Minimal Front Logo',
                description: 'Clean left chest logo on a premium T-shirt.',
                base_price: basePrice,
                print_area_width: printW,
                print_area_height: printH,
                min_dpi: minDpi,
                max_file_size: maxFileSize,
                image_url: 'img/templates/t-shirt design.webp',     // card image
                mockup_url: 'img/mock-ups/sample1-mockup.png'   // on T-shirt
            },

            {
                ...base,
                id: 1,
                template_key: 'front-logo',
                name: 'Minimal Front Logo',
                description: 'Clean left chest logo on a premium T-shirt.',
                base_price: basePrice,
                print_area_width: printW,
                print_area_height: printH,
                min_dpi: minDpi,
                max_file_size: maxFileSize,
                image_url: 'img/templates/t-shirt design.webp',     // card image
                mockup_url: 'img/mock-ups/sample1-mockup.png'   // on T-shirt
            },

            {
                ...base,
                id: 1,
                template_key: 'front-logo',
                name: 'Minimal Front Logo',
                description: 'Clean left chest logo on a premium T-shirt.',
                base_price: basePrice,
                print_area_width: printW,
                print_area_height: printH,
                min_dpi: minDpi,
                max_file_size: maxFileSize,
                image_url: 'img/templates/t-shirt design.webp',     // card image
                mockup_url: 'img/mock-ups/sample1-mockup.png'   // on T-shirt
            },

            {
                ...base,
                id: 1,
                template_key: 'front-logo',
                name: 'Minimal Front Logo',
                description: 'Clean left chest logo on a premium T-shirt.',
                base_price: basePrice,
                print_area_width: printW,
                print_area_height: printH,
                min_dpi: minDpi,
                max_file_size: maxFileSize,
                image_url: 'img/templates/t-shirt design.webp',     // card image
                mockup_url: 'img/mock-ups/sample1-mockup.png'   // on T-shirt
            },
        ];
    }

// Render templates in the grid
renderProducts() {
    const productGrid = document.getElementById('product-grid');
    
    if (!productGrid) return;
    
    if (this.products.length === 0) {
        productGrid.innerHTML = `
            <div class="col-span-full text-center py-16">
                <p class="text-gray-500 text-lg">No templates available at the moment.</p>
            </div>
        `;
        return;
    }

    productGrid.innerHTML = this.products.map(product => {
        const imageUrl = this.escapeHtml(
            product.image_url || 'img/premium-tshirt.png'
        );

        return `
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition group cursor-pointer relative" onclick="app.selectProduct(${product.id})">
                <!-- Template Image -->
                <div class="bg-gray-100 h-64 flex items-center justify-center relative overflow-hidden">
                    <img 
                        src="${imageUrl}" 
                        alt="${this.escapeHtml(product.name)}"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        onerror="this.src='img/premium-tshirt.png';"
                    >
                    <!-- Gradient Overlay (shows on hover) -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <!-- Template Name (shows on hover) -->
                    <div class="absolute bottom-0 left-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="bg-gradient-to-t from-black/90 to-transparent pt-8 -mt-8">
                            <h3 class="font-semibold text-lg text-black bg-white/90 px-3 py-2 rounded text-center">${this.escapeHtml(product.name)}</h3>
                        </div>
                    </div>
                </div>
                
                <!-- Template Info (hidden) -->
                <div class="p-4 hidden">
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">${this.escapeHtml(product.name)}</h3>
                    <p class="text-gray-600 text-sm mb-3">${this.escapeHtml(product.description || 'Custom printable template')}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-2xl font-bold text-blue-600">$${(product.base_price || 0).toFixed(2)}</span>
                        <span class="bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded">Use Template</span>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

    // Select a template for customization
    selectProduct(productId) {
        const product = this.products.find(p => p.id === productId);
        if (product) {
            this.currentProduct = product;
            this.openDesigner(product);
        }
    }

    // Open product designer (modal)
    openDesigner(product) {
        this.currentProduct = product;
        this.currentDesign = null;
        this.selectedQuality = 'premium';

        // Fill modal fields (quality/specs of T-shirt)
        document.getElementById('modal-product-name').textContent = product.name;
        document.getElementById('modal-product-title').textContent = product.name;
        document.getElementById('modal-product-description').textContent =
            product.description || 'Customize this premium T-shirt with your own design.';

        // Specs
        const printWidth = product.print_area_width || 10;
        const printHeight = product.print_area_height || 12;
        const minDpi = product.min_dpi || 150;
        const maxFileSize = product.max_file_size || 10485760;

        document.getElementById('print-area-size').textContent =
            `${printWidth}" x ${printHeight}"`;
        document.getElementById('min-dpi').textContent = `${minDpi} DPI`;
        document.getElementById('max-file-size').textContent =
            `${(maxFileSize / (1024 * 1024)).toFixed(1)} MB`;

        const recommendedWidthPx = Math.round(printWidth * minDpi);
        const recommendedHeightPx = Math.round(printHeight * minDpi);

        document.getElementById('required-dpi-text').textContent = minDpi;
        document.getElementById('recommended-size-text').textContent =
            `${recommendedWidthPx} × ${recommendedHeightPx}`;

        // Reset designer UI
        this.resetDesignerUI();

        // Show template art + template on T-shirt
        const templateWrapper = document.getElementById('template-previews');
        const placeholder = document.getElementById('preview-placeholder');
        const templateArtImg = document.getElementById('template-art-img');
        const tshirtImg = document.getElementById('design-preview-img');
        const templateDesignPreview = document.getElementById('template-design-preview');
        const mockupPreview = document.getElementById('mockup-preview');
        const previewTitle = document.getElementById('preview-title');
        const toggleBtnText = document.getElementById('toggle-btn-text');

        if (templateWrapper && placeholder && templateArtImg && tshirtImg) {
            const artUrl = product.template_art_url || product.image_url;
            if (artUrl) templateArtImg.src = artUrl;
            if (product.mockup_url) tshirtImg.src = product.mockup_url;

            templateWrapper.classList.remove('hidden');
            placeholder.classList.add('hidden');
            
            // Show template design by default
            if (templateDesignPreview) templateDesignPreview.classList.remove('hidden');
            if (mockupPreview) mockupPreview.classList.add('hidden');
            if (previewTitle) previewTitle.textContent = 'Template Design';
            if (toggleBtnText) toggleBtnText.textContent = 'Preview Mock-up';
            
            // Reset preview state
            this.previewMode = 'template'; // 'template' or 'mockup'
        }

        // Initialize quality options & price
        this.initQualityOptions();
        this.updateQualityPriceDisplay();

        // Show modal
        const modal = document.getElementById('product-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
        document.body.style.overflow = 'hidden';

        // Designer events hook (for future)
        this.initDesignerEventListeners();
    }

    // Reset all UI state inside the designer modal
    resetDesignerUI() {
        const templateWrapper = document.getElementById('template-previews');
        const placeholder = document.getElementById('preview-placeholder');
        const templateArtImg = document.getElementById('template-art-img');
        const tshirtImg = document.getElementById('design-preview-img');
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        const templateDesignPreview = document.getElementById('template-design-preview');
        const mockupPreview = document.getElementById('mockup-preview');
        const previewTitle = document.getElementById('preview-title');
        const toggleBtnText = document.getElementById('toggle-btn-text');

        if (templateWrapper) templateWrapper.classList.add('hidden');
        if (placeholder) placeholder.classList.remove('hidden');

        if (templateArtImg) {
            templateArtImg.src = '';
        }
        if (tshirtImg) {
            tshirtImg.src = '';
        }

        // Enable button by default for templates
        if (addToCartBtn) addToCartBtn.disabled = false;
        
        // Reset preview state
        if (templateDesignPreview) templateDesignPreview.classList.remove('hidden');
        if (mockupPreview) mockupPreview.classList.add('hidden');
        if (previewTitle) previewTitle.textContent = 'Template Design';
        if (toggleBtnText) toggleBtnText.textContent = 'Preview Mock-up';
        this.previewMode = 'template';
    }

    // Close product modal
    closeProductModal() {
        const modal = document.getElementById('product-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
        document.body.style.overflow = 'auto';
        this.currentDesign = null;
    }

    // Quality helpers
    getCurrentQualityPrice() {
        if (!this.currentProduct) return 0;
        let price = this.currentProduct.base_price || 0;

        if (this.selectedQuality === 'heavyweight') {
            price += 2;
        } else if (this.selectedQuality === 'performance') {
            price += 4;
        }

        return price;
    }

    updateQualityPriceDisplay() {
        const priceEl = document.getElementById('modal-product-price');
        if (!priceEl) return;
        const price = this.getCurrentQualityPrice();
        priceEl.textContent = `$${price.toFixed(2)}`;
    }

    initQualityOptions() {
        const radios = document.querySelectorAll('input[name="quality-option"]');
        radios.forEach(radio => {
            radio.checked = radio.value === 'premium';
            radio.onchange = () => {
                if (radio.checked) {
                    this.selectedQuality = radio.value;
                    this.updateQualityPriceDisplay();
                }
            };
        });
    }

    // Handle file input change
    async handleDesignFileSelected(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (!file.type.includes('png')) {
            this.showError('Please upload a PNG file with transparent background.');
            event.target.value = '';
            return;
        }

        const maxSizeBytes = (this.currentProduct && this.currentProduct.max_file_size) || 10485760;
        if (file.size > maxSizeBytes) {
            this.showError('File is too large for this product.');
            event.target.value = '';
            return;
        }

        const tshirtImg = document.getElementById('design-preview-img');
        const templateArtImg = document.getElementById('template-art-img');
        const placeholder = document.getElementById('preview-placeholder');
        const templateWrapper = document.getElementById('template-previews');
        const uploadProgress = document.getElementById('upload-progress');
        const progressBar = document.getElementById('progress-bar');
        const uploadStatus = document.getElementById('upload-status');

        if (tshirtImg && placeholder && templateWrapper) {
            const fileUrl = URL.createObjectURL(file);
            // Update both template art preview and mockup preview with user's design
            if (templateArtImg) templateArtImg.src = fileUrl;
            tshirtImg.src = fileUrl; // override t-shirt preview with user's design
            templateWrapper.classList.remove('hidden');
            placeholder.classList.add('hidden');
        }

        if (uploadProgress && progressBar && uploadStatus) {
            uploadProgress.classList.remove('hidden');
            progressBar.style.width = '10%';
            uploadStatus.textContent = 'Uploading...';
        }

        this.uploadInProgress = true;

        try {
            // Try backend upload
            const designData = await this.uploadDesignToServer(file);

            this.currentDesign = {
                ...designData,
                localPreviewUrl: tshirtImg ? tshirtImg.src : null
            };

            if (progressBar && uploadStatus) {
                progressBar.style.width = '100%';
                uploadStatus.textContent = 'Upload complete';
            }

            const uploadedInfo = document.getElementById('uploaded-file-info');
            if (uploadedInfo) {
                const filenameEl = document.getElementById('uploaded-filename');
                const filesizeEl = document.getElementById('uploaded-filesize');
                if (filenameEl) filenameEl.textContent = file.name;
                if (filesizeEl) filesizeEl.textContent = `${(file.size / (1024 * 1024)).toFixed(2)} MB`;
                uploadedInfo.classList.remove('hidden');
            }

            const addToCartBtn = document.getElementById('add-to-cart-btn');
            const cartHint = document.getElementById('cart-button-hint');
            if (addToCartBtn) addToCartBtn.disabled = false;
            if (cartHint) cartHint.textContent = 'Design ready – you can add to cart.';
        } catch (err) {
            console.warn('Upload failed or backend not available, using local-only design.', err);

            this.currentDesign = {
                id: null,
                localPreviewUrl: tshirtImg ? tshirtImg.src : null,
                filename: file.name,
                size: file.size
            };

            if (progressBar && uploadStatus) {
                progressBar.style.width = '100%';
                uploadStatus.textContent = 'Preview only (not uploaded to server)';
            }

            const uploadedInfo = document.getElementById('uploaded-file-info');
            if (uploadedInfo) {
                const filenameEl = document.getElementById('uploaded-filename');
                const filesizeEl = document.getElementById('uploaded-filesize');
                if (filenameEl) filenameEl.textContent = file.name;
                if (filesizeEl) filesizeEl.textContent = `${(file.size / (1024 * 1024)).toFixed(2)} MB`;
                uploadedInfo.classList.remove('hidden');
            }

            const addToCartBtn = document.getElementById('add-to-cart-btn');
            const cartHint = document.getElementById('cart-button-hint');
            if (addToCartBtn) addToCartBtn.disabled = false;
            if (cartHint) cartHint.textContent = 'Design ready – working in local preview mode.';
        } finally {
            this.uploadInProgress = false;
        }
    }

    // Upload design to backend
    async uploadDesignToServer(file) {
        const formData = new FormData();
        formData.append('design_file', file);

        if (this.currentProduct && this.currentProduct.id) {
            formData.append('product_id', this.currentProduct.id);
        }

        const response = await this.shopifyAPI.uploadDesign(formData);

        if (!response.success) {
            throw new Error(response.message || 'Upload failed');
        }

        return response.data.design || response.data || {};
    }

    // Add currently customized product to cart
    addProductToCart() {
        if (!this.currentProduct) {
            this.showError('No template selected.');
            return;
        }

        const price = this.getCurrentQualityPrice();

        const item = {
            id: Date.now(),
            name: this.currentProduct.name,
            price: price,
            quantity: 1,
            productId: this.currentProduct.id,
            templateKey: this.currentProduct.template_key || null,
            quality: this.selectedQuality,
            designPreview: this.currentProduct.image_url || this.currentProduct.mockup_url || null
        };

        this.cart.push(item);
        this.updateCartDisplay();
        this.showNotification('Item added to cart!');
        this.openCart();
        this.closeProductModal();
    }

    // Remove uploaded design and reset UI
    removeUploadedDesign() {
        this.currentDesign = null;

        const fileInput = document.getElementById('design-file-input');
        if (fileInput) fileInput.value = '';

        this.resetDesignerUI();
    }

    // Toggle between template design and mockup preview
    togglePreview() {
        const templateDesignPreview = document.getElementById('template-design-preview');
        const mockupPreview = document.getElementById('mockup-preview');
        const previewTitle = document.getElementById('preview-title');
        const toggleBtnText = document.getElementById('toggle-btn-text');

        if (!templateDesignPreview || !mockupPreview) return;

        if (this.previewMode === 'template') {
            // Switch to mockup
            templateDesignPreview.classList.add('hidden');
            mockupPreview.classList.remove('hidden');
            if (previewTitle) previewTitle.textContent = 'Mock-up on T-Shirt';
            if (toggleBtnText) toggleBtnText.textContent = 'View Template Design';
            this.previewMode = 'mockup';
        } else {
            // Switch to template
            templateDesignPreview.classList.remove('hidden');
            mockupPreview.classList.add('hidden');
            if (previewTitle) previewTitle.textContent = 'Template Design';
            if (toggleBtnText) toggleBtnText.textContent = 'Preview Mock-up';
            this.previewMode = 'template';
        }
    }


    // Cart management
    addToCart(item) {
        this.cart.push({
            ...item,
            id: Date.now(),
            quantity: 1
        });
        this.updateCartDisplay();
        this.showNotification('Item added to cart!');
    }

    removeFromCart(itemId) {
        this.cart = this.cart.filter(item => item.id !== itemId);
        this.updateCartDisplay();
    }

    updateCartDisplay() {
        const cartCount = document.getElementById('cart-count');
        const cartItems = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');
        const checkoutButton = document.getElementById('checkout-button');

        // Update cart count
        if (cartCount) cartCount.textContent = this.cart.length;

        // Update cart items
        if (!cartItems) return;

        if (this.cart.length === 0) {
            cartItems.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <p>Your cart is empty</p>
                </div>
            `;
        } else {
            cartItems.innerHTML = this.cart.map(item => `
                <div class="flex items-center space-x-4 bg-gray-50 p-4 rounded-lg">
                    <div class="flex-shrink-0 w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                        ${
                            item.designPreview
                                ? `<img src="${item.designPreview}" class="w-14 h-14 object-contain rounded" />`
                                : `<svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                   </svg>`
                        }
                    </div>
                    <div class="flex-grow">
                        <h4 class="font-semibold text-gray-900">${this.escapeHtml(item.name)}</h4>
                        <p class="text-gray-600 text-sm">
                            $${(item.price || 0).toFixed(2)} x ${item.quantity}
                            ${item.quality ? ` · ${this.escapeHtml(item.quality)}` : ''}
                        </p>
                    </div>
                    <button onclick="app.removeFromCart(${item.id})" class="text-red-500 hover:text-red-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            `).join('');
        }

        // Update total
        const total = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        if (cartTotal) cartTotal.textContent = `$${total.toFixed(2)}`;

        // Update checkout button
        if (checkoutButton) {
            checkoutButton.disabled = this.cart.length === 0;
        }
    }

    // Cart sidebar methods
    openCart() {
        const sidebar = document.getElementById('cart-sidebar');
        const overlay = document.getElementById('cart-overlay');
        if (sidebar) sidebar.classList.remove('translate-x-full');
        if (overlay) overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    closeCart() {
        const sidebar = document.getElementById('cart-sidebar');
        const overlay = document.getElementById('cart-overlay');
        if (sidebar) sidebar.classList.add('translate-x-full');
        if (overlay) overlay.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Checkout process
    checkout() {
        if (this.cart.length === 0) {
            this.showError('Your cart is empty!');
            return;
        }

        alert('🚀 Proceeding to checkout!\n\nIn a real implementation, this would:\n• Collect shipping information\n• Process payment\n• Create order in Shopify\n• Send confirmation email');
        
        // Reset cart after checkout
        this.cart = [];
        this.updateCartDisplay();
        this.closeCart();
    }

    // Utility methods
    escapeHtml(unsafe) {
        if (!unsafe && unsafe !== 0) return '';
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    // Initialize event listeners
    initEventListeners() {
        // Close cart when clicking overlay
        const cartOverlay = document.getElementById('cart-overlay');
        if (cartOverlay) {
            cartOverlay.addEventListener('click', () => {
                this.closeCart();
            });
        }

        // Escape key to close cart & modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeCart();
                this.closeProductModal();
            }
        });
    }

    initDesignerEventListeners() {
        // For future enhancements (drag, scale, etc.)
    }
}

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.app = new CustomPrintApp();
});

// Global functions for HTML onclick handlers
window.openCart = () => window.app.openCart();
window.closeCart = () => window.app.closeCart();
window.checkout = () => window.app.checkout();
window.closeProductModal = () => window.app.closeProductModal();
window.addProductToCart = () => window.app.addProductToCart();
window.togglePreview = () => window.app.togglePreview();
