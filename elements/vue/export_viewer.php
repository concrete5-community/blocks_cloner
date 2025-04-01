<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\View\View $view
 */

$view->element('vue/references_viewer', null, 'blocks_cloner');

ob_start();
?>
<div style="display: flex; flex-direction: column; height: 100%;">
    <blocks-cloner-references-viewer
        v-bind:cid="cid"
        v-bind:references="references"
        operation="export"
        v-bind:max-lists-height="maxListsHeight"
    ></blocks-cloner-references-viewer>
    <div style="flex-grow: 2">
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
                style="flex-grow: 1; font-family: Menlo, Monaco, Consolas, 'Courier New', monospace; font-size: 0.9em; resize: none"
                v-bind:value="outputXml"
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
<?php
$template = ob_get_contents();
ob_end_clean();

?>
<script>$(document).ready(function() {

Vue.component('blocks-cloner-export-viewer', {
    template: <?= json_encode($template) ?>,
    props: {
        cid: {
            type: Number,
            required: true,
        },
        references: {
            type: Object,
            required: true,
        },
        xml: {
            type: String,
            required: true,
        },
        maxListsHeight: {
            type: Number,
            default: 200,
        },
    },
    data() {
        return {
            copyHighlighed: false,
        };
    },
    mounted() {
        setTimeout(() => this.$refs.textarea?.focus(), 50);
    },
    computed: {
        listStyle() {
            return this.maxListsHeight ? {'max-height' : `${this.maxListsHeight}px`, 'overflow-y': 'auto'} : {};
        },
        fileIDs() {
            const result = [];
            if (this.references?.files) {
                Object.values(this.references.files).forEach((file) => {
                    if (file.id) {
                        result.push(file.id);
                    }
                });
            }
            return result;
        },
        outputXml() {
            if (typeof this.xml !== 'string' || this.xml === '') {
                return '';
            }
            let xml = window.ccmBlocksCloner.environment.addCurrentToXml(this.xml);
            try {
                xml = window.ccmBlocksCloner.xml.normalizeXml(xml, true);
            } catch (e) {
                console.warn(e);
            }
            return xml;
        },
    },
    methods: {
        copy(andClose) {
            try {
                if (window.navigator && window.navigator.clipboard && window.navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(this.outputXml)
                        .then(() => this.reportCopyResult(null, andClose))
                        .catch((e) => this.reportCopyResult(e || <?= json_encode(t('Unknown error')) ?>))
                    ;
                } else {
                    this.$refs.textarea.focus();
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

