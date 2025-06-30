/**
 * Frontend JavaScript - views/js/front.js
 * Custom Product Configurator Frontend Logic
 */

class CustomProductConfigurator {
    constructor() {
        this.config = window.customProductConfig || {};
        this.currentSelection = {
            attributes: {},
            quantity: null,
            deliveryDays: null,
            withTax: false
        };
        this.isInitialized = false;
        this.touchStartX = 0;
        this.currentGridIndex = 0;
        this.maxGridIndex = 0;
        
        this.init();
    }

    /**
     * Initialize the configurator
     */
    init() {
        if (!document.getElementById('custom-product-configurator')) {
            return;
        }

        this.bindEvents();
        this.initializeGrid();
        this.setupMobileNavigation();
        this.preloadDeliveryDates();
        this.isInitialized = true;
        
        console.log('Custom Product Configurator initialized');
    }

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // Attribute selection
        document.querySelectorAll('.attribute-option').forEach(option => {
            option.addEventListener('click', (e) => this.selectAttribute(e));
            option.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.selectAttribute(e);
                }
            });
        });

        // Price cell selection
        document.querySelectorAll('.price-cell:not(.unavailable)').forEach(cell => {
            cell.addEventListener('click', (e) => this.selectPrice(e));
            cell.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.selectPrice(e);
                }
            });
        });

        // Tax toggle
        const taxToggle = document.getElementById('tax-toggle');
        if (taxToggle) {
            taxToggle.addEventListener('change', (e) => this.toggleTax(e));
        }

        // Add to cart button
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', (e) => this.addToCart(e));
        }

        // Mobile navigation
        document.querySelectorAll('.btn-nav').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleMobileNav(e));
        });

        // Grid collapse toggle
        const toggleGrid = document.querySelector('.toggle-grid');
        if (toggleGrid) {
            toggleGrid.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleGrid();
            });
        }

        // Touch events for mobile swiping
        const gridContainer = document.querySelector('.grid-container');
        if (gridContainer) {
            gridContainer.addEventListener('touchstart', (e) => this.handleTouchStart(e), { passive: true });
            gridContainer.addEventListener('touchmove', (e) => this.handleTouchMove(e), { passive: true });
            gridContainer.addEventListener('touchend', (e) => this.handleTouchEnd(e), { passive: true });
        }

        // Resize handler
        window.addEventListener('resize', () => this.handleResize());
    }

    /**
     * Select an attribute option
     */
    selectAttribute(event) {
        const option = event.currentTarget;
        const attributeType = option.dataset.type;
        const attributeValue = option.dataset.value;
        const multiplier = parseFloat(option.dataset.multiplier);

        // Remove previous selection in this attribute group
        const group = option.closest('.attribute-group');
        group.querySelectorAll('.attribute-option').forEach(opt => {
            opt.classList.remove('selected');
            opt.setAttribute('aria-pressed', 'false');
        });

        // Select current option
        option.classList.add('selected');
        option.setAttribute('aria-pressed', 'true');

        // Update selection
        this.currentSelection.attributes[attributeType] = attributeValue;

        // Update summary
        this.updateSummary();

        // Recalculate price if we have a selection
        if (this.currentSelection.quantity && this.currentSelection.deliveryDays) {
            this.calculatePrice();
        }

        // Add visual feedback
        this.addFeedback(option, 'success');
    }

    /**
     * Select a price cell
     */
    selectPrice(event) {
        const cell = event.currentTarget;
        const quantity = parseInt(cell.dataset.quantity);
        const days = parseInt(cell.dataset.days);
        const price = parseFloat(cell.dataset.price);
        const unitPrice = parseFloat(cell.dataset.unitPrice);

        if (!price || price <= 0) {
            this.showError('Prezzo non disponibile per questa configurazione');
            return;
        }

        // Remove previous selection
        document.querySelectorAll('.price-cell').forEach(c => {
            c.classList.remove('selected');
            c.setAttribute('aria-pressed', 'false');
        });

        document.querySelectorAll('.quantity-row').forEach(row => {
            row.classList.remove('selected');
        });

        // Select current cell and row
        cell.classList.add('selected');
        cell.setAttribute('aria-pressed', 'true');
        cell.closest('.quantity-row').classList.add('selected');

        // Update selection
        this.currentSelection.quantity = quantity;
        this.currentSelection.deliveryDays = days;

        // Update summary
        this.updateSummary();

        // Calculate final price
        this.calculatePrice();

        // Add visual feedback
        this.addFeedback(cell, 'success');
    }

    /**
     * Toggle tax display
     */
    toggleTax(event) {
        this.currentSelection.withTax = event.target.checked;
        
        const taxToggle = document.querySelector('.tax-toggle');
        if (taxToggle) {
            taxToggle.classList.toggle('with-tax', this.currentSelection.withTax);
        }

        // Recalculate price
        if (this.currentSelection.quantity && this.currentSelection.deliveryDays) {
            this.calculatePrice();
        }
    }

    /**
     * Calculate final price with attributes
     */
    async calculatePrice() {
        if (!this.currentSelection.quantity || !this.currentSelection.deliveryDays) {
            return;
        }

        try {
            const response = await fetch(this.config.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'calculatePrice',
                    id_product: this.config.productId,
                    quantity: this.currentSelection.quantity,
                    delivery_days: this.currentSelection.deliveryDays,
                    attributes: JSON.stringify(this.currentSelection.attributes),
                    with_tax: this.currentSelection.withTax ? 1 : 0
                })
            });

            const data = await response.json();

            if (data.error) {
                this.showError(data.error);
                return;
            }

            // Update total price display
            const totalElement = document.getElementById('total-price');
            if (totalElement) {
                const formattedPrice = new Intl.NumberFormat('it-IT', {
                    style: 'currency',
                    currency: 'EUR'
                }).format(data.price);
                
                totalElement.textContent = formattedPrice;
            }

            // Enable add to cart button
            this.enableAddToCart();

        } catch (error) {
            console.error('Price calculation error:', error);
            this.showError('Errore nel calcolo del prezzo');
        }
    }

    /**
     * Update summary section
     */
    updateSummary() {
        // Update attribute selections
        Object.keys(this.currentSelection.attributes).forEach(type => {
            const element = document.getElementById(`selected-${type}`);
            if (element) {
                element.textContent = this.currentSelection.attributes[type];
            }
        });

        // Update quantity
        const quantityElement = document.getElementById('selected-quantity');
        if (quantityElement && this.currentSelection.quantity) {
            quantityElement.textContent = `${this.currentSelection.quantity} pz`;
        }

        // Update delivery
        const deliveryElement = document.getElementById('selected-delivery');
        if (deliveryElement && this.currentSelection.deliveryDays) {
            const deliveryInfo = this.getDeliveryInfo(this.currentSelection.deliveryDays);
            deliveryElement.textContent = deliveryInfo;
        }
    }

    /**
     * Get delivery information
     */
    getDeliveryInfo(days) {
        const deliveryDate = this.config.deliveryDates?.find(d => d.days === days);
        if (deliveryDate) {
            return `${days} giorni (${deliveryDate.day_name} ${deliveryDate.formatted_date})`;
        }
        return `${days} giorni`;
    }

    /**
     * Enable add to cart button
     */
    enableAddToCart() {
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        if (addToCartBtn) {
            const hasAllAttributes = Object.keys(this.config.attributes || {}).every(type => 
                this.currentSelection.attributes[type]
            );
            
            const hasQuantityAndDelivery = this.currentSelection.quantity && this.currentSelection.deliveryDays;
            
            if (hasAllAttributes && hasQuantityAndDelivery) {
                addToCartBtn.disabled = false;
                addToCartBtn.classList.remove('disabled');
            }
        }
    }

    /**
     * Add product to cart
     */
    async addToCart(event) {
        event.preventDefault();
        
        const addToCartBtn = event.currentTarget;
        if (addToCartBtn.disabled) {
            return;
        }

        // Validate selection
        if (!this.validateSelection()) {
            return;
        }

        // Show loading state
        this.showLoading(true);
        addToCartBtn.disabled = true;

        try {
            const response = await fetch(this.config.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'addToCart',
                    id_product: this.config.productId,
                    quantity: this.currentSelection.quantity,
                    delivery_days: this.currentSelection.deliveryDays,
                    attributes: JSON.stringify(this.currentSelection.attributes)
                })
            });

            const data = await response.json();

            if (data.error) {
                this.showError(data.error);
                return;
            }

            // Show success message
            this.showSuccess(data.message || this.config.translations.productAdded);

            // Optional: Redirect to cart or update cart count
            this.handleCartUpdate(data);

        } catch (error) {
            console.error('Add to cart error:', error);
            this.showError(this.config.translations.addToCartError);
        } finally {
            this.showLoading(false);
            addToCartBtn.disabled = false;
        }
    }

    /**
     * Validate current selection
     */
    validateSelection() {
        // Check all attributes are selected
        const requiredAttributes = Object.keys(this.config.attributes || {});
        const selectedAttributes = Object.keys(this.currentSelection.attributes);
        
        if (!requiredAttributes.every(attr => selectedAttributes.includes(attr))) {
            this.showError('Seleziona tutte le caratteristiche del prodotto');
            return false;
        }

        // Check quantity and delivery
        if (!this.currentSelection.quantity || !this.currentSelection.deliveryDays) {
            this.showError('Seleziona quantità e data di consegna');
            return false;
        }

        // Check minimum quantity
        if (this.currentSelection.quantity < this.config.minimumQty) {
            this.showError(`Quantità minima: ${this.config.minimumQty} pezzi`);
            return false;
        }

        return true;
    }

    /**
     * Initialize pricing grid
     */
    initializeGrid() {
        const gridContainer = document.querySelector('.grid-container');
        if (!gridContainer) return;

        // Calculate grid dimensions for mobile
        this.calculateGridDimensions();

        // Set up mobile indicators
        this.updateMobileIndicators();
    }

    /**
     * Setup mobile navigation
     */
    setupMobileNavigation() {
        const prevBtn = document.querySelector('.btn-nav.prev');
        const nextBtn = document.querySelector('.btn-nav.next');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.navigateGrid(-1));
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.navigateGrid(1));
        }
    }

    /**
     * Handle mobile navigation
     */
    handleMobileNav(event) {
        const direction = event.currentTarget.classList.contains('prev') ? -1 : 1;
        this.navigateGrid(direction);
    }

    /**
     * Navigate grid for mobile
     */
    navigateGrid(direction) {
        const newIndex = this.currentGridIndex + direction;
        
        if (newIndex < 0 || newIndex > this.maxGridIndex) {
            return;
        }

        this.currentGridIndex = newIndex;
        this.updateGridPosition();
        this.updateMobileIndicators();
    }

    /**
     * Update grid position
     */
    updateGridPosition() {
        const gridBody = document.querySelector('.price-cells');
        if (!gridBody) return;

        const cellWidth = 120; // Min width from CSS
        const offset = this.currentGridIndex * cellWidth;
        
        gridBody.scrollTo({
            left: offset,
            behavior: 'smooth'
        });
    }

    /**
     * Calculate grid dimensions
     */
    calculateGridDimensions() {
        const deliveryHeaders = document.querySelectorAll('.delivery-header');
        const containerWidth = document.querySelector('.grid-container')?.offsetWidth || 0;
        const cellWidth = 120;
        const visibleCells = Math.floor(containerWidth / cellWidth);
        
        this.maxGridIndex = Math.max(0, deliveryHeaders.length - visibleCells);
    }

    /**
     * Update mobile indicators
     */
    updateMobileIndicators() {
        const currentQuantities = document.querySelector('.current-quantities');
        const navDots = document.querySelector('.nav-dots');
        const prevBtn = document.querySelector('.btn-nav.prev');
        const nextBtn = document.querySelector('.btn-nav.next');

        // Update navigation buttons
        if (prevBtn) {
            prevBtn.disabled = this.currentGridIndex === 0;
        }

        if (nextBtn) {
            nextBtn.disabled = this.currentGridIndex >= this.maxGridIndex;
        }

        // Update dots
        if (navDots) {
            navDots.innerHTML = '';
            for (let i = 0; i <= this.maxGridIndex; i++) {
                const dot = document.createElement('div');
                dot.className = `nav-dot ${i === this.currentGridIndex ? 'active' : ''}`;
                navDots.appendChild(dot);
            }
        }
    }

    /**
     * Handle touch events
     */
    handleTouchStart(event) {
        this.touchStartX = event.touches[0].clientX;
    }

    handleTouchMove(event) {
        // Optional: Add visual feedback during swipe
    }

    handleTouchEnd(event) {
        const touchEndX = event.changedTouches[0].clientX;
        const touchDiff = this.touchStartX - touchEndX;
        const threshold = 50;

        if (Math.abs(touchDiff) > threshold) {
            if (touchDiff > 0) {
                // Swipe left - next
                this.navigateGrid(1);
            } else {
                // Swipe right - previous
                this.navigateGrid(-1);
            }
        }
    }

    /**
     * Handle window resize
     */
    handleResize() {
        if (!this.isInitialized) return;
        
        clearTimeout(this.resizeTimeout);
        this.resizeTimeout = setTimeout(() => {
            this.calculateGridDimensions();
            this.updateMobileIndicators();
        }, 250);
    }

    /**
     * Toggle grid visibility
     */
    toggleGrid() {
        const pricingGrid = document.getElementById('pricing-grid');
        if (!pricingGrid) return;

        const isVisible = pricingGrid.classList.contains('show');
        
        if (isVisible) {
            pricingGrid.classList.remove('show');
        } else {
            pricingGrid.classList.add('show');
            
            // Recalculate grid dimensions when shown
            setTimeout(() => {
                this.calculateGridDimensions();
                this.updateMobileIndicators();
            }, 350); // After CSS transition
        }
    }

    /**
     * Preload delivery dates
     */
    async preloadDeliveryDates() {
        try {
            const response = await fetch(this.config.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'getDeliveryDates',
                    delivery_days: JSON.stringify(this.config.deliveryDates?.map(d => d.days) || [])
                })
            });

            const data = await response.json();
            
            if (data.dates) {
                this.config.deliveryDates = data.dates;
                this.updateDeliveryHeaders();
            }
        } catch (error) {
            console.error('Error preloading delivery dates:', error);
        }
    }

    /**
     * Update delivery headers with current dates
     */
    updateDeliveryHeaders() {
        document.querySelectorAll('.delivery-header').forEach(header => {
            const days = parseInt(header.dataset.days);
            const deliveryInfo = this.config.deliveryDates?.find(d => d.days === days);
            
            if (deliveryInfo) {
                const dayNameElement = header.querySelector('.day-name');
                const dateValueElement = header.querySelector('.date-value');
                
                if (dayNameElement) {
                    dayNameElement.textContent = deliveryInfo.day_name;
                }
                
                if (dateValueElement) {
                    dateValueElement.textContent = deliveryInfo.formatted_date;
                }
            }
        });
    }

    /**
     * Handle cart update after successful add
     */
    handleCartUpdate(data) {
        // Update cart count in header if exists
        const cartCount = document.querySelector('.cart-products-count');
        if (cartCount && data.cart_count) {
            cartCount.textContent = data.cart_count;
        }

        // Trigger custom event for other scripts
        document.dispatchEvent(new CustomEvent('customProductAdded', {
            detail: {
                productId: this.config.productId,
                quantity: this.currentSelection.quantity,
                configuration: this.currentSelection
            }
        }));
    }

    /**
     * Show loading overlay
     */
    showLoading(show) {
        const loadingOverlay = document.getElementById('configurator-loading');
        if (loadingOverlay) {
            loadingOverlay.classList.toggle('active', show);
        }
    }

    /**
     * Show success message
     */
    showSuccess(message) {
        this.showMessage('success', message);
    }

    /**
     * Show error message
     */
    showError(message) {
        this.showMessage('error', message);
    }

    /**
     * Show message
     */
    showMessage(type, message) {
        const messageElement = document.getElementById(`${type}-message`);
        if (!messageElement) return;

        const textElement = messageElement.querySelector(`#${type}-text, span`);
        if (textElement) {
            textElement.textContent = message;
        }

        messageElement.classList.add('show');

        // Auto hide after 5 seconds
        setTimeout(() => {
            messageElement.classList.remove('show');
        }, 5000);
    }

    /**
     * Add visual feedback to element
     */
    addFeedback(element, type) {
        element.classList.add(`feedback-${type}`);
        
        setTimeout(() => {
            element.classList.remove(`feedback-${type}`);
        }, 300);
    }

    /**
     * Reset configurator state
     */
    reset() {
        this.currentSelection = {
            attributes: {},
            quantity: null,
            deliveryDays: null,
            withTax: false
        };

        // Clear visual selections
        document.querySelectorAll('.attribute-option.selected, .price-cell.selected').forEach(el => {
            el.classList.remove('selected');
            el.setAttribute('aria-pressed', 'false');
        });

        document.querySelectorAll('.quantity-row.selected').forEach(row => {
            row.classList.remove('selected');
        });

        // Clear summary
        document.querySelectorAll('.summary-value').forEach(el => {
            el.textContent = '-';
        });

        // Reset total price
        const totalElement = document.getElementById('total-price');
        if (totalElement) {
            totalElement.textContent = '-';
        }

        // Disable add to cart
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        if (addToCartBtn) {
            addToCartBtn.disabled = true;
            addToCartBtn.classList.add('disabled');
        }

        // Reset tax toggle
        const taxToggle = document.getElementById('tax-toggle');
        if (taxToggle) {
            taxToggle.checked = false;
        }
    }

    /**
     * Get current configuration
     */
    getCurrentConfiguration() {
        return {
            ...this.currentSelection,
            isValid: this.validateSelection()
        };
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('custom-product-configurator')) {
        window.configuratorInstance = new CustomProductConfigurator();
    }
});

// Add CSS feedback classes
const style = document.createElement('style');
style.textContent = `
    .feedback-success {
        animation: pulse-success 0.3s ease;
    }
    
    .feedback-error {
        animation: pulse-error 0.3s ease;
    }
    
    @keyframes pulse-success {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.3); }
        100% { transform: scale(1); }
    }
    
    @keyframes pulse-error {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.3); }
        100% { transform: scale(1); }
    }
`;
document.head.appendChild(style);