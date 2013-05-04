APNSClient
==========

Client for Apple Push Notification Service (APNS)

Standard payload creation
-------

    // Create simple alert item
    $SimpleItem = new \APNS\Notification\AlertItem();
    $SimpleItem->setBody('просто уведомление');

    // Create simple payload
    $SimplePayload = new \APNS\Notification\BasePayload();
    $SimplePayload->setAlertItem($SimpleItem);

    // Look at simple payload
    var_dump((string) $SimplePayload);

Localized payload creation
-------

    // Create localized alert item
    $LocalizedItem = new \APNS\Notification\AlertItem();
    $LocalizedItem->setLocalizedKey('GAME_PLAY_REQUEST_FORMAT')
        ->setLocalizedArgs(array(
            'Jenna',
            'Frank',
        ));

    // Create localized payload
    $LocalizedPayload = new \APNS\Notification\BasePayload();
    $LocalizedPayload->setAlertItem($LocalizedItem);

    // Look at localized payload
    var_dump((string) $LocalizedPayload);

Custom payload creation
-------

    // Create custom payload class
    final class CustomPayload extends \APNS\Notification\BasePayload {
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
    $SimpleItem = new \APNS\Notification\AlertItem();
    $SimpleItem->setBody('You got your emails.');

    // Create payload instance
    $CustomPayload = new CustomPayload();
    $CustomPayload->setAcme('foo')
        ->setAlertItem($SimpleItem)
        ->setBadgeNumber(9)
        ->setSoundFile('bingbong.aiff');

    // Look at payload
    var_dump((string) $CustomPayload);

Send notification
-------

    // Create APNS notification client
    $Client = new \APNS\Notification\Client();

    // Set secure certificate filename
    $Client->setCertificateFile('certificate.production.pem');

    // Create needed alert item
    $Item = new \APNS\Notification\AlertItem();
    $Item->setBody('test1');

    // Create payload
    $Payload = new \APNS\Notification\BasePayload();
    $Payload->setAlertItem($Item)
        ->setBadgeNumber(1)
        ->setIdentifier(time());

    // Send notification to the device
    $result = $Client->send('c0RreCtT0kEN', $Payload);
    var_dump($result);

Use the feedback service
-------

    // Create APNS notification client
    $Client = new \APNS\Feedback\Client();

    // Set secure certificate filename
    $Client->setCertificateFile('certificate.production.pem')
        ->setProtocolSchemeSSL();

    $Client->process(function ($time, $token) {
        echo date('Y-m-d H:i:s', $time) . ' - ' . $token . "\n";
        return true;
    }, false);

    var_dump(
        $Client->getReadCount(),
        $Client->getProcessedCount(),
        $Client->getUnprocessedCount(),
        $Client->getErrorCount()
    );