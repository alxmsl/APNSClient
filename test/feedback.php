<?php
/**
 * APNS feedback service example
 * @author alxmsl
 * @date 5/4/13
 */

// Include autoloader
include '../source/Autoloader.php';

// Create APNS notification client
$Client = new \APNS\Feedback\Client();

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