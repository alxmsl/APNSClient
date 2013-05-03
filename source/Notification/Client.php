<?php

namespace APNS\Notification;

use APNS\ClientException,
    APNS\Notification\BasePayload,
    APNS\AbstractClient;

/**
 * APNS notificaton service client
 * @author alxmsl
 * @date 5/1/13
 */ 
final class Client extends AbstractClient {
    /**
     * Error strings
     */
    const   ERROR_NONE                  = 'No errors encountered',
            ERROR_PROCESSING            = 'Processing error',
            ERROR_MISSING_TOKEN         = 'Missing device token',
            ERROR_MISSING_TOPIC         = 'Missing topic',
            ERROR_MISSING_PAYLOAD       = 'Missing payload',
            ERROR_INVALID_TOKEN_SIZE    = 'Invalid token size',
            ERROR_INVALID_TOPIC_SIZE    = 'Invalid topic size',
            ERROR_INVALID_PAYLOAD_SIZE  = 'Invalid payload size',
            ERROR_INVALID_TOKEN         = 'Invalid token',
            ERROR_SERVICE_SHUTDOWN      = 'Shutdown',
            ERROR_UNKNOWN               = 'None (unknown)',
            ERROR_UNSUPPORTED           = 'Unsupported';

    /**
     * Read error timeout, usec
     */
    const DEFAULT_READ_TIMEOUT = 500000;

    /**
     * APNS command constants
     */
    const   COMMAND_SIMPLE_PUSH     = 0, // Push command
            COMMAND_ENHANCED_PUSH   = 1, // Enhanced push command
            COMMAND_RESPONSE        = 8; // Error response command

    /**
     * Length of binary values
     */
    const   LENGTH_BINARY_TOKEN = 32,   // Length of binary token
            LENGTH_REQUEST      = 256,  // Length of notification request
            LENGTH_RESPONSE     = 6;    // Length of enhanced response

    /**
     * APNS notification service endpoints
     */
    const   ENDPOINT_PRODUCTION = 'gateway.push.apple.com:2195',            // For production
            ENDPOINT_SANDBOX    = 'gateway.sandbox.push.apple.com:2195';    // For developer

    /**
     * @var int read error state timeout, usec
     */
    private $readTimeout = self::DEFAULT_READ_TIMEOUT;

    /**
     * @var bool enabled enhanced mode
     */
    private $enhancedMode = true;

    /**
     * Setter for read error timeout
     * @param int $readTimeout read error timeout, usec
     * @return Client self
     * @throws \InvalidArgumentException when not admitted value
     */
    public function setReadTimeout($readTimeout) {
        if ($readTimeout < 0) {
            throw new \InvalidArgumentException('incorrect read error state timeout \'' . $readTimeout . '\'');
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
     * Setter for enhanced mode
     * @param bool $enhancedMode enable enhanced value
     * @return Client self
     */
    public function setEnhancedMode($enhancedMode) {
        $this->enhancedMode = (bool) $enhancedMode;
        return $this;
    }

    /**
     * Getter of enabled enhanced mode value
     * @return bool enabled enhanced mode value or not
     */
    public function isEnhancedMode() {
        return $this->enhancedMode;
    }

    /**
     * @param bool $isSandbox enable sandbox mode
     */
    public function __construct($isSandbox = false) {
        $url = $isSandbox
            ? self::ENDPOINT_SANDBOX
            : self::ENDPOINT_PRODUCTION;
        $this->setUrl($url);
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
        $command = $this->createPushCommand($token, $Payload, $this->isEnhancedMode());
        $sentBytes = @fwrite($this->getHandler(), $command);
        if ($sentBytes == strlen($command)) {
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
                            throw new UnsupportedCommandException('Unsupported enhanced command \'' . $response['command'] . '\'');
                        }
                    } else {
                        return false;
                    }
                case feof($this->getHandler()):
                    $this->disconnect();
                    if ($panic) {
                        throw new SendNotificationErrorException('sent \'' . $sentBytes . '\' bytes, except \'' . strlen($command) . '\' bytes');
                    } else {
                        return false;
                    }
                default:
                    return true;
            }
        } else {
            $this->disconnect();
            if ($panic) {
                throw new SendNotificationErrorException('sent \'' . $sentBytes . '\' bytes, except \'' . strlen($command) . '\' bytes');
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
        $expirationTime = time() + $Payload->getDeliveryTimeout();
        $command = $this->isEnhancedMode()
            ? pack('CNNnH*', self::COMMAND_ENHANCED_PUSH, $Payload->getIdentifier(), $expirationTime, self::LENGTH_BINARY_TOKEN, $token)
            : pack('CnH*', self::COMMAND_SIMPLE_PUSH, self::LENGTH_BINARY_TOKEN, $token);
        $command .= pack('n', strlen($Payload));
        $command .= (string) $Payload;
        return $command;
    }
}

/**
 * Base exception for send notification errors
 */
class SendNotificationErrorException extends ClientException {}

/**
 * Exception for processing error on delivery payload in enhanced mode
 */
final class ProcessingErrorException extends SendNotificationErrorException {}

/**
 * Exception for missing device token on delivery payload in enhanced mode
 */
final class MissingDeviceTokenErrorException extends SendNotificationErrorException {}

/**
 * Exception for missing topic on delivery payload in enhanced mode
 */
final class MissingTopicErrorException extends SendNotificationErrorException {}

/**
 * Exception for missing payload on delivery payload in enhanced mode
 */
final class MissingPayloadErrorException extends SendNotificationErrorException {}

/**
 * Exception for invalid token size on delivery payload in enhanced mode
 */
final class InvalidTokenSizeErrorException extends SendNotificationErrorException {}

/**
 * Exception for invalid topic size on delivery payload in enhanced mode
 */
final class InvalidTopicSizeErrorException extends SendNotificationErrorException {}

/**
 * Exception for invalid payload size on delivery payload in enhanced mode
 */
final class InvalidPayloadSizeErrorException extends SendNotificationErrorException {}

/**
 * Exception for invalid token on delivery payload in enhanced mode
 */
final class InvalidTokenErrorException extends SendNotificationErrorException {}

/**
 * Exception for service shutdown error on delivery payload in enhanced mode
 */
final class ShutdownServiceErrorException extends SendNotificationErrorException {}

/**
 * Exception for unknown error on delivery payload in enhanced mode
 */
final class UnknownErrorException extends SendNotificationErrorException {}

/**
 * Exception for unsupported error on delivery payload in enhanced mode
 */
final class UnsupportedErrorException extends SendNotificationErrorException {}

/**
 * Exception for unsupported response command on delivery payload in enhanced mode
 */
class UnsupportedCommandException extends \Exception {}