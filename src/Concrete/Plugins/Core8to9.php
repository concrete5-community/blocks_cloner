<?php

namespace Concrete\Package\BlocksCloner\Plugins;

use Concrete\Package\BlocksCloner\Conversion\Environment;
use Concrete\Package\BlocksCloner\Converter;
use Concrete\Package\BlocksCloner\Plugin\ConvertImport;
use SimpleXMLElement;

defined('C5_EXECUTE') or die('Access Denied.');

class Core8To9 implements ConvertImport
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\Plugin\ConvertImport::getImportConverters()
     */
    public function getImportConverters()
    {
        return [
            new Converter\Description('from_core8', t('From concrete5 v8')),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\Plugin\ConvertImport::applyImportConverterByHandle()
     */
    public function applyImportConverterByHandle(SimpleXMLElement $xDocument, $handle)
    {
        if ($handle !== 'from_core8') {
            return false;
        }
        $this->convertFrom8($xDocument);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\Plugin\ConvertImport::applyImportConvertersByEnvironment()
     */
    public function applyImportConvertersByEnvironment(SimpleXMLElement $xDocument, Environment $sourceEnvironment, Environment $targetEnvironment)
    {
        if (!preg_match('/^8(\.|$)/', $sourceEnvironment->getCoreVersion()) || !preg_match('/^(9|([1-9]\d+))(\.|$)/', $targetEnvironment->getCoreVersion())) {
            return;
        }
        $this->convertFrom8($xDocument);
    }

    private function convertFrom8(SimpleXMLElement $xDocument)
    {
        $converter = new Converter($xDocument);
        $converter
            ->blocks('event_list')
                ->table('btEventList')
                    ->addField('titleFormat', 'h5')
                ->done()
            ->done()
            ->blocks('event_list')
                ->table('btEventList')
                    ->addField('titleFormat', 'h5')
                ->done()
            ->done()
            ->blocks('express_entry_list')
                ->table('btExpressEntryList')
                    ->addField('titleFormat', 'h2')
                ->done()
            ->done()
            ->blocks('feature')
                ->table('btFeature')
                    ->addField('titleFormat', 'h4')
                    ->convertFontAwesome4to5Field('icon')
                ->done()
            ->done()
            ->blocks('google_map')
                ->table('btGoogleMap')
                    ->addField('titleFormat', 'h3')
                ->done()
            ->done()
            ->blocks('page_list')
                ->table('btPageList')
                    ->addField('titleFormat', 'h5')
                ->done()
            ->done()
            ->blocks('rss_displayer')
                ->table('btRssDisplay')
                    ->addField('titleFormat', 'h5')
                ->done()
            ->done()
            ->blocks('tags')
                ->table('btTags')
                    ->addField('titleFormat', 'h5')
                ->done()
            ->done()
            ->blocks('topic_list')
                ->table('btTopicList')
                    ->addField('titleFormat', 'h5')
                ->done()
            ->done()
        ;
    }
}
