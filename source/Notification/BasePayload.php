<?php
/*
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 */

namespace alxmsl\APNS\Notification;
use alxmsl\APNS\Notification\Exception\CannotCropBodyException;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Notification payload class
 * @author alxmsl
 * @date 5/1/13
 */ 
class BasePayload implements JsonSerializable {
    /**
     * Payload constants
     */
    const LENGTH_IOS7_MAX          = 256,     // Maximum payload size for iOS prior v.8 and OSX
          LENGTH_IOS8_MAX          = 2048,    // Maximum payload size for iOS 8
          DEFAULT_DELIVERY_TIMEOUT = 86400;   // Default payload delivery timeout

    const PRIORITY_IMMEDIATE = 10, // Immediate priority code
          PRIORITY_POWERLESS = 5;  // Powerless priority code

    /**
     * @var int payload identifier
     */
    private $identifier = 0;

    /**
     * @var int delivery timeout
     */
    private $deliveryTimeout = self::DEFAULT_DELIVERY_TIMEOUT;

    /**
     * @var AlertItem|null payload alert item
     */
    private $AlertItem = null;

    /**
     * @var null|int badge number
     */
    private $badgeNumber = null;

    /**
     * @var null|string sound application file
     */
    private $soundFile = null;

    /**
     * @var bool true if payload sends for iOS 8 devices (with extended payload size)
     */
    private $isExtended = false;

    /**
     * @var bool new content availability flag
     */
    private $isContentAvailable = false;

    /**
     * @var int payload delivery priority
     */
    private $priority = self::PRIORITY_IMMEDIATE;

    /**
     * Payload identifier setter
     * @param int $identifier payload identifier
     * @return BasePayload self
     */
    public function setIdentifier($identifier) {
        $this->identifier = (int) $identifier;
        return $this;
    }

    /**
     * Payload identifier getter
     * @return int payload identifier
     */
    public function getIdentifier() {
        return $this->identifier;
    }

    /**
     * Delivery timeout setter
     * @param int $deliveryTimeout delivery timeout
     * @return BasePayload self
     */
    public function setDeliveryTimeout($deliveryTimeout) {
        $this->deliveryTimeout = (int) $deliveryTimeout;
        return $this;
    }

    /**
     * Delivery timeout getter
     * @return int delivery timeout
     */
    public function getDeliveryTimeout() {
        return $this->deliveryTimeout;
    }

    /**
     * @return int expiration time for this notification
     */
    public function getExpirationTime() {
        return time() + $this->getDeliveryTimeout();
    }

    /**
     * Alert item setter
     * @param AlertItem $Alert alert item if needed
     * @return BasePayload self
     */
    public function setAlertItem(AlertItem $Alert = null) {
        $this->AlertItem = $Alert;
        return $this;
    }

    /**
     * Alert item getter
     * @return AlertItem|null alert item if set
     */
    public function getAlertItem() {
        return $this->AlertItem;
    }

    /**
     * Badge number setter
     * @param int|null $badgeNumber badge number if needed
     * @return BasePayload self
     */
    public function setBadgeNumber($badgeNumber) {
        if (!is_null($badgeNumber)) {
            $this->badgeNumber = ($badgeNumber >= 0)
                ? $badgeNumber
                : 0;
        } else {
            $this->badgeNumber = null;
        }
        return $this;
    }

    /**
     * Badge number getter
     * @return int|null badge number if set
     */
    public function getBadgeNumber() {
        return $this->badgeNumber;
    }

    /**
     * Sound filename setter
     * @param string|null $soundFile sound filename if needed
     * @return BasePayload self
     */
    public function setSoundFile($soundFile) {
        $this->soundFile = !is_null($soundFile)
            ? $soundFile
            : null;
        return $this;
    }

    /**
     * Sound filename getter
     * @return null|string sound filename if set
     */
    public function getSoundFile() {
        return $this->soundFile;
    }

    /**
     * @return boolean true if payload sends for iOS 8 devices (with extended payload size)
     */
    public function isExtended() {
        return $this->isExtended;
    }

    /**
     * @param boolean $isExtended true if payload sends for iOS 8 devices (with extended payload size)
     * @return $this self instance
     */
    public function setIsExtended($isExtended) {
        $this->isExtended = (bool) $isExtended;
        return $this;
    }

    /**
     * @return int maximum payload size for notification payload
     */
    public function getMaximumPayloadSize() {
        return $this->isExtended()
            ? self::LENGTH_IOS8_MAX
            : self::LENGTH_IOS7_MAX;
    }

    /**
     * @return boolean new content availability flag
     */
    public function isContentAvailable() {
        return $this->isContentAvailable;
    }

    /**
     * @param boolean $isContentAvailable new content availability flag
     * @return $this self instance
     */
    public function setIsContentAvailable($isContentAvailable) {
        $this->isContentAvailable = (bool) $isContentAvailable;
        return $this;
    }

    /**
     * @return int notification delivery priority
     */
    public function getPriority() {
        return $this->priority;
    }

    /**
     * @param int $priority notification delivery priority
     * @return $this self instance
     * @throws InvalidArgumentException when needed priority was unsupported
     */
    public function setPriority($priority) {
        switch ($priority) {
            case self::PRIORITY_IMMEDIATE:
            case self::PRIORITY_POWERLESS:
                $this->priority = (int) $priority;
                return $this;
            default:
                throw new InvalidArgumentException(sprintf('unsupported priority code %s', $priority));
        }
    }

    /**
     * JsonSerializable implementation
     * @return array payload json serializable instance
     */
    public function jsonSerialize() {
        $result = array();

        if (!is_null($this->getSoundFile())) {
            $result['sound'] = (string) $this->getSoundFile();
        }
        if (!is_null($this->getBadgeNumber())) {
            $result['badge'] = (int) $this->getBadgeNumber();
        }
        if (!is_null($this->getAlertItem())) {
            $alert = $this->getAlertItem()->jsonSerialize();
            if (!is_null($alert)) {
                $result['alert'] = $alert;
            }
        }
        if ($this->isContentAvailable()) {
            $result['content-available'] = 1;
        }

        if (!empty($result)) {
            return array(
                'aps' => $result,
            );
        } else {
            return null;
        }
    }

    /**
     * Object string cast implementation
     * @return null|string object string view
     */
    public function __toString() {
        $result = json_encode($this, JSON_UNESCAPED_UNICODE);
        $maximumPayloadSize = $this->getMaximumPayloadSize();
        if (strlen($result) > $maximumPayloadSize) {
            $excess = mb_strlen($result) - $maximumPayloadSize;
            $bodySize = mb_strlen($this->getAlertItem()->getBody());
            try {
                $this->getAlertItem()->crop($bodySize - $excess);
                $result = json_encode($this, JSON_UNESCAPED_UNICODE);
            } catch (CannotCropBodyException $Ex) {
                return null;
            }
        }
        return $result;
    }
}
