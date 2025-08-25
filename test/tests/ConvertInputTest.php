<?php

namespace BlocksCloner\Test;

use Concrete\Package\BlocksCloner\Conversion\Environment;
use Concrete\Package\BlocksCloner\Plugin;
use Concrete\Package\BlocksCloner\Xml;
use PHPUnit\Framework\TestCase;

class ConvertInputTest extends TestCase
{
    const ASSETS_REL_DIR = 'test/assets/xml/convert-input';

    /**
     * @var \Concrete\Package\BlocksCloner\Xml|null
     */
    private static $xmlService = null;

    /**
     * @var \Concrete\Package\BlocksCloner\Conversion\Environment\Service|null
     */
    private static $environmentService = null;

    /**
     * @dataProvider convertProvider
     *
     * @param string $baseName
     */
    public function testConvert($baseName)
    {
        $targetEnvironment = $this->readTargetEnvironment("{$baseName}.json");
        $sx = $this->readSimpleXml("{$baseName}-in.xml");
        $sourceEnvironment = self::getEnvironmentService()->extractEnvironmentFromXml($sx);
        $this->assertNotNull($sourceEnvironment, "The file {$baseName}-in.xml doesn't contain the source environment");
        $expectedXml = $this->readXml("{$baseName}-out.xml");
        $pluginManager = app(Plugin\Manager::class);
        array_map(
            static function (Plugin\ConvertImport $plugin) use ($sx, $sourceEnvironment, $targetEnvironment) {
                $plugin->applyImportConvertersByEnvironment($sx, $sourceEnvironment, $targetEnvironment);
            },
            $pluginManager->getConvertImportPlugins()
        );
        $xmlService = self::getXmlService();
        $this->assertSame($expectedXml, $xmlService->normalize($sx));
    }

    /**
     * @return array
     */
    public static function convertProvider()
    {
        $result = [];
        self::listTestFilesIn('', $result);

        return $result;
    }

    /**
     * @return void
     */
    private static function listTestFilesIn($parentRelativePath, array &$result)
    {
        $matches = null;
        foreach (scandir(BC_ROOT_DIR . '/' . self::ASSETS_REL_DIR . '/' . $parentRelativePath) as $item) {
            if ($item[0] === '.') {
                continue;
            }
            $itemRelativePath = ($parentRelativePath === '' ? '' : "{$parentRelativePath}/") . $item;
            if (is_dir(BC_ROOT_DIR . '/' . self::ASSETS_REL_DIR . '/' . $itemRelativePath)) {
                self::listTestFilesIn($itemRelativePath, $result);
            } elseif (preg_match('{^(?<baseName>[^.].*)((-(in|out)\.xml)|\.json)$}', $itemRelativePath, $matches)) {
                $item = [$matches['baseName']];
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

    /**
     * @return \Concrete\Package\BlocksCloner\Conversion\Environment\Service
     */
    private static function getEnvironmentService()
    {
        if (self::$environmentService === null) {
            self::$environmentService = app(Environment\Service::class);
        }

        return self::$environmentService;
    }

    /**
     * @param string $fileRelative
     *
     * @return \Concrete\Package\BlocksCloner\Conversion\Environment
     */
    private function readTargetEnvironment($fileRelative)
    {
        $file = BC_ROOT_DIR . '/' . self::ASSETS_REL_DIR . '/' . $fileRelative;
        $json = @file_get_contents($file);
        $this->assertTrue(is_string($json), "Failed to load file {$fileRelative}");
        $data = @json_decode($json, true);
        $this->assertTrue(is_array($data), "Failed to decode the file {$fileRelative}");
        $this->assertTrue(
            isset($data['targetEnvironment']['core']) && is_string($data['targetEnvironment']['core']),
            "The file {$fileRelative} is missing the targetEnvironment.core key (or it's not a string)"
        );
        $this->assertTrue(
            isset($data['targetEnvironment']['packages']) && is_array($data['targetEnvironment']['packages']),
            "The file {$fileRelative} is missing the targetEnvironment.core packages (or it's not an array)"
        );

        return new Environment($data['targetEnvironment']['core'], $data['targetEnvironment']['packages']);
    }

    /**
     * @param string $fileRelative
     *
     * @return string
     */
    private function readXml($fileRelative)
    {
        $file = BC_ROOT_DIR . '/' . self::ASSETS_REL_DIR . '/' . $fileRelative;
        $xml = @file_get_contents($file);
        $this->assertTrue(is_string($xml), "Failed to load file {$fileRelative}");

        return $xml;
    }

    /**
     * @param string $fileRelative
     *
     * @return \SimpleXMLElement
     */
    private function readSimpleXml($fileRelative)
    {
        return self::getXmlService()->getSimpleXMLElement($this->readXml($fileRelative));
    }
}
