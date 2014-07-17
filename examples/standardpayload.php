<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 *
 * Standard payload example
 * @author alxmsl
 * @date 5/1/13
 */

// Include autoloader
include '../source/Autoloader.php';

use alxmsl\APNS\Notification\AlertItem;
use alxmsl\APNS\Notification\BasePayload;
use alxmsl\APNS\Notification\Client;

// Create simple alert item
$SimpleItem = new AlertItem();
$SimpleItem->setBody('просто уведомление');

// Create simple payload
$SimplePayload = new BasePayload();
$SimplePayload->setAlertItem($SimpleItem);

// Look at simple payload
var_dump((string) $SimplePayload);

// Create localized alert item
$LocalizedItem = new AlertItem();
$LocalizedItem->setLocalizedKey('GAME_PLAY_REQUEST_FORMAT')
    ->setLocalizedArgs(array(
        'Jenna',
        'Frank',
    ));

// Create localized payload
$LocalizedPayload = new BasePayload();
$LocalizedPayload->setAlertItem($LocalizedItem);

// Look at localized payload
var_dump((string) $LocalizedPayload);

// Create custom action button item
$CustomActionItem = new AlertItem();
$CustomActionItem->setBody('Bob wants to play poker')
    ->setActionLocalizedKey('PLAY');

// Create payload with badge
$BadgePayload = new BasePayload();
$BadgePayload->setAlertItem($CustomActionItem)
    ->setBadgeNumber(5);

// Look at custom action payload
var_dump((string) $BadgePayload);

// Create payload with sound
$SoundPayload = new BasePayload();
$SoundPayload->setAlertItem($SimpleItem)
    ->setBadgeNumber(9)
    ->setSoundFile('bingbong.aiff');

// Look at simple payload with sound
var_dump((string) $SoundPayload);
