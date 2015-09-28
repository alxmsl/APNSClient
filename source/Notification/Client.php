<?php
/*
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 */

namespace alxmsl\APNS\Notification;
use alxmsl\APNS\AbstractClient;
use alxmsl\APNS\Exception\InvalidPayloadSizeErrorException;
use alxmsl\APNS\Exception\InvalidTokenErrorException;
use alxmsl\APNS\Exception\InvalidTokenSizeErrorException;
use alxmsl\APNS\Exception\InvalidTopicSizeErrorException;
use alxmsl\APNS\Exception\MissingDeviceTokenErrorException;
use alxmsl\APNS\Exception\MissingPayloadErrorException;
use alxmsl\APNS\Exception\MissingTopicErrorException;
use alxmsl\APNS\Exception\ProcessingErrorException;
use alxmsl\APNS\Exception\SendNotificationErrorException;
use alxmsl\APNS\Exception\ShutdownServiceErrorException;
use alxmsl\APNS\Exception\UnknownErrorException;
use alxmsl\APNS\Exception\UnsupportedCommandException;
use alxmsl\APNS\Exception\UnsupportedErrorException;
use InvalidArgumentException;
use RuntimeException;

/**
 * APNS notificaton service client
 * @author alxmsl
 * @date 5/1/13
 */ 
final class Client extends AbstractClient {
    /**
     * Error strings
     */
    const ERROR_NONE                 = 'No errors encountered',
          ERROR_PROCESSING           = 'Processing error',
          ERROR_MISSING_TOKEN        = 'Missing device token',
          ERROR_MISSING_TOPIC        = 'Missing topic',
          ERROR_MISSING_PAYLOAD      = 'Missing payload',
          ERROR_INVALID_TOKEN_SIZE   = 'Invalid token size',
          ERROR_INVALID_TOPIC_SIZE   = 'Invalid topic size',
          ERROR_INVALID_PAYLOAD_SIZE = 'Invalid payload size',
          ERROR_INVALID_TOKEN        = 'Invalid token',
          ERROR_SERVICE_SHUTDOWN     = 'Shutdown',
          ERROR_UNKNOWN              = 'None (unknown)',
          ERROR_UNSUPPORTED          = 'Unsupported';

    /**
     * Read error timeout, usec
     */
    const DEFAULT_READ_TIMEOUT = 500000;

    /**
     * APNS command constants
     */
    const COMMAND_SIMPLE_PUSH   = 0, // Push command
          COMMAND_ENHANCED_PUSH = 1, // Enhanced push command
          COMMAND_2_PUSH        = 2, // Code 2 push command
          COMMAND_RESPONSE      = 8; // Error response command

    /**
     * Length of binary values
     */
    const LENGTH_BINARY_TOKEN    = 32,  // Length of binary token
          LENGTH_REQUEST         = 256, // Length of notification request
          LENGTH_RESPONSE        = 6,   // Length of enhanced response
          LENGTH_IDENTIFIER      = 4,   // Length of notification identifier
          LENGTH_EXPIRATION_TIME = 4;   // Length of expiration time

    /**
     * APNS notification service endpoints
     */
    const ENDPOINT_PRODUCTION = 'gateway.push.apple.com:2195',            // For production
          ENDPOINT_SANDBOX    = 'gateway.sandbox.push.apple.com:2195';    // For developer

    /**
     * @var int read error state timeout, usec
     */
    private $readTimeout = self::DEFAULT_READ_TIMEOUT;

    /**
     * @var int push command code
     */
    private $commandCode = self::COMMAND_2_PUSH;

    /**
     * Setter for read error timeout
     * @param int $readTimeout read error timeout, usec
     * @return Client self
     * @throws InvalidArgumentException when not admitted value
     */
    public function setReadTimeout($readTimeout) {
        if ($readTimeout < 0) {
            throw new InvalidArgumentException('incorrect read error state timeout \'' . $readTimeout . '\'');
        }
        $this->readTimeout = (int) $readTimeout;
        return $this;
    }

    /**
     * Getter for read error timeout
     * @return int read error timeout
     */
    public function getReadTimeout() {
        return $this->readTimeout;
    }

    /**
     * @return int command push code
     */
    public function getCommandCode() {
        return $this->commandCode;
    }

    /**
     * @param int $commandCode command push code
     * @return $this self instance
     * @throws InvalidArgumentException when command code was unsupported now
     */
    public function setCommandCode($commandCode) {
        switch ($commandCode) {
            case self::COMMAND_2_PUSH:
            case self::COMMAND_ENHANCED_PUSH:
            case self::COMMAND_SIMPLE_PUSH:
                $this->commandCode = (int) $commandCode;
                return $this;
            default:
                throw new InvalidArgumentException(sprintf('unsupported command code %s', $commandCode));
        }
    }

