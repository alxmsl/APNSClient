<?php
/**
 * Standard payload example
 * @author alxmsl
 * @date 5/1/13
 */

// Include autoloader
include '../source/Autoloader.php';

// Create simple alert item
$SimpleItem = new \APNS\Notification\AlertItem();
$SimpleItem->setBody('просто уведомление');

// Create simple payload
$SimplePayload = new \APNS\Notification\BasePayload();
$SimplePayload->setAlertItem($SimpleItem);

// Look at simple payload
var_dump((string) $SimplePayload);

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

// Create custom action button item
$CustomActionItem = new \APNS\Notification\AlertItem();
$CustomActionItem->setBody('Bob wants to play poker')
    ->setActionLocalizedKey('PLAY');

// Create payload with badge
$BadgePayload = new \APNS\Notification\BasePayload();
$BadgePayload->setAlertItem($CustomActionItem)
    ->setBadgeNumber(5);

// Look at custom action payload
var_dump((string) $BadgePayload);

// Create payload with sound
$SoundPayload = new \APNS\Notification\BasePayload();
$SoundPayload->setAlertItem($SimpleItem)
    ->setBadgeNumber(9)
    ->setSoundFile('bingbong.aiff');

// Look at simple payload with sound
var_dump((string) $SoundPayload);
