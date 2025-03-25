<?php

use Concrete\Core\Entity\File\Version;
use Concrete\Core\Entity\Page\Feed as PageFeed;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Type\Type as PageType;
use Concrete\Package\BlocksCloner\XmlParser;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\BlocksCloner\Controller\Dialog\Export $controller
 * @var Concrete\Core\View\View $view
 * @var int $cID
 * @var string $xml
 * @var array $blockTypesAndPackages
 * @var array $references
 * @var Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface $resolverManager
 */

?>
<div id="ccm-blockscloker-export" style="display: flex; flex-direction: column; height: 100%;" v-cloak>
    <div style="max-height: 200px; overflow-y: scroll">
        <table class="table table-striped table-sm table-condensed caption-top">
            <caption><strong><?= t('Block Types')?></strong></caption>
            <colgroup>
                <col width="1" />
            </colgroup>
            <tbody>
                <?php
                foreach ($blockTypesAndPackages as $blockTypeAndPackage) {
                    extract($blockTypeAndPackage);
                    /**
                     * @var \Concrete\Core\Entity\Block\BlockType\BlockType $blockType
                     * @var \Concrete\Core\Entity\Package|null $package
                     */
                    ?>
                    <tr>
                        <td class="text-nowrap">
                            <?= h(t($blockType->getBlockTypeName())) ?><br />
                            <div class="small text-muted"><?= t('Handle: %s', '<code>' . h($blockType->getBlockTypeHandle()) . '</code>') ?></div>
                        </td>
                        <td>
                            <?php
                            if ($package === null) {
                                ?>
                                <i><?= t('Provided by %s', 'Concrete') ?></i>
                                <?php
                            } else {
                                ?>
                                <?= h(t('Provided by package %s', $package->getPackageName())) ?><br />
                                <div class="small text-muted"><?= t('Handle: %s', '<code>' . h($package->getPackageHandle()) . '</code>') ?></div>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
    if (!empty($references[XmlParser::KEY_PAGES])) {
        ?>
        <div style="max-height: 200px; overflow-y: scroll">
            <table class="table table-striped table-sm table-condensed caption-top">
                <caption><strong><?= t('Referenced Pages')?></strong></caption>
                <colgroup>
                    <col width="1" />
                    <col width="1" />
                </colgroup>
                <tbody>
                    <?php
                    foreach ($references[XmlParser::KEY_PAGES] as $key => $page) {
                        ?>
                        <tr>
                            <td style="white-space: nowrap">
                                <?php
                                if ($page instanceof Page) {
                                    ?>
                                    <a class="btn btn-sm btn-xs btn-primary" target="_blank" href="<?= h($page->getCollectionLink()) ?>"><?= t('Visit') ?></a>
                                    <?php
                                } else {
                                    ?>
                                    <button class="btn btn-sm btn-xs btn-primary" disabled><?= t('Visit') ?></button>
                                    <?php
                                }
                                ?>
                            </td>
                            <td style="white-space: nowrap">
                                <code><?= h($key) ?></code>
                            </td>
                            <td>
                                <?php
                                if ($page instanceof Page) {
                                    ?>
                                    <?= h($page->getCollectionName()) ?>
                                    <?php
                                } else {
                                    ?>
                                    <i><?= h($page) ?></i>
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    if (!empty($references[XmlParser::KEY_FILES])) {
        $fileIDs = [];
        foreach ($references[XmlParser::KEY_FILES] as $key => $fileVersion) {
            if ($fileVersion instanceof Version) {
                $fileIDs[] = $fileVersion->getFileID();
            }
        }
        ?>
        <div style="max-height: 200px; overflow-y: scroll">
            <table class="table table-striped table-sm table-condensed caption-top">
                <caption>
                    <strong><?= t('Used Files and Images') ?></strong>
                    <?php
                    if (count($fileIDs) > 1) {
                        ?>
                        - <a href="<?= h($resolverManager->resolve(['/ccm/blocks_cloner/dialogs/export/files']) . "?cID={$cID}&fIDs=" . implode(',', $fileIDs)) ?>"><?= t('Download All') ?></a>
                        <?php
                    }
                    ?>
                </caption>
                <colgroup>
                    <col width="1" />
                    <col width="1" />
                </colgroup>
                <tbody>
                    <?php
                    foreach ($references[XmlParser::KEY_FILES] as $key => $fileVersion) {
                        ?>
                        <tr>
                            <td style="white-space: nowrap">
                                <?php
                                if ($fileVersion instanceof Version) {
                                    ?>
                                    <a class="btn btn-sm btn-xs btn-primary" href="<?= h($resolverManager->resolve(['/ccm/blocks_cloner/dialogs/export/files']) . "?cID={$cID}&fIDs={$fileVersion->getFileID()}") ?>">
                                        <?= t('Download') ?>
                                    </a>
                                    <?php
                                } else {
                                    ?>
                                    <button class="btn btn-sm btn-xs btn-primary" disabled><?= t('Download') ?></button>
                                    <?php
                                }
                                ?>
                            </td>
                            <td style="white-space: nowrap">
                                <code><?= h($key) ?></code>
                            </td>
                            <td>
                                <?php
                                if ($fileVersion instanceof Version) {
                                    ?>
                                    <?= h($fileVersion->getFileName()) ?>
                                    <?php
                                } else {
                                    ?>
                                    <i><?= h($fileVersion) ?></i>
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    if (isset($references[XmlParser::KEY_PAGETYPES])) {
        ?>
        <div style="max-height: 200px; overflow-y: scroll">
            <table class="table table-striped table-sm table-condensed caption-top">
                <caption><strong><?= t('Referenced Page Types')?></strong></caption>
                <colgroup>
                    <col width="1" />
                </colgroup>
                <tbody>
                    <?php
                    foreach ($references[XmlParser::KEY_PAGETYPES] as $key => $pageType) {
                        ?>
                        <tr>
                            <td style="white-space: nowrap">
                                <code><?= h($key) ?></code>
                            </td>
                            <td>
                                <?php
                                if ($pageType instanceof PageType) {
                                    ?>
                                    <?= h($pageType->getPageTypeName()) ?>
                                    <?php
                                } else {
                                    ?>
                                    <i><?= h($pageType) ?></i>
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    if (isset($references[XmlParser::KEY_PAGEFEEDS])) {
        ?>
        <div style="max-height: 200px; overflow-y: scroll">
            <table class="table table-striped table-sm table-condensed caption-top">
                <caption><strong><?= t('Referenced RSS Page Feeds')?></strong></caption>
                <colgroup>
                    <col width="1" />
                </colgroup>
                <tbody>
                    <?php
                    foreach ($references[XmlParser::KEY_PAGEFEEDS] as $key => $pageFeed) {
                        ?>
                        <tr>
                            <td style="white-space: nowrap">
                                <code><?= h($key) ?></code>
                            </td>
                            <td>
                                <?php
                                if ($pageFeed instanceof PageFeed) {
                                    ?>
                                    <?= h($pageFeed->getFeedDisplayTitle('text')) ?>
                                    <?php
                                } else {
                                    ?>
                                    <i><?= h($pageFeed) ?></i>
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    ?>
    <div style="flex-grow: 1">
        <div style="display: flex; flex-direction: column; height: 100%;">
            <div>
                <strong style="color: #777777"><?= t('XML Data')?></strong>
            </div>
            <textarea
                ref="textarea"
                readonly
                nowrap
                spellcheck="false"
                class="form-control"
                style="flex-grow: 1; font-family: Menlo, Monaco, Consolas, 'Courier New', monospace; font-size: 0.9em; resize: none" v-bind:value="finalXml"
            ></textarea>
            <div class="text-end text-right" style="padding-top: 1rem">
                <button
                    class="btn"
                    ref="copy"
                    v-on:click.prevent="copy(false)"
                    v-bind:class='{
                        "btn-primary": !copyHighlighed,
                        "btn-success": copyHighlighed,
                    }'
                >
                    <span v-if="copyHighlighed"><?= t('Copied!') ?></span>
                    <span v-else><?= t('Copy') ?></span>
                </button>
                <button class="btn btn-primary" v-on:click.prevent="copy(true)"><?= h(t('Copy & Close')) ?></button>
            </div>
        </div>
    </div>
</div>

<script>$(document).ready(function() {

new Vue({
    el: '#ccm-blockscloker-export',
    data() {
        return {
            xml: <?= json_encode($xml) ?>,
            copyHighlighed: false,
        };
    },
    mounted() {
        setTimeout(() => this.$refs.textarea.focus(), 50);
    },
    computed: {
        finalXml() {
            return window.ccmBlocksCloner.envirorment.addCurrentToXml(this.xml);
        },
    },
    methods: {
        copy(andClose) {
            try {
                if (window.navigator && window.navigator.clipboard && window.navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(this.finalXml)
                        .then(() => this.reportCopyResult(null, andClose))
                        .catch((e) => this.reportCopyResult(e || <?= json_encode(t('Unknown error')) ?>))
                    ;
                } else {
                    this.$refs.textarea.select();
                    document.execCommand('copy');
                    this.reportCopyResult(null, andClose);
                }
            } catch (e) {
                this.reportCopyResult(e || <?= json_encode(t('Unknown error')) ?>);
            }
        },
        reportCopyResult(error, closeOnSuccess) {
            if (error) {
                window.ConcreteAlert.error({
                    message: e.message || e,
                });
            } else if(closeOnSuccess) {
                window.ConcreteAlert.info({
                    message: <?= json_encode(t('Copied')) ?>,
                    delay: 500,
                });
                $.fn.dialog.closeTop();
            } else {
                this.copyHighlighed = true;
                setTimeout(() => this.copyHighlighed = false, 500);
            } 
        }
    },
});

});</script>
