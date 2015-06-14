<?php

namespace alxmsl\APNS\Test\Notification;

use alxmsl\APNS\Notification\AlertItem;
use alxmsl\APNS\Notification\Exception\CannotCropBodyException;

/**
 * Test AlertItem behavior
 * @author Andrey Artemov <andrey.artemov@gmail.com>
 */
class AlertItemTest extends \PHPUnit_Framework_TestCase {

    /**
     * Checking correct body crop in AlertItem::crop()
     * @dataProvider getBodiesForCrop
     *
     * @param string $body
     * @param int $cropLength
     * @param string $expectedBody
     */
    public function testCrop($body, $cropLength, $expectedBody) {
        // checking correct internal encoding
        $this->assertSame('UTF-8', mb_internal_encoding());

        $Item = new AlertItem();
        $Item->setBody($body);
        $Item->crop($cropLength);
        $this->assertSame($expectedBody, $Item->getBody());
    }

    /**
     * Getting different test cases for checking correct body crop in AlertItem::crop()
     * @return array
     */
    public function getBodiesForCrop() {
        return [
            ['абв', 7, 'аб…'], // 7 bytes
            ['абв', 6, 'а…'],  // 5 bytes
            ['абв', 5, 'а…'],  // 5 bytes
            ['абв', 4, '…'],   // 3 bytes
        ];
    }

    /**
     * Checking expected exception is thrown when cropping length is less then minimum body length
     */
    public function testExceptionIsThrownWhenCropLengthIsLessThenMinimumLength() {
        $minimumLength = 5;
        $Item = new AlertItem($minimumLength);
        $Item->setBody('foobar');

        $this->setExpectedException(CannotCropBodyException::class);
        $Item->crop($minimumLength - 1);
    }

    /**
     * Checking expected exception is thrown when cropping length is equal to minimum body length
     */
    public function testExceptionIsThrownWhenCropLengthIsEqualToMinimumLength() {
        $minimumLength = 5;
        $Item = new AlertItem($minimumLength);
        $Item->setBody('foobar');

        $this->setExpectedException(CannotCropBodyException::class);
        $Item->crop($minimumLength);
    }

}
