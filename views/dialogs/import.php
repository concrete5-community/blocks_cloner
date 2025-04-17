<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\BlocksCloner\Controller\Dialog\Import $controller
 * @var Concrete\Core\View\View $view
 * @var int $cID
 * @var Concrete\Core\Area\Area $area
 * @var Concrete\Core\Validation\CSRF\Token $token
 * @var string $sitemapPageUrl
 */

$view->element('vue/references_viewer', null, 'blocks_cloner');

?>
<div id="ccm-blockscloker-import" style="display: flex; height: 100%; width: 100%" v-cloak>
    <div v-if="step === STEPS.INPUT" style="display: flex; flex-direction: column; width: 100%; height: 100%;">
        <div style="flex-grow: 1; display: flex; flex-direction: row">
            <div style="display: flex; flex-direction: column; height: 100%; flex: 1; padding-right: 10px;">
                <div>
                    <?= t('Paste here the XML of the data to be added to the area') ?>
                </div>
                <textarea
                    class="form-control"
                    v-model.trim="inputXml"
                    v-on:blur="normalizeInputXml()"
                    ref="inputXml"
                    nowrap
                    spellcheck="false"
                    v-bind:readonly="busy"
                    style="flex-grow: 1; font-family: Menlo, Monaco, Consolas, 'Courier New', monospace; font-size: 0.9em; resize: none"
                ></textarea>
                <div v-if="convertedXml !== null">
                    <?= t('Converted XML') ?>
                </div>
                <div v-if="convertedXml !== null && convertedXml === inputXml" class="alert alert-info">
                    <?= t('The conversion did not produce any changes') ?>
                </div>
                <textarea
                    v-if="convertedXml !== null && convertedXml !== inputXml"
                    class="form-control"
                    v-bind:value="convertedXml"
                    nowrap
                    spellcheck="false"
                    readonly
                    style="flex-grow: 1; font-family: Menlo, Monaco, Consolas, 'Courier New', monospace; font-size: 0.9em; resize: none"
                ></textarea>
                <div v-if="inputXml && xmlInputError" class="alert alert-danger" v-html="xmlInputError" style="white-space: pre-wrap"></div>
                <div v-else-if="analyzeError" class="alert alert-danger" style="white-space: pre-wrap">{{ analyzeError }}</div>
            </div>
            <div style="display: flex; flex-direction: column">
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
                <div>
                    <?= t('Conversion') ?>
                </div>
                <div style="flex-grow: 1; width: 100%">
                    <div v-if="allConverters.length === 0" class="alert alert-info">
                        <?= t('No converters available') ?>
                    </div>
                    <div v-else>
                        <select
                            class="form-control"
                            v-model="inputConversionMode"
                            v-bind:disabled="busy"
                        >
                            <option v-bind:value="CONVERSION_MODE.NONE"><?= t('No Conversion') ?></option>
                            <option v-bind:value="CONVERSION_MODE.AUTO" v-if="allowAutoConverters"><?= t('Automatic Converter Selection') ?></option>
                            <option v-bind:value="CONVERSION_MODE.MANUAL"><?= t('Manual Converter Selection') ?></option>
                        </select>
                        <div v-for="(c, i) in allConverters" class="form-check" v-if="conversionMode !== CONVERSION_MODE.NONE">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                v-bind:value="c"
                                v-bind:id="`ccm-blockscloker-import-converter-${i}`"
                                v-model="selectedConverters"
                                v-bind:disabled="busy || conversionMode === CONVERSION_MODE.AUTO"
                            />
                            <label class="form-check-label" v-bind:for="`ccm-blockscloker-import-converter-${i}`">
                                {{ c.name }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-right text-end">
            <button v-on:click.prevent="analyzeXml()" v-bind:disabled="busy || !finalXml || !!xmlInputError" class="btn btn-primary"><?= t('Analyze') ?></button>
        </div>
    </div>
    <div v-else-if="step === STEPS.CHECK" style="display: flex; flex-direction: column; width: 100%; height: 100%;">
        <blocks-cloner-references-viewer
            v-bind:cid="<?= $cID ?>"
            v-bind:references="references"
            operation="import"
            v-on:files-upload-started="busy = true"
            v-on:files-upload-completed="filesUploadCompleted"
            v-on:change="analyzeXml()"
            v-bind:busy="busy"
        ></blocks-cloner-references-viewer>
        <div class="text-right text-end">
            <button v-on:click.prevent="step = STEPS.INPUT" v-bind:disabled="busy" class="btn btn-secondary btn-default"><?= t('Back') ?></button>
            <button v-on:click.prevent="analyzeXml()" v-bind:disabled="busy" class="btn btn-secondary btn-default"><?= t('Reanalyze') ?></button>
            <button v-on:click.prevent="importXml()" v-bind:disabled="!canImport" class="btn btn-primary"><?= t('Import') ?></button>
        </div>
    </div>
</div>
<script>$(document).ready(function() {

const currentEnvironment = window.ccmBlocksCloner.environment.getCurrent();

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

new Vue({
    el: '#ccm-blockscloker-import',
    data() {
        const STEPS = {
            INPUT: 1,
            CHECK: 2,
        };
        const CONVERSION_MODE = {
            NONE: 0,
            AUTO: 1,
            MANUAL: 2,
        }
        return {
            STEPS,
            ICON: {
                // HEAVY CHECK MARK
                GOOD: '\u2705',
                // CROSS MARK
                BAD: '\u274c',
            },
            step: STEPS.INPUT,
            existingBlocksInArea: getExistingBlocksInArea(),
            busy: false,
            inputXml: '',
            CONVERSION_MODE,
            inputConversionMode: CONVERSION_MODE.MANUAL,
            selectedConverters: [],
            addBefore: null,
            analyzeError: '',
            importType: '',
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
            this.recalcSelectedConverters(true);
        },
        conversionMode() {
            this.recalcSelectedConverters();
        },
    },
    computed: {
        xmlInputError() {
            if (this.inputXml === '') {
                return <?= json_encode(t('Please specify the XML to be imported')) ?>;
            }
            try {
                const doc = window.ccmBlocksCloner.xml.parse(this.inputXml, false);
                if (doc.documentElement.tagName !== 'block' || !doc.documentElement.getAttribute('type')) {
                    if (doc.documentElement.tagName !== 'area') {
                        throw new Error(<?= json_encode(t('The XML does not represent a block in ConcreteCMS CIF Format')) ?>);
                    }
                }
            } catch (e) {
                return (e ? (e.message || e.toString()) : <?= json_encode(t('Unknown error')) ?>);
            }
            return '';
        },
        xmlEnvironment() {
            if (this.inputXml === '' || this.xmlInputError !== '') {
                return null;
            }
            try {
                return window.ccmBlocksCloner.environment.extractFromXml(this.inputXml);
            } catch (e) {
                console.warn(e);
                return null;
            }
        },
        convertedXml() {
            if (this.inputXml === '' || this.xmlInputError !== '' || this.conversionMode === this.CONVERSION_MODE.NONE || this.selectedConverters.length === 0) {
                return null;
            }

            return window.ccmBlocksCloner.conversion.convertXml(this.inputXml, this.selectedConverters) ?? this.inputXml;
        },
        finalXml() {
            return this.convertedXml || this.inputXml;
        },
        conversionMode() {
            return this.allowAutoConverters ? this.inputConversionMode : this.CONVERSION_MODE.MANUAL;
        },
        allowAutoConverters() {
            return this.xmlEnvironment !== null;
        },
        suggestedConverters() {
            return window.ccmBlocksCloner.conversion.getConvertersForEvironments(this.xmlEnvironment, currentEnvironment);
        },
        allConverters() {
            return window.ccmBlocksCloner.conversion.getConverters();
        },
        canImport() {
            if (this.busy) {
                return false;
            }
            if (this.references.blockTypes) {
                if (Object.values(this.references.blockTypes).some((blockType) => blockType.error)) {
                    return false;
                }
            }
            return true;
        },
    },
    methods: {
        normalizeInputXml() {
            if (this.inputXml && !this.xmlInputError) {
                try {
                    this.inputXml = window.ccmBlocksCloner.xml.normalizeXml(this.inputXml, true);
                } catch (e) {
                }
            }
        },
        recalcSelectedConverters(setAutoIfApplicable) {
            if (this.allowAutoConverters) {
                if (setAutoIfApplicable) {
                    this.inputConversionMode = this.CONVERSION_MODE.AUTO;
                }
            } else {
                if (this.inputConversionMode === this.CONVERSION_MODE.AUTO) {
                    this.inputConversionMode = this.CONVERSION_MODE.MANUAL;
                }
            }
            this.selectedConverters.splice(0, this.selectedConverters.length);
            this.suggestedConverters.forEach((c) => this.selectedConverters.push(c));
        },
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
                        ['aID', <?= $area->getAreaID() ?>],
                        ['aHandle', <?= json_encode($area->getAreaHandle()) ?>],
                        ['xml', this.finalXml],
                    ]),
                };
                const response = await window.fetch(
                    `${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/import/analyze?cID=<?= $cID ?>`,
                    request
                );
                if (!response.ok) {
                    throw new Error(await response.text());
                }
                const responseData = await response.json();
                if (responseData.error) {
                    throw new Error(responseData.error);
                }
                this.importToken = responseData.importToken;
                this.importType = responseData.importType;
                this.references = responseData.references;
            } catch (e) {
                this.analyzeError = e?.message || e?.toString() || <?= json_encode(t('Unknown error')) ?>;
                return;
            } finally {
                this.busy = false;
            }
            this.step = this.STEPS.CHECK;
        },
        filesUploadCompleted(withSuccess) {
            this.busy =  false;
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
            try {
                const ccmEditMode = window.Concrete.getEditMode();
                const ccmArea = ccmEditMode.getAreaByID(<?= $area->getAreaID() ?>);
                if (!ccmArea) {
                    throw new Error(<?= json_encode(t('Unable to find the requested area')) ?>);
                }
                let ccmBlockBefore = this.addBefore ? ccmEditMode.getBlockByID(this.addBefore.id) : null;
                if (this.addBefore !== null && !ccmBlockBefore) {
                    throw new Error(<?= json_encode(t('Unable to find the requested block')) ?>);
                }
                const importResponse = await window.fetch(
                    `${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/import/${this.importType}?cID=<?= $cID ?>`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            Accept: 'application/json',
                        },
                        body: new URLSearchParams([
                            [<?= json_encode($token::DEFAULT_TOKEN_NAME) ?>, this.importToken],
                            ['xml', this.finalXml],
                            ['areaHandle', <?= json_encode($area->arHandle) ?>],
                            ['beforeBlockID', this.addBefore?.id || ''],
                            ['__ccm_consider_request_as_xhr', '1'],
                        ]),
                    }
                );
                if (!importResponse.ok) {
                    throw new Error(await importResponse.text());
                }
                const importResult = await importResponse.json();
                if (importResult.error) {
                    throw new Error(importResult.error);
                }
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
                const newBlockIDs = importResult.newBlockIDs;
                const newBlocksHtml = await Promise.all(newBlockIDs.map((newBlockID) => this.loadNewBlockHtml(ccmArea, newBlockID)));
                for (let intex = 0; intex < newBlocksHtml.length; intex++) {
                    const newCCMBlock = await this.renderNewBlockHtml(ccmArea, newBlocksHtml[intex].blockID, newBlocksHtml[intex].html, ccmBlockBefore);
                    ccmBlockBefore = newCCMBlock;
                }
                await this.refreshDesigns(ccmArea, newBlockIDs, false);
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
            window.ConcreteAlert.info({
                message: <?= json_encode(t('The block has been imported')) ?>,
            });
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
            if (!renderResponse.ok) {
                renderResponse.text().then((text) => {
                    console.error(text);
                });
                throw new Error(<?= json_encode(t('Unable to render the block')) ?>);
            }
            const newBlockHtml = await renderResponse.text();
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
            if (!response.ok) {
                throw new Error(await response.text());
            }
            const responseData = await response.json();
            if (responseData?.error || typeof responseData?.length !== 'number') {
                throw new Error(responseData.error || <?= json_encode(t('Unknown error')) ?>);
            }
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
