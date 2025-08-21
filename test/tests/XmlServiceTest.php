<?php

namespace BlocksCloner;

use Concrete\Package\BlocksCloner\Xml;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

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
        $this->assertInstanceOf(SimpleXMLElement::class, $sx);
        $doc = $xmlService->getDOMDocument($xml);
        $this->assertInstanceOf(DOMDocument::class, $doc);

        $this->assertSame($sx, $xmlService->getSimpleXMLElement($sx));
        $this->assertInstanceOf(DOMDocument::class, $xmlService->getDOMDocument($sx));

        $this->assertSame($doc, $xmlService->getDOMDocument($doc));
        $this->assertInstanceOf(SimpleXMLElement::class, $xmlService->getSimpleXMLElement($sx));
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
        $this->assertTrue(is_string($inXml), "Failed to load file {$baseName}-in.xml");
        $outFile = BC_ROOT_DIR . "/{$baseName}-out.xml";
        $outXml = @file_get_contents($outFile);
        $this->assertTrue(is_string($outXml), "Failed to load file {$baseName}-out.xml");
        $actualXml = $this->getXmlService()->normalize($inXml);
        $this->assertSame($outXml, $actualXml);
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
