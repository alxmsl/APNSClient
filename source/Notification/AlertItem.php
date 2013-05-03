<?php

namespace APNS\Notification;

/**
 * Alert notification item
 * @author alxmsl
 * @date 5/1/13
 */ 
final class AlertItem implements \JsonSerializable {
    /**
     * Default minimum text length
     */
    const DEFAULT_LENGTH_MIN = 3;

    /**
     * Three dots symbol for auto-cropping text
     */
    const POSTFIX = '…';

    /**
     * @var null|string text notification body
     */
    private $body = null;

    /**
     * @var string|null localized key for action button or NULL for no action button
     */
    private $actionLocalizedKey = PHP_EOL;

    /**
     * @var null|string localized application key for text notification
     */
    private $localizedKey = null;

    /**
     * @var null|array|string argument for localized text notification
     */
    private $localizedArgs = null;

    /**
     * @var null|string launch image application file
     */
    private $launchImageFile = null;

    /**
     * @var int minimum text length
     */
    private $minimumLength = 0;

    /**
     * @param int $minimumLength minimum length for notification text
     * @throws \LogicException when not admitted value
     */
    public function __construct($minimumLength = self::DEFAULT_LENGTH_MIN) {
        if ($minimumLength < 1) {
            throw new \LogicException('minimum text body length must be greater less 0');
        }
        $this->minimumLength = (int) $minimumLength;
    }

    /**
     * Setter for action button localized key
     * @param string|null $actionLocalizedKey action button localized key if needed
     * @return AlertItem self
     */
    public function setActionLocalizedKey($actionLocalizedKey) {
        $this->actionLocalizedKey = !is_null($actionLocalizedKey)
            ? (string) $actionLocalizedKey
            : null;
        return $this;
    }

    /**
     * Getter for action button localized key
     * @return null|string action button localized key if set
     */
    public function getActionLocalizedKey() {
        return $this->actionLocalizedKey;
    }

    /**
     * Setter for notification text
     * @param string|null $body notification text if needed
     * @return $this
     */
    public function setBody($body) {
        $this->body = !is_null($body)
            ? (string) $body
            : null;
        return $this;
    }

    /**
     * Getter for notification text
     * @return null|string notification text if set
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Setter for launch application image filename
     * @param string|null $launchImageFile launch application filename if needed
     * @return $this
     */
    public function setLaunchImageFile($launchImageFile) {
        $this->launchImageFile = !is_null($launchImageFile)
            ? (string) $launchImageFile
            : null;
        return $this;
    }

    /**
     * Getter for launch application image filename
     * @return null|string launch application filename if set
     */
    public function getLaunchImageFile() {
        return $this->launchImageFile;
    }

    /**
     * Setter for localized text arguments
     * @param null|string|array $localizedArgs localized text arguments if needed
     * @return $this
     */
    public function setLocalizedArgs($localizedArgs) {
        $this->localizedArgs = !is_null($localizedArgs)
            ? (string) $localizedArgs
            : null;
        return $this;
    }

    /**
     * Getter for localized text arguments
     * @return array|null|string localized text arguments if set
     */
    public function getLocalizedArgs() {
        return $this->localizedArgs;
    }

    /**
     * Setter for localized text key
     * @param string|null $localizedKey localized text key if needed
     * @return $this
     */
    public function setLocalizedKey($localizedKey) {
        $this->localizedKey = !is_null($localizedKey)
            ? (string) $localizedKey
            : null;
        return $this;
    }

    /**
     * Getter for localized text key
     * @return null|string localized text key if set
     */
    public function getLocalizedKey() {
        return $this->localizedKey;
    }

    /**
     * JsonSerializable implementation
     * @return array alert item json serializable instance
     */
    public function jsonSerialize() {
        $result = array();

        if (!is_null($this->getLocalizedKey())) {
            $result['loc-key'] = (string) $this->getLocalizedKey();
        }
        if (!is_null($this->getLocalizedArgs())) {
            $result['loc-args'] = $this->getLocalizedArgs();
        }
        if (!is_null($this->getLaunchImageFile())) {
            $result['launch-image'] = (string) $this->getLaunchImageFile();
        }
        if ($this->getActionLocalizedKey() != PHP_EOL) {
            $result['action-loc-key'] = $this->getActionLocalizedKey();
        }
        if (!is_null($this->getBody())) {
            if (!empty($result)) {
                $result['body'] = (string) $this->getBody();
            } else {
                $result = (string) $this->getBody();
            }
        }

        return (!empty($result))
            ? $result
            : null;
    }

    /**
     * Crop notification text for needed length
     * @param int $length needed notification text
     * @throws CannotCropBodyException when can not crop notification text
     */
    public function crop($length) {
        if ($length > $this->minimumLength) {
            $this->body = mb_substr($this->body, 0, $length - 1) . self::POSTFIX;
        } else {
            throw new CannotCropBodyException();
        }
    }
}

/**
 * Base alert item exception
 */
class AlertItemException extends \Exception {}

/**
 * Exception when can not crop notification text for needed length
 */
final class CannotCropBodyException extends AlertItemException {}
