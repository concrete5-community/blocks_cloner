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
            <div style="display: flex; flex-direction: column; height: 100%; flex: 1">
                <div>
                    <?= t('Paste here the XML of the block to be added to the area') ?>
                </div>
                <textarea
                    class="form-control"
                    v-model.trim="xml"
                    ref="xml"
                    nowrap
                    spellcheck="false"
                    v-bind:readonly="busy"
                    style="flex-grow: 1; font-family: Menlo, Monaco, Consolas, 'Courier New', monospace; font-size: 0.9em; resize: none"
                ></textarea>
                <div style="min-height: 7em">
                    <div v-if="xml && xmlInputError" class="alert alert-danger" v-html="xmlInputError" style="white-space: pre-wrap"></div>
                    <div v-else-if="analyzeError" class="alert alert-danger" style="white-space: pre-wrap">{{ analyzeError }}</div>
                </div>
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
                <div style="min-height: 7em"></div>
            </div>
        </div>
        <div class="text-right text-end">
            <button v-on:click.prevent="analyzeXml()" v-bind:disabled="busy || !xml || !!xmlInputError" class="btn btn-primary"><?= t('Analyze') ?></button>
        </div>
    </div>
    <div v-else-if="step === STEPS.CHECK" style="display: flex; flex-direction: column; width: 100%; height: 100%;">
        <div style="flex-grow: 1">
            <table class="table table-sm table-contensed caption-top">
                <caption>
                    <strong><?= t('Block Types') ?></strong>
                </caption>
                <colgroup>
                    <col width="1" />
                    <col width="1" />
                </colgroup>
                <thead>
                    <tr>
                        <th style="white-space: nowrap"><?= t('Handle') ?></th>
                        <th style="white-space: nowrap"><?= t('Name') ?></th>
                        <th style="white-space: nowrap"><?= t('Package') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in referenced.blockTypes">
                        <td style="white-space: nowrap"><code>{{ i.handle }}</code></td>
                        <td style="white-space: nowrap">{{ i.displayName }}</td>
                        <td>
                            <span v-if="i.package" v-bind:title="`<?= t('Handle: %s', '${i.package.handle}') ?>`">
                                <?= t('Provided by package %s', '{{ i.package.displayName }}') ?>
                            </span>
                            <i v-else><?= t('Provided by %s', 'Concrete') ?></i>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table v-if="referenced.files.length" class="table table-sm table-contensed caption-top">
                <caption>
                    <strong><?= t('Referenced Files') ?></strong>
                    <span v-if="someFilesWithErrors">
                        -
                        <a href="#" v-on:click.prevent="pickFile()"><?= t('Upload File') ?></a>
                    </span>
                </caption>
                <colgroup>
                    <col width="1" />
                    <col width="1" />
                </colgroup>
                <thead>
                    <tr>
                        <th style="white-space: nowrap"><?= t('Key') ?></th>
                        <th><?= t('File Name') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in referenced.files">
                        <td style="white-space: nowrap"><code>{{ i.key }}</code></td>
                        <td>
                            <div class="text-danger" v-if="i.error" style="white-space: pre-wrap">{{ i.error }}</div>
                            <code v-else v-bind:title="`<?= t('Prefix: %s', '${i.prefix}') ?>`">{{ i.name }}</code>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table v-if="referenced.pages.length" class="table table-sm table-contensed caption-top">
                <caption>
                    <strong><?= t('Referenced Pages') ?></strong>
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
                        <th style="white-space: nowrap"><?= t('Path') ?></th>
                        <th><?= t('Name') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in referenced.pages">
                        <td style="white-space: nowrap"><code>{{ i.key }}</code></td>
                        <td>
                        <td>
                            <div class="text-danger" v-if="i.error" style="white-space: pre-wrap">{{ i.error }}</div>
                            <a v-else target="_blank" v-bind:href="i.link" v-bind:title="`<?= t('ID: %s', '${i.cID}') ?>`">{{ i.name }}</a>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table v-if="referenced.pageTypes.length" class="table table-sm table-contensed caption-top">
                <caption>
                    <strong><?= t('Referenced Page Types') ?></strong>
                </caption>
                <colgroup>
                    <col width="1" />
                </colgroup>
                <thead>
                    <tr>
                        <th style="white-space: nowrap"><?= t('Handle') ?></th>
                        <th><?= t('Name') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in referenced.pageTypes">
                        <td style="white-space: nowrap"><code>{{ i.key }}</code></td>
                        <td>
                            <div class="text-danger" v-if="i.error" style="white-space: pre-wrap">{{ i.error }}</div>
                            <a v-else target="_blank" v-bind:href="i.link" v-bind:title="`<?= t('ID: %s', '${i.cID}') ?>`">{{ i.name }}</a>
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
        return {
            STEPS,
            step: STEPS.INPUT,
            existingBlocksInArea: getExistingBlocksInArea(),
            busy: false,
            xml: '',
            addBefore: null,
            analyzeError: '',
            importToken: '',
            referenced: {
                blockTypes: [],
                files: [],
                pages: [],
                pageTypes: [],
            },
        };
    },
    mounted() {
        this.$nextTick(() => this.$refs.xml.focus());
        this.$refs.pickFile.addEventListener('change', (e) => {
            this.pickFileChanged();
        });
    },
    watch: {
        xml() {
            this.analyzeError = '';
        },
    },
    computed: {
        xmlInputError() {
            if (this.xml === '') {
                return <?= json_encode(t('Please specify the XML to be imported')) ?>;
            }
            try {
                const parser = new DOMParser();
                const doc = parser.parseFromString(this.xml, 'text/xml');
                const errorNode = doc.querySelector('parsererror');
                if (errorNode) {
                    return errorNode.textContent;
                }
                if (doc.documentElement.tagName !== 'block' || !doc.documentElement.getAttribute('type')) {
                    throw new Error(<?= json_encode(t('The XML does not represent a block in ConcreteCMS CIF Format')) ?>);
                }
            } catch (e) {
                return (e ? (e.message || e.toString()) : <?= json_encode(t('Unknown error')) ?>).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }
            return '';
        },
        someFilesWithErrors() {
            return this.referenced.files.some((file) => file.error);
        },
        somePagesWithErrors() {
            return this.referenced.pages.some((page) => page.error);
        },
    },
    methods: {
        async analyzeXml() {
            if (this.busy) {
                return false;
            }
            this.analyzeError = '';
            if (!this.xml || this.xmlInputError) {
                this.$refs.xml?.focus();
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
                        ['xml', this.xml],
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
                const request = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        Accept: 'application/json',
                    },
                    body: new URLSearchParams([
                        [<?= json_encode($token::DEFAULT_TOKEN_NAME) ?>, this.importToken],
                        ['xml', this.xml],
                        ['areaHandle', <?= json_encode($area->arHandle) ?>],
                        ['beforeBlockID', this.addBefore?.id || ''],
                        ['__ccm_consider_request_as_xhr', '1'],
                    ]),
                };
                const response = await window.fetch(
                    `${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/import/import?cID=<?= $cID ?>`,
                    request
                );
                const responseData = await response.json();
                if (responseData.error) {
                    throw new Error(responseData.error);
                }
                const bID = responseData.bID;
                $.get(
                    CCM_DISPATCHER_FILENAME + '/ccm/system/block/render',
                    {
                        arHandle: ccmArea.getHandle(),
                        cID: <?= $cID ?>,
                        bID,
                        arEnableGridContainer: ccmArea.getEnableGridContainer() ? 1 : 0,
                    },
                    (html) => {
                        if (ccmBlockBefore) {
                            ccmBlockBefore.getContainer().before(html);
                        } else {
                            ccmArea.getBlockContainer().append(html);
                        }
                        _.defer(function () {
                            ccmEditMode.scanBlocks();
                        });
                    }
                );
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
    },
});

});
</script>
