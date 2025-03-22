<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\BlocksCloner\Controller\Dialog\Import $controller
 * @var Concrete\Core\View\View $view
 * @var int $cID
 * @var Concrete\Core\Area\Area $area
 * @var Concrete\Core\Validation\CSRF\Token $token
 */

?>
<div id="ccm-blockscloker-import" v-cloak style="display: flex; height: 100%; width: 100%">
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
                    v-bind:readonly="busy"
                    style="flex-grow: 1; font-family: Menlo, Monaco, Consolas, 'Courier New', monospace; font-size: 0.9em; resize: none"
                ></textarea>
                <div style="min-height: 7em">
                    <div v-if="xml && xmlInputError" class="alert alert-danger" v-html="xmlInputError" style="white-space: pre-wrap"></div>
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
                <caption><?= t('Block Types') ?></caption>
                <thead>
                    <tr>
                        <th><?= t('Name') ?></th>
                        <th><?= t('Handle') ?></th>
                        <th><?= t('Package') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in referenced.blockTypes">
                        <td>{{ i.displayName }}</td>
                        <td><code>{{ i.handle }}</code></td>
                        <td>
                            <span v-if="i.package" v-bind:title="`<?= t('Handle: %s', '${i.package.handle}') ?>`">
                                {{ i.package.displayName }}
                            </span>
                            <i v-else><?= tc('Package', 'none') ?></i>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table v-if="referenced.files.length" class="table table-sm table-contensed caption-top">
                <caption><?= t('Referenced Files') ?></caption>
                <thead>
                    <tr>
                        <th><?= t('Key') ?></th>
                        <th><?= t('File Name') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in referenced.files">
                        <td><code>{{ i.key }}</code></td>
                        <td>
                            <div class="text-danger" v-if="i.error" style="white-space: pre-wrap">{{ i.error }}</div>
                            <code v-else v-bind:title="`<?= t('Prefix: %s', '${i.prefix}') ?>`">{{ i.name }}</code>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table v-if="referenced.pages.length" class="table table-sm table-contensed caption-top">
                <caption><?= t('Referenced Pages') ?></caption>
                <thead>
                    <tr>
                        <th><?= t('Path') ?></th>
                        <th><?= t('Name') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in referenced.pages">
                        <td><code>{{ i.path }}</code></td>
                        <td>
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
            busy: false,
            xml: '',
            addBefore: null,
            existingBlocksInArea: getExistingBlocksInArea(),
            importToken: '',
            referenced: {
                blockTypes: [],
                files: [],
                pages: [],
            },
        };
    },
    mounted() {
        this.$nextTick(() => this.$refs.xml.focus());
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
    },
    methods: {
        async analyzeXml() {
            if (this.busy) {
                return false;
            }
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
                    },
                    body: new URLSearchParams([
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
                window.ConcreteAlert.error({
                    message: e?.messsage || e?.toString() || <?= json_encode(t('Unknown error')) ?>,
                    delay: 5000,
                });
                return;
            } finally {
                this.busy = false;
            }
            this.step = this.STEPS.CHECK;
        },
        async importXml() {
            if (this.busy) {
                return false;
            }
            this.busy = true;
            try {
                const request = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams([
                        [<?= json_encode($token::DEFAULT_TOKEN_NAME) ?>, this.importToken],
                        ['xml', this.xml],
                        ['areaHandle', <?= json_encode($area->arHandle) ?>],
                        ['beforeBlockID', this.addBefore?.id || ''],
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
            } catch (e) {
                window.ConcreteAlert.error({
                    message: e?.messsage || e?.toString() || <?= json_encode(t('Unknown error')) ?>,
                    delay: 5000,
                });
                return;
            } finally {
                this.busy = false;
            }
            this.step = this.STEPS.IMPORTED;
        },
    },
});

});
</script>
