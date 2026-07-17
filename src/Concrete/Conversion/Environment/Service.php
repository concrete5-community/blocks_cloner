<?php

namespace Concrete\Package\BlocksCloner\Conversion\Environment;

use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Package\PackageService;
use Concrete\Package\BlocksCloner\Conversion\Environment;
use Concrete\Package\BlocksCloner\Xml;

defined('C5_EXECUTE') or die('Access Denied.');

final class Service
{
    const XML_ENVIRONMENT_PREFIX = 'Environment:';

    const UNICODE_SOFT_HYPHEN_UTF8 = "\xC2\xAD";

    /**
     * @var \Concrete\Core\Package\PackageService
     */
    private $packageService;

    /**
     * @var \Concrete\Package\BlocksCloner\Xml
     */
    private $xmlService;

    /**
     * @var \Concrete\Package\BlocksCloner\Conversion\Environment|null
     */
    private $currentEnvironment = null;

    public function __construct(PackageService $packageService, Xml $xmlService)
    {
        $this->packageService = $packageService;
        $this->xmlService = $xmlService;
    }

    /**
     * @return \Concrete\Package\BlocksCloner\Conversion\Environment
     */
    public function getCurrentEnvironment()
    {
        if ($this->currentEnvironment === null) {
            $packagesAndVersions = [];
            foreach ($this->packageService->getInstalledList() as $package) {
                $packagesAndVersions[$package->getPackageHandle()] = $package->getPackageVersion();
            }
            $this->currentEnvironment = new Environment(APP_VERSION, $packagesAndVersions);
        }

        return $this->currentEnvironment;
    }

    /**
     * @return void
     */
    public function addCurrentEnvironmentToDoc(\DOMDocument $doc)
    {
        $data = json_encode($this->getCurrentEnvironment(), JSON_UNESCAPED_SLASHES);
        $data = ' ' . self::XML_ENVIRONMENT_PREFIX . ' ' . str_replace('--', '-' . self::UNICODE_SOFT_HYPHEN_UTF8 . '-', $data) . ' ';
        $comment = $doc->createComment($data);
        $doc->appendChild($comment);
    }

    /**
     * @param string $xml
     *
     * @return string
     */
    public function addCurrentEnvironmentToXml($xml)
    {
        $data = json_encode($this->getCurrentEnvironment(), JSON_UNESCAPED_SLASHES);
        $comment = '<!-- ' . self::XML_ENVIRONMENT_PREFIX . ' ' . str_replace('--', '-' . self::UNICODE_SOFT_HYPHEN_UTF8 . '-', $data) . ' -->';

        return rtrim($xml) . "\n" . $comment;
    }

    /**
     * @param string|\SimpleXMLElement|\DOMDocument $xml
     *
     * @return \Concrete\Package\BlocksCloner\Conversion\Environment|null
     */
    public function extractEnvironmentFromXml($xml)
    {
        $doc = $this->xmlService->getDOMDocument($xml);
        $xpath = new \DOMXPath($doc);
        $comments = $xpath->query('//comment()');
        $result = null;
        foreach ($comments as $comment) {
            $environment = $this->extractEnvironmentFromComment($comment);
            if ($environment === null) {
                continue;
            }
            if ($result !== null) {
                throw new UserMessageException(t('Multiple environment comments found'));
            }
            $result = $environment;
        }

        return $result;
    }

    /**
     * @return \Concrete\Package\BlocksCloner\Conversion\Environment|null
     */
    private function extractEnvironmentFromComment(\DOMComment $comment)
    {
        $commentText = trim((string) $comment->textContent);
        if (strpos($commentText, self::XML_ENVIRONMENT_PREFIX) !== 0) {
            return null;
        }
        $commentText = ltrim(substr($commentText, strlen(self::XML_ENVIRONMENT_PREFIX)));
        $commentText = str_replace(self::UNICODE_SOFT_HYPHEN_UTF8, '', $commentText);
        $parsed = json_decode($commentText);
        if (!is_object($parsed)) {
            return null;
        }
        $coreVersion = isset($parsed->core) ? $parsed->core : '';
        $coreVersion = is_string($coreVersion) ? trim($coreVersion) : '';
        if ($coreVersion === '') {
            return null;
        }
        $rawPackages = isset($parsed->packages) ? $parsed->packages : null;
        if (!is_object($rawPackages)) {
            return null;
        }
        $packagesAndVersions = [];
        foreach ($rawPackages as $handle => $version) {
            if (!is_string($handle) || ($handle = trim($handle)) === '' || !is_string($version) || ($version = trim($version)) === '') {
                continue;
            }
            $packagesAndVersions[$handle] = $version;
        }

        return new Environment($coreVersion, $packagesAndVersions);
    }
}
