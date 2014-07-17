<?php

namespace alxmsl\APNS;

// append autoloader
spl_autoload_register(array('\alxmsl\APNS\Autoloader', 'autoload'));

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
        'alxmsl\\APNS\\Autoloader'                                       => 'Autoloader.php',
        'alxmsl\\APNS\\AbstractClient'                                   => 'AbstractClient.php',
        'alxmsl\\APNS\\Exception\\ClientException'                       => 'Exception/ClientException.php',
        'alxmsl\\APNS\\Exception\\ClientConnectException'                => 'Exception/ClientConnectException.php',
        'alxmsl\\APNS\\Exception\\SendNotificationErrorException'        => 'Exception/SendNotificationErrorException.php',
        'alxmsl\\APNS\\Exception\\ProcessingErrorException'              => 'Exception/ProcessingErrorException.php',
        'alxmsl\\APNS\\Exception\\MissingDeviceTokenErrorException'      => 'Exception/MissingDeviceTokenErrorException.php',
        'alxmsl\\APNS\\Exception\\MissingTopicErrorException'            => 'Exception/MissingTopicErrorException.php',
        'alxmsl\\APNS\\Exception\\MissingPayloadErrorException'          => 'Exception/MissingPayloadErrorException.php',
        'alxmsl\\APNS\\Exception\\InvalidTopicSizeErrorException'        => 'Exception/InvalidTopicSizeErrorException.php',
        'alxmsl\\APNS\\Exception\\InvalidPayloadSizeErrorException'      => 'Exception/InvalidPayloadSizeErrorException.php',
        'alxmsl\\APNS\\Exception\\InvalidTokenErrorException'            => 'Exception/InvalidTokenErrorException.php',
        'alxmsl\\APNS\\Exception\\ShutdownServiceErrorException'         => 'Exception/ShutdownServiceErrorException.php',
        'alxmsl\\APNS\\Exception\\UnknownErrorException'                 => 'Exception/UnknownErrorException.php',
        'alxmsl\\APNS\\Exception\\UnsupportedErrorException'             => 'Exception/UnsupportedErrorException.php',
        'alxmsl\\APNS\\Exception\\UnsupportedCommandException'           => 'Exception/UnsupportedCommandException.php',

        'alxmsl\\APNS\\Feedback\\Client'                                 => 'Feedback/Client.php',
        'alxmsl\\APNS\\Feedback\\Exception\\FeedbackProcessorException'  => 'Feedback/Exception/FeedbackProcessorException.php',

        'alxmsl\\APNS\\Notification\\AlertItem'                          => 'Notification/AlertItem.php',
        'alxmsl\\APNS\\Notification\\Client'                             => 'Notification/Client.php',
        'alxmsl\\APNS\\Notification\\BasePayload'                        => 'Notification/BasePayload.php',
        'alxmsl\\APNS\\Notification\\Exception\\AlertItemException'      => 'Notification/Exception/AlertItemException.php',
        'alxmsl\\APNS\\Notification\\Exception\\CannotCropBodyException' => 'Notification/Exception/CannotCropBodyException.php',
    );

    /**
     * Component autoloader
     * @param string $className claass name
     */
    public static function autoload($className) {
        if (array_key_exists($className, self::$classes)) {
            $fileName = realpath(dirname(__FILE__)) . '/' . self::$classes[$className];
            if (file_exists($fileName)) {
                include $fileName;
            }
        }
    }
}
