<?php

namespace alxmsl\APNS\Exception;

/**
 * Exception for missing topic on delivery payload in enhanced mode
 * @author alxmsl
 * @date 7/18/14
 */ 
final class MissingTopicErrorException extends SendNotificationErrorException {}
 