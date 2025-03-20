<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\BlocksCloner\Controller\Panel\Copy $controller
 * @var Concrete\Core\View\View $view
 * @var int $cID
 * @var array $blockTypeNames
 */

$view->markHeaderAssetPosition();
?>
<style>
#ccm-panel-blocks_cloner-copy {
    background-color: #2a2c30;
    color: #999;
}
#ccm-panel-blocks_cloner-copy header {
    color: #3baaf7;
    background-color: #202226;
}
#ccm-panel-blocks_cloner-copy li {
    color: #999;
}
#ccm-panel-blocks_cloner-copy li a {
    padding: 0;
}
</style>
<section id="blocks_cloner-copy" v-cloak>
    <header><?= t('Copy Block') ?></header>
    <div v-if="items.length === 0" class="alert alert-info">
        <?= t('No blocks found in the page') ?>
    </div>
    <menu v-else>
        <li
            v-for="item in flatItems"
            v-bind:key="`${item.type}-${item.id}`"
            style="white-space: nowrap"
            v-bind:style="{'margin-left': (item.depth * 1) + 'rem'}"
        >
            <a
                v-if="item.type === 'area' || item.children.length > 0"
                style="text-decoration: none; display: inline"
                href="javascript:void(0)"
                v-on:click.prevent="item.expanded = item.children.length === 0 || !item.expanded"
            >
                {{ item.expanded ? '\u229f' : '\u229e' }}
                <span
                    v-if="item.type !== 'block'"
                    v-on:mouseenter="highlight(item, true)"
                    v-on:mouseleave="highlight(item, false)"
                >
                    {{ item.displayName }}
                    <span v-if="item.isGlobal">(<?= tc('Area', 'sitewide') ?>)</span>
                </span>
            </a>
            <span v-else style="opacity: 0.5">&#x22A1;</span>
            <a
                v-if="item.type === 'block'"
                style="text-decoration: none; display: inline"
                v-bind:dialog-title="`<?= t('Export %s', '${item.displayName}') ?>`"
                class="dialog-launch"
                dialog-width="90%"
                dialog-height="80%"
                v-bind:href="`${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/copy/export?cID=<?= $cID ?>&&bId=${item.id}`"
                v-on:mouseenter="highlight(item, true)"
                v-on:mouseleave="highlight(item, false)"
            >
                <strong>{{item.displayName }}</strong>
            </a>
        </li>
    </menu>
</section>
<?php
$view->markFooterAssetPosition();
?>
<script>$(document).ready(function() {
new Vue({
    el: '#blocks_cloner-copy',
    data() {
        const items = window.blocksCloner.getPageStructure({
            skipAreasWithoutBlocks: true,
            blockTypeNames: <?= json_encode($blockTypeNames) ?>,
        });
        const walk = function(item, depth) {
            item.depth = depth;
            item.expanded = depth === 0 || item.children.length === 0;
            item.children.forEach((child) => walk(child, depth + 1));
        };
        items.forEach((item) => walk(item, 0));
        return {
            items,
        };
    },
    computed: {
        flatItems() {
            const result = [];
            const walk = function(item) {
                result.push(item);
                if (item.expanded) {
                    item.children.forEach((child) => walk(child));
                }
            };
            this.items.forEach((item) => walk(item));
            this.$nextTick(() => setTimeout(
                () => $('#blocks_cloner-copy').find('.dialog-launch').dialog(),
                10
            ));
            return result;
        },
    },
    methods: {
        highlight(item, highlight) {
            window.blocksCloner.setItemHighlighted(item, highlight, highlight);
        },
    },
});

});</script>