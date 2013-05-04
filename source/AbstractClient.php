<?php

namespace APNS;

/**
 * Abstract client for APNS connection
 * @author alxmsl
 * @date 5/2/13
 */ 
abstract class AbstractClient {
    /**
     * Supported protocol constants
     */
    const   PROTOCOL_SSL = 'ssl',
            PROTOCOL_TLS = 'tls';

    /**
     * Default connect parameters
     */
    const   DEFAULT_CONNECT_TIMEOUT = 3,    // Default connect timeout, sec
            DEFAULT_CONNECT_TRIES   = 3;    // Default connection tries

    /**
     * APNS service endpoints
     */
    const   ENDPOINT_PRODUCTION = '',   // For production
            ENDPOINT_SANDBOX    = '';   // For developer

    /**
     * @var string connection url
     */
    private $url = '';

    /**
     * @var int connection tries
     */
    private $connectionTries = self::DEFAULT_CONNECT_TRIES;

    /**
     * @var int connect timeout, sec
     */
    private $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT;

    /**
     * @var int connect attempt timeout, usec
     */
    private $connectAttemptTimeout = 0;

    /**
     * @var string certificate filename
     */
    private $certificateFile = '';

    /**
     * @var null|string certificate password phrase if needed
     */
    private $certificatePassPhrase = null;

    /**
     * @var null|string authority certificate file if needed
     */
    private $authorityCertificateFile = null;

    /**
     * @var string connection protocol scheme
     */
    private $protocolScheme = self::PROTOCOL_TLS;

    /**
     * @var resource socket connection resource
     */
    protected $Handler = null;

    /**
     * @param bool $isSandbox enable sandbox mode
     */
    public function __construct($isSandbox = false) {
        $url = $isSandbox
            ? static::ENDPOINT_SANDBOX
            : static::ENDPOINT_PRODUCTION;
        $this->setUrl($url);
    }

    /**
     * Setter for connect attempt timeout
     * @param int $connectAttemptTimeout connect attempt timeout, usec
     * @return AbstractClient self
     * @throws \InvalidArgumentException when not admitted value
     */
    public function setConnectAttemptTimeout($connectAttemptTimeout) {
        if ($connectAttemptTimeout < 0) {
            throw new \InvalidArgumentException('incorrect attempt timeout \'' . $connectAttemptTimeout . '\' for connection');
        }
        $this->connectAttemptTimeout = (int) $connectAttemptTimeout;
        return $this;
    }

    /**
     * Getter for connect attempt timeout
     * @return int connect attempt timeout
     */
    public function getConnectAttemptTimeout() {
        return $this->connectAttemptTimeout;
    }

    /**
     * Setter for connect timeout
     * @param int $connectTimeout connect timeout, sec
     * @return AbstractClient self
     * @throws \InvalidArgumentException when not admitted value
     */
    public function setConnectTimeout($connectTimeout) {
        if ($connectTimeout < 0) {
            throw new \InvalidArgumentException('incorrect connect timeout \'' . $connectTimeout . '\'');
        }
        $this->connectTimeout = (int) $connectTimeout;
        return $this;
    }

    /**
     * Getter for connect timeout
     * @return int connect timeout
     */
    public function getConnectTimeout() {
        return $this->connectTimeout;
    }

    /**
     * Setter for connection url
     * @param string $url connection url
     * @return AbstractClient self
     */
    public function setUrl($url) {
        $this->url = (string) $url;
        return $this;
    }

    /**
     * Getter for connection url
     * @return string connection url
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Getter for connection url with protocol scheme
     * @return string connection url with protocol scheme
     */
    public function getConnectionUrl() {
        return $this->getProtocolScheme() . '://' . $this->getUrl();
    }

    /**
     * Setter for connection tries value
     * @param int $connectionTries connection tries value
     * @return AbstractClient self
     * @throws \InvalidArgumentException
     */
    public function setConnectionTries($connectionTries) {
        if ($connectionTries < 1) {
            throw new \InvalidArgumentException('incorrect connection tries value \'' . $connectionTries . '\'');
        }
        $this->connectionTries = (int) $connectionTries;
        return $this;
    }

    /**
     * Getter for connection tries value
     * @return int connection tries value
     */
    public function getConnectionTries() {
        return $this->connectionTries;
    }

    /**
     * Setter for certificate filename
     * @param string $certificateFile certificate filename
     * @return AbstractClient self
     */
    public function setCertificateFile($certificateFile) {
        $this->certificateFile = (string) $certificateFile;
        return $this;
    }

