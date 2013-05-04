<?php

namespace APNS\Feedback;

/**
 * APNS feedback service client
 * @author alxmsl
 * @date 5/4/13
 */ 
final class Client {
    /**
     * APNS feedback service endpoints
     */
    const   ENDPOINT_PRODUCTION = 'feedback.push.apple.com:2196',           // For production
            ENDPOINT_SANDBOX    = 'feedback.sandbox.push.apple.com:2196';   // For developer
}
