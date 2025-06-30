<?php
/**
 * SQL Uninstallation file - sql/uninstall.php
 */

$sql = array();

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'custom_order_product`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'custom_cart_product`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'custom_product_pricing`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'custom_product_attribute`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'custom_product_config`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

return true;