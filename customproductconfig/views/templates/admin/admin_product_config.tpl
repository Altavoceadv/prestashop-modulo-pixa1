{**
 * Admin Product Configuration Template
 * views/templates/admin/admin_product_config.tpl
 *}

<div class="panel" id="custom-product-configuration">
    <div class="panel-heading">
        <i class="icon-cogs"></i>
        {l s='Configurazione Prodotto Personalizzato' mod='customproductconfig'}
        
        <span class="panel-heading-action">
            <a class="list-toolbar-btn" href="#" onclick="toggleConfigurationMode(); return false;">
                <span title="Modalità Avanzata">
                    <i class="process-icon-configure"></i>
                </span>
            </a>
        </span>
    </div>

    <form id="custom-product-config-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id_product" value="{$id_product}" />
        <input type="hidden" name="ajax_url" value="{$ajax_url}" />

        <!-- Main Configuration -->
        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s='Abilita Configurazione Personalizzata' mod='customproductconfig'}
                </label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="custom_config_active" id="custom_config_active_on" value="1" 
                               {if $config->active}checked="checked"{/if} />
                        <label for="custom_config_active_on" class="radioCheck">
                            {l s='Sì' mod='customproductconfig'}
                        </label>
                        <input type="radio" name="custom_config_active" id="custom_config_active_off" value="0" 
                               {if !$config->active}checked="checked"{/if} />
                        <label for="custom_config_active_off" class="radioCheck">
                            {l s='No' mod='customproductconfig'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                    <p class="help-block">
                        {l s='Abilita la configurazione personalizzata per questo prodotto' mod='customproductconfig'}
                    </p>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3 required">
                    {l s='Quantità Minima Ordine' mod='customproductconfig'}
                </label>
                <div class="col-lg-9">
                    <input type="number" name="custom_minimum_order_qty" 
                           value="{$config->minimum_order_qty|default:50}" 
                           class="form-control fixed-width-sm" min="1" />
                    <p class="help-block">
                        {l s='Numero minimo di pezzi che il cliente deve ordinare' mod='customproductconfig'}
                    </p>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3 required">
                    {l s='Prezzo Base di Riferimento' mod='customproductconfig'}
                </label>
                <div class="col-lg-9">
                    <div class="input-group fixed-width-lg">
                        <input type="number" name="custom_base_price" 
                               value="{$config->base_price|default:100}" 
                               class="form-control" step="0.01" min="0" />
                        <span class="input-group-addon">€</span>
                    </div>
                    <p class="help-block">
                        {l s='Prezzo base utilizzato per calcolare i moltiplicatori' mod='customproductconfig'}
                    </p>
                </div>
            </div>
        </div>

        <!-- Attributes Configuration -->
        <div class="panel-group" id="attributes-accordion">
            <h3 class="attributes-title">
                <i class="icon-list"></i>
                {l s='Configurazione Attributi' mod='customproductconfig'}
            </h3>

            {assign var="attribute_types" value=['formato','carta','finitura','colori']}
            {assign var="attribute_labels" value=[
                'formato' => {l s='Formato Scatola' mod='customproductconfig'},
                'carta' => {l s='Tipo di Carta' mod='customproductconfig'},
                'finitura' => {l s='Finitura' mod='customproductconfig'},
                'colori' => {l s='Colori di Stampa' mod='customproductconfig'}
            ]}

            {foreach from=$attribute_types item=type}
                <div class="panel panel-default attribute-panel" data-type="{$type}">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#attributes-accordion" 
                               href="#collapse-{$type}" aria-expanded="true">
                                <i class="icon-{$type}"></i>
                                {$attribute_labels[$type]}
                                <span class="badge attribute-count">
                                    {if isset($attributes[$type])}{count($attributes[$type])}{else}0{/if}
                                </span>
                            </a>
                        </h4>
                    </div>
                    
                    <div id="collapse-{$type}" class="panel-collapse collapse in">
                        <div class="panel-body">
                            <div class="attribute-list" data-type="{$type}">
                                {if isset($attributes[$type])}
                                    {foreach from=$attributes[$type] item=attribute key=index}
                                        <div class="attribute-item" data-index="{$index}">
                                            <div class="attribute-controls">
                                                <span class="drag-handle">
                                                    <i class="icon-reorder"></i>
                                                </span>
                                            </div>
                                            
                                            <div class="attribute-content">
                                                <input type="text" 
                                                       name="custom_{$type}_attributes[{$index}][name]" 
                                                       value="{$attribute.attribute_name|escape:'html':'UTF-8'}"
                                                       class="form-control attribute-name" 
                                                       placeholder="{l s='Nome attributo' mod='customproductconfig'}" />
                                                
                                                <div class="input-group">
                                                    <input type="number" 
                                                           name="custom_{$type}_attributes[{$index}][multiplier]" 
                                                           value="{$attribute.price_multiplier}"
                                                           class="form-control attribute-multiplier" 
                                                           step="0.01" min="0.01" max="10" />
                                                    <span class="input-group-addon">x</span>
                                                </div>
                                                
                                                <button type="button" class="btn btn-danger btn-sm btn-remove-attribute">
                                                    <i class="icon-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    {/foreach}
                                {/if}
                            </div>
                            
                            <button type="button" class="btn btn-success btn-add-attribute" data-type="{$type}">
                                <i class="icon-plus"></i>
                                {l s='Aggiungi' mod='customproductconfig'} {$attribute_labels[$type]}
                            </button>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>

        <!-- Pricing Matrix -->
        <div class="panel panel-default" id="pricing-matrix-panel">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="icon-table"></i>
                    {l s='Matrice Prezzi' mod='customproductconfig'}
                    
                    <div class="panel-actions">
                        <button type="button" class="btn btn-default btn-sm" id="btn-generate-matrix">
                            <i class="icon-magic"></i>
                            {l s='Genera Matrice' mod='customproductconfig'}
                        </button>
                        
                        <button type="button" class="btn btn-default btn-sm" id="btn-import-csv">
                            <i class="icon-upload"></i>
                            {l s='Importa CSV' mod='customproductconfig'}
                        </button>
                        
                        <button type="button" class="btn btn-default btn-sm" id="btn-export-csv">
                            <i class="icon-download"></i>
                            {l s='Esporta CSV' mod='customproductconfig'}
                        </button>
                    </div>
                </h3>
            </div>
            
            <div class="panel-body">
                <div class="pricing-matrix-container">
                    <table class="table table-bordered pricing-matrix" id="pricing-matrix">
                        <thead>
                            <tr>
                                <th class="quantity-header">
                                    {l s='Quantità' mod='customproductconfig'}
                                </th>
                                {foreach from=$delivery_days item=days}
                                    <th class="delivery-header" data-days="{$days}">
                                        {$days} {l s='giorni' mod='customproductconfig'}
                                    </th>
                                {/foreach}
                                <th class="actions-header">
                                    {l s='Azioni' mod='customproductconfig'}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {if isset($pricing) && $pricing}
                                {foreach from=$pricing key=quantity item=delivery_options}
                                    <tr class="quantity-row" data-quantity="{$quantity}">
                                        <td class="quantity-cell">
                                            <input type="number" 
                                                   name="custom_quantities[]" 
                                                   value="{$quantity}" 
                                                   class="form-control quantity-input" 
                                                   min="1" />
                                        </td>
                                        
                                        {foreach from=$delivery_days item=days}
                                            <td class="price-cell" data-days="{$days}">
                                                <input type="number" 
                                                       name="custom_price_{$quantity}_{$days}" 
                                                       value="{if isset($delivery_options[$days])}{$delivery_options[$days].price}{/if}"
                                                       class="form-control price-input" 
                                                       step="0.01" min="0" 
                                                       placeholder="0.00" />
                                            </td>
                                        {/foreach}
                                        
                                        <td class="actions-cell">
                                            <button type="button" class="btn btn-danger btn-sm btn-remove-row">
                                                <i class="icon-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                {/foreach}
                            {/if}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="{count($delivery_days) + 2}">
                                    <button type="button" class="btn btn-success btn-add-quantity">
                                        <i class="icon-plus"></i>
                                        {l s='Aggiungi Quantità' mod='customproductconfig'}
                                    </button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Submit Actions -->
        <div class="panel-footer">
            <button type="submit" name="submitCustomProductConfig" class="btn btn-default pull-right">
                <i class="process-icon-save"></i>
                {l s='Salva Configurazione' mod='customproductconfig'}
            </button>
            
            <button type="button" class="btn btn-warning" id="btn-reset-config">
                <i class="icon-refresh"></i>
                {l s='Reset' mod='customproductconfig'}
            </button>
        </div>
    </form>
</div>

<script type="text/javascript">
    // Configuration data for admin JavaScript
    window.adminCustomProductConfig = {
        ajaxUrl: '{$ajax_url|escape:"javascript":"UTF-8"}',
        productId: {$id_product|intval},
        deliveryDays: {$delivery_days|json_encode},
        currentPricing: {if isset($pricing)}{$pricing|json_encode}{else}{}{{/if},
        currentAttributes: {if isset($attributes)}{$attributes|json_encode}{else}{}{{/if},
        translations: {
            confirmDelete: '{l s="Sei sicuro di voler eliminare questo elemento?" mod="customproductconfig" js=1}',
            saveSuccess: '{l s="Configurazione salvata con successo" mod="customproductconfig" js=1}',
            saveError: '{l s="Errore durante il salvataggio" mod="customproductconfig" js=1}',
            matrixGenerated: '{l s="Matrice generata con successo" mod="customproductconfig" js=1}',
            invalidCSV: '{l s="File CSV non valido" mod="customproductconfig" js=1}',
            priceCalculationError: '{l s="Errore nel calcolo del prezzo" mod="customproductconfig" js=1}'
        }
    };
</script>
