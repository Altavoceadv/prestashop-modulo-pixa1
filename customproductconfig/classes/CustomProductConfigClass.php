<?php
/**
 * Custom Product Config Class - classes/CustomProductConfigClass.php
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomProductConfigClass extends ObjectModel
{
    /** @var int */
    public $id_config;

    /** @var int */
    public $id_product;

    /** @var bool */
    public $active;

    /** @var int */
    public $minimum_order_qty;

    /** @var float */
    public $base_price;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'custom_product_config',
        'primary' => 'id_config',
        'fields' => [
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'minimum_order_qty' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'base_price' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
        
        // If constructing with product ID, try to load existing config
        if ($id && !$this->id && is_numeric($id)) {
            $this->loadByProductId($id);
        }
    }

    public function loadByProductId($id_product)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'custom_product_config` 
                WHERE `id_product` = '.(int)$id_product;
        
        $result = Db::getInstance()->getRow($sql);
        
        if ($result) {
            $this->id = $this->id_config = (int)$result['id_config'];
            $this->id_product = (int)$result['id_product'];
            $this->active = (bool)$result['active'];
            $this->minimum_order_qty = (int)$result['minimum_order_qty'];
            $this->base_price = (float)$result['base_price'];
            $this->date_add = $result['date_add'];
            $this->date_upd = $result['date_upd'];
            return true;
        }
        
        // Set defaults for new config
        $this->id_product = (int)$id_product;
        $this->active = false;
        $this->minimum_order_qty = 50;
        $this->base_price = 0.0;
        
        return false;
    }

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
     * Get configuration by product ID
     */
    public static function getByProductId($id_product)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'custom_product_config` 
                WHERE `id_product` = '.(int)$id_product;
        
        return Db::getInstance()->getRow($sql);
    }

    /**
     * Check if product has custom configuration enabled
     */
    public static function isProductCustomizable($id_product)
    {
        $sql = 'SELECT `active` FROM `'._DB_PREFIX_.'custom_product_config` 
                WHERE `id_product` = '.(int)$id_product.' AND `active` = 1';
        
        return (bool)Db::getInstance()->getValue($sql);
    }

    /**
     * Get all customizable products
     */
    public static function getCustomizableProducts()
    {
        $sql = 'SELECT cpc.*, pl.`name` as product_name
                FROM `'._DB_PREFIX_.'custom_product_config` cpc
                LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (cpc.id_product = pl.id_product)
                WHERE cpc.`active` = 1 
                AND pl.`id_lang` = '.(int)Context::getContext()->language->id.'
                ORDER BY pl.`name`';
        
        return Db::getInstance()->executeS($sql);
    }

    /**
     * Delete configuration by product ID
     */
    public static function deleteByProductId($id_product)
    {
        return Db::getInstance()->delete(
            'custom_product_config',
            '`id_product` = '.(int)$id_product
        );
    }

    /**
     * Get product quantities for pricing matrix
     */
    public function getQuantities()
    {
        $pricing = CustomProductPricing::getByProduct($this->id_product);
        $quantities = [];
        
        foreach ($pricing as $price) {
            if (!in_array($price['quantity'], $quantities)) {
                $quantities[] = (int)$price['quantity'];
            }
        }
        
        sort($quantities);
        return $quantities;
    }

    /**
     * Get minimum price for this product
     */
    public function getMinimumPrice()
    {
        $sql = 'SELECT MIN(price) FROM `'._DB_PREFIX_.'custom_product_pricing` 
                WHERE `id_product` = '.(int)$this->id_product.' AND `active` = 1';
        
        return (float)Db::getInstance()->getValue($sql);
    }

    /**
     * Get maximum price for this product
     */
    public function getMaximumPrice()
    {
        $sql = 'SELECT MAX(price) FROM `'._DB_PREFIX_.'custom_product_pricing` 
                WHERE `id_product` = '.(int)$this->id_product.' AND `active` = 1';
        
        return (float)Db::getInstance()->getValue($sql);
    }
}