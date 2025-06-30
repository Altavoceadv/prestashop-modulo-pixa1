/**
 * Admin JavaScript - views/js/admin.js
 * Custom Product Configurator Admin Panel Logic
 */

class CustomProductAdminManager {
    constructor() {
        this.config = window.adminCustomProductConfig || {};
        this.isAdvancedMode = false;
        this.unsavedChanges = false;
        this.attributeIndexes = {
            formato: 0,
            carta: 0,
            finitura: 0,
            colori: 0
        };
        
        this.init();
    }

    /**
     * Initialize admin manager
     */
    init() {
        if (!document.getElementById('custom-product-configuration')) {
            return;
        }

        this.bindEvents();
        this.initializeSortable();
        this.loadExistingConfiguration();
        this.updateAttributeIndexes();
        
        console.log('Custom Product Admin Manager initialized');
    }

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // Configuration form
        const configForm = document.getElementById('custom-product-config-form');
        if (configForm) {
            configForm.addEventListener('submit', (e) => this.saveConfiguration(e));
            configForm.addEventListener('change', () => this.markUnsaved());
        }

        // Attribute management
        document.querySelectorAll('.btn-add-attribute').forEach(btn => {
            btn.addEventListener('click', (e) => this.addAttribute(e));
        });

        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-remove-attribute')) {
                this.removeAttribute(e);
            }
        });

        // Pricing matrix
        document.getElementById('btn-generate-matrix')?.addEventListener('click', (e) => this.generateMatrix(e));
        document.getElementById('btn-import-csv')?.addEventListener('click', (e) => this.showImportModal(e));
        document.getElementById('btn-export-csv')?.addEventListener('click', (e) => this.exportCSV(e));
        document.getElementById('btn-confirm-import')?.addEventListener('click', (e) => this.importCSV(e));

        // Quantity management
        document.querySelector('.btn-add-quantity')?.addEventListener('click', (e) => this.addQuantityRow(e));
        
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-remove-row')) {
                this.removeQuantityRow(e);
            }
        });

        // Advanced tools
        document.getElementById('btn-copy-config')?.addEventListener('click', (e) => this.copyConfiguration(e));
        document.getElementById('btn-test-calc')?.addEventListener('click', (e) => this.testCalculator(e));
        document.getElementById('btn-preview-frontend')?.addEventListener('click', (e) => this.previewFrontend(e));
        document.getElementById('btn-reset-config')?.addEventListener('click', (e) => this.resetConfiguration(e));

        // Advanced mode toggle
        window.toggleConfigurationMode = () => this.toggleAdvancedMode();

        // Pricing matrix changes
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('price-input') || e.target.classList.contains('quantity-input')) {
                this.markUnsaved();
                this.validatePricingInput(e);
            }
        });

        // Window beforeunload
        window.addEventListener('beforeunload', (e) => {
            if (this.unsavedChanges) {
                e.preventDefault();
                e.returnValue = 'Hai modifiche non salvate. Sei sicuro di voler uscire?';
                return e.returnValue;
            }
        });

        // Load products for copy functionality
        this.loadProductsList();
    }

    /**
     * Initialize sortable for attributes
     */
    initializeSortable() {
        document.querySelectorAll('.attribute-list').forEach(list => {
            if (typeof jQuery !== 'undefined' && jQuery.fn.sortable) {
                jQuery(list).sortable({
                    handle: '.drag-handle',
                    placeholder: 'ui-sortable-placeholder',
                    helper: 'clone',
                    update: (event, ui) => {
                        this.updateAttributePositions(list);
                        this.markUnsaved();
                    }
                });
            }
        });
    }

    /**
     * Load existing configuration
     */
    async loadExistingConfiguration() {
        if (!this.config.productId) return;

        try {
            const response = await this.makeAjaxRequest('loadConfiguration', {
                id_product: this.config.productId
            });

            if (response.success) {
                this.populateConfiguration(response);
            }
        } catch (error) {
            console.error('Error loading configuration:', error);
        }
    }

    /**
     * Populate form with configuration data
     */
    populateConfiguration(data) {
        // Main configuration
        if (data.config) {
            const activeRadio = document.querySelector(`input[name="custom_config_active"][value="${data.config.active ? 1 : 0}"]`);
            if (activeRadio) {
                activeRadio.checked = true;
            }

            const minQtyInput = document.querySelector('input[name="custom_minimum_order_qty"]');
            if (minQtyInput) {
                minQtyInput.value = data.config.minimum_order_qty || 50;
            }

            const basePriceInput = document.querySelector('input[name="custom_base_price"]');
            if (basePriceInput) {
                basePriceInput.value = data.config.base_price || 100;
            }
        }

        // Attributes
        if (data.attributes) {
            this.populateAttributes(data.attributes);
        }

        // Pricing
        if (data.pricing) {
            this.populatePricing(data.pricing);
        }
    }

    /**
     * Populate attributes section
     */
    populateAttributes(attributes) {
        Object.keys(attributes).forEach(type => {
            const attributeList = document.querySelector(`.attribute-list[data-type="${type}"]`);
            if (!attributeList) return;

            // Clear existing
            attributeList.innerHTML = '';

            // Add attributes
            attributes[type].forEach((attr, index) => {
                this.addAttributeItem(type, attr.attribute_name, attr.price_multiplier, index);
            });

            // Update count
            this.updateAttributeCount(type);
        });
    }

    /**
     * Populate pricing matrix
     */
    populatePricing(pricing) {
        const tbody = document.querySelector('#pricing-matrix tbody');
        if (!tbody) return;

        // Clear existing rows
        tbody.innerHTML = '';

        // Add rows for each quantity
        Object.keys(pricing).forEach(quantity => {
            this.addQuantityRowWithData(quantity, pricing[quantity]);
        });
    }

    /**
     * Add attribute item to list
     */
    addAttributeItem(type, name = '', multiplier = 1.0, index = null) {
        const list = document.querySelector(`.attribute-list[data-type="${type}"]`);
        if (!list) return;

        if (index === null) {
            index = this.attributeIndexes[type]++;
        }

        const item = document.createElement('div');
        item.className = 'attribute-item';
        item.dataset.index = index;

        item.innerHTML = `
            <div class="attribute-controls">
                <span class="drag-handle">
                    <i class="icon-reorder"></i>
                </span>
            </div>
            
            <div class="attribute-content">
                <input type="text" 
                       name="custom_${type}_attributes[${index}][name]" 
                       value="${this.escapeHtml(name)}"
                       class="form-control attribute-name" 
                       placeholder="Nome attributo" />
                
                <div class="input-group">
                    <input type="number" 
                           name="custom_${type}_attributes[${index}][multiplier]" 
                           value="${multiplier}"
                           class="form-control attribute-multiplier" 
                           step="0.01" min="0.01" max="10" />
                    <span class="input-group-addon">x</span>
                </div>
                
                <button type="button" class="btn btn-danger btn-sm btn-remove-attribute">
                    <i class="icon-trash"></i>
                </button>
            </div>
        `;

        list.appendChild(item);
        this.updateAttributeCount(type);
        
        // Focus on name input
        const nameInput = item.querySelector('.attribute-name');
        if (nameInput && !name) {
            nameInput.focus();
        }
    }

    /**
     * Add attribute
     */
    addAttribute(event) {
        const type = event.currentTarget.dataset.type;
        this.addAttributeItem(type);
        this.markUnsaved();
    }

    /**
     * Remove attribute
     */
    removeAttribute(event) {
        if (!confirm(this.config.translations.confirmDelete)) {
            return;
        }

        const item = event.currentTarget.closest('.attribute-item');
        const list = item.closest('.attribute-list');
        const type = list.dataset.type;

        item.remove();
        this.updateAttributeCount(type);
        this.markUnsaved();
    }

    /**
     * Update attribute count badge
     */
    updateAttributeCount(type) {
        const panel = document.querySelector(`.attribute-panel[data-type="${type}"]`);
        const badge = panel?.querySelector('.attribute-count');
        const list = document.querySelector(`.attribute-list[data-type="${type}"]`);
        
        if (badge && list) {
            const count = list.querySelectorAll('.attribute-item').length;
            badge.textContent = count;
        }
    }

    /**
     * Update attribute positions after sorting
     */
    updateAttributePositions(list) {
        const items = list.querySelectorAll('.attribute-item');
        items.forEach((item, index) => {
            const inputs = item.querySelectorAll('input[name*="["]');
            inputs.forEach(input => {
                const name = input.name;
                const newName = name.replace(/\[\d+\]/, `[${index}]`);
                input.name = newName;
            });
            item.dataset.index = index;
        });
    }

    /**
     * Update attribute indexes
     */
    updateAttributeIndexes() {
        Object.keys(this.attributeIndexes).forEach(type => {
            const items = document.querySelectorAll(`.attribute-list[data-type="${type}"] .attribute-item`);
            this.attributeIndexes[type] = items.length;
        });
    }

    /**
     * Generate pricing matrix
     */
    async generateMatrix(event) {
        event.preventDefault();

        const basePriceInput = document.querySelector('input[name="custom_base_price"]');
        const basePrice = basePriceInput ? parseFloat(basePriceInput.value) : 100;

        if (!basePrice || basePrice <= 0) {
            this.showNotification('error', 'Inserisci un prezzo base valido');
            return;
        }

        if (!confirm('Generare una nuova matrice prezzi? Questo sovrascriverà i prezzi esistenti.')) {
            return;
        }

        try {
            this.showLoading(event.currentTarget, true);

            const response = await this.makeAjaxRequest('generateMatrix', {
                id_product: this.config.productId,
                base_price: basePrice
            });

            if (response.success) {
                this.showNotification('success', this.config.translations.matrixGenerated);
                this.generateDefaultMatrix(basePrice);
                this.markUnsaved();
            } else {
                this.showNotification('error', response.error || 'Errore nella generazione della matrice');
            }
        } catch (error) {
            console.error('Matrix generation error:', error);
            this.showNotification('error', 'Errore nella generazione della matrice');
        } finally {
            this.showLoading(event.currentTarget, false);
        }
    }

    /**
     * Generate default matrix in frontend
     */
    generateDefaultMatrix(basePrice) {
        const tbody = document.querySelector('#pricing-matrix tbody');
        if (!tbody) return;

        // Clear existing
        tbody.innerHTML = '';

        const quantities = [250, 500, 750, 1000, 2500, 5000];
        const deliveryDays = this.config.deliveryDays || [3, 5, 7, 10, 14];
        const discounts = [0, 8, 17, 25, 35];

        quantities.forEach(quantity => {
            const row = document.createElement('tr');
            row.className = 'quantity-row';
            row.dataset.quantity = quantity;

            let html = `
                <td class="quantity-cell">
                    <input type="number" 
                           name="custom_quantities[]" 
                           value="${quantity}" 
                           class="form-control quantity-input" 
                           min="1" />
                </td>
            `;

            deliveryDays.forEach((days, index) => {
                // Skip impossible combinations
                if (quantity >= 5000 && days <= 3) {
                    html += `<td class="price-cell" data-days="${days}">
                        <input type="number" class="form-control price-input" placeholder="N.D." disabled />
                    </td>`;
                    return;
                }

                // Calculate price
                const unitPrice = basePrice / Math.pow(quantity / 250, 0.3);
                const price = unitPrice * quantity;
                const discount = discounts[index] || 0;
                const finalPrice = price * (1 - discount / 100);

                html += `
                    <td class="price-cell" data-days="${days}">
                        <input type="number" 
                               name="custom_price_${quantity}_${days}" 
                               value="${finalPrice.toFixed(2)}"
                               class="form-control price-input" 
                               step="0.01" min="0" 
                               placeholder="0.00" />
                    </td>
                `;
            });

            html += `
                <td class="actions-cell">
                    <button type="button" class="btn btn-danger btn-sm btn-remove-row">
                        <i class="icon-trash"></i>
                    </button>
                </td>
            `;

            row.innerHTML = html;
            tbody.appendChild(row);
        });
    }

    /**
     * Add quantity row
     */
    addQuantityRow(event) {
        event.preventDefault();
        
        const tbody = document.querySelector('#pricing-matrix tbody');
        if (!tbody) return;

        const deliveryDays = this.config.deliveryDays || [3, 5, 7, 10, 14];
        const newQuantity = this.getNextQuantity();

        const row = document.createElement('tr');
        row.className = 'quantity-row';
        row.dataset.quantity = newQuantity;

        let html = `
            <td class="quantity-cell">
                <input type="number" 
                       name="custom_quantities[]" 
                       value="${newQuantity}" 
                       class="form-control quantity-input" 
                       min="1" />
            </td>
        `;

        deliveryDays.forEach(days => {
            html += `
                <td class="price-cell" data-days="${days}">
                    <input type="number" 
                           name="custom_price_${newQuantity}_${days}" 
                           value=""
                           class="form-control price-input" 
                           step="0.01" min="0" 
                           placeholder="0.00" />
                </td>
            `;
        });

        html += `
            <td class="actions-cell">
                <button type="button" class="btn btn-danger btn-sm btn-remove-row">
                    <i class="icon-trash"></i>
                </button>
            </td>
        `;

        row.innerHTML = html;
        tbody.appendChild(row);
        
        // Focus on quantity input
        const quantityInput = row.querySelector('.quantity-input');
        if (quantityInput) {
            quantityInput.focus();
            quantityInput.select();
        }

        this.markUnsaved();
    }

    /**
     * Add quantity row with data
     */
    addQuantityRowWithData(quantity, deliveryOptions) {
        const tbody = document.querySelector('#pricing-matrix tbody');
        if (!tbody) return;

        const deliveryDays = this.config.deliveryDays || [3, 5, 7, 10, 14];

        const row = document.createElement('tr');
        row.className = 'quantity-row';
        row.dataset.quantity = quantity;

        let html = `
            <td class="quantity-cell">
                <input type="number" 
                       name="custom_quantities[]" 
                       value="${quantity}" 
                       class="form-control quantity-input" 
                       min="1" />
            </td>
        `;

        deliveryDays.forEach(days => {
            const priceData = deliveryOptions[days];
            const price = priceData ? priceData.price : '';

            html += `
                <td class="price-cell" data-days="${days}">
                    <input type="number" 
                           name="custom_price_${quantity}_${days}" 
                           value="${price}"
                           class="form-control price-input" 
                           step="0.01" min="0" 
                           placeholder="0.00" />
                </td>
            `;
        });

        html += `
            <td class="actions-cell">
                <button type="button" class="btn btn-danger btn-sm btn-remove-row">
                    <i class="icon-trash"></i>
                </button>
            </td>
        `;

        row.innerHTML = html;
        tbody.appendChild(row);
    }

    /**
     * Remove quantity row
     */
    removeQuantityRow(event) {
        if (!confirm(this.config.translations.confirmDelete)) {
            return;
        }

        const row = event.currentTarget.closest('.quantity-row');
        row.remove();
        this.markUnsaved();
    }

    /**
     * Get next quantity suggestion
     */
    getNextQuantity() {
        const existingQuantities = Array.from(document.querySelectorAll('.quantity-input'))
            .map(input => parseInt(input.value))
            .filter(q => !isNaN(q))
            .sort((a, b) => a - b);

        if (existingQuantities.length === 0) {
            return 250;
        }

        const lastQuantity = existingQuantities[existingQuantities.length - 1];
        
        // Suggest next logical quantity
        if (lastQuantity < 500) return 500;
        if (lastQuantity < 1000) return 1000;
        if (lastQuantity < 2500) return 2500;
        if (lastQuantity < 5000) return 5000;
        
        return lastQuantity + 1000;
    }

    /**
     * Show import modal
     */
    showImportModal(event) {
        event.preventDefault();
        
        const modal = document.getElementById('csv-import-modal');
        if (modal && typeof jQuery !== 'undefined') {
            jQuery(modal).modal('show');
        }
    }

    /**
     * Import CSV
     */
    async importCSV(event) {
        event.preventDefault();

        const fileInput = document.querySelector('#csv-import-form input[type="file"]');
        if (!fileInput || !fileInput.files[0]) {
            this.showNotification('error', 'Seleziona un file CSV');
            return;
        }

        const formData = new FormData();
        formData.append('csv_file', fileInput.files[0]);
        formData.append('action', 'importCSV');
        formData.append('id_product', this.config.productId);

        try {
            this.showLoading(event.currentTarget, true);

            const response = await fetch(this.config.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification('success', 'CSV importato con successo');
                
                // Close modal
                const modal = document.getElementById('csv-import-modal');
                if (modal && typeof jQuery !== 'undefined') {
                    jQuery(modal).modal('hide');
                }
                
                // Reload configuration
                await this.loadExistingConfiguration();
            } else {
                this.showNotification('error', data.error || this.config.translations.invalidCSV);
            }
        } catch (error) {
            console.error('CSV import error:', error);
            this.showNotification('error', this.config.translations.invalidCSV);
        } finally {
            this.showLoading(event.currentTarget, false);
        }
    }

    /**
     * Export CSV
     */
    async exportCSV(event) {
        event.preventDefault();

        try {
            const response = await fetch(this.config.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'exportCSV',
                    id_product: this.config.productId
                })
            });

            if (response.ok) {
                // Trigger download
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `product_${this.config.productId}_pricing.csv`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);

                this.showNotification('success', 'CSV esportato con successo');
            } else {
                this.showNotification('error', 'Errore durante l\'esportazione');
            }
        } catch (error) {
            console.error('CSV export error:', error);
            this.showNotification('error', 'Errore durante l\'esportazione');
        }
    }

    /**
     * Copy configuration from another product
     */
    async copyConfiguration(event) {
        event.preventDefault();

        const sourceSelect = document.getElementById('source-product-select');
        const sourceProductId = sourceSelect?.value;

        if (!sourceProductId) {
            this.showNotification('error', 'Seleziona un prodotto sorgente');
            return;
        }

        if (!confirm('Copiare la configurazione dal prodotto selezionato? Questo sovrascriverà la configurazione attuale.')) {
            return;
        }

        try {
            this.showLoading(event.currentTarget, true);

            const response = await this.makeAjaxRequest('copyConfiguration', {
                source_product: sourceProductId,
                target_product: this.config.productId
            });

            if (response.success) {
                this.showNotification('success', 'Configurazione copiata con successo');
                await this.loadExistingConfiguration();
                this.markUnsaved();
            } else {
                this.showNotification('error', response.error || 'Errore durante la copia');
            }
        } catch (error) {
            console.error('Copy configuration error:', error);
            this.showNotification('error', 'Errore durante la copia');
        } finally {
            this.showLoading(event.currentTarget, false);
        }
    }

    /**
     * Test price calculator
     */
    async testCalculator(event) {
        event.preventDefault();

        const quantityInput = document.getElementById('test-quantity');
        const deliverySelect = document.getElementById('test-delivery');
        const resultDiv = document.getElementById('test-result');

        const quantity = parseInt(quantityInput?.value);
        const deliveryDays = parseInt(deliverySelect?.value);

        if (!quantity || !deliveryDays) {
            this.showNotification('error', 'Inserisci quantità e giorni di consegna');
            return;
        }

        try {
            this.showLoading(event.currentTarget, true);

            const response = await this.makeAjaxRequest('calculatePrice', {
                id_product: this.config.productId,
                quantity: quantity,
                delivery_days: deliveryDays,
                attributes: {}
            });

            if (response.success) {
                resultDiv.className = 'test-result success';
                resultDiv.textContent = `€${response.price} (€${response.unit_price}/pz)`;
            } else {
                resultDiv.className = 'test-result error';
                resultDiv.textContent = response.error || this.config.translations.priceCalculationError;
            }
        } catch (error) {
            console.error('Price calculation error:', error);
            resultDiv.className = 'test-result error';
            resultDiv.textContent = this.config.translations.priceCalculationError;
        } finally {
            this.showLoading(event.currentTarget, false);
        }
    }

    /**
     * Preview frontend
     */
    previewFrontend(event) {
        event.preventDefault();

        const productUrl = `/product/${this.config.productId}`;
        window.open(productUrl, '_blank');
    }

    /**
     * Reset configuration
     */
    resetConfiguration(event) {
        event.preventDefault();

        if (!confirm('Reset della configurazione? Tutte le modifiche non salvate andranno perse.')) {
            return;
        }

        // Reset form
        const form = document.getElementById('custom-product-config-form');
        if (form) {
            form.reset();
        }

        // Clear attributes
        document.querySelectorAll('.attribute-list').forEach(list => {
            list.innerHTML = '';
            const type = list.dataset.type;
            this.updateAttributeCount(type);
        });

        // Clear pricing matrix
        const tbody = document.querySelector('#pricing-matrix tbody');
        if (tbody) {
            tbody.innerHTML = '';
        }

        // Reset test result
        const testResult = document.getElementById('test-result');
        if (testResult) {
            testResult.className = 'test-result';
            testResult.textContent = '';
        }

        this.unsavedChanges = false;
        this.showNotification('info', 'Configurazione resettata');
    }

    /**
     * Toggle advanced mode
     */
    toggleAdvancedMode() {
        this.isAdvancedMode = !this.isAdvancedMode;
        
        const advancedTools = document.getElementById('advanced-tools');
        if (advancedTools) {
            advancedTools.style.display = this.isAdvancedMode ? 'block' : 'none';
        }

        const toggleBtn = document.querySelector('.panel-heading-action .list-toolbar-btn');
        if (toggleBtn) {
            toggleBtn.title = this.isAdvancedMode ? 'Modalità Standard' : 'Modalità Avanzata';
            const icon = toggleBtn.querySelector('i');
            if (icon) {
                icon.className = this.isAdvancedMode ? 'process-icon-back' : 'process-icon-configure';
            }
        }
    }

    /**
     * Save configuration
     */
    async saveConfiguration(event) {
        event.preventDefault();

        const form = event.currentTarget;
        const formData = new FormData(form);
        
        // Add action
        formData.append('action', 'saveConfiguration');

        try {
            this.showLoading(form.querySelector('[type="submit"]'), true);

            const response = await fetch(this.config.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification('success', this.config.translations.saveSuccess);
                this.unsavedChanges = false;
            } else {
                this.showNotification('error', data.error || this.config.translations.saveError);
            }
        } catch (error) {
            console.error('Save configuration error:', error);
            this.showNotification('error', this.config.translations.saveError);
        } finally {
            this.showLoading(form.querySelector('[type="submit"]'), false);
        }
    }

    /**
     * Validate pricing input
     */
    validatePricingInput(event) {
        const input = event.currentTarget;
        const value = parseFloat(input.value);
        
        if (input.classList.contains('price-input')) {
            if (value < 0) {
                input.value = 0;
            }
            
            // Update related quantity inputs' name attributes
            if (input.classList.contains('quantity-input')) {
                this.updateQuantityInputNames(input);
            }
        }
    }

    /**
     * Update quantity input names
     */
    updateQuantityInputNames(quantityInput) {
        const row = quantityInput.closest('.quantity-row');
        const newQuantity = quantityInput.value;
        
        row.querySelectorAll('.price-input').forEach(priceInput => {
            const name = priceInput.name;
            const daysPart = name.split('_').pop();
            priceInput.name = `custom_price_${newQuantity}_${daysPart}`;
        });
        
        row.dataset.quantity = newQuantity;
    }

    /**
     * Load products list for copy functionality
     */
    async loadProductsList() {
        const sourceSelect = document.getElementById('source-product-select');
        if (!sourceSelect) return;

        // This would typically load from an API endpoint
        // For now, we'll add a placeholder
        sourceSelect.innerHTML = '<option value="">Seleziona prodotto sorgente</option>';
        
        // Add current product configurations
        Object.keys(this.config.currentPricing || {}).forEach(productId => {
            if (productId !== this.config.productId.toString()) {
                const option = document.createElement('option');
                option.value = productId;
                option.textContent = `Prodotto #${productId}`;
                sourceSelect.appendChild(option);
            }
        });
    }

    /**
     * Mark form as having unsaved changes
     */
    markUnsaved() {
        this.unsavedChanges = true;
        
        // Add visual indicator
        const submitBtn = document.querySelector('[name="submitCustomProductConfig"]');
        if (submitBtn && !submitBtn.classList.contains('btn-warning')) {
            submitBtn.classList.add('btn-warning');
            submitBtn.innerHTML = '<i class="process-icon-save"></i> Salva Modifiche *';
        }
    }

    /**
     * Make AJAX request
     */
    async makeAjaxRequest(action, data = {}) {
        const response = await fetch(this.config.ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: action,
                ajax: 1,
                ...data
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    /**
     * Show loading state
     */
    showLoading(element, show) {
        if (!element) return;

        if (show) {
            element.classList.add('loading');
            element.disabled = true;
        } else {
            element.classList.remove('loading');
            element.disabled = false;
        }
    }

    /**
     * Show notification
     */
    showNotification(type, message) {
        // Remove existing notifications
        document.querySelectorAll('.notification').forEach(n => n.remove());

        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <span>${this.escapeHtml(message)}</span>
            <span class="close">&times;</span>
        `;

        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        // Auto hide
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 5000);

        // Close button
        notification.querySelector('.close').addEventListener('click', () => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        });
    }

    /**
     * Escape HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('custom-product-configuration')) {
        window.adminManagerInstance = new CustomProductAdminManager();
    }
});