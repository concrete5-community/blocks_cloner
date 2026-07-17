<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\BlocksCloner\Controller\Panel\Import $controller
 * @var Concrete\Core\View\View $view
 * @var int $cID
 */

$view->markHeaderAssetPosition();
$view->element('panel_style', ['panelID' => 'import'], 'blocks_cloner');
?>
<div id="blocks_cloner-import" v-cloak>
    <section>
        <header><?= t('Import from XML') ?></header>
        <div class="section-body text-center">
            <a
                class="dialog-launch text-nowap"
                href="#"
                dialog-title="<?= h(t('Import page attributes from XML')) ?>"
                dialog-width="90%"
                dialog-height="80%"
                v-bind:href="`${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/import?cID=<?= $cID ?>`"
            ><?= t('Import page attributes') ?></a>
        </div>
    </section>
    <section>
        <header>
            <?= t('Import content into') ?>
            (<a href="#" v-on:click.prevent="refreshPageStructure(true)" title="<?= t('Refresh') ?>">&#8635;</a>)
        </header>
        <div class="section-body">
            <div v-if="items.length === 0 && !refreshing" class="alert alert-info">
                <?= t('No areas found in the page') ?>
            </div>
            <div v-else-if="!refreshing">
                <menu class="blocks_cloner-ccmtree">
                    <li
                        v-for="item in flatItems"
                        v-bind:key="item.id"
                        class="text-nowrap"
                        v-bind:style="{'margin-left': (item.depth * 1) + 'rem'}"
                    >
                        <a
                            v-if="item.type !== 'area' || item.children.length > 0"
                            style="text-decoration: none; display: inline"
                            href="javascript:void(0)"
                            v-on:click.prevent="item.expanded = item.children.length === 0 || !item.expanded"
                        >
                            {{ item.expanded ? '\u229f' : '\u229e' }}
                            <span
                                v-if="item.type !== 'area'"
                                v-on:mouseenter="highlight(item, true)"
                                v-on:mouseleave="highlight(item, false)"
                            >
                                {{ item.displayName }}
                            </span>
                        </a>
                        <span v-else style="opacity: 0.5">&#x22A1;</span>
                        <a
                            v-if="item.type === 'area'"
                            style="text-decoration: none; display: inline"
                            v-bind:dialog-title="`<?= t('Import content from XML into %s', '${item.displayName}') ?>`"
                            class="dialog-launch"
                            dialog-width="90%"
                            dialog-height="80%"
                            v-bind:href="`${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/import?cID=<?= $cID ?>&aID=${item.id}&aHandle=${encodeURIComponent(item.handle)}`"
                            v-on:mouseenter="highlight(item, true)"
                            v-on:mouseleave="highlight(item, false)"
                        >
                            <strong>
                                {{ item.displayName }}
                                <span v-if="item.isGlobal">(<?= tc('Area', 'sitewide') ?>)</span>
                            </strong>
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
    el: '#blocks_cloner-import',
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
            const items = window.ccmBlocksCloner.getPageStructure({
                skipBlocksWithoutChildAreas: true,
            });
            const walk = (item, depth) => {
                item.depth = depth;
                item.expanded = previousState[`${item.type}@${item.id}`]?.expanded ?? (depth === 0 || item.children.length === 0);
                item.children.forEach((child) => walk(child, depth + 1));
            };
            items.forEach((item) => walk(item, 0));
            items.forEach(item => this.items.push(item));
            this.refreshing = false;
            this.setupLaunchDialogElements();
        },
        setupLaunchDialogElements(immediate) {
            if (immediate) {
                $('#blocks_cloner-import').find('.dialog-launch').dialog();
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
    },
});

});</script>
