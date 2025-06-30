<?php
/**
 * Admin Controller - controllers/admin/AdminCustomProductController.php
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminCustomProductController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->table = 'custom_product_config';
        $this->className = 'CustomProductConfigClass';
        $this->identifier = 'id_config';
        
        parent::__construct();
        
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        
        $this->fields_list = [
            'id_config' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ],
            'id_product' => [
                'title' => $this->l('Product ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ],
            'product_name' => [
                'title' => $this->l('Product Name'),
                'filter_key' => 'pl!name'
            ],
            'active' => [
                'title' => $this->l('Active'),
                'align' => 'center',
                'active' => 'status',
                'type' => 'bool',
                'class' => 'fixed-width-sm'
            ],
            'minimum_order_qty' => [
                'title' => $this->l('Min. Qty'),
                'align' => 'center',
                'class' => 'fixed-width-sm'
            ],
            'base_price' => [
                'title' => $this->l('Base Price'),
                'align' => 'right',
                'type' => 'price',
                'currency' => true
            ]
        ];
        
        $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (a.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)$this->context->language->id.')';
        $this->_select = 'pl.name as product_name';
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAddcustom_product_config')) {
            $this->processSaveConfiguration();
        } elseif (Tools::isSubmit('ajax')) {
            $this->processAjax();
        } else {
            parent::postProcess();
        }
    }

    public function processAjax()
    {
        $action = Tools::getValue('action');
        
        switch ($action) {
            case 'saveConfiguration':
                $this->ajaxProcessSaveConfiguration();
                break;
                
            case 'loadConfiguration':
                $this->ajaxProcessLoadConfiguration();
                break;
                
            case 'generateMatrix':
                $this->ajaxProcessGenerateMatrix();
                break;
                
            case 'importCSV':
                $this->ajaxProcessImportCSV();
                break;
                
            case 'exportCSV':
                $this->ajaxProcessExportCSV();
                break;
                
            case 'copyConfiguration':
                $this->ajaxProcessCopyConfiguration();
                break;
                
            case 'calculatePrice':
                $this->ajaxProcessCalculatePrice();
                break;
                
            default:
                $this->ajaxDie('Invalid action');
        }
    }

    /**
     * Save product configuration via AJAX
     */
    public function ajaxProcessSaveConfiguration()
    {
        try {
            $id_product = (int)Tools::getValue('id_product');
            
            if (!$id_product) {
                $this->ajaxDie('Invalid product ID');
            }
            
            // Save main configuration
            $config = new CustomProductConfigClass($id_product);
            $config->id_product = $id_product;
            $config->active = (bool)Tools::getValue('active');
            $config->minimum_order_qty = (int)Tools::getValue('minimum_order_qty');
            $config->base_price = (float)Tools::getValue('base_price');
            
            if ($config->id) {
                $config->update();
            } else {
                $config->add();
            }
            
            // Save attributes
            $this->saveProductAttributes($id_product);
            
            // Save pricing matrix
            $this->saveProductPricing($id_product);
            
            $this->ajaxDie(json_encode(['success' => true, 'message' => 'Configuration saved successfully']));
            
        } catch (Exception $e) {
            $this->ajaxDie(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    /**
     * Load product configuration via AJAX
     */
    public function ajaxProcessLoadConfiguration()
    {
        try {
            $id_product = (int)Tools::getValue('id_product');
            
            if (!$id_product) {
                $this->ajaxDie('Invalid product ID');
            }
            
            $config = new CustomProductConfigClass($id_product);
            $attributes = CustomProductAttribute::getByProduct($id_product);
            $pricing = CustomProductPricing::getFormattedMatrix($id_product);
            
            $this->ajaxDie(json_encode([
                'success' => true,
                'config' => [
                    'active' => $config->active,
                    'minimum_order_qty' => $config->minimum_order_qty,
                    'base_price' => $config->base_price
                ],
                'attributes' => $attributes,
                'pricing' => $pricing
            ]));
            
        } catch (Exception $e) {
            $this->ajaxDie(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    /**
     * Generate default pricing matrix
     */
    public function ajaxProcessGenerateMatrix()
    {
        try {
            $id_product = (int)Tools::getValue('id_product');
            $base_price = (float)Tools::getValue('base_price', 100.0);
            
            if (!$id_product) {
                $this->ajaxDie('Invalid product ID');
            }
            
            CustomProductPricing::generateDefaultMatrix($id_product, $base_price);
            
            $this->ajaxDie(json_encode(['success' => true, 'message' => 'Matrix generated successfully']));
            
        } catch (Exception $e) {
            $this->ajaxDie(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    /**
     * Import pricing from CSV
     */
    public function ajaxProcessImportCSV()
    {
        try {
            $id_product = (int)Tools::getValue('id_product');
            
            if (!$id_product) {
                $this->ajaxDie('Invalid product ID');
            }
            
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                $this->ajaxDie('No file uploaded or upload error');
            }
            
            $csv_content = file_get_contents($_FILES['csv_file']['tmp_name']);
            CustomProductPricing::importFromCSV($id_product, $csv_content);
            
            $this->ajaxDie(json_encode(['success' => true, 'message' => 'CSV imported successfully']));
            
        } catch (Exception $e) {
            $this->ajaxDie(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    /**
     * Export pricing to CSV
     */
    public function ajaxProcessExportCSV()
    {
        try {
            $id_product = (int)Tools::getValue('id_product');
            
            if (!$id_product) {
                $this->ajaxDie('Invalid product ID');
            }
            
            $csv_content = CustomProductPricing::exportToCSV($id_product);
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="product_'.$id_product.'_pricing.csv"');
            echo $csv_content;
            exit;
            
        } catch (Exception $e) {
            $this->ajaxDie(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    /**
     * Copy configuration from another product
     */
    public function ajaxProcessCopyConfiguration()
    {
        try {
            $source_id = (int)Tools::getValue('source_product');
            $target_id = (int)Tools::getValue('target_product');
            
            if (!$source_id || !$target_id) {
                $this->ajaxDie('Invalid product IDs');
            }
            
            // Copy main configuration
            $source_config = new CustomProductConfigClass($source_id);
            $target_config = new CustomProductConfigClass($target_id);
            
            $target_config->id_product = $target_id;
            $target_config->active = $source_config->active;
            $target_config->minimum_order_qty = $source_config->minimum_order_qty;
            $target_config->base_price = $source_config->base_price;
            
            if ($target_config->id) {
                $target_config->update();
            } else {
                $target_config->add();
            }
            
            // Copy attributes
            $this->copyProductAttributes($source_id, $target_id);
            
            // Copy pricing
            $this->copyProductPricing($source_id, $target_id);
            
            $this->ajaxDie(json_encode(['success' => true, 'message' => 'Configuration copied successfully']));
            
        } catch (Exception $e) {
            $this->ajaxDie(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    /**
     * Calculate price for testing
     */
    public function ajaxProcessCalculatePrice()
    {
        try {
            $id_product = (int)Tools::getValue('id_product');
            $quantity = (int)Tools::getValue('quantity');
            $delivery_days = (int)Tools::getValue('delivery_days');
            $attributes = Tools::getValue('attributes', []);
            
            $price = CustomProductPricing::calculateFinalPrice($id_product, $quantity, $delivery_days, $attributes);
            
            $this->ajaxDie(json_encode([
                'success' => true,
                'price' => round($price, 2),
                'unit_price' => round($price / $quantity, 3)
            ]));
            
        } catch (Exception $e) {
            $this->ajaxDie(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    /**
     * Save product attributes
     */
    private function saveProductAttributes($id_product)
    {
        // Delete existing attributes
        CustomProductAttribute::deleteByProduct($id_product);
        
        $attribute_types = ['formato', 'carta', 'finitura', 'colori'];
        
        foreach ($attribute_types as $type) {
            $attributes = Tools::getValue($type.'_attributes', []);
            
            foreach ($attributes as $key => $attribute_data) {
                if (!empty($attribute_data['name'])) {
                    $attribute = new CustomProductAttribute();
                    $attribute->id_product = $id_product;
                    $attribute->attribute_type = $type;
                    $attribute->attribute_name = $attribute_data['name'];
                    $attribute->price_multiplier = (float)$attribute_data['multiplier'];
                    $attribute->position = (int)$key;
                    $attribute->active = true;
                    $attribute->save();
                }
            }
        }
    }

    /**
     * Save product pricing matrix
     */
    private function saveProductPricing($id_product)
    {
        $pricing_data = Tools::getValue('pricing', []);
        
        // Delete existing pricing
        CustomProductPricing::deleteByProduct($id_product);
        
        foreach ($pricing_data as $quantity => $delivery_options) {
            foreach ($delivery_options as $days => $price_data) {
                if (isset($price_data['price']) && $price_data['price'] > 0) {
                    $pricing = new CustomProductPricing();
                    $pricing->id_product = $id_product;
                    $pricing->quantity = (int)$quantity;
                    $pricing->delivery_days = (int)$days;
                    $pricing->price = (float)$price_data['price'];
                    $pricing->discount_percentage = (float)($price_data['discount'] ?? 0);
                    $pricing->active = true;
                    $pricing->save();
                }
            }
        }
    }

    /**
     * Copy attributes from source to target product
     */
    private function copyProductAttributes($source_id, $target_id)
    {
        // Delete existing attributes
        CustomProductAttribute::deleteByProduct($target_id);
        
        // Get source attributes
        $attributes = CustomProductAttribute::getByProduct($source_id);
        
        foreach ($attributes as $type => $type_attributes) {
            foreach ($type_attributes as $attr) {
                $new_attribute = new CustomProductAttribute();
                $new_attribute->id_product = $target_id;
                $new_attribute->attribute_type = $attr['attribute_type'];
                $new_attribute->attribute_name = $attr['attribute_name'];
                $new_attribute->price_multiplier = $attr['price_multiplier'];
                $new_attribute->position = $attr['position'];
                $new_attribute->active = $attr['active'];
                $new_attribute->save();
            }
        }
    }

    /**
     * Copy pricing from source to target product
     */
    private function copyProductPricing($source_id, $target_id)
    {
        // Delete existing pricing
        CustomProductPricing::deleteByProduct($target_id);
        
        // Get source pricing
        $pricing = CustomProductPricing::getByProduct($source_id);
        
        foreach ($pricing as $price) {
            $new_pricing = new CustomProductPricing();
            $new_pricing->id_product = $target_id;
            $new_pricing->quantity = $price['quantity'];
            $new_pricing->delivery_days = $price['delivery_days'];
            $new_pricing->price = $price['price'];
            $new_pricing->discount_percentage = $price['discount_percentage'];
            $new_pricing->active = $price['active'];
            $new_pricing->save();
        }
    }

    /**
     * Render form for editing
     */
    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Custom Product Configuration'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'hidden',
                    'name' => 'id_product'
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Enable Custom Configuration'),
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        ]
                    ]
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Minimum Order Quantity'),
                    'name' => 'minimum_order_qty',
                    'class' => 'fixed-width-sm',
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Base Reference Price'),
                    'name' => 'base_price',
                    'class' => 'fixed-width-sm',
                    'required' => true,
                    'suffix' => $this->context->currency->sign
                ]
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        return parent::renderForm();
    }

    /**
     * Get toolbar title
     */
    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_custom_product'] = [
                'href' => self::$currentIndex.'&addcustom_product_config&token='.$this->token,
                'desc' => $this->l('Add new custom product configuration'),
                'icon' => 'process-icon-new'
            ];
        }

        parent::initPageHeaderToolbar();
    }
}