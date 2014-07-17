<?php
/*
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 */

namespace alxmsl\APNS\Exception;

/**
 * Exception for invalid token on delivery payload in enhanced mode
 * @author alxmsl
 * @date 7/18/14
 */ 
final class InvalidTokenErrorException extends SendNotificationErrorException {}
 