<?php
/**
 * Custom Product Attribute Class - classes/CustomProductAttribute.php
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomProductAttribute extends ObjectModel
{
    /** @var int */
    public $id_attribute;

    /** @var int */
    public $id_product;

    /** @var string */
    public $attribute_type;

    /** @var string */
    public $attribute_name;

    /** @var float */
    public $price_multiplier;

    /** @var int */
    public $position;

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
        'table' => 'custom_product_attribute',
        'primary' => 'id_attribute',
        'fields' => [
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'attribute_type' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 50],
            'attribute_name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'price_multiplier' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    // Attribute types constants
    const TYPE_FORMATO = 'formato';
    const TYPE_CARTA = 'carta';
    const TYPE_FINITURA = 'finitura';
    const TYPE_COLORI = 'colori';

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
     * Get attributes by product ID
     */
    public static function getByProduct($id_product, $attribute_type = null)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'custom_product_attribute` 
                WHERE `id_product` = '.(int)$id_product;
        
        if ($attribute_type) {
            $sql .= ' AND `attribute_type` = "'.pSQL($attribute_type).'"';
        }
        
        $sql .= ' AND `active` = 1 ORDER BY `attribute_type`, `position`';
        
        $results = Db::getInstance()->executeS($sql);
        
        // Group by attribute type
        $grouped = [];
        foreach ($results as $result) {
            $grouped[$result['attribute_type']][] = $result;
        }
        
        return $grouped;
    }

    /**
     * Get attributes by type for a product
     */
    public static function getByProductAndType($id_product, $attribute_type)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'custom_product_attribute` 
                WHERE `id_product` = '.(int)$id_product.'
                AND `attribute_type` = "'.pSQL($attribute_type).'"
                AND `active` = 1 
                ORDER BY `position`';
        
        return Db::getInstance()->executeS($sql);
    }

    /**
     * Delete attributes by product ID
     */
    public static function deleteByProduct($id_product)
    {
        return Db::getInstance()->delete(
            'custom_product_attribute',
            '`id_product` = '.(int)$id_product
        );
    }

    /**
     * Delete attributes by product ID and type
     */
    public static function deleteByProductAndType($id_product, $attribute_type)
    {
        return Db::getInstance()->delete(
            'custom_product_attribute',
            '`id_product` = '.(int)$id_product.' AND `attribute_type` = "'.pSQL($attribute_type).'"'
        );
    }

    /**
     * Get attribute by name and type for a product
     */
    public static function getByNameAndType($id_product, $attribute_name, $attribute_type)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'custom_product_attribute` 
                WHERE `id_product` = '.(int)$id_product.'
                AND `attribute_name` = "'.pSQL($attribute_name).'"
                AND `attribute_type` = "'.pSQL($attribute_type).'"
                AND `active` = 1';
        
        return Db::getInstance()->getRow($sql);
    }

    /**
     * Get price multiplier for specific attribute
     */
    public static function getPriceMultiplier($id_product, $attribute_name, $attribute_type)
    {
        $sql = 'SELECT `price_multiplier` FROM `'._DB_PREFIX_.'custom_product_attribute` 
                WHERE `id_product` = '.(int)$id_product.'
                AND `attribute_name` = "'.pSQL($attribute_name).'"
                AND `attribute_type` = "'.pSQL($attribute_type).'"
                AND `active` = 1';
        
        $result = Db::getInstance()->getValue($sql);
        return $result ? (float)$result : 1.0;
    }

    /**
     * Get default attributes for new product
     */
    public static function getDefaultAttributes()
    {
        return [
            self::TYPE_FORMATO => [
                ['name' => 'Piccolo (15x15cm)', 'multiplier' => 1.0],
                ['name' => 'Medio (20x20cm)', 'multiplier' => 1.2],
                ['name' => 'Grande (25x25cm)', 'multiplier' => 1.5],
            ],
            self::TYPE_CARTA => [
                ['name' => 'Standard 300gr', 'multiplier' => 1.0],
                ['name' => 'Premium 400gr', 'multiplier' => 1.3],
                ['name' => 'Luxury 500gr', 'multiplier' => 1.6],
            ],
            self::TYPE_FINITURA => [
                ['name' => 'Opaca', 'multiplier' => 1.0],
                ['name' => 'Lucida', 'multiplier' => 1.1],
                ['name' => 'Soft Touch', 'multiplier' => 1.25],
            ],
            self::TYPE_COLORI => [
                ['name' => '1 Colore', 'multiplier' => 1.0],
                ['name' => '2 Colori', 'multiplier' => 1.4],
                ['name' => 'Quadricromia', 'multiplier' => 2.0],
            ],
        ];
    }

    /**
     * Create default attributes for a product
     */
    public static function createDefaultAttributes($id_product)
    {
        $defaults = self::getDefaultAttributes();
        
        foreach ($defaults as $type => $attributes) {
            foreach ($attributes as $position => $attribute) {
                $attr = new self();
                $attr->id_product = (int)$id_product;
                $attr->attribute_type = $type;
                $attr->attribute_name = $attribute['name'];
                $attr->price_multiplier = $attribute['multiplier'];
                $attr->position = $position;
                $attr->active = true;
                $attr->save();
            }
        }
    }

    /**
     * Get attribute type labels
     */
    public static function getTypeLabels()
    {
        return [
            self::TYPE_FORMATO => 'Formato Scatola',
            self::TYPE_CARTA => 'Tipo di Carta',
            self::TYPE_FINITURA => 'Finitura',
            self::TYPE_COLORI => 'Colori di Stampa',
        ];
    }

    /**
     * Validate attribute type
     */
    public static function isValidType($type)
    {
        $valid_types = [
            self::TYPE_FORMATO,
            self::TYPE_CARTA,
            self::TYPE_FINITURA,
            self::TYPE_COLORI
        ];
        
        return in_array($type, $valid_types);
    }

    /**
     * Update positions for attributes of same type
     */
    public static function updatePositions($id_product, $attribute_type, $positions)
    {
        foreach ($positions as $id_attribute => $position) {
            Db::getInstance()->update(
                'custom_product_attribute',
                ['position' => (int)$position],
                '`id_attribute` = '.(int)$id_attribute.' AND `id_product` = '.(int)$id_product.' AND `attribute_type` = "'.pSQL($attribute_type).'"'
            );
        }
    }
}