<?php
/**
 * SQL Installation file - sql/install.php
 */

$sql = array();

// Table for product custom configuration
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'custom_product_config` (
    `id_config` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_product` int(10) unsigned NOT NULL,
    `active` tinyint(1) unsigned NOT NULL DEFAULT 0,
    `minimum_order_qty` int(10) unsigned NOT NULL DEFAULT 1,
    `base_price` decimal(20,6) NOT NULL DEFAULT 0.000000,
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY (`id_config`),
    KEY `id_product` (`id_product`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

// Table for product custom attributes (formato, carta, finitura, colori)
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'custom_product_attribute` (
    `id_attribute` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_product` int(10) unsigned NOT NULL,
    `attribute_type` varchar(50) NOT NULL,
    `attribute_name` varchar(255) NOT NULL,
    `price_multiplier` decimal(10,4) NOT NULL DEFAULT 1.0000,
    `position` int(10) unsigned NOT NULL DEFAULT 0,
    `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY (`id_attribute`),
    KEY `id_product` (`id_product`),
    KEY `attribute_type` (`attribute_type`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

// Table for pricing matrix (quantity × delivery_days)
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'custom_product_pricing` (
    `id_pricing` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_product` int(10) unsigned NOT NULL,
    `quantity` int(10) unsigned NOT NULL,
    `delivery_days` int(10) unsigned NOT NULL,
    `price` decimal(20,6) NOT NULL DEFAULT 0.000000,
    `discount_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
    `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY (`id_pricing`),
    UNIQUE KEY `product_quantity_days` (`id_product`, `quantity`, `delivery_days`),
    KEY `id_product` (`id_product`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

// Table for cart customizations
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'custom_cart_product` (
    `id_cart_custom` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_cart` int(10) unsigned NOT NULL,
    `id_product` int(10) unsigned NOT NULL,
    `customization_data` text,
    `final_price` decimal(20,6) NOT NULL DEFAULT 0.000000,
    `date_add` datetime NOT NULL,
    PRIMARY KEY (`id_cart_custom`),
    KEY `id_cart` (`id_cart`),
    KEY `id_product` (`id_product`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

// Table for order customizations
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'custom_order_product` (
    `id_order_custom` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_order` int(10) unsigned NOT NULL,
    `id_product` int(10) unsigned NOT NULL,
    `customization_data` text,
    `final_price` decimal(20,6) NOT NULL DEFAULT 0.000000,
    `delivery_date` date,
    `date_add` datetime NOT NULL,
    PRIMARY KEY (`id_order_custom`),
    KEY `id_order` (`id_order`),
    KEY `id_product` (`id_product`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

return true;