<?php

namespace APNS;

// append autoloader
spl_autoload_register(array('\APNS\Autoloader', 'Autoloader'));

/**
 * APNS classes autoloader
 * @author alxmsl
 * @date 1/13/13
 */
final class Autoloader {
    /**
     * @var array array of available classes
     */
    private static $classes = array(
        'APNS\\Autoloader' => 'Autoloader.php',
        'APNS\\AbstractClient' => 'AbstractClient.php',

        'APNS\\Feedback\\Client' => 'Feedback/Client.php',

        'APNS\\Notification\\Client' => 'Notification/Client.php',
        'APNS\\Notification\\AlertItem' => 'Notification/AlertItem.php',
        'APNS\\Notification\\BasePayload' => 'Notification/BasePayload.php',
    );

    /**
     * Component autoloader
     * @param string $className claass name
     */
    public static function Autoloader($className) {
        if (array_key_exists($className, self::$classes)) {
            $fileName = realpath(dirname(__FILE__)) . '/' . self::$classes[$className];
            if (file_exists($fileName)) {
                include $fileName;
            }
        }
    }
}
