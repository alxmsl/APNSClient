<?php
/**
 * APNS notification send example
 * @author alxmsl
 * @date 5/2/13
 */

// Include autoloader
include '../source/Autoloader.php';

// Create APNS notification client
$Client = new \APNS\Notification\Client();

// Set secure certificate filename
$Client->setCertificateFile('certificate.prodaction.pem');

// Create needed alert item
$Item = new \APNS\Notification\AlertItem();
$Item->setBody('test1');

// Create payload
$Payload = new \APNS\Notification\BasePayload();
$Payload->setAlertItem($Item)
    ->setBadgeNumber(1)
    ->setIdentifier(time());

// Send notification to the device
$result = $Client->send('c0RreCtT0kEN', $Payload);
var_dump($result);