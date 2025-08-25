<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\BlocksCloner\Controller\Dialog\Export\Attributes $controller
 * @var Concrete\Core\View\View $view
 * @var int $cID
 * @var Concrete\Core\Validation\CSRF\Token $token
 * @var array[] $attributeKeys
 */

if ($attributeKeys === []) {
    ?>
    <div class="alert alert-info">
        <?= t("This page doesn't have any attributes at the moment.") ?>
    </div>
    <?php
    return;
}

$view->element('vue/export_viewer', null, 'blocks_cloner');

?>
<div class="ccm-blockscloker-dialog-content" id="ccm-blockscloker-export-attributes" v-cloak>
    <div class="ccmbc-attributes">
        <div class="ccmbc-attributes-aside">
            <strong><?= t('Attributes to be exported') ?></strong>
        </div>
        <div class="ccmbc-attributes-list">
            <button
                v-for="ak in allAttributeKeys"
                v-bind:key="`ak${ak.id}`"
                class="btn"
                v-bind:class="selectedAttributeKeys.includes(ak) ? 'btn-success' : 'btn-secondary'"
                v-on:click.prevent="toggleSelectedAttributeKey(ak)"
                v-bind:title="ak.name"
            >
                {{ ak.name }}
                <span class="small text-muted"><br /><code>{{ ak.handle }}</code></span>
            </button>
        </div>
        <div class="ccmbc-attributes-aside">
            <a href="#" v-on:click.prevent="selectAll(true)"><?= t('Select All') ?></a>
            <span class="text-muted">&nbsp;|&nbsp;</span>
            <a href="#" v-on:click.prevent="selectAll(false)"><?= t('Select None') ?></a>
        </div>
    </div>
    <div class="ccmbc-result">
        <div v-if="selectedAttributeKeys.length === 0" class="alert alert-info">
            <?= t('Please select at least one attribute to be exported.') ?>
        </div>
        <div v-else-if="busy" class="text-muted">
            <?= t('Loading... ') ?>
        </div>
        <div v-else-if="exportError !== ''" class="alert alert-danger" style="white-space: pre-wrap">{{ exportError }}</div>
        <blocks-cloner-export-viewer
            v-else
            v-bind:cid="<?= $cID ?>"
            v-bind:references="exportData.references"
            v-bind:xml="exportData.xml"
        ></blocks-cloner-export-viewer>
        <!--
        <template v-else>
            <div class="ccmbc-result-references">
                <pre>{{  }}</pre>
            </div>
            <div class="ccmbc-result-xml">
                <textarea readonly v-bind:value=""></textarea>
            </div>
        </template>
        -->
    </div>
</div>
<script>$(document).ready(function() {

new Vue({
    el: '#ccm-blockscloker-export-attributes',
    data() {
        return {
            allAttributeKeys: <?= json_encode($attributeKeys) ?>,
            selectedAttributeKeys: [],
            exportError: '',
            exportData: null,
            busy: false,
        };
    },
    mounted() {
        this.selectAll(true);
    },
    watch: {
        selectedAttributeKeys() {
            this.exportSelectedAttributeKeys();
        },
    },
    methods: {
        toggleSelectedAttributeKey(ak) {
            const index = this.selectedAttributeKeys.indexOf(ak);
            if (index < 0) {
                this.selectedAttributeKeys.push(ak);
            } else {
                this.selectedAttributeKeys.splice(index, 1);
            }
        },
        selectAll(all) {
            if (this.busy) {
                return false;
            }
            if (all) {
                this.allAttributeKeys.forEach((ak) => {
                    if (!this.selectedAttributeKeys.includes(ak)) {
                        this.selectedAttributeKeys.push(ak);
                    }
                });
            } else {
                this.selectedAttributeKeys.splice(0, this.selectedAttributeKeys.length);
            }
        },
        async exportSelectedAttributeKeys() {
            if (this.busy || this.selectedAttributeKeys.length === 0) {
                return;
            }
            this.busy = true;
            this.exportError = '';
            try {
                const request = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        Accept: 'application/json',
                    },
                    body: new URLSearchParams([
                        ['__ccm_consider_request_as_xhr', '1'],
                        [<?= json_encode($token::DEFAULT_TOKEN_NAME) ?>, <?= json_encode($token->generate('blocks_cloner:export:attributes:export')) ?>],
                        ['attributeKeyHandles', this.selectedAttributeKeys.map((ak) => ak.handle).join(' ')],
                        ['xml', this.inputXml],
                    ]),
                };
                const response = await window.fetch(
                    `${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/export/attributes/export?cID=<?= $cID ?>`,
                    request
                );
                this.exportData = await window.ccmBlocksCloner.service.parseJsonResponse(response);
            } catch (e) {
                this.exportError = e?.message || e?.toString() || <?= json_encode(t('Unknown error')) ?>;
            } finally {
                this.busy = false;
            }
        },
    },
});

});</script>
