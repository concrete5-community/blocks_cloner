<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\BlocksCloner\Controller\Panel\Export $controller
 * @var Concrete\Core\View\View $view
 * @var int $cID
 */

$view->markHeaderAssetPosition();
?>
<style>
#ccm-panel-blocks_cloner-export {
    background-color: #2a2c30;
    color: #999;
}
#ccm-panel-blocks_cloner-export header {
    color: #3baaf7;
    background-color: #202226;
}
#ccm-panel-blocks_cloner-export li {
    color: #999;
}
#ccm-panel-blocks_cloner-export li a {
    padding: 0;
}
</style>
<section id="blocks_cloner-export" v-cloak>
    <header><?= t('Export as XML') ?></header>
    <div v-if="items.length === 0" class="alert alert-info">
        <?= t('No blocks found in the page') ?>
    </div>
    <menu v-else>
        <li
            v-for="item in flatItems"
            v-bind:key="`${item.type}-${item.id}`"
            class="text-nowrap"
            v-bind:style="{'margin-left': (item.depth * 1) + 'rem'}"
        >
            <a
                v-if="item.children.length > 0"
                style="text-decoration: none; display: inline"
                href="javascript:void(0)"
                v-on:click.prevent="item.expanded = item.children.length === 0 || !item.expanded"
            >
                {{ item.expanded ? '\u229f' : '\u229e' }}
            </a>
            <span v-else style="opacity: 0.5">&#x22A1;</span>
            <a
                style="text-decoration: none; display: inline"
                v-bind:dialog-title="item.type === 'block' ? `<?= t('Export %s block as XML', '${item.displayName}') ?>` : `<?= t('Export %s area as XML', '${item.displayName}') ?>`"
                class="dialog-launch"
                dialog-width="90%"
                dialog-height="80%"
                v-bind:href="getItemExportUrl(item)"
                v-on:mouseenter="highlight(item, true)"
                v-on:mouseleave="highlight(item, false)"
            >
                <strong>{{item.displayName }}</strong>
            </a>
        </li>
    </menu>
    <div style="margin-top: 10px" class="text-center">
        <a class="small" href="#" v-on:click="setAllExpanded(true)"><?= t('Expand All') ?></a>
        |
        <a class="small" href="#" v-on:click="setAllExpanded(false)"><?= t('Collapse All') ?></a>
    </div>
</section>
<?php
$view->markFooterAssetPosition();
?>
<script>$(document).ready(function() {
new Vue({
    el: '#blocks_cloner-export',
    data() {
        const items = window.ccmBlocksCloner.getPageStructure();
        const walk = function(item, depth) {
            item.depth = depth;
            item.expanded = depth === 0;
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
                () => $('#blocks_cloner-export').find('.dialog-launch').dialog(),
                10
            ));
            return result;
        },
    },
    methods: {
        highlight(item, highlight) {
            window.ccmBlocksCloner.setElementHighlighted(item.element, highlight, highlight);
        },
        setAllExpanded(expanded) {
            const walk = (item) => {
                if (!item.children.length) {
                    return;
                }
                item.expanded = expanded;
                item.children.forEach((child) => walk(child));
            };
            this.items.forEach((item) => walk(item));
        },
        getItemExportUrl(item) {
            switch (item.type) {
                case 'area':
                    return `${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/export/area?cID=<?= $cID ?>&aHandle=${encodeURIComponent(item.handle)}&aID=${item.id}`;
                case 'block':
                    const area = window.ccmBlocksCloner.findParentArea(item.element);
                    if (!area) {
                        console.error('Failed to find the parent area for the element', element);
                        return '';
                    }
                    return `${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/export/block?cID=<?= $cID ?>&aHandle=${encodeURIComponent(area.handle)}&bID=${item.id}`;
            }
        },
    },
});

});</script>
