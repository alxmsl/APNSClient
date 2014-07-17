<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 *
 * APNS notification send example
 * @author alxmsl
 * @date 5/2/13
 */

// Include autoloader
include '../source/Autoloader.php';

use alxmsl\APNS\Notification\AlertItem;
use alxmsl\APNS\Notification\BasePayload;
use alxmsl\APNS\Notification\Client;

// Create APNS notification client
$Client = new Client();

// Set secure certificate filename
$Client->setCertificateFile('certificate.production.pem');

// Create needed alert item
$Item = new AlertItem();
$Item->setBody('test1');

// Create payload
$Payload = new BasePayload();
$Payload->setAlertItem($Item)
    ->setBadgeNumber(1)
    ->setIdentifier(time());

// Send notification to the device
$result = $Client->send('c0RreCtT0kEN', $Payload);
var_dump($result);