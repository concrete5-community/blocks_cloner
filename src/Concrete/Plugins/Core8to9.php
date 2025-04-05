<?php

namespace Concrete\Package\BlocksCloner\Plugins;

use Concrete\Package\BlocksCloner\Converter\ApplicableTo;
use Concrete\Package\BlocksCloner\Converter\Import;
use Concrete\Package\BlocksCloner\Converter\Import\BlockType;
use Concrete\Package\BlocksCloner\Plugin\ConvertImport;

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
        $import = new Import(
            t('From concrete5 v8'),
            new ApplicableTo\Core('^8', '>=9')
        );

        $import
            ->addBlockType(
                'event_list',
                BlockType::create()
                    ->addRecordFields('btEventList', ['titleFormat' => 'h5'])
            )
            ->addBlockType(
                'express_entry_list',
                BlockType::create()
                    ->addRecordFields('btExpressEntryList', ['titleFormat' => 'h2'])
            )
            ->addBlockType(
                'feature',
                BlockType::create()
                    ->addRecordFields('btFeature', ['titleFormat' => 'h4'])
                    ->fontAwesome4to5Fields('btFeature', ['icon'])
            )
            ->addBlockType(
                'google_map',
                BlockType::create()
                    ->addRecordFields('btGoogleMap', ['titleFormat' => 'h3'])
            )
            ->addBlockType(
                'page_list',
                BlockType::create()
                    ->addRecordFields('btPageList', ['titleFormat' => 'h5'])
            )
            ->addBlockType(
                'rss_displayer',
                BlockType::create()
                    ->addRecordFields('btRssDisplay', ['titleFormat' => 'h5'])
            )
            ->addBlockType(
                'tags',
                BlockType::create()
                    ->addRecordFields('btTags', ['titleFormat' => 'h5'])
            )
            ->addBlockType(
                'topic_list',
                BlockType::create()
                    ->addRecordFields('btTopicList', ['titleFormat' => 'h5'])
            )
        ;

        return [$import];
    }
}
