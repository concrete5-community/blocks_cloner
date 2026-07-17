<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\BlocksCloner\Controller\Panel\Export $controller
 * @var Concrete\Core\View\View $view
 * @var int $cID
 */

$view->markHeaderAssetPosition();
$view->element('panel_style', ['panelID' => 'export'], 'blocks_cloner');
?>
<div id="blocks_cloner-export" v-cloak>
    <section>
        <header><?= t('Export as XML') ?></header>
        <div class="section-body text-center">
            <a
                class="dialog-launch text-nowap"
                href="#"
                dialog-title="<?= h(t('Export page attributes as XML')) ?>"
                dialog-width="90%"
                dialog-height="80%"
                v-bind:href="`${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/export/attributes?cID=<?= $cID ?>`"
            ><?= t('Export page attributes') ?></a>
        </div>
    </section>
    <section>
        <header>
            <?= t('Export content of') ?>
            (<a href="#" v-on:click.prevent="refreshPageStructure(true)" title="<?= t('Refresh') ?>">&#8635;</a>)
        </header>
        <div class="section-body">
            <div v-if="items.length === 0 && !refreshing" class="alert alert-info">
                <?= t('No blocks found in the page') ?>
            </div>
            <div v-else-if="!refreshing">
                <menu class="blocks_cloner-ccmtree">
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
                    <a class="small" href="#" v-on:click.prevent="setAllExpanded(true)"><?= t('Expand All') ?></a>
                    |
                    <a class="small" href="#" v-on:click.prevent="setAllExpanded(false)"><?= t('Collapse All') ?></a>
                </div>
            </div>
        </div>
    </section>
</div>
<?php
$view->markFooterAssetPosition();
?>
<script>$(document).ready(function() {
new Vue({
    el: '#blocks_cloner-export',
    data() {
        return {
            CCM_DISPATCHER_FILENAME: window.CCM_DISPATCHER_FILENAME,
            refreshing: false,
            items: [],
        };
    },
    beforeMount() {
        this.refreshPageStructure();
    },
    mounted() {
        this.setupLaunchDialogElements();
    },
    computed: {
        flatItems() {
            const result = [];
            const walk = (item) => {
                result.push(item);
                if (item.expanded) {
                    item.children.forEach((child) => walk(child));
                }
            };
            this.items.forEach((item) => walk(item));
            this.setupLaunchDialogElements();
            return result;
        },
    },
    methods: {
        refreshPageStructure(delayed, previousState) {
            if (!previousState) {
                previousState = {};
                const walk = (item) => {
                    previousState[`${item.type}@${item.id}`] = {expanded: item.expanded}
                    item.children.forEach((child) => walk(child));
                }
                this.items.forEach((item) => walk(item));
            }
            this.refreshing = true;
            this.items.splice(0, this.items.length);
            if (delayed) {
                setTimeout(() => this.refreshPageStructure(false, previousState), 100);
                return;
            }
            const items = window.ccmBlocksCloner.getPageStructure();
            const walk = (item, depth) => {
                item.depth = depth;
                item.expanded = previousState[`${item.type}@${item.id}`]?.expanded ?? (depth === 0);
                item.children.forEach((child) => walk(child, depth + 1));
            };
            items.forEach((item) => walk(item, 0));
            items.forEach(item => this.items.push(item));
            this.refreshing = false;
            this.setupLaunchDialogElements();
        },
        setupLaunchDialogElements(immediate) {
            if (immediate) {
                $('#blocks_cloner-export').find('.dialog-launch').dialog();
            } else {
                this.$nextTick(() => setTimeout(() => this.setupLaunchDialogElements(true), 100));
            }
        },
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
