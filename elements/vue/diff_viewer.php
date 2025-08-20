<?php

defined('C5_EXECUTE') or die('Access Denied.');

ob_start();
?>
<div style="display: flex; flex-direction: column; overflow-y: auto; flex-grow: 1">
    <div>
        <select class="form-control" v-model="type">
            <option v-for="t in TYPE" v-bind:value="t">{{ getTypeName(t) }}</option>
        </select>
    </div>
    <div v-html="diffHtml" style="white-space: pre; font-family: Menlo, Monaco, Consolas, 'Courier New', monospace; font-size: 0.9em"></div>
</div>
<?php
$template = ob_get_contents();
ob_end_clean();

?>
<script>$(document).ready(function() {

let mountCount = 0;

Vue.component('blocks-cloner-diff-viewer', {
    template: <?= json_encode($template) ?>,
    props: {
        left: {
            type: String,
            required: true,
        },
        right: {
            type: String,
            required: true,
        },
    },
    data() {
        const TYPE = window.ccmBlocksCloner.diff.TYPE;
        return {
            TYPE,
            type: TYPE.LINES,
            idPrefix: '',
        };
    },
    watch: {
        type() {
            window.localStorage.setItem('ccmBlocksCloner-diffviewer-type', this.type);
        },
    },
    mounted() {
        this.idPrefix = 'ccm-blockscloner-diffviewer-' + mountCount++;
        const type = window.localStorage.getItem('ccmBlocksCloner-diffviewer-type');
        if (type && Object.values(this.TYPE).includes(type)) {
            this.type = type;
        }
    },
    computed: {
        diffHtml() {
            if (typeof this.left !== 'string' || typeof this.right !== 'string') {
                return '';
            }
            const diffs = window.ccmBlocksCloner.diff.create(this.type, this.left, this.right);
            const chunks = diffs.map((diff) => {
                const a = document.createElement('span');
                a.textContent = diff.value;
                if (diff.added) {
                    a.className = 'text-success';
                } else if (diff.removed) {
                    a.className = 'text-danger';
                } else {
                    a.className = 'text-muted';
                }
                return a.outerHTML;
            });
            return chunks.join('');
        },
    },
    methods: {
        getTypeName(type) {
            switch (type) {
                case this.TYPE.PATCH:
                    return <?= json_encode(t(/* i18n: this is the format of patch files */ 'Diff format')) ?>;
                case this.TYPE.CHARS:
                    return <?= json_encode(t('Break at characters')) ?>;
                case this.TYPE.WORDS:
                    return <?= json_encode(t('Break at words')) ?>;
                case this.TYPE.WORDS_WITH_SPACE:
                    return <?= json_encode(t('Break at words (with space)')) ?>;
                case this.TYPE.LINES:
                    return <?= json_encode(t('Break at lines')) ?>;
            }
            return type;
        },
    },
});

});</script>

