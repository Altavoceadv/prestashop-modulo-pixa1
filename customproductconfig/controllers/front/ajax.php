<?php
/**
 * AJAX Controller - controllers/front/ajax.php
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomProductConfigAjaxModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
    }

    public function initContent()
    {
        parent::initContent();
        $this->processAjax();
    }

    public function processAjax()
    {
        $action = Tools::getValue('action');
        
        switch ($action) {
            case 'getDeliveryDates':
                $this->getDeliveryDates();
                break;
                
            case 'calculatePrice':
                $this->calculatePrice();
                break;
                
            case 'validateConfiguration':
                $this->validateConfiguration();
                break;
                
            case 'addToCart':
                $this->addToCart();
                break;
                
            default:
                $this->ajaxResponse(['error' => 'Invalid action'], 400);
        }
    }

    /**
     * Get delivery dates for given delivery days
     */
    private function getDeliveryDates()
    {
        try {
            $delivery_days = Tools::getValue('delivery_days');
            if (!$delivery_days || !is_array($delivery_days)) {
                $delivery_days = explode(',', Configuration::get('CUSTOM_PRODUCT_DELIVERY_DAYS'));
            }
            
            require_once dirname(__FILE__) . '/../../classes/DeliveryDateCalculator.php';
            $calculator = new DeliveryDateCalculator();
            
            $current_time = Tools::getValue('current_time');
            $base_date = $current_time ? new DateTime($current_time) : new DateTime();
            
            $dates = $calculator->getDeliveryColumns($delivery_days, $base_date);
            
            $this->ajaxResponse(['dates' => $dates]);
            
        } catch (Exception $e) {
            $this->ajaxResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Calculate price based on configuration
     */
    private function calculatePrice()
    {
        try {
            $id_product = (int)Tools::getValue('id_product');
            $quantity = (int)Tools::getValue('quantity');
            $delivery_days = (int)Tools::getValue('delivery_days');
            $attributes = Tools::getValue('attributes', []);
            $with_tax = (bool)Tools::getValue('with_tax', false);
            
            if (!$id_product || !$quantity || !$delivery_days) {
                $this->ajaxResponse(['error' => 'Missing required parameters'], 400);
                return;
            }
            
            // Load required classes
            require_once dirname(__FILE__) . '/../../classes/CustomProductConfigClass.php';
            require_once dirname(__FILE__) . '/../../classes/CustomProductPricing.php';
            require_once dirname(__FILE__) . '/../../classes/CustomProductAttribute.php';
            
            // Check if product is customizable
            if (!CustomProductConfigClass::isProductCustomizable($id_product)) {
                $this->ajaxResponse(['error' => 'Product is not customizable'], 400);
                return;
            }
            
            // Calculate price
            $price = CustomProductPricing::calculateFinalPrice($id_product, $quantity, $delivery_days, $attributes);
            
            if (!$price) {
                $this->ajaxResponse(['error' => 'Price not available for this configuration'], 404);
                return;
            }
            
            // Apply tax if requested
            if ($with_tax) {
                $tax_rate = $this->getTaxRate($id_product);
                $price *= (1 + $tax_rate / 100);
            }
            
            // Calculate unit price
            $unit_price = $price / $quantity;
            
            $this->ajaxResponse([
                'price' => round($price, 2),
                'unit_price' => round($unit_price, 3),
                'with_tax' => $with_tax,
                'currency' => $this->context->currency->sign
            ]);
            
        } catch (Exception $e) {
            $this->ajaxResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validate configuration before adding to cart
     */
    private function validateConfiguration()
    {
        try {
            $id_product = (int)Tools::getValue('id_product');
            $quantity = (int)Tools::getValue('quantity');
            $delivery_days = (int)Tools::getValue('delivery_days');
            $attributes = Tools::getValue('attributes', []);
            
            $errors = [];
            
            // Check if product exists and is customizable
            if (!$id_product || !CustomProductConfigClass::isProductCustomizable($id_product)) {
                $errors[] = 'Product is not customizable';
            }
            
            // Check minimum order quantity
            $config = new CustomProductConfigClass($id_product);
            if ($quantity < $config->minimum_order_qty) {
                $errors[] = 'Minimum order quantity is ' . $config->minimum_order_qty;
            }
            
            // Check if price exists for this configuration
            $price = CustomProductPricing::getPrice($id_product, $quantity, $delivery_days);
            if (!$price) {
                $errors[] = 'Price not available for this configuration';
            }
            
            // Validate attributes
            foreach ($attributes as $type => $value) {
                if (!CustomProductAttribute::getByNameAndType($id_product, $value, $type)) {
                    $errors[] = 'Invalid attribute: ' . $type . ' = ' . $value;
                }
            }
            
            if (empty($errors)) {
                $this->ajaxResponse(['valid' => true]);
            } else {
                $this->ajaxResponse(['valid' => false, 'errors' => $errors]);
            }
            
        } catch (Exception $e) {
            $this->ajaxResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Add customized product to cart
     */
    private function addToCart()
    {
        try {
            $id_product = (int)Tools::getValue('id_product');
            $quantity = (int)Tools::getValue('quantity');
            $delivery_days = (int)Tools::getValue('delivery_days');
            $attributes = Tools::getValue('attributes', []);
            
            // Validate configuration first
            $validation = $this->validateConfigurationData($id_product, $quantity, $delivery_days, $attributes);
            if (!$validation['valid']) {
                $this->ajaxResponse(['error' => implode(', ', $validation['errors'])], 400);
                return;
            }
            
            // Calculate final price
            $final_price = CustomProductPricing::calculateFinalPrice($id_product, $quantity, $delivery_days, $attributes);
            
            // Prepare customization data
            $customization_data = [
                'attributes' => $attributes,
                'quantity' => $quantity,
                'delivery_days' => $delivery_days,
                'final_price' => $final_price,
                'delivery_date' => $this->getDeliveryDate($delivery_days)
            ];
            
            // Add to cart
            $cart = $this->context->cart;
            if (!$cart->id) {
                $cart->add();
            }
            
            // Create cart product entry
            $result = $cart->updateQty(
                $quantity,
                $id_product,
                null, // id_product_attribute
                null, // id_customization
                'up',
                0, // id_address_delivery
                null, // shop
                true, // auto_add_cart_rule
                true  // skip_quantity_check
            );
            
            if ($result) {
                // Save customization data
                $this->saveCartCustomization($cart->id, $id_product, $customization_data);
                
                $this->ajaxResponse([
                    'success' => true,
                    'cart_id' => $cart->id,
                    'message' => 'Product added to cart successfully'
                ]);
            } else {
                $this->ajaxResponse(['error' => 'Failed to add product to cart'], 500);
            }
            
        } catch (Exception $e) {
            $this->ajaxResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get tax rate for product
     */
    private function getTaxRate($id_product)
    {
        $product = new Product($id_product);
        $tax = new Tax($product->id_tax_rules_group);
        return $tax->rate ?? 22; // Default 22% VAT for Italy
    }

    /**
     * Get delivery date
     */
    private function getDeliveryDate($delivery_days)
    {
        require_once dirname(__FILE__) . '/../../classes/DeliveryDateCalculator.php';
        $calculator = new DeliveryDateCalculator();
        return $calculator->calculateDeliveryDate($delivery_days)->format('Y-m-d');
    }

    /**
     * Validate configuration data
     */
    private function validateConfigurationData($id_product, $quantity, $delivery_days, $attributes)
    {
        $errors = [];
        
        // Check if product is customizable
        if (!CustomProductConfigClass::isProductCustomizable($id_product)) {
            $errors[] = 'Product is not customizable';
        }
        
        // Check minimum quantity
        $config = new CustomProductConfigClass($id_product);
        if ($quantity < $config->minimum_order_qty) {
            $errors[] = 'Minimum order quantity is ' . $config->minimum_order_qty;
        }
        
        // Check if price exists
        $price = CustomProductPricing::getPrice($id_product, $quantity, $delivery_days);
        if (!$price) {
            $errors[] = 'Price not available for this configuration';
        }
        
        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Save cart customization
     */
    private function saveCartCustomization($id_cart, $id_product, $customization_data)
    {
        // Remove existing customization for this cart/product
        Db::getInstance()->delete(
            'custom_cart_product',
            '`id_cart` = ' . (int)$id_cart . ' AND `id_product` = ' . (int)$id_product
        );
        
        // Insert new customization
        return Db::getInstance()->insert(
            'custom_cart_product',
            [
                'id_cart' => (int)$id_cart,
                'id_product' => (int)$id_product,
                'customization_data' => pSQL(json_encode($customization_data)),
                'final_price' => (float)$customization_data['final_price'],
                'date_add' => date('Y-m-d H:i:s')
            ]
        );
    }

    /**
     * Send AJAX response
     */
    private function ajaxResponse($data, $status_code = 200)
    {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Check if request is POST
     */
    private function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if user is logged in
     */
    private function isLoggedIn()
    {
        return $this->context->customer->isLogged();
    }

    /**
     * Get current customer
     */
    private function getCurrentCustomer()
    {
        return $this->context->customer;
    }
}