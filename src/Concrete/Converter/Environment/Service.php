<?php

namespace Concrete\Package\BlocksCloner\Converter\Environment;

use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Package\PackageService;
use Concrete\Package\BlocksCloner\Converter\Environment;
use DOMComment;
use DOMDocument;
use DOMXPath;
use RuntimeException;
use SimpleXMLElement;

defined('C5_EXECUTE') or die('Access Denied.');

final class Service
{
    const XML_ENVIRONMENT_PREFIX = 'Environment:';

    const UNICODE_SOFT_HYPHEN_UTF8 = "\xC2\xAD";

    /**
     * @var \Concrete\Package\BlocksCloner\Converter\Environment\Service|null
     */
    private static $instance = null;

    /**
     * @var \Concrete\Package\BlocksCloner\Converter\Environment|null
     */
    private $currentEnvironment = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return \Concrete\Package\BlocksCloner\Converter\Environment
     */
    public function getCurrentEnvironment()
    {
        if ($this->currentEnvironment === null) {
            $packageService = app(PackageService::class);
            $packagesAndVersions = [];
            foreach ($packageService->getInstalledList() as $package) {
                $packagesAndVersions[$package->getPackageHandle()] = $package->getPackageVersion();
            }
            $this->currentEnvironment = new Environment(APP_VERSION, $packagesAndVersions);
        }

        return $this->currentEnvironment;
    }

    /**
     * @return void
     */
    public function addCurrentEnvironmentToDoc(DOMDocument $doc)
    {
        $data = json_encode($this->getCurrentEnvironment(), JSON_UNESCAPED_SLASHES);
        $data = ' ' . self::XML_ENVIRONMENT_PREFIX . ' ' . str_replace('--', '-' . self::UNICODE_SOFT_HYPHEN_UTF8 . '-', $data) . ' ';
        $comment = $doc->createComment($data);
        $doc->appendChild($comment);
    }

    /**
     * @param string|\SimpleXMLElement|\DOMDocument $xml
     *
     * @return \Concrete\Package\BlocksCloner\Converter\Environment|null
     */
    public function extractEnvironmentFromXml($xml)
    {
        $doc = $this->ensureDOMDocument($xml);
        $xpath = new DOMXPath($doc);
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
     * @param string|\SimpleXMLElement|\DOMDocument $xml
     *
     * @return \DOMDocument
     */
    private function ensureDOMDocument($xml)
    {
        if ($xml instanceof DOMDocument) {
            return $xml;
        }
        if ($xml instanceof SimpleXMLElement) {
            $el = dom_import_simplexml($xml);

            return $el->ownerDocument ?: $el;
        }
        if (!is_string($xml)) {
            throw new RuntimeException(t('Invalid type of parameter %1$s of function %2$s', '$xml', __METHOD__));
        }
        $doc = new DOMDocument();
        if (!$doc->loadXML($xml, LIBXML_NOERROR | LIBXML_NOWARNING)) {
            throw new UserMessageException(t('Invalid XML received'));
        }

        return $doc;
    }

    /**
     * @return \Concrete\Package\BlocksCloner\Converter\Environment|null
     */
    private function extractEnvironmentFromComment(DOMComment $comment)
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
