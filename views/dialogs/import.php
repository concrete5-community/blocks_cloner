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

?>
<div id="ccm-blockscloker-import" v-cloak style="display: flex; height: 100%; width: 100%" v-cloak>
    <div v-if="step === STEPS.INPUT" style="display: flex; flex-direction: column; width: 100%; height: 100%;">
        <div style="flex-grow: 1; display: flex; flex-direction: row">
            <div style="display: flex; flex-direction: column; height: 100%; flex: 1; padding-right: 10px;">
                <div>
                    <?= t('Paste here the XML of the block to be added to the area') ?>
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
                    <?= t('Insert block') ?>
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
        <div style="flex-grow: 1">
            <table class="table table-hover table-sm table-contensed caption-top">
                <caption>
                    <strong>{{ ICON.GOOD}} <?= t('Referenced Block Types') ?></strong>
                </caption>
                <colgroup>
                    <col width="1" />
                    <col width="1" />
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-nowrap"><?= t('Handle') ?></th>
                        <th class="text-nowrap"><?= t('Name') ?></th>
                        <th><?= t('Package') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in referenced.blockTypes">
                        <td class="text-nowrap"><code>{{ i.handle }}</code></td>
                        <td class="text-nowrap">{{ i.displayName }}</td>
                        <td>
                            <span v-if="i.package" v-bind:title="`<?= t('Handle: %s', '${i.package.handle}') ?>`">
                                <?= t('Provided by package %s', '{{ i.package.displayName }}') ?>
                            </span>
                            <i v-else><?= t('Provided by %s', 'Concrete') ?></i>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table v-if="referenced.files.length" class="table table-hover table-sm table-contensed caption-top">
                <caption>
                    <strong>{{ someFilesWithErrors ? ICON.BAD : ICON.GOOD }} <?= t('Referenced Files') ?></strong>
                    <span v-if="someFilesWithErrors">
                        -
                        <a href="#" v-on:click.prevent="pickFile()"><?= t('Upload File') ?></a>
                    </span>
                </caption>
                <colgroup>
                    <col width="1" />
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-nowrap"><?= t('Key') ?></th>
                        <th><?= t('File Name') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in referenced.files">
                        <td class="text-nowrap"><code>{{ i.key }}</code></td>
                        <td>
                            <div class="text-danger" v-if="i.error" style="white-space: pre-wrap">{{ i.error }}</div>
                            <span v-else v-bind:title="`<?= t('Prefix: %s', '${i.prefix}') ?>`">{{ i.name }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table v-if="referenced.pages.length" class="table table-hover table-sm table-contensed caption-top">
                <caption>
                    <strong>{{ somePagesWithErrors ? ICON.BAD : ICON.GOOD }} <?= t('Referenced Pages') ?></strong>
                    <?php
                    if ($sitemapPageUrl !== '') {
                        ?>
                        <span v-if="somePagesWithErrors">
                            - <a target="_blank" href="<?= h($sitemapPageUrl) ?>"><?= t('open sitemap') ?></a>
                        </span>
                        <?php
                    }
                    ?>
                </caption>
                <colgroup>
                    <col width="1" />
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-nowrap"><?= t('Path') ?></th>
                        <th><?= t('Name') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in referenced.pages">
                        <td class="text-nowrap"><code>{{ i.key }}</code></td>
                        <td>
                            <div class="text-danger" v-if="i.error" style="white-space: pre-wrap">{{ i.error }}</div>
                            <a v-else target="_blank" v-bind:href="i.link" v-bind:title="`<?= t('ID: %s', '${i.cID}') ?>`">{{ i.name }}</a>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table v-if="referenced.pageTypes.length" class="table table-hover table-sm table-contensed caption-top">
                <caption>
                    <strong>{{ somePageTypesWithErrors ? ICON.BAD : ICON.GOOD }} <?= t('Referenced Page Types') ?></strong>
                </caption>
                <colgroup>
                    <col width="1" />
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-nowrap"><?= t('Handle') ?></th>
                        <th><?= t('Name') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in referenced.pageTypes">
                        <td class="text-nowrap"><code>{{ i.key }}</code></td>
                        <td>
                            <div class="text-danger" v-if="i.error" style="white-space: pre-wrap">{{ i.error }}</div>
                            <a v-else target="_blank" v-bind:href="i.link" v-bind:title="`<?= t('ID: %s', '${i.cID}') ?>`">{{ i.name }}</a>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table v-if="referenced.pageFeeds.length" class="table table-hover table-sm table-contensed caption-top">
                <caption>
                    <strong>{{ somePageFeedsWithErrors ? ICON.BAD : ICON.GOOD }}<?= t('Referenced RSS Page Feeds') ?></strong>
                </caption>
                <colgroup>
                    <col width="1" />
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-nowrap"><?= t('Handle') ?></th>
                        <th><?= t('Title') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in referenced.pageFeeds">
                        <td class="text-nowrap"><code>{{ i.key }}</code></td>
                        <td>
                            <div class="text-danger" v-if="i.error" style="white-space: pre-wrap">{{ i.error }}</div>
                            <a v-else target="_blank" v-bind:href="i.link" v-bind:title="`<?= t('ID: %s', '${i.cID}') ?>`">{{ i.title }}</a>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table v-if="referenced.stacks.length" class="table table-hover table-sm table-contensed caption-top">
                <caption>
                    <strong>{{ someStacksWithErrors ? ICON.BAD : ICON.GOOD }}<?= t('Referenced Stacks') ?></strong>
                </caption>
                <colgroup>
                    <col width="1" />
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-nowrap"><?= t('Key') ?></th>
                        <th><?= t('Name') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in referenced.stacks">
                        <td class="text-nowrap"><code>{{ i.key }}</code></td>
                        <td>
                            <div class="text-danger" v-if="i.error" style="white-space: pre-wrap">{{ i.error }}</div>
                            <a v-else target="_blank" v-bind:href="i.link">{{ i.name }}</a>
                            <span v-else>{{ i.name }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table v-if="referenced.containers.length" class="table table-hover table-sm table-contensed caption-top">
                <caption>
                    <strong>{{ someContainersWithErrors ? ICON.BAD : ICON.GOOD }}<?= t('Referenced Containers') ?></strong>
                </caption>
                <colgroup>
                    <col width="1" />
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-nowrap"><?= t('Handle') ?></th>
                        <th><?= t('Name') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in referenced.containers">
                        <td class="text-nowrap"><code>{{ i.key }}</code></td>
                        <td>
                            <div class="text-danger" v-if="i.error" style="white-space: pre-wrap">{{ i.error }}</div>
                            <span v-else>{{ i.name }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="text-right text-end">
            <button v-on:click.prevent="step = STEPS.INPUT" v-bind:disabled="busy" class="btn btn-secondary btn-default"><?= t('Back') ?></button>
            <button v-on:click.prevent="analyzeXml()" v-bind:disabled="busy" class="btn btn-secondary btn-default"><?= t('Reanalyze') ?></button>
            <button v-on:click.prevent="importXml()" v-bind:disabled="busy" class="btn btn-primary"><?= t('Import') ?></button>
        </div>
    </div>
    <input type="file" ref="pickFile" style="display: none" />
</div>
<script>$(document).ready(function() {

const currentEnvironment = window.ccmBlocksCloner.envirorment.getCurrent();

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
            importToken: '',
            referenced: {
                blockTypes: [],
                files: [],
                pages: [],
                pageTypes: [],
                pageFeeds: [],
                stacks: [],
                containers: [],
            },
        };
    },
    mounted() {
        this.$nextTick(() => this.$refs.inputXml.focus());
        this.$refs.pickFile.addEventListener('change', (e) => {
            this.pickFileChanged();
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
                    throw new Error(<?= json_encode(t('The XML does not represent a block in ConcreteCMS CIF Format')) ?>);
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
                return window.ccmBlocksCloner.envirorment.extractFromXml(this.inputXml);
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
        someFilesWithErrors() {
            return this.referenced.files.some((file) => file.error);
        },
        somePagesWithErrors() {
            return this.referenced.pages.some((page) => page.error);
        },
        somePageTypesWithErrors() {
            return this.referenced.pageTypes.some((pageType) => pageType.error);
        },
        somePageFeedsWithErrors() {
            return this.referenced.pageFeeds.some((pageFeed) => pageFeed.error);
        },
        someStacksWithErrors() {
            return this.referenced.stacks.some((stack) => stack.error);
        },
        someContainersWithErrors() {
            return this.referenced.containers.some((container) => container.error);
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
                const responseData = await response.json();
                if (responseData.error) {
                    throw new Error(responseData.error);
                }
                this.importToken = responseData.importToken;
                delete responseData.importToken;
                const referencedKeys = Object.keys(this.referenced);
                referencedKeys.forEach((key) => this.referenced[key].splice(0, this.referenced[key].length));
                Object.keys(responseData).forEach((key) => {
                    if (!referencedKeys.includes(key)) {
                        throw new Error(`Invalid key: ${key}`);
                    }
                    responseData[key].forEach((value) => this.referenced[key].push(value));
                });
            } catch (e) {
                this.analyzeError = e?.messsage || e?.toString() || <?= json_encode(t('Unknown error')) ?>;
                return;
            } finally {
                this.busy = false;
            }
            this.step = this.STEPS.CHECK;
        },
        pickFile() {
            this.$refs.pickFile.click();
        },
        async pickFileChanged() {
            let reanalyze = false;
            try {
                if (this.busy || this.step !== this.STEPS.CHECK) {
                    return;
                }
                const file = this.$refs.pickFile.files?.length === 1 ? this.$refs.pickFile.files[0] : null;
                if (!file) {
                    return;
                }
                const decompressZip = /\.zip$/i.test(file.name) && window.confirm(<?= json_encode(t('Should the ZIP archive be extracted?')) ?>);
                this.busy = true;
                try {
                    const request = {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                        },
                        body: new FormData(),
                    };
                    request.body.append('file', file);
                    request.body.append('decompressZip', decompressZip ? 'true' : 'false');
                    request.body.append('__ccm_consider_request_as_xhr', '1');
                    request.body.append(<?= json_encode($token::DEFAULT_TOKEN_NAME) ?>, <?= json_encode($token->generate('blocks_cloner:import:uploadFile')) ?>);
                    const response = await window.fetch(
                        `${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/import/upload-file?cID=<?= $cID ?>`,
                        request
                    );
                    const responseData = await response.json();
                    if (responseData.error) {
                        throw new Error(responseData.error);
                    }
                    reanalyze = true;
                } finally {
                    this.busy = false;
                }
            } catch (e) {
                window.ConcreteAlert.error({
                    message: e?.messsage || e?.toString() || <?= json_encode(t('Unknown error')) ?>,
                    delay: 5000,
                });
            } finally {
                this.$refs.pickFile.value = '';
            }
            if (reanalyze) {
                this.analyzeXml();
            }
        },
        async importXml() {
            if (this.busy) {
                return false;
            }
            this.busy = true;
            try {
                const ccmEditMode = window.Concrete.getEditMode();
                const ccmArea = ccmEditMode.getAreaByID(<?= $area->getAreaID() ?>);
                if (!ccmArea) {
                    throw new Error(<?= json_encode(t('Unable to find the requested area')) ?>);
                }
                const ccmBlockBefore = this.addBefore ? ccmEditMode.getBlockByID(this.addBefore.id) : null;
                if (this.addBefore !== null && !ccmBlockBefore) {
                    throw new Error(<?= json_encode(t('Unable to find the requested block')) ?>);
                }
                const importResponse = await window.fetch(
                    `${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/import/import?cID=<?= $cID ?>`,
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
                const importResult = await importResponse.json();
                if (importResult.error) {
                    throw new Error(importResult.error);
                }
                const bID = importResult.bID;
                const renderResponse = await window.fetch(
                    `${CCM_DISPATCHER_FILENAME}/ccm/system/block/render?cID=<?= $cID ?>&arHandle=${encodeURIComponent(ccmArea.getHandle())}&bID=${bID}&arEnableGridContainer=${ccmArea.getEnableGridContainer() ? 1 : 0}`,
                    {
                        method: 'GET',
                        headers: {
                            Accept: 'application/json, text/html',
                        },
                    }
                );
                if (!renderResponse.ok) {
                    renderResponse.text().then((text) => {
                        console.error(text);
                    });
                    throw new Error(<?= json_encode(t('Unable to render the block')) ?>);
                }
                const renderHtml = await renderResponse.text();
                if (ccmBlockBefore) {
                    ccmBlockBefore.getContainer().before(renderHtml);
                } else {
                    ccmArea.getBlockContainer().append(renderHtml);
                }
                _.defer(() => {
                    ccmEditMode.scanBlocks();
                    setTimeout(() => this.refreshBlockDesign(ccmArea.getHandle(), bID), 100);
                });
            } catch (e) {
                window.ConcreteAlert.error({
                    message: e?.messsage || e?.toString() || <?= json_encode(t('Unknown error')) ?>,
                    delay: 5000,
                });
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
        async refreshBlockDesign(areaHandle, bID) {
            const ccmEditMode = window.Concrete.getEditMode();
            const ccmBlock = ccmEditMode.getBlockByID(bID);
            const blockElement = ccmBlock.getElem()[0];
            const blockIDsByAreaHandles = {
                [areaHandle]: [bID],
            };
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
            for (const item of window.ccmBlocksCloner.getPageStructureStartingAt(blockElement)) {
                walk(item, areaHandle);
            }
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
