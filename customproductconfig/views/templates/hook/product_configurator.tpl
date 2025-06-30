{**
 * Frontend Product Configurator Template
 * views/templates/hook/product_configurator.tpl
 *}

<div id="custom-product-configurator" class="custom-configurator-wrapper">
    <div class="configurator-header">
        <h3 class="configurator-title">
            <i class="icon-cogs"></i>
            {l s='Personalizza il tuo prodotto' mod='customproductconfig'}
        </h3>
        <p class="configurator-subtitle">
            {l s='Configura le caratteristiche del prodotto per ottenere il prezzo migliore' mod='customproductconfig'}
        </p>
        
        {if $product_config->minimum_order_qty > 1}
        <div class="minimum-order-notice">
            <i class="icon-info-circle"></i>
            {l s='Ordine minimo:' mod='customproductconfig'} 
            <strong>{$product_config->minimum_order_qty}</strong> 
            {l s='pezzi' mod='customproductconfig'}
        </div>
        {/if}
    </div>

    <div class="configurator-body">
        <!-- Attributes Section -->
        <div class="attributes-section">
            <h4 class="section-title">
                {l s='Seleziona le caratteristiche' mod='customproductconfig'}
            </h4>
            
            <div class="attributes-grid">
                {foreach from=$product_attributes key=attribute_type item=attributes}
                    <div class="attribute-group" data-type="{$attribute_type}">
                        <label class="attribute-label">
                            {if $attribute_type == 'formato'}
                                {l s='Formato Scatola' mod='customproductconfig'}
                            {elseif $attribute_type == 'carta'}
                                {l s='Tipo di Carta' mod='customproductconfig'}
                            {elseif $attribute_type == 'finitura'}
                                {l s='Finitura' mod='customproductconfig'}
                            {elseif $attribute_type == 'colori'}
                                {l s='Colori di Stampa' mod='customproductconfig'}
                            {/if}
                        </label>
                        
                        <div class="attribute-options">
                            {foreach from=$attributes item=attribute}
                                <div class="attribute-option" 
                                     data-value="{$attribute.attribute_name|escape:'html':'UTF-8'}"
                                     data-multiplier="{$attribute.price_multiplier}"
                                     data-type="{$attribute_type}">
                                    <div class="option-content">
                                        <div class="option-icon">
                                            <img src="{$module_dir}views/img/attributes/{$attribute_type}_{$attribute.position + 1}.svg" 
                                                 alt="{$attribute.attribute_name|escape:'html':'UTF-8'}"
                                                 onerror="this.style.display='none'">
                                        </div>
                                        <div class="option-text">
                                            <span class="option-name">{$attribute.attribute_name}</span>
                                            {if $attribute.price_multiplier != 1}
                                                <span class="option-multiplier">
                                                    {if $attribute.price_multiplier > 1}
                                                        +{math equation="(x-1)*100" x=$attribute.price_multiplier format="%.0f"}%
                                                    {else}
                                                        -{math equation="(1-x)*100" x=$attribute.price_multiplier format="%.0f"}%
                                                    {/if}
                                                </span>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>

        <!-- Pricing Section -->
        <div class="pricing-section">
            <div class="pricing-header">
                <h4 class="section-title">
                    {l s='Seleziona il prezzo e la data di consegna' mod='customproductconfig'}
                </h4>
                
                <div class="pricing-controls">
                    <button type="button" class="btn btn-link toggle-grid" data-toggle="collapse" data-target="#pricing-grid">
                        <i class="icon-table"></i>
                        {l s='Mostra griglia prezzi' mod='customproductconfig'}
                    </button>
                    
                    <div class="tax-toggle">
                        <label class="switch">
                            <input type="checkbox" id="tax-toggle" />
                            <span class="slider"></span>
                        </label>
                        <span class="tax-label">
                            <span class="without-tax">{l s='Senza IVA' mod='customproductconfig'}</span>
                            <span class="with-tax">{l s='Con IVA' mod='customproductconfig'}</span>
                        </span>
                    </div>
                </div>
            </div>

            <div id="pricing-grid" class="pricing-grid collapse">
                <div class="grid-container">
                    <div class="grid-header">
                        <div class="quantity-header">
                            {l s='Quantità' mod='customproductconfig'}
                        </div>
                        
                        <div class="delivery-headers">
                            {foreach from=$delivery_dates item=date}
                                <div class="delivery-header" data-days="{$date.days}">
                                    <div class="delivery-days">{$date.days} giorni</div>
                                    <div class="delivery-date">
                                        <span class="day-name">{$date.day_name}</span>
                                        <span class="date-value">{$date.formatted_date}</span>
                                    </div>
                                </div>
                            {/foreach}
                            
                            <div class="more-dates">
                                <button type="button" class="btn-more-dates">
                                    <i class="icon-chevron-right"></i>
                                    <span>{l s='Altre date' mod='customproductconfig'}</span>
                                </button>
                                <div class="more-dates-tooltip">
                                    {l s='Scorri per vedere tutte le date' mod='customproductconfig'}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid-body">
                        {if isset($product_pricing) && $product_pricing}
                            {foreach from=$product_pricing key=quantity item=delivery_options}
                                <div class="quantity-row" data-quantity="{$quantity}">
                                    <div class="quantity-cell">
                                        <span class="quantity-value">{$quantity}</span>
                                        <span class="quantity-label">pz</span>
                                    </div>
                                    
                                    <div class="price-cells">
                                        {foreach from=$delivery_dates item=date}
                                            {if isset($delivery_options[$date.days])}
                                                {assign var="price_data" value=$delivery_options[$date.days]}
                                                <div class="price-cell" 
                                                     data-quantity="{$quantity}" 
                                                     data-days="{$date.days}"
                                                     data-price="{$price_data.price}"
                                                     data-unit-price="{$price_data.unit_price}">
                                                    <div class="price-value">
                                                        <span class="price-amount">{$price_data.price|string_format:"%.2f"}</span>
                                                        <span class="currency">€</span>
                                                    </div>
                                                    
                                                    {if $price_data.discount > 0}
                                                        <div class="price-discount">
                                                            -{$price_data.discount|string_format:"%.0f"}%
                                                        </div>
                                                    {/if}
                                                    
                                                    <div class="unit-price">
                                                        {$price_data.unit_price|string_format:"%.3f"}€/pz
                                                    </div>
                                                </div>
                                            {else}
                                                <div class="price-cell unavailable">
                                                    <span class="unavailable-text">N.D.</span>
                                                </div>
                                            {/if}
                                        {/foreach}
                                    </div>
                                </div>
                            {/foreach}
                        {/if}
                    </div>
                </div>
                
                <!-- Mobile Navigation -->
                <div class="mobile-nav">
                    <button type="button" class="btn-nav prev" disabled>
                        <i class="icon-chevron-left"></i>
                    </button>
                    
                    <div class="nav-info">
                        <span class="current-quantities"></span>
                        <div class="nav-dots"></div>
                    </div>
                    
                    <button type="button" class="btn-nav next">
                        <i class="icon-chevron-right"></i>
                    </button>
                </div>
                
                <div class="more-quantities">
                    <button type="button" class="btn btn-link btn-more-quantities">
                        <i class="icon-plus"></i>
                        {l s='Altre quantità' mod='customproductconfig'}
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-card">
                <h4 class="summary-title">
                    {l s='Riepilogo Ordine' mod='customproductconfig'}
                </h4>
                
                <div class="summary-content">
                    <div class="summary-row">
                        <span class="summary-label">{l s='Formato:' mod='customproductconfig'}</span>
                        <span class="summary-value" id="selected-formato">-</span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">{l s='Carta:' mod='customproductconfig'}</span>
                        <span class="summary-value" id="selected-carta">-</span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">{l s='Finitura:' mod='customproductconfig'}</span>
                        <span class="summary-value" id="selected-finitura">-</span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">{l s='Colori:' mod='customproductconfig'}</span>
                        <span class="summary-value" id="selected-colori">-</span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">{l s='Quantità:' mod='customproductconfig'}</span>
                        <span class="summary-value" id="selected-quantity">-</span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">{l s='Consegna:' mod='customproductconfig'}</span>
                        <span class="summary-value" id="selected-delivery">-</span>
                    </div>
                    
                    <div class="summary-divider"></div>
                    
                    <div class="summary-total">
                        <span class="total-label">{l s='Totale:' mod='customproductconfig'}</span>
                        <span class="total-value" id="total-price">-</span>
                    </div>
                </div>
                
                <div class="summary-actions">
                    <button type="button" id="add-to-cart-btn" class="btn btn-primary btn-add-to-cart" disabled>
                        <i class="icon-shopping-cart"></i>
                        {l s='Aggiungi al Carrello' mod='customproductconfig'}
                    </button>
                </div>
                
                <div class="delivery-notice">
                    <i class="icon-info-circle"></i>
                    {l s='Ricorda, la data di consegna è indicativa' mod='customproductconfig'}
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="configurator-loading">
        <div class="loading-content">
            <div class="spinner"></div>
            <span class="loading-text">{l s='Aggiungendo al carrello...' mod='customproductconfig'}</span>
        </div>
    </div>

    <!-- Success Message -->
    <div class="success-message" id="success-message">
        <div class="success-content">
            <i class="icon-check-circle"></i>
            <span>{l s='Prodotto aggiunto al carrello!' mod='customproductconfig'}</span>
        </div>
    </div>

    <!-- Error Message -->
    <div class="error-message" id="error-message">
        <div class="error-content">
            <i class="icon-exclamation-triangle"></i>
            <span id="error-text">{l s='Errore durante l\'aggiunta al carrello' mod='customproductconfig'}</span>
        </div>
    </div>
</div>

<script type="text/javascript">
    // Configuration data for JavaScript
    window.customProductConfig = {
        ajaxUrl: '{$ajax_url|escape:"javascript":"UTF-8"}',
        productId: {$product.id_product|intval},
        minimumQty: {$product_config->minimum_order_qty|intval},
        currency: '{$currency.sign|escape:"javascript":"UTF-8"}',
        deliveryDates: {$delivery_dates|json_encode},
        pricing: {$product_pricing|json_encode},
        attributes: {$product_attributes|json_encode},
        translations: {
            addingToCart: '{l s="Aggiungendo al carrello..." mod="customproductconfig" js=1}',
            productAdded: '{l s="Prodotto aggiunto al carrello!" mod="customproductconfig" js=1}',
            addToCartError: '{l s="Errore durante l\'aggiunta al carrello" mod="customproductconfig" js=1}',
            invalidConfiguration: '{l s="Configurazione non valida" mod="customproductconfig" js=1}',
            priceNotAvailable: '{l s="Prezzo non disponibile per questa configurazione" mod="customproductconfig" js=1}'
        }
    };
</script>