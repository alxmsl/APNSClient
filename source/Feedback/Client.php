<?php

namespace APNS\Feedback;
use alxmsl\APNS\AbstractClient;
use alxmsl\APNS\Feedback\Exception\FeedbackProcessorException;
use Closure;

/**
 * APNS feedback service client
 * @author alxmsl
 * @date 5/4/13
 */
final class Client extends AbstractClient {
    /**
     * APNS feedback service endpoints
     */
    const ENDPOINT_PRODUCTION = 'feedback.push.apple.com:2196',           // For production
          ENDPOINT_SANDBOX    = 'feedback.sandbox.push.apple.com:2196';   // For developer

    /**
     * Length of binary packet
     */
    const LENGTH_PACKET = 38;

    /**
     * @var int count of processed feedback items
     */
    private $processedCount = 0;

    /**
     * @var int count of unprocessed feedback items
     */
    private $unprocessedCount = 0;

    /**
     * @var int count of errors on feedback items
     */
    private $errorCount = 0;

    /**
     * @var int count of read feedback items
     */
    private $readCount = 0;

    /**
     * Get count of feedback item error processing
     * @return int count of feedback item errors
     */
    public function getErrorCount() {
        return $this->errorCount;
    }

    /**
     * Get count of processed feedback items
     * @return int count of processed feedback items
     */
    public function getProcessedCount() {
        return $this->processedCount;
    }

    /**
     * Get count of read feedback items
     * @return int count of read feedback items
     */
    public function getReadCount() {
        return $this->readCount;
    }

    /**
     * Get count of unprocessed feedback items
     * @return int count of unprocessed feedback items
     */
    public function getUnprocessedCount() {
        return $this->unprocessedCount;
    }

    /**
     * Process feedback items
     * @param callable $processor feedback item processor.
     *
     * Processor function must have 2 parameters:
     *      1. int $timestamp time when application no longer exists on device
     *      2. string $token device token
     * And returns bool, when device token processed completely
     * Processor can throw exception extends FeedbackProcessorException on some error situations
     *
     * @param bool $panic throw exception on error or not
     * @throws FeedbackProcessorException feedback item processor item exception
     * @return int count of processed feedback items
     */
    public function process(Closure $processor, $panic = true) {
        while (!feof($this->getHandler())) {
            $data = @fread($this->getHandler(), self::LENGTH_PACKET);
            if (strlen($data) == self::LENGTH_PACKET) {
                $this->readCount += 1;

                $response = unpack('Ntimestamp/ntokenLength/H*deviceToken', $data);
                try {
                    if ($processor($response['timestamp'], $response['deviceToken'])) {
                        $this->processedCount += 1;
                    } else {
                        $this->unprocessedCount += 1;
                    }
                } catch (FeedbackProcessorException $Ex) {
                    if (!$panic) {
                        $this->errorCount += 1;
                    } else {
                        throw $Ex;
                    }
                }
            }
        }
        $this->disconnect();
        return $this->processedCount;
    }
}
