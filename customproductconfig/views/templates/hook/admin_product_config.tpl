{**
 * Hook Template per displayAdminProductsExtra
 * views/templates/hook/admin_product_config.tpl
 *}

<div class="panel" id="custom-product-configuration">
    <div class="panel-heading">
        <i class="icon-cogs"></i>
        {l s='Configurazione Prodotto Personalizzato' mod='customproductconfig'}
    </div>

    <div class="panel-body">
        <!-- IMPORTANTE: Non creare un form separato, aggiungi i campi al form esistente del prodotto -->
        <input type="hidden" name="custom_product_configured" value="1" />
        
        <!-- Configurazione Principale -->
        <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='Abilita Configurazione Personalizzata' mod='customproductconfig'}
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="custom_config_active" id="custom_config_active_on" value="1" 
                           {if isset($config) && $config->active}checked="checked"{/if} />
                    <label for="custom_config_active_on" class="radioCheck">
                        {l s='Sì' mod='customproductconfig'}
                    </label>
                    <input type="radio" name="custom_config_active" id="custom_config_active_off" value="0" 
                           {if !isset($config) || !$config->active}checked="checked"{/if} />
                    <label for="custom_config_active_off" class="radioCheck">
                        {l s='No' mod='customproductconfig'}
                    </label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">
                    {l s='Abilita la configurazione personalizzata per questo prodotto' mod='customproductconfig'}
                </p>
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='Quantità Minima Ordine' mod='customproductconfig'}
            </label>
            <div class="col-lg-9">
                <input type="number" name="custom_minimum_order_qty" 
                       value="{if isset($config)}{$config->minimum_order_qty}{else}50{/if}" 
                       class="form-control fixed-width-sm" min="1" />
                <p class="help-block">
                    {l s='Numero minimo di pezzi che il cliente deve ordinare' mod='customproductconfig'}
                </p>
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='Prezzo Base di Riferimento' mod='customproductconfig'}
            </label>
            <div class="col-lg-9">
                <div class="input-group fixed-width-lg">
                    <input type="number" name="custom_base_price" 
                           value="{if isset($config)}{$config->base_price}{else}100{/if}" 
                           class="form-control" step="0.01" min="0" />
                    <span class="input-group-addon">€</span>
                </div>
                <p class="help-block">
                    {l s='Prezzo base utilizzato per calcolare i moltiplicatori' mod='customproductconfig'}
                </p>
            </div>
            <div class="clearfix"></div>
        </div>

        <!-- Configurazione Attributi -->
        <div class="form-group" id="custom-attributes-section" {if !isset($config) || !$config->active}style="display:none;"{/if}>
            <div class="col-lg-12">
                <h4>{l s='Configurazione Attributi' mod='customproductconfig'}</h4>
                
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
                            <h5 class="panel-title">
                                {$attribute_labels[$type]}
                                <span class="badge attribute-count">
                                    {if isset($attributes[$type])}{count($attributes[$type])}{else}0{/if}
                                </span>
                            </h5>
                        </div>
                        
                        <div class="panel-body">
                            <div class="attribute-list" data-type="{$type}">
                                {if isset($attributes[$type])}
                                    {foreach from=$attributes[$type] item=attribute key=index}
                                        <div class="attribute-item row" data-index="{$index}">
                                            <div class="col-md-6">
                                                <input type="text" 
                                                       name="custom_{$type}_attributes[{$index}][name]" 
                                                       value="{$attribute.attribute_name|escape:'html':'UTF-8'}"
                                                       class="form-control attribute-name" 
                                                       placeholder="{l s='Nome attributo' mod='customproductconfig'}" />
                                            </div>
                                            <div class="col-md-3">
                                                <div class="input-group">
                                                    <input type="number" 
                                                           name="custom_{$type}_attributes[{$index}][multiplier]" 
                                                           value="{$attribute.price_multiplier}"
                                                           class="form-control attribute-multiplier" 
                                                           step="0.01" min="0.01" max="10" />
                                                    <span class="input-group-addon">x</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <button type="button" class="btn btn-danger btn-sm btn-remove-attribute">
                                                    <i class="icon-trash"></i> {l s='Rimuovi' mod='customproductconfig'}
                                                </button>
                                            </div>
                                        </div>
                                    {/foreach}
                                {/if}
                            </div>
                            
                            <button type="button" class="btn btn-success btn-sm btn-add-attribute" data-type="{$type}">
                                <i class="icon-plus"></i>
                                {l s='Aggiungi' mod='customproductconfig'} {$attribute_labels[$type]}
                            </button>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>

        <!-- Status Info -->
        <div class="alert alert-info">
            <h4>{l s='Stato Configurazione:' mod='customproductconfig'}</h4>
            <ul>
                <li>
                    {l s='Attributi configurati:' mod='customproductconfig'} 
                    <strong id="total-attributes">{if isset($attributes)}{count($attributes)}{else}0{/if}</strong>
                </li>
                <li>
                    {l s='Prezzi configurati:' mod='customproductconfig'} 
                    <strong>{if isset($pricing)}{count($pricing)}{else}0{/if}</strong>
                </li>
                <li>
                    {l s='Stato:' mod='customproductconfig'} 
                    <strong>
                        {if isset($config) && $config->active}
                            <span style="color: green;">{l s='Attivo' mod='customproductconfig'}</span>
                        {else}
                            <span style="color: red;">{l s='Disattivo' mod='customproductconfig'}</span>
                        {/if}
                    </strong>
                </li>
            </ul>
            
            <div class="alert alert-warning">
                <strong>{l s='Importante:' mod='customproductconfig'}</strong> 
                {l s='Dopo aver abilitato la configurazione, salva il prodotto e riapri questa sezione per accedere alle funzionalità avanzate.' mod='customproductconfig'}
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
{literal}
document.addEventListener('DOMContentLoaded', function() {
    let attributeIndexes = {
        formato: 0,
        carta: 0,
        finitura: 0,
        colori: 0
    };
    
    // Initialize indexes from existing attributes
    document.querySelectorAll('.attribute-list').forEach(function(list) {
        const type = list.dataset.type;
        const items = list.querySelectorAll('.attribute-item');
        attributeIndexes[type] = items.length;
    });
    
    // Toggle attribute section visibility
    const configToggle = document.querySelectorAll('input[name="custom_config_active"]');
    const attributesSection = document.getElementById('custom-attributes-section');
    
    configToggle.forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            if (this.value === '1' && this.checked) {
                attributesSection.style.display = 'block';
            } else if (this.value === '0' && this.checked) {
                attributesSection.style.display = 'none';
            }
        });
    });
    
    // Add attribute functionality
    document.querySelectorAll('.btn-add-attribute').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const type = this.dataset.type;
            const list = document.querySelector('.attribute-list[data-type="' + type + '"]');
            const index = attributeIndexes[type]++;
            
            const item = document.createElement('div');
            item.className = 'attribute-item row';
            item.dataset.index = index;
            
            item.innerHTML = `
                <div class="col-md-6">
                    <input type="text" 
                           name="custom_${type}_attributes[${index}][name]" 
                           value=""
                           class="form-control attribute-name" 
                           placeholder="Nome attributo" />
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <input type="number" 
                               name="custom_${type}_attributes[${index}][multiplier]" 
                               value="1.0"
                               class="form-control attribute-multiplier" 
                               step="0.01" min="0.01" max="10" />
                        <span class="input-group-addon">x</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-danger btn-sm btn-remove-attribute">
                        <i class="icon-trash"></i> Rimuovi
                    </button>
                </div>
            `;
            
            list.appendChild(item);
            
            // Update count badge
            const badge = document.querySelector('.attribute-panel[data-type="' + type + '"] .attribute-count');
            if (badge) {
                badge.textContent = list.children.length;
            }
        });
    });
    
    // Remove attribute functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remove-attribute') || 
            e.target.parentElement.classList.contains('btn-remove-attribute')) {
            
            const item = e.target.closest('.attribute-item');
            const list = item.closest('.attribute-list');
            const type = list.dataset.type;
            
            if (confirm('Sei sicuro di voler rimuovere questo attributo?')) {
                item.remove();
                
                // Update count badge
                const badge = document.querySelector('.attribute-panel[data-type="' + type + '"] .attribute-count');
                if (badge) {
                    badge.textContent = list.children.length;
                }
            }
        }
    });
    
    // Update total attributes count
    function updateTotalCount() {
        let total = 0;
        document.querySelectorAll('.attribute-list').forEach(function(list) {
            total += list.children.length;
        });
        
        const totalEl = document.getElementById('total-attributes');
        if (totalEl) {
            totalEl.textContent = total;
        }
    }
    
    // Update count periodically
    setInterval(updateTotalCount, 1000);
});
{/literal}
</script>
