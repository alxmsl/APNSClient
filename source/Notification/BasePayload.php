<?php

namespace alxmsl\APNS\Notification;
use alxmsl\APNS\Notification\Exception\CannotCropBodyException;
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
    const LENGTH_MAX = 211,                   // Maximum payload size
          DEFAULT_DELIVERY_TIMEOUT = 86400;   // Default payload delivery timeout

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
        if (mb_strlen($result) > self::LENGTH_MAX) {
            $excess = mb_strlen($result) - self::LENGTH_MAX;
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
