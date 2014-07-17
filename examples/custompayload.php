<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 *
 * Custom payload example
 * @author alxmsl
 * @date 5/1/13
 */

// Include autoloader
include '../source/Autoloader.php';

use alxmsl\APNS\Notification\AlertItem;
use alxmsl\APNS\Notification\BasePayload;

// Create custom payload class
final class CustomPayload extends BasePayload {
    /**
     * @var null|string custom field
     */
    private $acme = null;

    /**
     * @param string|null $acme custom parameter value if needed
     * @return CustomPayload self
     */
    public function setAcme($acme) {
        $this->acme = (string) $acme;
        return $this;
    }

    /**
     * @return null|string custom parameter if set
     */
    public function getAcme() {
        return $this->acme;
    }

    /**
     * JsonSerializable implementation
     * @return array payload json serializable instance
     */
    public function jsonSerialize() {
        $result = parent::jsonSerialize();

        if (!is_null($this->getAcme())) {
            $result['acme'] = (string) $this->getAcme();
        }
        return $result;
    }
}

// Create simple alert item
$SimpleItem = new AlertItem();
$SimpleItem->setBody('You got your emails.');

// Create payload instance
$CustomPayload = new CustomPayload();
$CustomPayload->setAcme('foo')
    ->setAlertItem($SimpleItem)
    ->setBadgeNumber(9)
    ->setSoundFile('bingbong.aiff');

// Look at payload
var_dump((string) $CustomPayload);