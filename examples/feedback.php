<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 *
 * APNS feedback service example
 * @author alxmsl
 * @date 5/4/13
 */

// Include autoloader
include '../source/Autoloader.php';

use alxmsl\APNS\Feedback\Client;

// Create APNS notification client
$Client = new Client();

// Set secure certificate filename
$Client->setCertificateFile('certificate.production.pem')
    ->setProtocolSchemeSSL();

$Client->process(function ($time, $token) {
    echo date('Y-m-d H:i:s', $time) . ' - ' . $token . "\n";
    return true;
}, false);

var_dump(
    $Client->getReadCount(),
    $Client->getProcessedCount(),
    $Client->getUnprocessedCount(),
    $Client->getErrorCount()
);