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
        this.selectedSize = 'adult-m'; // default size
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
                <div class="col-span-full flex flex-col items-center justify-center py-20">
                    <div class="relative">
                        <!-- Outer ring -->
                        <div class="animate-spin rounded-full h-20 w-20 border-4 border-gray-200"></div>
                        <!-- Inner ring -->
                        <div class="absolute top-0 left-0 animate-spin rounded-full h-20 w-20 border-4 border-blue-600 border-t-transparent"></div>
                    </div>
                    <div class="mt-6 text-center">
                        <p class="text-gray-900 text-lg font-semibold mb-1">Loading templates</p>
                        <p class="text-gray-500 text-sm">Please wait while we fetch the latest designs...</p>
                    </div>
                    <!-- Animated dots -->
                    <div class="flex gap-1 mt-4">
                        <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
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
                name: 'Volleyball Template 1',
                description: 'Clean left chest logo on a premium T-shirt.',
                base_price: basePrice,
                print_area_width: printW,
                print_area_height: printH,
                min_dpi: minDpi,
                max_file_size: maxFileSize,
                image_url: 'img/templates/voll1_sample.jpg',     // card image
                mockup_url: 'img/mock-ups/sample3-mockup.png'   // on T-shirt
            },

            {
                ...base,
                id: 2,
                template_key: 'front-logo',
                name: 'Basketball Template 1',
                description: 'Clean left chest logo on a premium T-shirt.',
                base_price: basePrice,
                print_area_width: printW,
                print_area_height: printH,
                min_dpi: minDpi,
                max_file_size: maxFileSize,
                image_url: 'img/templates/bask1_sample.jpg',     // card image
                mockup_url: 'img/mock-ups/sample3-mockup.png'   // on T-shirt
            },

            {
                ...base,
                id: 1,
                template_key: 'front-logo',
                name: 'Football Template 1',
                description: 'Clean left chest logo on a premium T-shirt.',
                base_price: basePrice,
                print_area_width: printW,
                print_area_height: printH,
                min_dpi: minDpi,
                max_file_size: maxFileSize,
                image_url: 'img/templates/foot1_sample.jpg',     // card image
                mockup_url: 'img/mock-ups/sample3-mockup.png'   // on T-shirt
            },

            {
                ...base,
                id: 1,
                template_key: 'front-logo',
                name: 'Football Template 3',
                description: 'Clean left chest logo on a premium T-shirt.',
                base_price: basePrice,
                print_area_width: printW,
                print_area_height: printH,
                min_dpi: minDpi,
                max_file_size: maxFileSize,
                image_url: 'img/templates/foot3_sample.jpg',     // card image
                mockup_url: 'img/mock-ups/sample3-mockup.png'   // on T-shirt
            },

            {
                ...base,
                id: 1,
                template_key: 'front-logo',
                name: 'Hockey Template 1',
                description: 'Clean left chest logo on a premium T-shirt.',
                base_price: basePrice,
                print_area_width: printW,
                print_area_height: printH,
                min_dpi: minDpi,
                max_file_size: maxFileSize,
                image_url: 'img/templates/hockey1_sample.jpg',     // card image
                mockup_url: 'img/mock-ups/sample3-mockup.png'   // on T-shirt
            },

            {
                ...base,
                id: 1,
                template_key: 'front-logo',
                name: 'Volleyball Template 1',
                description: 'Clean left chest logo on a premium T-shirt.',
                base_price: basePrice,
                print_area_width: printW,
                print_area_height: printH,
                min_dpi: minDpi,
                max_file_size: maxFileSize,
                image_url: 'img/templates/voll1_sample.jpg',     // card image
                mockup_url: 'img/mock-ups/sample3-mockup.png'   // on T-shirt
            },

            {
                ...base,
                id: 1,
                template_key: 'front-logo',
                name: 'Basketball Template 1',
                description: 'Clean left chest logo on a premium T-shirt.',
                base_price: basePrice,
                print_area_width: printW,
                print_area_height: printH,
                min_dpi: minDpi,
                max_file_size: maxFileSize,
                image_url: 'img/templates/bask1_sample.jpg',     // card image
                mockup_url: 'img/mock-ups/sample3-mockup.png'   // on T-shirt
            },

            {
                ...base,
                id: 1,
                template_key: 'front-logo',
                name: 'Football Template 1',
                description: 'Clean left chest logo on a premium T-shirt.',
                base_price: basePrice,
                print_area_width: printW,
                print_area_height: printH,
                min_dpi: minDpi,
                max_file_size: maxFileSize,
                image_url: 'img/templates/foot3_sample.jpg',     // card image
                mockup_url: 'img/mock-ups/sample3-mockup.png'   // on T-shirt
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
                <div class="max-w-md mx-auto">
                    <svg class="w-24 h-24 mx-auto mb-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-600 text-lg font-medium mb-2">No templates available</p>
                    <p class="text-gray-500 text-sm">Check back soon for new designs!</p>
                </div>
            </div>
        `;
        return;
    }

    productGrid.innerHTML = this.products.map(product => {
        const imageUrl = this.escapeHtml(
            product.image_url || 'img/premium-tshirt.png'
        );

        return `
            <div class="group relative bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 cursor-pointer transform hover:-translate-y-1" onclick="app.selectProduct(${product.id})">
                <!-- Template Image Container -->
                <div class="relative aspect-[3/4] bg-gradient-to-br from-gray-50 to-gray-100 overflow-hidden">
                    <!-- Image -->
                    <img
                        src="${imageUrl}"
                        alt="${this.escapeHtml(product.name)}"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        onerror="this.src='img/premium-tshirt.png';"
                    >

                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                    <!-- Price Badge (top-left) -->
                    <div class="absolute top-3 left-3">
                        <div class="bg-blue-600 text-white px-3 py-1.5 rounded-full shadow-lg">
                            <span class="text-sm font-bold">$${(product.base_price || 0).toFixed(2)}</span>
                        </div>
                    </div>

                    <!-- Hover Action Button -->
                    <div class="absolute bottom-0 left-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                        <button class="w-full bg-white text-gray-900 font-semibold py-3 px-4 rounded-xl shadow-xl hover:bg-blue-600 hover:text-white transition-all duration-200 flex items-center justify-center gap-2">
                            <span>Design</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Template Info Card -->
                <div class="p-4 bg-white">
                    <h3 class="font-bold text-gray-900 mb-1 text-base line-clamp-1 group-hover:text-blue-600 transition-colors">${this.escapeHtml(product.name)}</h3>
                </div>

                <!-- Subtle shine effect on hover -->
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none">
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent transform -skew-x-12 translate-x-full group-hover:translate-x-[-200%] transition-transform duration-1000"></div>
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
        this.selectedSize = 'adult-m'; // reset to default size

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

        // Initialize quality options, size options & price
        this.initQualityOptions();
        this.initSizeOptions();
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
        const checkoutBtn = document.getElementById('checkout-btn');
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
        if (addToCartBtn) addToCartBtn.disabled = true;
        if (checkoutBtn) checkoutBtn.disabled = true;
        const cartHint = document.getElementById('cart-button-hint');
        if (cartHint) {
            cartHint.textContent = 'Complete the required steps to continue';
            cartHint.classList.remove('hidden');
        }
        
        // Reset preview state
        if (templateDesignPreview) templateDesignPreview.classList.remove('hidden');
        if (mockupPreview) mockupPreview.classList.add('hidden');
        if (previewTitle) previewTitle.textContent = 'Template Design';
        if (toggleBtnText) toggleBtnText.textContent = 'Preview Mock-up';
        this.previewMode = 'template';

        ['upload-image-1', 'upload-image-2', 'upload-logo'].forEach(id => {
            const input = document.getElementById(id);
            if (input) input.value = '';
        });

        ['image-1-preview', 'image-2-preview', 'logo-preview'].forEach(id => {
            const preview = document.getElementById(id);
            if (preview) preview.classList.add('hidden');
        });
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

    initSizeOptions() {
        const radios = document.querySelectorAll('input[name="size-option"]');
        radios.forEach(radio => {
            radio.checked = radio.value === 'adult-m';
            radio.onchange = () => {
                if (radio.checked) {
                    this.selectedSize = radio.value;
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
            const checkoutBtn = document.getElementById('checkout-btn');
            const cartHint = document.getElementById('cart-button-hint');
            if (addToCartBtn) addToCartBtn.disabled = false;
            if (checkoutBtn) checkoutBtn.disabled = false;
            if (cartHint) {
                cartHint.textContent = 'Design ready – you can add to cart.';
                cartHint.classList.remove('hidden');
            }
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
            const checkoutBtn = document.getElementById('checkout-btn');
            const cartHint = document.getElementById('cart-button-hint');
            if (addToCartBtn) addToCartBtn.disabled = false;
            if (checkoutBtn) checkoutBtn.disabled = false;
            if (cartHint) {
                cartHint.textContent = 'Design ready – working in local preview mode.';
                cartHint.classList.remove('hidden');
            }
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

        // Check if bulk order mode is active
        const bulkCheckbox = document.getElementById('bulk-order-checkbox');
        const isBulkOrder = bulkCheckbox && bulkCheckbox.checked;

        if (isBulkOrder) {
            // Handle bulk order
            this.addBulkOrderToCart();
        } else {
            // Handle single order
            const price = this.getCurrentQualityPrice();

            const item = {
                id: Date.now(),
                name: this.currentProduct.name,
                price: price,
                quantity: 1,
                productId: this.currentProduct.id,
                templateKey: this.currentProduct.template_key || null,
                quality: this.selectedQuality,
                size: this.selectedSize,
                designPreview: this.currentProduct.image_url || this.currentProduct.mockup_url || null
            };

            this.cart.push(item);
            this.updateCartDisplay();
            this.showNotification('Item added to cart!');
            this.openCart();
            this.closeProductModal();
        }
    }

    addBulkOrderToCart() {
        // Get quantities from Step 5 (format: "style-size" => quantity)
        const quantities = window.quantities || {};

        if (Object.keys(quantities).length === 0) {
            this.showError('Please select sizes and set quantities in Step 5.');
            return;
        }

        let itemsAdded = 0;
        const price = this.getCurrentQualityPrice();

        // Create cart items from quantities object
        // Each key is in format "teeStyle-size" (e.g., "short-adult-l")
        Object.keys(quantities).forEach(key => {
            const quantity = quantities[key];

            if (quantity > 0) {
                // Parse the key to extract tee style and size
                const parts = key.split('-');
                // First part is the style (short, long, sleeveless)
                // Remaining parts form the size (e.g., adult-l, kids-m)
                const teeStyle = parts[0];
                const size = parts.slice(1).join('-');

                const item = {
                    id: Date.now() + itemsAdded, // Ensure unique IDs
                    name: `${this.currentProduct.name} - ${this.formatTeeStyleName(teeStyle)} - ${this.formatSizeName(size)}`,
                    price: price,
                    quantity: quantity,
                    productId: this.currentProduct.id,
                    templateKey: this.currentProduct.template_key || null,
                    quality: this.selectedQuality,
                    size: size,
                    teeStyle: teeStyle,
                    designPreview: this.currentProduct.image_url || this.currentProduct.mockup_url || null
                };

                this.cart.push(item);
                itemsAdded++;
            }
        });

        if (itemsAdded > 0) {
            this.updateCartDisplay();
            this.showNotification(`${itemsAdded} item(s) added to cart!`);
            this.openCart();
            this.closeProductModal();
        } else {
            this.showError('No items to add. Please set quantities greater than 0.');
        }
    }

    formatSizeName(size) {
        const names = {
            'kids-xs': 'Kids XS',
            'kids-s': 'Kids S',
            'kids-m': 'Kids M',
            'kids-l': 'Kids L',
            'adult-xs': 'Adult XS',
            'adult-s': 'Adult S',
            'adult-m': 'Adult M',
            'adult-l': 'Adult L',
            'adult-xl': 'Adult XL',
            'adult-xxl': 'Adult XXL'
        };
        return names[size] || size;
    }

    formatTeeStyleName(style) {
        const names = {
            'short': 'Short Sleeve',
            'long': 'Long Sleeve',
            'sleeveless': 'Sleeveless'
        };
        return names[style] || style;
    }

    // Remove uploaded design and reset UI
    removeUploadedDesign() {
        this.currentDesign = null;

        ['upload-image-1', 'upload-image-2', 'upload-logo'].forEach(id => {
            const input = document.getElementById(id);
            if (input) input.value = '';
        });

        this.removeImage(1);
        this.removeImage(2);
        this.removeImage('logo');

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

    updateCartItemQuantity(itemId, newQuantity) {
        // Validate quantity
        if (newQuantity < 1) {
            // If quantity is less than 1, remove the item
            this.removeFromCart(itemId);
            return;
        }

        if (newQuantity > 99) {
            this.showError('Maximum quantity is 99');
            return;
        }

        // Find and update the item
        const item = this.cart.find(item => item.id === itemId);
        if (item) {
            item.quantity = newQuantity;
            this.updateCartDisplay();
        }
    }

    updateCartDisplay() {
        const cartCount = document.getElementById('cart-count');
        const cartItems = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');
        const checkoutButton = document.getElementById('checkout-button');

        // Update cart count with animation
        if (cartCount) {
            const count = this.cart.length;
            cartCount.textContent = count;

            // Add pulse animation on update
            if (count > 0) {
                cartCount.classList.add('animate-pulse');
                setTimeout(() => cartCount.classList.remove('animate-pulse'), 600);
            }
        }

        // Update cart items
        if (!cartItems) return;

        if (this.cart.length === 0) {
            cartItems.innerHTML = `
                <div class="flex flex-col items-center justify-center py-16 px-4">
                    <div class="relative mb-6">
                        <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <div class="absolute -top-1 -right-1 w-8 h-8 bg-red-500 rounded-full flex items-center justify-center text-white text-xs font-bold shadow-lg">
                            0
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Your cart is empty</h3>
                    <p class="text-gray-500 text-sm text-center mb-6">Browse our templates and start customizing your perfect T-shirt!</p>
                    <button onclick="app.closeCart()" class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Browse Templates
                    </button>
                </div>
            `;
        } else {
            cartItems.innerHTML = this.cart.map((item, index) => `
                <div class="group relative bg-white rounded-xl border-2 border-gray-100 hover:border-blue-200 p-4 transition-all duration-200 hover:shadow-md" style="animation: slideIn 0.3s ease-out ${index * 0.05}s backwards;">
                    <div class="flex items-start gap-4">
                        <!-- Thumbnail -->
                        <div class="relative flex-shrink-0">
                            <div class="w-20 h-20 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl overflow-hidden border-2 border-gray-200 group-hover:border-blue-300 transition-colors shadow-sm">
                                ${
                                    item.designPreview
                                        ? `<img src="${item.designPreview}" alt="${this.escapeHtml(item.name)}" class="w-full h-full object-cover" />`
                                        : `<div class="w-full h-full flex items-center justify-center">
                                              <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                              </svg>
                                           </div>`
                                }
                            </div>
                        </div>

                        <!-- Item Details -->
                        <div class="flex-grow min-w-0">
                            <h4 class="font-semibold text-gray-900 mb-1 truncate">${this.escapeHtml(item.name)}</h4>

                            <!-- Badges Row -->
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <!-- Quality Badge -->
                                ${item.quality ? `
                                    <div class="inline-flex items-center gap-1 bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 px-2 py-1 rounded-full">
                                        <svg class="w-3 h-3 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                        <span class="text-xs font-medium text-purple-700 capitalize">${this.escapeHtml(item.quality)}</span>
                                    </div>
                                ` : ''}

                                <!-- Size Badge -->
                                ${item.size ? `
                                    <div class="inline-flex items-center gap-1 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 px-2 py-1 rounded-full">
                                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                        </svg>
                                        <span class="text-xs font-medium text-blue-700 uppercase">${this.escapeHtml(item.size.replace('-', ' '))}</span>
                                    </div>
                                ` : ''}
                            </div>

                            <!-- Quantity Selector & Price -->
                            <div class="flex items-center justify-between gap-3">
                                <!-- Quantity Selector -->
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-500 font-medium">Qty:</span>
                                    <div class="flex items-center bg-gray-50 border-2 border-gray-200 rounded-lg overflow-hidden">
                                        <button
                                            onclick="event.stopPropagation(); app.updateCartItemQuantity(${item.id}, ${item.quantity - 1})"
                                            class="px-2.5 py-1 bg-white hover:bg-blue-50 text-gray-700 hover:text-blue-600 font-bold transition-colors border-r-2 border-gray-200 disabled:opacity-30 disabled:cursor-not-allowed"
                                            ${item.quantity <= 1 ? 'disabled' : ''}
                                            title="Decrease quantity"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"></path>
                                            </svg>
                                        </button>
                                        <div class="px-3 py-1 bg-white min-w-[2.5rem] text-center">
                                            <span class="text-sm font-bold text-gray-900">${item.quantity}</span>
                                        </div>
                                        <button
                                            onclick="event.stopPropagation(); app.updateCartItemQuantity(${item.id}, ${item.quantity + 1})"
                                            class="px-2.5 py-1 bg-white hover:bg-blue-50 text-gray-700 hover:text-blue-600 font-bold transition-colors border-l-2 border-gray-200 disabled:opacity-30 disabled:cursor-not-allowed"
                                            ${item.quantity >= 99 ? 'disabled' : ''}
                                            title="Increase quantity"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Price -->
                                <div class="flex items-center gap-1.5 text-sm">
                                    <span class="font-bold text-gray-900">$${(item.price || 0).toFixed(2)}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Remove Button -->
                        <button
                            onclick="app.removeFromCart(${item.id})"
                            class="flex-shrink-0 w-8 h-8 bg-red-50 hover:bg-red-100 text-red-500 hover:text-red-700 rounded-lg flex items-center justify-center transition-all duration-200 group-hover:scale-110"
                            title="Remove item"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Update total with better formatting
        const total = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        if (cartTotal) {
            cartTotal.textContent = `$${total.toFixed(2)}`;
        }

        // Update checkout button with better disabled state
        if (checkoutButton) {
            checkoutButton.disabled = this.cart.length === 0;
            if (this.cart.length === 0) {
                checkoutButton.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                checkoutButton.classList.remove('opacity-50', 'cursor-not-allowed');
            }
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
        // Remove any existing notifications first
        const existingNotifications = document.querySelectorAll('.toast-notification');
        existingNotifications.forEach(notif => notif.remove());

        const notification = document.createElement('div');
        notification.className = 'toast-notification fixed top-4 right-4 z-50 transform translate-x-0 transition-all duration-300 ease-out';

        const bgColor = type === 'success' ? 'bg-gradient-to-r from-green-500 to-green-600' : 'bg-gradient-to-r from-red-500 to-red-600';
        const icon = type === 'success'
            ? `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
               </svg>`
            : `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
               </svg>`;

        notification.innerHTML = `
            <div class="${bgColor} text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 min-w-[320px] max-w-md">
                <div class="flex-shrink-0">
                    ${icon}
                </div>
                <div class="flex-grow">
                    <p class="font-semibold text-sm leading-relaxed">${this.escapeHtml(message)}</p>
                </div>
                <button onclick="this.closest('.toast-notification').remove()" class="flex-shrink-0 opacity-75 hover:opacity-100 transition-opacity">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);

        // Auto remove with fade out
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 4000);
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    // Mark step as complete
    markStepComplete(stepNumber) {
        const step = document.querySelector(`[data-step="${stepNumber}"]`);
        if (!step) return;

        const statusBadge = step.querySelector('.step-status-badge');
        if (statusBadge) {
            statusBadge.classList.remove('hidden');
        }
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
        // Color picker synchronization
        this.initColorPickers();

        // Image upload handlers
        this.initImageUploadHandlers();
    }

    // Initialize color pickers with synchronization between color input and hex input
    initColorPickers() {
        const colorPairs = [
            { colorInput: 'school-color', hexInput: 'school-color-hex', preview: 'school-color-preview' },
            { colorInput: 'font-fill-color', hexInput: 'font-fill-hex', preview: 'font-fill-preview' },
            { colorInput: 'font-outline-color', hexInput: 'font-outline-hex', preview: 'font-outline-preview' }
        ];

        colorPairs.forEach(pair => {
            const colorInput = document.getElementById(pair.colorInput);
            const hexInput = document.getElementById(pair.hexInput);
            const preview = document.getElementById(pair.preview);

            if (!colorInput || !hexInput || !preview) return;

            // Update hex input and preview when color picker changes
            colorInput.addEventListener('input', (e) => {
                const color = e.target.value.toUpperCase();
                hexInput.value = color;
                preview.style.backgroundColor = color;
            });

            // Update color picker and preview when hex input changes
            hexInput.addEventListener('input', (e) => {
                let hex = e.target.value.toUpperCase();
                // Ensure it starts with #
                if (!hex.startsWith('#')) {
                    hex = '#' + hex;
                }
                // Validate hex color format
                if (/^#[0-9A-F]{6}$/i.test(hex)) {
                    colorInput.value = hex;
                    preview.style.backgroundColor = hex;
                    hexInput.value = hex;
                }
            });
        });
    }

    // Initialize image upload handlers
    initImageUploadHandlers() {
        const imageInputs = [
            { input: 'upload-image-1', preview: 'image-1-preview', thumb: 'image-1-thumb', name: 'image-1-name', size: 'image-1-size' },
            { input: 'upload-image-2', preview: 'image-2-preview', thumb: 'image-2-thumb', name: 'image-2-name', size: 'image-2-size' },
            { input: 'upload-logo', preview: 'logo-preview', thumb: 'logo-thumb', name: 'logo-name', size: 'logo-size' }
        ];

        imageInputs.forEach(img => {
            const input = document.getElementById(img.input);
            if (!input) return;

            input.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (!file) return;

                // Validate file type
                if (!file.type.match('image/(png|jpeg|jpg)')) {
                    this.showError('Please upload a PNG or JPEG image.');
                    e.target.value = '';
                    return;
                }

                // Validate file size (max 10MB)
                if (file.size > 10 * 1024 * 1024) {
                    this.showError('Image size must be less than 10MB.');
                    e.target.value = '';
                    return;
                }

                // Show preview
                const preview = document.getElementById(img.preview);
                const thumb = document.getElementById(img.thumb);
                const nameEl = document.getElementById(img.name);
                const sizeEl = document.getElementById(img.size);

                if (preview && thumb && nameEl && sizeEl) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        thumb.src = e.target.result;
                        nameEl.textContent = file.name;
                        sizeEl.textContent = `${(file.size / 1024).toFixed(2)} KB`;
                        preview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);

                    if (img.input === 'upload-image-1') {
                        this.handleDesignFileSelected(e).catch(err => console.error('Primary image upload failed', err));
                        // Mark Step 1 as complete when primary photo is uploaded
                        this.markStepComplete(1);
                    }

                    this.showNotification('Image uploaded successfully!');
                }
            });
        });
    }

    // Apply school color
    applySchoolColor() {
        const color = document.getElementById('school-color-hex').value;
        // In a real implementation, this would update the preview
        this.showNotification(`School color set to ${color}`, 'success');
        console.log('School color applied:', color);
    }

    // Apply font fill color
    applyFontFillColor() {
        const color = document.getElementById('font-fill-hex').value;
        // In a real implementation, this would update the preview
        this.showNotification(`Font fill color set to ${color}`, 'success');
        console.log('Font fill color applied:', color);
    }

    // Apply font outline color
    applyFontOutlineColor() {
        const color = document.getElementById('font-outline-hex').value;
        // In a real implementation, this would update the preview
        this.showNotification(`Font outline color set to ${color}`, 'success');
        console.log('Font outline color applied:', color);
    }

    // Apply last name
    applyLastName() {
        const lastName = document.getElementById('last-name-input').value.trim();
        if (!lastName) {
            this.showError('Please enter a last name.');
            return;
        }
        // In a real implementation, this would update the preview
        this.showNotification(`Last name set to "${lastName}"`, 'success');
        console.log('Last name applied:', lastName);
    }

    // Apply school name
    applySchoolName() {
        const schoolName = document.getElementById('school-name-input').value.trim();
        if (!schoolName) {
            this.showNotification('School name cleared.', 'success');
            return;
        }
        // In a real implementation, this would update the preview
        this.showNotification(`School name set to "${schoolName}"`, 'success');
        console.log('School name applied:', schoolName);
    }

    // Apply team name
    applyTeamName() {
        const teamName = document.getElementById('team-name-input').value.trim();
        if (!teamName) {
            this.showNotification('Team name cleared.', 'success');
            return;
        }
        // In a real implementation, this would update the preview
        this.showNotification(`Team name set to "${teamName}"`, 'success');
        console.log('Team name applied:', teamName);
    }

    // Remove uploaded image
    removeImage(imageNumber) {
        let input, preview;

        if (imageNumber === 1) {
            input = document.getElementById('upload-image-1');
            preview = document.getElementById('image-1-preview');
        } else if (imageNumber === 2) {
            input = document.getElementById('upload-image-2');
            preview = document.getElementById('image-2-preview');
        } else if (imageNumber === 'logo') {
            input = document.getElementById('upload-logo');
            preview = document.getElementById('logo-preview');
        }

        if (input && preview) {
            input.value = '';
            preview.classList.add('hidden');
            this.showNotification('Image removed.', 'success');
        }
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