    /**
     * Getter for certificate filename
     * @return string certificate filename
     */
    public function getCertificateFile() {
        return $this->certificateFile;
    }

    /**
     * Setter for authority certificate filename
     * @param string|null $authorityCertificateFile authority certificate filename if needed
     * @return AbstractClient self
     */
    public function setAuthorityCertificateFile($authorityCertificateFile) {
        $this->authorityCertificateFile = !is_null($authorityCertificateFile)
            ? (string) $authorityCertificateFile
            : null;
        return $this;
    }

    /**
     * Getter for authority certificate filename
     * @return null|string authority certificate filename if set
     */
    public function getAuthorityCertificateFile() {
        return $this->authorityCertificateFile;
    }

    /**
     * Setter for certificate password phrase
     * @param string|null $certificatePassPhrase certificate password phrase if needed
     * @return AbstractClient self
     */
    public function setCertificatePassPhrase($certificatePassPhrase) {
        $this->certificatePassPhrase = !is_null($certificatePassPhrase)
            ? (string) $certificatePassPhrase
            : null;
        return $this;
    }

    /**
     * Getter for certificate password phrase
     * @return null|string certificate password phrase if set
     */
    public function getCertificatePassPhrase() {
        return $this->certificatePassPhrase;
    }

    /**
     * Setter SSL protocol scheme
     * @return AbstractClient self
     */
    public function setProtocolSchemeSSL() {
        $this->protocolScheme = self::PROTOCOL_SSL;
        return $this;
    }

    /**
     * Setter TSL protocol scheme
     * @return AbstractClient self
     */
    public function setProtocolSchemeTLS() {
        $this->protocolScheme = self::PROTOCOL_TLS;
        return $this;
    }

    /**
     * Getter for protocol scheme
     * @return string protocol scheme
     */
    public function getProtocolScheme() {
        return $this->protocolScheme;
    }

    /**
     * Connection wrapper for needed tries
     * @return resource connected socket resource
     * @throws ClientConnectException when not connected for needed tries
     */
    protected function getHandler() {
        if (is_null($this->Handler)) {
            $tries = $this->getConnectionTries();
            $LastException = null;
            do {
                try {
                    $this->connect();
                    return $this->Handler;
                } catch (ClientConnectException $Ex) {
                    $LastException = $Ex;
                    $tries -= 1;
                }
                usleep($this->getConnectAttemptTimeout());
            } while ($tries > 0 && is_null($this->Handler));

            throw new ClientConnectException($LastException->getMessage()
                . ' on \'' . $this->getConnectionTries() . '\' tries');
        }
        return $this->Handler;
    }

    /**
     * Connect to APNS service
     * @throws ClientConnectException when not connected
     */
    public function connect() {
        $Context = stream_context_create(array(
            'ssl' => array(
                'local_cert' => $this->getCertificateFile(),
            ),
        ));
        if (!is_null($this->getCertificatePassPhrase())) {
            stream_context_set_option($Context, 'ssl', 'passphrase', $this->getCertificatePassPhrase());
        }
        if (!is_null($this->getAuthorityCertificateFile())) {
            stream_context_set_option($Context, 'ssl', 'verify_peer', true);
            stream_context_set_option($Context, 'ssl', 'cafile', $this->getAuthorityCertificateFile());
        }

        $this->Handler = @stream_socket_client($this->getConnectionUrl(), $errorNumber, $errorString, $this->getConnectTimeout(), STREAM_CLIENT_CONNECT, $Context);
        if ($this->Handler) {
            stream_set_blocking($this->Handler, false);
            stream_set_write_buffer($this->Handler, 0);
            stream_set_read_buffer($this->Handler, 6);
        } else {
            throw new ClientConnectException('Cannot connect to \''
                . $this->getConnectionUrl() . '\' with error code ['
                . $errorNumber . '] and message \''
                . $errorString . '\' with \''
                . $this->getConnectTimeout() . '\' seconds timeout');
        }
    }

    /**
     * Disconnect from APNS service
     */
    public function disconnect() {
        if (is_resource($this->Handler)) {
            fclose($this->Handler);
        }
        $this->Handler = null;
    }
}

/**
 * Client exception
 */
class ClientException extends \Exception {}

/**
 * Client connect exception
 */
final class ClientConnectException extends ClientException {}
