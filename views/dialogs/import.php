<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\BlocksCloner\Controller\Dialog\Import $controller
 * @var Concrete\Core\View\View $view
 * @var int $cID
 * @var Concrete\Core\Area\Area|null $area
 * @var bool $isImportingIntoStack
 * @var Concrete\Core\Validation\CSRF\Token $token
 */

$view->element('vue/references_viewer', null, 'blocks_cloner');
$view->element('vue/diff_viewer', null, 'blocks_cloner');

?>
<div class="ccm-blockscloker-dialog-content" id="ccm-blockscloker-import" style="display: flex; height: 100%; width: 100%" v-cloak>
    <div v-if="page === PAGE.INPUT" style="display: flex; flex-direction: column; width: 100%; height: 100%;">
        <div style="flex-grow: 1; display: flex; flex-direction: row">
            <div style="display: flex; flex-direction: column; height: 100%; flex: 1; padding-right: 10px;">
                <div>
                    <?= $area === null ? t('Paste here the XML of the data to be added to the page') : t('Paste here the XML of the data to be added to the area') ?>
                </div>
                <textarea
                    class="form-control"
                    v-model.trim="inputXml"
                    ref="inputXml"
                    nowrap
                    spellcheck="false"
                    v-bind:readonly="busy"
                    style="flex-grow: 1; font-family: Menlo, Monaco, Consolas, 'Courier New', monospace; font-size: 0.9em; resize: none"
                ></textarea>
                <div v-if="inputXml && xmlInputError" class="alert alert-danger" v-html="xmlInputError" style="white-space: pre-wrap"></div>
                <div v-else-if="analyzeError" class="alert alert-danger" style="white-space: pre-wrap">{{ analyzeError }}</div>
            </div>
            <div style="display: flex; flex-direction: column">
                <?php
                if ($area !== null) {
                    ?>
                    <div>
                        <?= t('Insert blocks') ?>
                    </div>
                    <select
                        class="form-control"
                        v-model="addBefore"
                        size="2"
                        v-bind:disabled="busy"
                        style="flex-grow: 1; width: 100%"
                    >
                        <option v-for="b in existingBlocksInArea" v-bind:value="b"><?= t('Before the %s block', '{{ b.displayName }}') ?></option>
                        <option v-bind:value="null"><?= t('At the end of the area') ?></option>
                    </select>
                    <?php
                }
                ?>
                <div>
                    <?= t('Conversion') ?>
                </div>
                <div style="flex-grow: 0; width: 100%">
                    <div v-if="allConverters.length === 0" class="alert alert-info">
                        <?= t('No converters available') ?>
                    </div>
                    <div v-else>
                        <select
                            class="form-control"
                            v-model="conversionMode"
                            v-bind:disabled="busy"
                        >
                            <option v-bind:value="CONVERSION_MODE.NONE"><?= t('No Conversion') ?></option>
                            <option v-bind:value="CONVERSION_MODE.AUTO"><?= t('Automatic Converter Selection') ?></option>
                            <option v-bind:value="CONVERSION_MODE.MANUAL"><?= t('Manual Converter Selection') ?></option>
                        </select>
                        <div v-bind:style="{visibility: conversionMode === CONVERSION_MODE.MANUAL ? '' : 'hidden'}">
                            <div v-for="c in allConverters" class="form-check">
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    v-bind:value="c"
                                    v-bind:id="`ccm-blockscloker-import-converter-${c.handle}`"
                                    v-model="selectedConverters"
                                    v-bind:disabled="busy"
                                />
                                <label class="form-check-label" v-bind:for="`ccm-blockscloker-import-converter-${c.handle}`">
                                    {{ c.name }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-right text-end">
            <button v-on:click.prevent="analyzeXml()" v-bind:disabled="busy || !inputXml || !!xmlInputError" class="btn btn-primary"><?= t('Analyze') ?></button>
        </div>
    </div>
    <div v-else-if="page === PAGE.CHECK" style="display: flex; flex-direction: column; width: 100%; height: 100%;">
        <blocks-cloner-references-viewer
            v-bind:cid="<?= $cID ?>"
            v-bind:references="references"
            operation="import"
            v-on:files-upload-started="busy = true"
            v-on:files-upload-completed="filesUploadCompleted"
            v-on:change="analyzeXml()"
            v-bind:busy="busy"
        ></blocks-cloner-references-viewer>
        <div class="text-left text-start">
            <button v-on:click.prevent="page = PAGE.DIFF" v-bind:disabled="busy || !inputXmlHasBeenConverted" class="btn btn-secondary btn-default"><?= t('View Conversion') ?></button>
            <span class="text-right text-end" style="float: right">
                <button v-on:click.prevent="page = PAGE.INPUT" v-bind:disabled="busy" class="btn btn-secondary btn-default"><?= t('Back') ?></button>
                <button v-on:click.prevent="analyzeXml()" v-bind:disabled="busy" class="btn btn-secondary btn-default"><?= t('Reanalyze') ?></button>
                <button v-on:click.prevent="importXml()" v-bind:disabled="!canImport" class="btn btn-primary"><?= t('Import') ?></button>
            </span>
        </div>
    </div>
    <div v-else-if="page === PAGE.DIFF" style="display: flex; flex-direction: column; width: 100%; height: 100%;">
        <blocks-cloner-diff-viewer
            v-bind:left="inputXmlNormalized"
            v-bind:right="processedXml"
        ></blocks-cloner-diff-viewer>
        <div class="text-left text-start">
            <button v-on:click.prevent="page = PAGE.CHECK" class="btn btn-primary"><?= t('Back') ?></button>
        </div>
    </div>
</div>
<script>$(document).ready(function() {

<?php
if ($area !== null) {
    ?>
    function getExistingBlocksInArea()
    {
        let area = null;
        function walk(item) {
            if (item.type === 'area' && item.id === <?= (int) $area->getAreaID() ?>) {
                area = item;
                return true;
            }
            return item.children.some(walk);
        }
        window.ccmBlocksCloner.getPageStructure().some(walk);
        const blocks = [];
        area?.children.forEach((child) => {
            if (child.type === 'block') {
                blocks.push({
                    id: child.id,
                    handle: child.handle,
                    displayName: child.displayName,
                });
            }
        });

        return blocks;
    };
    <?php
}
?>
new Vue({
    el: '#ccm-blockscloker-import',
    data() {
        const PAGE = {
            INPUT: 1,
            CHECK: 2,
            DIFF: 3,
        };
        const CONVERSION_MODE = {
            NONE: 'none',
            AUTO: 'auto',
            MANUAL: 'manual',
        }
        return {
            PAGE,
            ICON: {
                // HEAVY CHECK MARK
                GOOD: '\u2705',
                // CROSS MARK
                BAD: '\u274c',
            },
            page: PAGE.INPUT,
            <?php
            if ($area !== null) {
                ?>
                existingBlocksInArea: getExistingBlocksInArea(),
                addBefore: null,
                <?php
            }
            ?>
            busy: false,
            inputXml: '',
            inputXmlNormalized: '',
            CONVERSION_MODE,
            conversionMode: CONVERSION_MODE.AUTO,
            selectedConverters: [],
            processedXml: '',
            analyzeError: '',
            importSubject: '',
            importToken: '',
            references: {},
        };
    },
    mounted() {
        this.$nextTick(() => this.$refs.inputXml?.focus());
        const unloadHook = (e) => {
            if (this.busy) {
                e.preventDefault();
            }
        };
        window.addEventListener('beforeunload', unloadHook);
        $('#ccm-blockscloker-import').closest('.ui-dialog-content').dialog('option', 'beforeClose', () => {
            if (this.busy) {
                return false;
            }
            window.removeEventListener('beforeunload', unloadHook);
        });
    },
    watch: {
        inputXml() {
            this.analyzeError = '';
            this.inputXmlNormalized = '';
            this.processedXml = '';
        },
    },
    computed: {
        xmlInputError() {
            if (this.inputXml === '') {
                return <?= json_encode(t('Please specify the XML to be imported')) ?>;
            }
            try {
                const doc = window.ccmBlocksCloner.xml.parse(this.inputXml);
                switch (doc.documentElement.tagName) {
                    case 'block':
                        <?php
                        if ($area === null) {
                            ?>
                            throw new Error(<?= json_encode(t('In order to import area contents you have to specify the target area')) ?>);
                            <?php
                        } else {
                            ?>
                            if (!doc.documentElement.getAttribute('type')) {
                                throw new Error(<?= json_encode(t("The XML is invalid (it doesn't specify the block type)")) ?>);
                            }
                            break;
                            <?php
                        }
                        ?>
                    case 'area':
                        <?php
                        if ($area === null) {
                            ?>
                            throw new Error(<?= json_encode(t('In order to import area contents you have to specify the target area')) ?>);
                            <?php
                        } else {
                            ?>
                            break;
                            <?php
                        }
                        ?>
                    case 'attributes':
                        <?php
                        if ($isImportingIntoStack) {
                            ?>
                            throw new Error(<?= json_encode(t("It's not possible to import stack attributes")) ?>);
                            <?php
                        } else {
                            ?>
                            break;
                            <?php
                        }
                        ?>
                    case 'attributekey':
                        <?php
                        if ($isImportingIntoStack) {
                            ?>
                            throw new Error(<?= json_encode(t("It's not possible to import stack attributes")) ?>);
                            <?php
                        } else {
                            ?>
                            if (!doc.documentElement.getAttribute('handle')) {
                                throw new Error(<?= json_encode(t("The XML is invalid (it doesn't specify the attribute handle)")) ?>);
                            }
                            break;
                            <?php
                        }
                        ?>
                    default:
                        throw new Error(<?= json_encode(t('The XML does not represent neither an area content nor page attributes in ConcreteCMS CIF Format')) ?>);
                }
            } catch (e) {
                return e?.message || e?.toString() || <?= json_encode(t('Unknown error')) ?>;
            }
            return '';
        },
        allConverters() {
            return window.ccmBlocksCloner.conversion.getConverters();
        },
        inputXmlHasBeenConverted() {
            return this.inputXmlNormalized && this.processedXml && this.inputXmlNormalized !== this.processedXml;
        },
        canImport() {
            if (this.busy || !this.processedXml) {
                return false;
            }
            if (this.references.blockTypes) {
                if (Object.values(this.references.blockTypes).some((blockType) => blockType.error)) {
                    return false;
                }
            }
            if (this.references.pageAttributes) {
                if (Object.values(this.references.pageAttributes).some((pageAttribute) => pageAttribute.error)) {
                    return false;
                }
            }
            return true;
        },
    },
    methods: {
        async analyzeXml() {
            if (this.busy) {
                return false;
            }
            this.analyzeError = '';
            if (!this.inputXml || this.xmlInputError) {
                this.$refs.inputXml?.focus();
                return;
            }
            this.busy = true;
            try {
                const request = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        Accept: 'application/json',
                    },
                    body: new URLSearchParams([
                        ['__ccm_consider_request_as_xhr', '1'],
                        <?php
                        if ($area !== null) {
                            ?>
                            ['aID', <?= $area->getAreaID() ?>],
                            ['aHandle', <?= json_encode($area->getAreaHandle()) ?>],
                            <?php
                        }
                        ?>
                        ['conversionMode', this.conversionMode],
                        ['selectedConverters', this.selectedConverters.map((c) => c.handle).join(' ')],
                        ['xml', this.inputXml],
                    ]),
                };
                const response = await window.fetch(
                    `${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/import/analyze?cID=<?= $cID ?>`,
                    request
                );
                const responseData = await window.ccmBlocksCloner.service.parseJsonResponse(response);
                this.importToken = responseData.importToken;
                this.importSubject = responseData.importSubject;
                this.references = responseData.references;
                this.inputXmlNormalized = responseData.xmlNormalized;
                this.processedXml = responseData.processedXml;
            } catch (e) {
                this.analyzeError = e?.message || e?.toString() || <?= json_encode(t('Unknown error')) ?>;
                return;
            } finally {
                this.busy = false;
            }
            this.page = this.PAGE.CHECK;
        },
        filesUploadCompleted(withSuccess) {
            this.busy = false;
            if (withSuccess) {
                this.analyzeXml();
            }
        },
        async importXml() {
            if (this.busy || !this.canImport) {
                return false;
            }
            this.busy = true;
            let imported = false;
            let ccmBlockBefore = null;
            try {
                const ccmEditMode = window.Concrete.getEditMode();
                <?php
                if ($area !== null) {
                    ?>
                    const ccmArea = ccmEditMode.getAreaByID(<?= $area->getAreaID() ?>);
                    if (!ccmArea) {
                        throw new Error(<?= json_encode(t('Unable to find the requested area')) ?>);
                    }
                    ccmBlockBefore = this.addBefore ? ccmEditMode.getBlockByID(this.addBefore.id) : null;
                    if (this.addBefore !== null && !ccmBlockBefore) {
                        throw new Error(<?= json_encode(t('Unable to find the requested block')) ?>);
                    }
                    <?php
                }
                ?>
                const importResponse = await window.fetch(
                    `${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/import/${this.importSubject}?cID=<?= $cID ?>`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            Accept: 'application/json',
                        },
                        body: new URLSearchParams([
                            [<?= json_encode($token::DEFAULT_TOKEN_NAME) ?>, this.importToken],
                            ['xml', this.processedXml],
                            <?php
                            if ($area !== null) {
                                ?>
                                ['areaHandle', <?= json_encode($area->arHandle) ?>],
                                ['beforeBlockID', this.addBefore?.id || ''],
                                <?php
                            }
                            ?>
                            ['__ccm_consider_request_as_xhr', '1'],
                        ]),
                    }
                );
                const importResult = await window.ccmBlocksCloner.service.parseJsonResponse(importResponse);
                imported = true;
                if (importResult.oldAreaStyleInlineStylesetID) {
                    document.querySelector(`head style[data-style-set="${importResult.oldAreaStyleInlineStylesetID}"]`)?.remove();
                }
                if (importResult.newAreaHtmlStyleElement) {
                    document.head.insertAdjacentHTML('beforeend', importResult.newAreaHtmlStyleElement);
                }
                if (importResult.newAreaContainerClass) {
                    ccmArea.getElem().addClass(importResult.newAreaContainerClass);
                }
                if (importResult.newBlockIDs) {
                    const newBlockIDs = importResult.newBlockIDs;
                    const newBlocksHtml = await Promise.all(newBlockIDs.map((newBlockID) => this.loadNewBlockHtml(ccmArea, newBlockID)));
                    for (let intex = 0; intex < newBlocksHtml.length; intex++) {
                        const newCCMBlock = await this.renderNewBlockHtml(ccmArea, newBlocksHtml[intex].blockID, newBlocksHtml[intex].html, ccmBlockBefore);
                        ccmBlockBefore = newCCMBlock;
                    }
                    await this.refreshDesigns(ccmArea, newBlockIDs, false);
                }
                if (importResult.message) {
                    window.ConcreteAlert.info({
                        message: importResult.message,
                    });
                }
            } catch (e) {
                window.ConcreteAlert.error({
                    message: e?.message || e?.toString() || <?= json_encode(t('Unknown error')) ?>,
                    delay: 5000,
                });
                if (imported) {
                    setTimeout(() => {
                        if (window.confirm(<?= json_encode(implode("\n", [
                            t('The data has been imported, but an error occurred while rendering it.'),
                            '',
                            t('To avoid problems, please reload the page.'),
                            t('Do you want to do it now?'),
                        ])) ?>)) {
                            window.location.reload();
                        }
                    }, 500);
                }
                return;
            } finally {
                this.busy = false;
            }
            $.fn.dialog.closeTop();
            window.ConcretePanelManager.getByIdentifier('blocks_cloner-import')?.hide()
            window.ConcretePanelManager.getByIdentifier('blocks_cloner-export')?.hide()
        },
        async loadNewBlockHtml(ccmArea, newBlockID) {
            const renderResponse = await window.fetch(
                `${CCM_DISPATCHER_FILENAME}/ccm/system/block/render?cID=<?= $cID ?>&arHandle=${encodeURIComponent(ccmArea.getHandle())}&bID=${newBlockID}&arEnableGridContainer=${ccmArea.getEnableGridContainer() ? 1 : 0}`,
                {
                    method: 'GET',
                    headers: {
                        Accept: 'text/html',
                    },
                }
            );
            let newBlockHtml;
            try {
                newBlockHtml = await window.ccmBlocksCloner.service.parseTextResponse(renderResponse);
            } catch (e) {
                console.error(e);
                throw new Error(<?= json_encode(t('Unable to render the block')) ?>);
            }
            return {
                blockID: newBlockID,
                html: newBlockHtml,
            };
        },
        async renderNewBlockHtml(ccmArea, newBlockID, newBlockHtml, ccmBlockBefore) {
            if (ccmBlockBefore) {
                ccmBlockBefore.getContainer().before(newBlockHtml);
            } else {
                ccmArea.getBlockContainer().append(newBlockHtml);
            }
            const ccmEditMode = window.Concrete.getEditMode();
            for (let i = 0; i < 10; i++) {
                ccmEditMode.scanBlocks();
                const ccmBlock = ccmEditMode.getBlockByID(newBlockID);
                if (ccmBlock) {
                    return ccmBlock
                }
                await new Promise((resolve) => setTimeout(resolve, 10));
            }
            throw new Error(<?= json_encode(t('Unable to find the new block')) ?>);
        },
        async refreshDesigns(ccmArea, blockIDs) {
            const ccmEditMode = window.Concrete.getEditMode();
            const blockIDsByAreaHandles = {};
            const areas = [];
            function walk(item, parentAreaHandle) {
                let childAreaHandle = parentAreaHandle;
                switch (item.type) {
                    case 'area':
                        areas.push({handle: item.handle, id: item.id});
                        childAreaHandle = item.handle;
                        break;
                    case 'block':
                        blockIDsByAreaHandles[parentAreaHandle] = blockIDsByAreaHandles[parentAreaHandle] || [];
                        if (!blockIDsByAreaHandles[parentAreaHandle].includes(item.id)) {
                            blockIDsByAreaHandles[parentAreaHandle].push(item.id);
                        }
                        break;
                }
                item.children.forEach((child) => walk(child, childAreaHandle));
            }
            blockIDs.forEach((blockID) => {
                const ccmBlock = ccmEditMode.getBlockByID(blockID);
                const blockElement = ccmBlock.getElem()[0];
                for (const item of window.ccmBlocksCloner.getPageStructureStartingAt(blockElement)) {
                    walk(item, ccmArea.getHandle());
                }
            });
            const response = await window.fetch(
                `${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/import/get-designs?cID=<?= $cID ?>`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        Accept: 'application/json',
                    },
                    body: new URLSearchParams([
                        ['blockIDsByAreaHandles', JSON.stringify(blockIDsByAreaHandles)],
                        ['areas', JSON.stringify(areas)],
                        ['__ccm_consider_request_as_xhr', '1'],
                    ]),
                }
            );
            const responseData = await window.ccmBlocksCloner.service.parseJsonResponse(response);
            const head = document.querySelector('head');
            responseData.forEach((item) => {
                document.head.insertAdjacentHTML('beforeend', item.htmlStyleElement);
                if (item.containerClass) {
                    let ccmItem = null;
                    if (item.blockID) {
                        ccmItem = ccmEditMode.getBlockByID(item.areaID);
                    } else if (item.areaID) {
                        ccmItem = ccmEditMode.getAreaByID(item.areaID);
                    }
                    if (ccmItem) {
                        ccmItem.getElem().addClass(item.containerClass);
                    }
                }
            });
        },
    },
});

});
</script>
