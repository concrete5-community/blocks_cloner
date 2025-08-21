<?php

defined('C5_EXECUTE') or die('Access Denied.');

ob_start();
?>
<div style="display: flex; flex-direction: column; overflow-y: auto; flex-grow: 1">
    <div>
        <select class="form-control" v-model="type">
            <option v-for="t in TYPES" v-bind:value="t">{{ t.name }}</option>
        </select>
    </div>
    <div v-html="diffHtml" style="white-space: pre; font-family: Menlo, Monaco, Consolas, 'Courier New', monospace; font-size: 0.9em; border: inset 2px #ddd; height: 100%; overflow: auto"></div>
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
        const TYPES = [];
        const RAW_TYPE = window.ccmBlocksCloner.diff.TYPE;
        const typeKeys = Object.keys(RAW_TYPE);
        [
            {key: 'PATCH', name: <?= json_encode(t('View differences in Diff format')) ?>},
            {key: 'CHARS', name: <?= json_encode(t('View character-by-character differences')) ?>},
            {key: 'WORDS', name: <?= json_encode(t('View word-by-word differences')) ?>},
            {key: 'WORDS_WITH_SPACE', name: <?= json_encode(t('View word-by-word differences (with spaces)')) ?>},
            {key: 'LINES', name: <?= json_encode(t('View line-by-line differences')) ?>},
        ].forEach((entry) => {
            const index = typeKeys.indexOf(entry.key);
            if (index < 0) {
                return;
            }
            typeKeys.splice(index, 1);
            TYPES.push({name: entry.name, key: entry.key, value: RAW_TYPE[entry.key]});
        });
        typeKeys.forEach((key) => {
            TYPES.push({name: key, key: key, value: RAW_TYPE[key]});
        });
        return {
            TYPES,
            type: TYPES[0],
            idPrefix: '',
        };
    },
    watch: {
        type() {
            window.localStorage.setItem('ccmBlocksCloner-diffviewer-type', this.type.key);
        },
    },
    mounted() {
        this.idPrefix = 'ccm-blockscloner-diffviewer-' + mountCount++;
        const typeKey = window.localStorage.getItem('ccmBlocksCloner-diffviewer-type');
        if (typeKey) {
            this.TYPES.some((type) => {
                if (type.key === typeKey) {
                    this.type = type;
                    return true;
                }
            });
        }
    },
    computed: {
        diffHtml() {
            if (typeof this.left !== 'string' || typeof this.right !== 'string') {
                return '';
            }
            const diffs = window.ccmBlocksCloner.diff.create(this.type.value, this.left, this.right);
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
});

});</script>

