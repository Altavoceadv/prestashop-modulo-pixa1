<?php
/**
 * Autoloader per classi custom del modulo
 * classes/autoload.php
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Registra l'autoloader per il modulo
spl_autoload_register(function ($className) {
    // Solo per le classi del nostro modulo
    $moduleClasses = [
        'CustomProductConfigClass',
        'CustomProductAttribute',
        'CustomProductPricing',
        'DeliveryDateCalculator'
    ];
    
    if (in_array($className, $moduleClasses)) {
        $classFile = dirname(__FILE__) . '/' . $className . '.php';
        if (file_exists($classFile)) {
            require_once $classFile;
            return true;
        }
    }
    
    return false;
});

// Carica tutte le classi immediatamente per compatibilità
$classFiles = [
    'CustomProductConfigClass.php',
    'CustomProductAttribute.php',
    'CustomProductPricing.php',
    'DeliveryDateCalculator.php'
];

foreach ($classFiles as $file) {
    $filePath = dirname(__FILE__) . '/' . $file;
    if (file_exists($filePath)) {
        require_once $filePath;
    }
}

// Log per debug se necessario
if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_) {
    error_log('Custom Product Config Autoloader: Classes loaded');
}
