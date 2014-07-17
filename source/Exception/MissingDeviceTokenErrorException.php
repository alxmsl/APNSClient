<?php

namespace alxmsl\APNS\Exception;

/**
 * Exception for missing device token on delivery payload in enhanced mode
 * @author alxmsl
 * @date 7/18/14
 */ 
final class MissingDeviceTokenErrorException extends SendNotificationErrorException {}
 