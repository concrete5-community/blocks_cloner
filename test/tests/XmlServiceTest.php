<?php

namespace BlocksCloner\Test;

use Concrete\Package\BlocksCloner\Xml;
use PHPUnit\Framework\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

class XmlServiceTest extends TestCase
{
    /**
     * @var \Concrete\Package\BlocksCloner\Xml|null
     */
    private static $xmlService = null;

    public function testLoading()
    {
        $xmlService = self::getXmlService();
        $xml = '<root />';

        $sx = $xmlService->getSimpleXMLElement($xml);
        static::assertInstanceOf(\SimpleXMLElement::class, $sx);
        $doc = $xmlService->getDOMDocument($xml);
        static::assertInstanceOf(\DOMDocument::class, $doc);

        static::assertSame($sx, $xmlService->getSimpleXMLElement($sx));
        static::assertInstanceOf(\DOMDocument::class, $xmlService->getDOMDocument($sx));

        static::assertSame($doc, $xmlService->getDOMDocument($doc));
        static::assertInstanceOf(\SimpleXMLElement::class, $xmlService->getSimpleXMLElement($sx));
    }

    /**
     * @dataProvider normalizeProvider
     *
     * @param string $baseName
     */
    public function testNormalize($baseName)
    {
        $inFile = BC_ROOT_DIR . "/{$baseName}-in.xml";
        $inXml = @file_get_contents($inFile);
        static::assertTrue(is_string($inXml), "Failed to load file {$baseName}-in.xml");
        $outFile = BC_ROOT_DIR . "/{$baseName}-out.xml";
        $outXml = @file_get_contents($outFile);
        static::assertTrue(is_string($outXml), "Failed to load file {$baseName}-out.xml");
        $actualXml = $this->getXmlService()->normalize($inXml);
        static::assertSame($outXml, $actualXml);
    }

    /**
     * @return array
     */
    public static function normalizeProvider()
    {
        $relDir = 'test/assets/xml/normalize';
        $result = [];
        $matches = null;
        foreach (scandir(BC_ROOT_DIR . '/' . $relDir) as $item) {
            if (preg_match('{^(?<filePrefix>[^.].*)-(in|out)\.xml$}', $item, $matches)) {
                $item = [$relDir . '/' . $matches['filePrefix']];
                if (!in_array($item, $result, true)) {
                    $result[] = $item;
                }
            }
        }

        return $result;
    }

    /**
     * @return \Concrete\Package\BlocksCloner\Xml
     */
    private static function getXmlService()
    {
        if (self::$xmlService === null) {
            self::$xmlService = app(Xml::class);
        }

        return self::$xmlService;
    }
}
