<?php

namespace BlocksCloner\Test;

use Concrete\Package\BlocksCloner\Conversion\FontAwesome;
use PHPUnit\Framework\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

class ConvertFontAwesomeTest extends TestCase
{
    /**
     * @var \Concrete\Package\BlocksCloner\Conversion\FontAwesome|null
     */
    private static $fontAwesome = null;

    /**
     * @dataProvider conver4To5tProvider
     *
     * @param string|mixed $input
     * @param string $expectedOutput
     *
     * @return void
     */
    public function testConvert4To5($input, $expectedOutput)
    {
        $actualOutput = self::getFontAwesome()->convertFontAwesomeIcon4To5($input);
        static::assertSame($expectedOutput, $actualOutput);
    }

    /**
     * @return array
     */
    public static function conver4To5tProvider()
    {
        return [
            [null, ''],
            ['', ''],
            [' ', ''],
            ['fa fa-cc-stripe', 'fab fa-cc-stripe'],
            ['fa-cc-stripe fa', 'fab fa-cc-stripe'],
            ['fa-cc-stripe', 'fab fa-cc-stripe'],
            ['cc-stripe', 'fab fa-cc-stripe'],
        ];
    }

    /**
     * @return \Concrete\Package\BlocksCloner\Conversion\FontAwesome
     */
    private static function getFontAwesome()
    {
        if (self::$fontAwesome === null) {
            self::$fontAwesome = app(FontAwesome::class);
        }

        return self::$fontAwesome;
    }
}
