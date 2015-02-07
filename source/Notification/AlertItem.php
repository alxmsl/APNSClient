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
use JsonSerializable;
use LogicException;

/**
 * Alert notification item
 * @author alxmsl
 * @date 5/1/13
 */ 
final class AlertItem implements JsonSerializable {
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
     * @var string|null|int localized key for action button or NULL for no action button
     */
    private $actionLocalizedKey = -1;

    /**
     * @var null|string localized application key for text notification
     */
    private $localizedKey = null;

    /**
     * @var null|array argument for localized text notification
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
     * @var null|string notification title
     */
    private $title = null;

    /**
     * @var null|string localized application key for notification title or NULL
     */
    private $titleLocalizedKey = -1;

    /**
     * @var null|array argument for localized notification title or NULL
     */
    private $titleLocalizedArgs = -1;

    /**
     * @param int $minimumLength minimum length for notification text
     * @throws LogicException when not admitted value
     */
    public function __construct($minimumLength = self::DEFAULT_LENGTH_MIN) {
        if ($minimumLength < 1) {
            throw new LogicException('minimum text body length must be greater less 0');
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
        $this->body = (string) $body;
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
        $this->launchImageFile = (string) $launchImageFile;
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
        $this->localizedArgs = (array) $localizedArgs;
        return $this;
    }

    /**
     * Getter for localized text arguments
     * @return array|null localized text arguments if set
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
        $this->localizedKey = (string) $localizedKey;
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
     * @return null|string notification title if exists
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param null|string $title notification title if exists
     * @return $this self instance
     */
    public function setTitle($title) {
        $this->title = (string) $title;
        return $this;
    }

    /**
     * @return null|string localized application key for notification title
     */
    public function getTitleLocalizedKey() {
        return $this->titleLocalizedKey;
    }

    /**
     * @param null|string $titleLocalizedKey localized application key for notification title
     * @return $this self instance
     */
    public function setTitleLocalizedKey($titleLocalizedKey) {
        $this->titleLocalizedKey = !is_null($titleLocalizedKey)
            ? (string) $titleLocalizedKey
            : null;
        return $this;
    }

    /**
     * @return array|null|string argument for localized notification title
     */
    public function getTitleLocalizedArgs() {
        return $this->titleLocalizedArgs;
    }

    /**
     * @param array|null|string $titleLocalizedArgs argument for localized notification title
     */
    public function setTitleLocalizedArgs($titleLocalizedArgs) {
        $this->titleLocalizedArgs = !is_null($titleLocalizedArgs)
            ? (array) $titleLocalizedArgs
            : null;
        return $this;
    }

    /**
     * JsonSerializable implementation
     * @return array alert item json serializable instance
     */
    public function jsonSerialize() {
        $result = array();

        if (!is_null($this->getLocalizedKey())) {
            $result['loc-key'] = $this->getLocalizedKey();
        }
        if (!is_null($this->getLocalizedArgs())) {
            $result['loc-args'] = $this->getLocalizedArgs();
        }
        if (!is_null($this->getLaunchImageFile())) {
            $result['launch-image'] = $this->getLaunchImageFile();
        }
        if ($this->getActionLocalizedKey() != -1) {
            $result['action-loc-key'] = $this->getActionLocalizedKey();
        }
        if (!is_null($this->getTitle())) {
            $result['title'] = $this->getTitle();
        }
        if ($this->getTitleLocalizedKey() != -1) {
            $result['title-loc-key'] = $this->getTitleLocalizedKey();
        }
        if ($this->getTitleLocalizedArgs() != -1) {
            $result['title-loc-args'] = $this->getTitleLocalizedArgs();
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
