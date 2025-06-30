<?php
/**
 * Custom Product Pricing Class - classes/CustomProductPricing.php
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomProductPricing extends ObjectModel
{
    /** @var int */
    public $id_pricing;

    /** @var int */
    public $id_product;

    /** @var int */
    public $quantity;

    /** @var int */
    public $delivery_days;

    /** @var float */
    public $price;

    /** @var float */
    public $discount_percentage;

    /** @var bool */
    public $active;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'custom_product_pricing',
        'primary' => 'id_pricing',
        'fields' => [
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'quantity' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'delivery_days' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'price' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true],
            'discount_percentage' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public function add($autodate = true, $null_values = false)
    {
        if ($autodate) {
            $this->date_add = date('Y-m-d H:i:s');
            $this->date_upd = date('Y-m-d H:i:s');
        }
        return parent::add($autodate, $null_values);
    }

    public function update($null_values = false)
    {
        $this->date_upd = date('Y-m-d H:i:s');
        return parent::update($null_values);
    }

    /**
     * Get pricing by product ID
     */
    public static function getByProduct($id_product)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'custom_product_pricing` 
                WHERE `id_product` = '.(int)$id_product.'
                AND `active` = 1 
                ORDER BY `quantity` ASC, `delivery_days` ASC';
        
        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get pricing matrix formatted for frontend
     */
    public static function getFormattedMatrix($id_product)
    {
        $pricing = self::getByProduct($id_product);
        $matrix = [];
        
        foreach ($pricing as $price) {
            $matrix[$price['quantity']][$price['delivery_days']] = [
                'price' => (float)$price['price'],
                'discount' => (float)$price['discount_percentage'],
                'unit_price' => (float)$price['price'] / (int)$price['quantity'],
                'active' => (bool)$price['active']
            ];
        }
        
        return $matrix;
    }

    /**
     * Get price for specific quantity and delivery days
     */
    public static function getPrice($id_product, $quantity, $delivery_days)
    {
        $sql = 'SELECT `price` FROM `'._DB_PREFIX_.'custom_product_pricing` 
                WHERE `id_product` = '.(int)$id_product.'
                AND `quantity` = '.(int)$quantity.'
                AND `delivery_days` = '.(int)$delivery_days.'
                AND `active` = 1';
        
        $result = Db::getInstance()->getValue($sql);
        return $result ? (float)$result : 0.0;
    }

    /**
     * Set price for specific quantity and delivery days
     */
    public static function setPrice($id_product, $quantity, $delivery_days, $price, $discount_percentage = 0)
    {
        // Check if pricing already exists
        $existing = self::getByQuantityAndDays($id_product, $quantity, $delivery_days);
        
        if ($existing) {
            // Update existing
            return Db::getInstance()->update(
                'custom_product_pricing',
                [
                    'price' => (float)$price,
                    'discount_percentage' => (float)$discount_percentage,
                    'date_upd' => date('Y-m-d H:i:s')
                ],
                '`id_product` = '.(int)$id_product.' AND `quantity` = '.(int)$quantity.' AND `delivery_days` = '.(int)$delivery_days
            );
        } else {
            // Create new
            $pricing = new self();
            $pricing->id_product = (int)$id_product;
            $pricing->quantity = (int)$quantity;
            $pricing->delivery_days = (int)$delivery_days;
            $pricing->price = (float)$price;
            $pricing->discount_percentage = (float)$discount_percentage;
            $pricing->active = true;
            return $pricing->save();
        }
    }

    /**
     * Get pricing by quantity and delivery days
     */
    public static function getByQuantityAndDays($id_product, $quantity, $delivery_days)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'custom_product_pricing` 
                WHERE `id_product` = '.(int)$id_product.'
                AND `quantity` = '.(int)$quantity.'
                AND `delivery_days` = '.(int)$delivery_days;
        
        return Db::getInstance()->getRow($sql);
    }

    /**
     * Delete pricing by product ID
     */
    public static function deleteByProduct($id_product)
    {
        return Db::getInstance()->delete(
            'custom_product_pricing',
            '`id_product` = '.(int)$id_product
        );
    }

    /**
     * Get all quantities for a product
     */
    public static function getQuantitiesByProduct($id_product)
    {
        $sql = 'SELECT DISTINCT `quantity` FROM `'._DB_PREFIX_.'custom_product_pricing` 
                WHERE `id_product` = '.(int)$id_product.'
                AND `active` = 1 
                ORDER BY `quantity` ASC';
        
        return array_column(Db::getInstance()->executeS($sql), 'quantity');
    }

    /**
     * Get all delivery days for a product
     */
    public static function getDeliveryDaysByProduct($id_product)
    {
        $sql = 'SELECT DISTINCT `delivery_days` FROM `'._DB_PREFIX_.'custom_product_pricing` 
                WHERE `id_product` = '.(int)$id_product.'
                AND `active` = 1 
                ORDER BY `delivery_days` ASC';
        
        return array_column(Db::getInstance()->executeS($sql), 'delivery_days');
    }

    /**
     * Calculate price with attributes multipliers
     */
    public static function calculateFinalPrice($id_product, $quantity, $delivery_days, $attributes = [])
    {
        $base_price = self::getPrice($id_product, $quantity, $delivery_days);
        
        if (!$base_price) {
            return 0.0;
        }
        
        $multiplier = 1.0;
        
        // Apply attribute multipliers
        foreach ($attributes as $type => $value) {
            $attr_multiplier = CustomProductAttribute::getPriceMultiplier($id_product, $value, $type);
            $multiplier *= $attr_multiplier;
        }
        
        return $base_price * $multiplier;
    }

    /**
     * Generate default pricing matrix
     */
    public static function generateDefaultMatrix($id_product, $base_price = 100.0)
    {
        $quantities = [250, 500, 750, 1000, 2500, 5000, 7500];
        $delivery_days = [3, 5, 7, 10, 14];
        $discounts = [0, 8, 17, 25, 35]; // Discounts for longer delivery times
        
        foreach ($quantities as $i => $quantity) {
            foreach ($delivery_days as $j => $days) {
                // Skip some combinations (e.g., 7500 pieces in 3 days)
                if ($quantity >= 7500 && $days <= 3) {
                    continue;
                }
                
                // Calculate price based on quantity (economies of scale)
                $unit_price = $base_price / pow($quantity / 250, 0.3);
                $price = $unit_price * $quantity;
                
                // Apply delivery time discount
                $discount = $discounts[$j] ?? 0;
                $final_price = $price * (1 - $discount / 100);
                
                self::setPrice($id_product, $quantity, $days, $final_price, $discount);
            }
        }
    }

    /**
     * Import pricing from CSV data
     */
    public static function importFromCSV($id_product, $csv_data)
    {
        $lines = explode("\n", $csv_data);
        $header = str_getcsv(array_shift($lines));
        
        // Expected format: quantity, delivery_days, price, discount_percentage
        foreach ($lines as $line) {
            if (trim($line)) {
                $data = str_getcsv($line);
                if (count($data) >= 3) {
                    $quantity = (int)$data[0];
                    $delivery_days = (int)$data[1];
                    $price = (float)$data[2];
                    $discount = isset($data[3]) ? (float)$data[3] : 0;
                    
                    self::setPrice($id_product, $quantity, $delivery_days, $price, $discount);
                }
            }
        }
    }

    /**
     * Export pricing to CSV format
     */
    public static function exportToCSV($id_product)
    {
        $pricing = self::getByProduct($id_product);
        $csv = "quantity,delivery_days,price,discount_percentage\n";
        
        foreach ($pricing as $price) {
            $csv .= $price['quantity'].','.$price['delivery_days'].','.$price['price'].','.$price['discount_percentage']."\n";
        }
        
        return $csv;
    }
}