    /**
     * Send notification by device token
     * @param string $token delivery device token
     * @param BasePayload $Payload payload object
     * @param bool $panic throws exception on errors or not
     * @return bool send result
     * @throws SendNotificationErrorException when was error on send payload command
     * @throws UnknownErrorException when was unknown error on delivery payload in enhanced mode
     * @throws ShutdownServiceErrorException when APNS service was shutdown on delivery payload in enhanced mode
     * @throws UnsupportedCommandException when was unsupported command on delivery payload in enhanced mode
     * @throws InvalidTopicSizeErrorException when was invalid topic on delivery payload in enhanced mode
     * @throws ProcessingErrorException when was processing error on delivery payload in enhanced mode
     * @throws UnsupportedErrorException when was unsupported error on delivery payload in enhanced mode
     * @throws MissingPayloadErrorException when was missing payload on delivery payload in enhanced mode
     * @throws MissingDeviceTokenErrorException when was missing device token on delivery payload in enhanced mode
     * @throws InvalidTokenSizeErrorException when was invalid token size on delivery payload in enhanced mode
     * @throws MissingTopicErrorException when was missing topic on delivery payload in enhanced mode
     * @throws InvalidPayloadSizeErrorException when was invalid payload size on delivery payload in enhanced mode
     * @throws InvalidTokenErrorException when was invalid token on delivery payload in enhanced mode
     */
    public function send($token, BasePayload $Payload, $panic = true) {
        $command = $this->createPushCommand($token, $Payload);
        $sentBytes = @fwrite($this->getHandler(), $command);
        if ($sentBytes == strlen($command)) {
            if ($this->getCommandCode() == self::COMMAND_SIMPLE_PUSH) {
                return true;
            }
            usleep($this->getReadTimeout());
            $data = @fread($this->getHandler(), self::LENGTH_RESPONSE);
            switch (true) {
                case strlen($data) == self::LENGTH_RESPONSE:
                    $this->disconnect();
                    if ($panic) {
                        $response = unpack('Ccommand/CstatusCode/Nidentifier', $data);
                        if ($response['command'] == self::COMMAND_RESPONSE) {
                            switch ($response['statusCode']) {
                                case 0:
                                    return true;
                                case 1:
                                    throw new ProcessingErrorException(self::ERROR_PROCESSING, $response['statusCode']);
                                case 2:
                                    throw new MissingDeviceTokenErrorException(self::ERROR_MISSING_TOKEN, $response['statusCode']);
                                case 3:
                                    throw new MissingTopicErrorException(self::ERROR_MISSING_TOPIC, $response['statusCode']);
                                case 4:
                                    throw new MissingPayloadErrorException(self::ERROR_MISSING_PAYLOAD, $response['statusCode']);
                                case 5:
                                    throw new InvalidTokenSizeErrorException(self::ERROR_INVALID_TOKEN_SIZE, $response['statusCode']);
                                case 6:
                                    throw new InvalidTopicSizeErrorException(self::ERROR_INVALID_TOPIC_SIZE, $response['statusCode']);
                                case 7:
                                    throw new InvalidPayloadSizeErrorException(self::ERROR_INVALID_PAYLOAD_SIZE, $response['statusCode']);
                                case 8:
                                    throw new InvalidTokenErrorException(self::ERROR_INVALID_TOKEN, $response['statusCode']);
                                case 10:
                                    throw new ShutdownServiceErrorException(self::ERROR_SERVICE_SHUTDOWN, $response['statusCode']);
                                case 255:
                                    throw new UnknownErrorException(self::ERROR_UNKNOWN, $response['statusCode']);
                                default:
                                    throw new UnsupportedErrorException(self::ERROR_UNKNOWN, $response['statusCode']);
                            }
                        } else {
                            throw new UnsupportedCommandException(sprintf('Unsupported enhanced command %s', $response['command']));
                        }
                    } else {
                        return false;
                    }
                case feof($this->getHandler()):
                    $this->disconnect();
                    if ($panic) {
                        throw new SendNotificationErrorException('Connection was closed after notification sending');
                    } else {
                        return false;
                    }
                default:
                    return true;
            }
        } else {
            $this->disconnect();
            if ($panic) {
                throw new SendNotificationErrorException(sprintf('sent %s bytes, expected %s bytes', $sentBytes, strlen($command)));
            } else {
                return false;
            }
        }
    }

    /**
     * Create push notification command
     * @param string $token device token
     * @param BasePayload $Payload payload instance
     * @return string push notification command
     */
    private function createPushCommand($token, BasePayload $Payload) {
        switch ($this->getCommandCode()) {
            case self::COMMAND_SIMPLE_PUSH:
                return pack('CnH*', $this->getCommandCode(), self::LENGTH_BINARY_TOKEN, $token)
                    . pack('nA*', strlen($Payload), (string) $Payload);
            case self::COMMAND_ENHANCED_PUSH:
                return pack('CNNnH*', $this->getCommandCode(), $Payload->getIdentifier(), $Payload->getExpirationTime(), self::LENGTH_BINARY_TOKEN, $token)
                    . pack('nA*', strlen($Payload), (string) $Payload);
            case self::COMMAND_2_PUSH:
                $frame = pack('CnH*', 1, self::LENGTH_BINARY_TOKEN, $token)
                    . pack('CnA*', 2, strlen($Payload), (string) $Payload)
                    . pack('CnA*', 3, self::LENGTH_IDENTIFIER, $Payload->getIdentifier())
                    . pack('CnN', 4, self::LENGTH_EXPIRATION_TIME, $Payload->getExpirationTime())
                    . pack('CnC', 5, 1, $Payload->getPriority());
                return pack('CNA*', $this->getCommandCode(), strlen($frame), $frame);
            default:
                throw new RuntimeException(sprintf('could not build command for code %s', $this->getCommandCode()));
        }
    }
}
