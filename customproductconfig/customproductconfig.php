<?php
/**
 * Custom Product Configurator Module for PrestaShop 8
 * Main module file - VERSIONE CORRETTA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomProductConfig extends Module
{
    public function __construct()
    {
        $this->name = 'customproductconfig';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Your Company';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        // AGGIUNTO: Carica le classi personalizzate PRIMA di chiamare parent::__construct()
        $this->loadCustomClasses();

        parent::__construct();

        $this->displayName = $this->l('Custom Product Configurator');
        $this->description = $this->l('Advanced product configurator with pricing matrix and delivery dates');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    }

    /**
     * NUOVO METODO: Carica tutte le classi personalizzate
     */
    private function loadCustomClasses()
    {
        $classFiles = [
            'CustomProductConfigClass.php',
            'CustomProductAttribute.php', 
            'CustomProductPricing.php',
            'DeliveryDateCalculator.php'
        ];

        foreach ($classFiles as $classFile) {
            $filePath = dirname(__FILE__) . '/classes/' . $classFile;
            if (file_exists($filePath)) {
                require_once $filePath;
            }
        }
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');
        
        return parent::install() &&
            $this->registerHook('displayProductAdditionalInfo') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayAdminProductsExtra') &&
            $this->registerHook('actionProductSave') &&
            $this->registerHook('actionCartSave') &&
            $this->registerHook('displayProductButtons') &&
            $this->registerHook('displayShoppingCartFooter') &&
            $this->registerHook('displayOrderConfirmation') &&
            Configuration::updateValue('CUSTOM_PRODUCT_DELIVERY_DAYS', '3,5,7,10,14') &&
            Configuration::updateValue('CUSTOM_PRODUCT_HOLIDAYS', '') &&
            Configuration::updateValue('CUSTOM_PRODUCT_CUTOFF_TIME', '16:00') &&
            Configuration::updateValue('CUSTOM_PRODUCT_WORKING_DAYS', '1,2,3,4,5') &&
            Configuration::updateValue('CUSTOM_PRODUCT_CACHE_ENABLED', true) &&
            Configuration::updateValue('CUSTOM_PRODUCT_DEBUG_MODE', false);
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');
        
        return parent::uninstall() &&
            Configuration::deleteByName('CUSTOM_PRODUCT_DELIVERY_DAYS') &&
            Configuration::deleteByName('CUSTOM_PRODUCT_HOLIDAYS') &&
            Configuration::deleteByName('CUSTOM_PRODUCT_CUTOFF_TIME') &&
            Configuration::deleteByName('CUSTOM_PRODUCT_WORKING_DAYS') &&
            Configuration::deleteByName('CUSTOM_PRODUCT_CACHE_ENABLED') &&
            Configuration::deleteByName('CUSTOM_PRODUCT_DEBUG_MODE');
    }

    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit'.$this->name)) {
            $delivery_days = strval(Tools::getValue('CUSTOM_PRODUCT_DELIVERY_DAYS'));
            $holidays = strval(Tools::getValue('CUSTOM_PRODUCT_HOLIDAYS'));
            $cutoff_time = strval(Tools::getValue('CUSTOM_PRODUCT_CUTOFF_TIME'));
            $working_days = strval(Tools::getValue('CUSTOM_PRODUCT_WORKING_DAYS'));
            $cache_enabled = (bool)Tools::getValue('CUSTOM_PRODUCT_CACHE_ENABLED');
            $debug_mode = (bool)Tools::getValue('CUSTOM_PRODUCT_DEBUG_MODE');

            if (!$delivery_days || empty($delivery_days)) {
                $output .= $this->displayError($this->l('Invalid delivery days configuration.'));
            } else {
                Configuration::updateValue('CUSTOM_PRODUCT_DELIVERY_DAYS', $delivery_days);
                Configuration::updateValue('CUSTOM_PRODUCT_HOLIDAYS', $holidays);
                Configuration::updateValue('CUSTOM_PRODUCT_CUTOFF_TIME', $cutoff_time);
                Configuration::updateValue('CUSTOM_PRODUCT_WORKING_DAYS', $working_days);
                Configuration::updateValue('CUSTOM_PRODUCT_CACHE_ENABLED', $cache_enabled);
                Configuration::updateValue('CUSTOM_PRODUCT_DEBUG_MODE', $debug_mode);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('General Settings'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Delivery Days'),
                    'name' => 'CUSTOM_PRODUCT_DELIVERY_DAYS',
                    'size' => 20,
                    'required' => true,
                    'desc' => $this->l('Comma separated delivery days (e.g., 3,5,7,10,14)')
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Holidays'),
                    'name' => 'CUSTOM_PRODUCT_HOLIDAYS',
                    'desc' => $this->l('One holiday per line in YYYY-MM-DD format')
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Order Cutoff Time'),
                    'name' => 'CUSTOM_PRODUCT_CUTOFF_TIME',
                    'size' => 10,
                    'desc' => $this->l('Orders after this time start next day (HH:MM)')
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Working Days'),
                    'name' => 'CUSTOM_PRODUCT_WORKING_DAYS',
                    'size' => 20,
                    'desc' => $this->l('Comma separated working days (1=Mon, 7=Sun)')
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Enable Cache'),
                    'name' => 'CUSTOM_PRODUCT_CACHE_ENABLED',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'cache_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ],
                        [
                            'id' => 'cache_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        ]
                    ],
                    'desc' => $this->l('Enable cache for better performance')
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Debug Mode'),
                    'name' => 'CUSTOM_PRODUCT_DEBUG_MODE',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'debug_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ],
                        [
                            'id' => 'debug_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        ]
                    ],
                    'desc' => $this->l('Enable debug mode for developers')
                ]
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit'.$this->name;

        $helper->fields_value['CUSTOM_PRODUCT_DELIVERY_DAYS'] = Configuration::get('CUSTOM_PRODUCT_DELIVERY_DAYS');
        $helper->fields_value['CUSTOM_PRODUCT_HOLIDAYS'] = Configuration::get('CUSTOM_PRODUCT_HOLIDAYS');
        $helper->fields_value['CUSTOM_PRODUCT_CUTOFF_TIME'] = Configuration::get('CUSTOM_PRODUCT_CUTOFF_TIME');
        $helper->fields_value['CUSTOM_PRODUCT_WORKING_DAYS'] = Configuration::get('CUSTOM_PRODUCT_WORKING_DAYS');
        $helper->fields_value['CUSTOM_PRODUCT_CACHE_ENABLED'] = Configuration::get('CUSTOM_PRODUCT_CACHE_ENABLED');
        $helper->fields_value['CUSTOM_PRODUCT_DEBUG_MODE'] = Configuration::get('CUSTOM_PRODUCT_DEBUG_MODE');

        return $helper->generateForm($fields_form);
    }

    public function hookDisplayHeader()
    {
        if (Tools::getValue('controller') == 'product') {
            $this->context->controller->addCSS($this->_path.'views/css/front.css');
            $this->context->controller->addJS($this->_path.'views/js/front.js');
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('controller') == 'AdminProducts') {
            $this->context->controller->addCSS($this->_path.'views/css/admin.css');
            $this->context->controller->addJS($this->_path.'views/js/admin.js');
        }
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        // AGGIUNTO: Assicurati che le classi siano caricate
        $this->loadCustomClasses();
        
        $product = $params['product'];
        
        // Check if this product has custom configuration enabled
        $config = new CustomProductConfigClass((int)$product['id_product']);
        if (!$config->active) {
            return '';
        }

        // Get product attributes and pricing
        $attributes = CustomProductAttribute::getByProduct((int)$product['id_product']);
        $pricing = CustomProductPricing::getByProduct((int)$product['id_product']);
        $deliveryDates = $this->getDeliveryDates();

        $this->context->smarty->assign([
            'product_config' => $config,
            'product_attributes' => $attributes,
            'product_pricing' => $pricing,
            'delivery_dates' => $deliveryDates,
            'module_dir' => $this->_path,
            'ajax_url' => $this->context->link->getModuleLink($this->name, 'ajax')
        ]);

        return $this->display(__FILE__, 'product_configurator.tpl');
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        // AGGIUNTO: Assicurati che le classi siano caricate
        $this->loadCustomClasses();
        
        // Debug logging se abilitato
        if (Configuration::get('CUSTOM_PRODUCT_DEBUG_MODE')) {
            error_log('=== CUSTOM PRODUCT CONFIG DEBUG ===');
            error_log('Hook: hookDisplayAdminProductsExtra called');
            error_log('Params: ' . print_r($params, true));
            error_log('CustomProductConfigClass exists: ' . (class_exists('CustomProductConfigClass') ? 'YES' : 'NO'));
        }
        
        $id_product = (int)$params['id_product'];
        
        if (!$id_product) {
            return '<div class="alert alert-warning">Prodotto non valido per configurazione personalizzata</div>';
        }
        
        try {
            // Inizializza configurazione vuota se non esiste
            $config = new CustomProductConfigClass($id_product);
            if (!$config->id) {
                $config->id_product = $id_product;
                $config->active = false;
                $config->minimum_order_qty = 50;
                $config->base_price = 100;
            }
            
            $attributes = CustomProductAttribute::getByProduct($id_product);
            $pricing = CustomProductPricing::getByProduct($id_product);
            $delivery_days = explode(',', Configuration::get('CUSTOM_PRODUCT_DELIVERY_DAYS'));

            $this->context->smarty->assign([
                'id_product' => $id_product,
                'config' => $config,
                'attributes' => $attributes ?: [],
                'pricing' => $pricing ?: [],
                'delivery_days' => $delivery_days,
                'ajax_url' => $this->context->link->getAdminLink('AdminModules', true)
            ]);

            // Prova prima il template nella directory hook
            $template = $this->display(__FILE__, 'views/templates/hook/admin_product_config.tpl');
            if (!$template) {
                // Fallback al template admin
                $template = $this->display(__FILE__, 'views/templates/admin/admin_product_config.tpl');
            }
            
            return $template;
            
        } catch (Exception $e) {
            if (Configuration::get('CUSTOM_PRODUCT_DEBUG_MODE')) {
                error_log('Error in hookDisplayAdminProductsExtra: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
            }
            
            // Template di emergenza
            return '<div class="panel">
                <div class="panel-heading">Custom Product Configurator</div>
                <div class="panel-body">
                    <div class="alert alert-info">
                        <strong>Configurazione Prodotto Personalizzato</strong><br>
                        ID Prodotto: ' . $id_product . '<br>
                        Errore: ' . $e->getMessage() . '<br>
                        <small>Controllare che tutte le classi e template siano presenti.</small>
                    </div>
                </div>
            </div>';
        }
    }

    public function hookActionProductSave($params)
    {
        // AGGIUNTO: Assicurati che le classi siano caricate
        $this->loadCustomClasses();
        
        $id_product = (int)$params['id_product'];
        
        if (Tools::isSubmit('custom_config_active')) {
            try {
                $config = new CustomProductConfigClass($id_product);
                $config->id_product = $id_product;
                $config->active = (bool)Tools::getValue('custom_config_active');
                $config->minimum_order_qty = (int)Tools::getValue('custom_minimum_order_qty', 50);
                $config->base_price = (float)Tools::getValue('custom_base_price', 100);
                
                if ($config->id) {
                    $config->update();
                } else {
                    $config->add();
                }

                // Save attributes
                $this->saveProductAttributes($id_product);
                
                // Save pricing matrix
                $this->saveProductPricing($id_product);
            } catch (Exception $e) {
                if (Configuration::get('CUSTOM_PRODUCT_DEBUG_MODE')) {
                    error_log('Error in hookActionProductSave: ' . $e->getMessage());
                }
            }
        }
    }

    private function saveProductAttributes($id_product)
    {
        // Delete existing attributes
        CustomProductAttribute::deleteByProduct($id_product);
        
        $attribute_types = ['formato', 'carta', 'finitura', 'colori'];
        
        foreach ($attribute_types as $type) {
            $attributes = Tools::getValue('custom_'.$type.'_attributes', []);
            
            foreach ($attributes as $key => $attribute_data) {
                if (!empty($attribute_data['name'])) {
                    $attribute = new CustomProductAttribute();
                    $attribute->id_product = $id_product;
                    $attribute->attribute_type = $type;
                    $attribute->attribute_name = $attribute_data['name'];
                    $attribute->price_multiplier = (float)($attribute_data['multiplier'] ?? 1.0);
                    $attribute->position = (int)$key;
                    $attribute->active = true;
                    $attribute->save();
                }
            }
        }
    }

    private function saveProductPricing($id_product)
    {
        // Delete existing pricing
        CustomProductPricing::deleteByProduct($id_product);
        
        $quantities = Tools::getValue('custom_quantities', []);
        $delivery_days = explode(',', Configuration::get('CUSTOM_PRODUCT_DELIVERY_DAYS'));
        
        foreach ($quantities as $quantity) {
            foreach ($delivery_days as $days) {
                $price_key = 'custom_price_'.$quantity.'_'.$days;
                $price = Tools::getValue($price_key);
                
                if ($price && $price > 0) {
                    $pricing = new CustomProductPricing();
                    $pricing->id_product = $id_product;
                    $pricing->quantity = (int)$quantity;
                    $pricing->delivery_days = (int)$days;
                    $pricing->price = (float)$price;
                    $pricing->discount_percentage = 0; // Will be calculated
                    $pricing->active = true;
                    $pricing->save();
                }
            }
        }
    }

    private function getDeliveryDates()
    {
        // Assicurati che la classe sia caricata
        $this->loadCustomClasses();
        
        $calculator = new DeliveryDateCalculator();
        $delivery_days = explode(',', Configuration::get('CUSTOM_PRODUCT_DELIVERY_DAYS'));
        
        return $calculator->getDeliveryColumns($delivery_days);
    }

    public function hookActionCartSave($params)
    {
        $cart = $params['cart'];
        $customization = Tools::getValue('custom_product_config');
        
        if ($customization) {
            // Save customization data for cart item
            $customization_data = json_encode($customization);
            
            // Update cart product with customization
            Db::getInstance()->update(
                'cart_product',
                ['customization_data' => pSQL($customization_data)],
                'id_cart = '.(int)$cart->id.' AND id_product = '.(int)Tools::getValue('id_product')
            );
        }
    }

    public function hookDisplayProductButtons($params)
    {
        // Hook aggiuntivo per compatibilità con alcuni temi
        return $this->hookDisplayProductAdditionalInfo($params);
    }

    public function hookDisplayShoppingCartFooter($params)
    {
        // Aggiungi informazioni personalizzazioni nel carrello se necessario
        return '';
    }

    public function hookDisplayOrderConfirmation($params)
    {
        // Mostra dettagli personalizzazioni nella conferma ordine
        return '';
    }
}
