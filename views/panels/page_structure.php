<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\BlocksCloner\Controller\Panel\PageStructure $controller
 * @var Concrete\Core\View\View $view
 */

$view->markHeaderAssetPosition();
$view->element('panel_style', ['panelID' => 'page_structure'], 'blocks_cloner');
?>
<div id="blocks_cloner-page_structure" v-cloak>
    <section>
        <header>
            <?= t('Page structure') ?>
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
                            href="#"
                            v-bind:style="item.children.length === 0 ? {cursor: 'default'} : {}"
                            v-on:mouseenter="highlight(item, true)"
                            v-on:mouseleave="highlight(item, false)"
                            v-on:click.prevent="item.expanded = item.children.length === 0 || !item.expanded"
                        >
                            <span v-if="item.children.length">
                                {{ item.expanded ? '\u229f' : '\u229e' }}
                            </span>
                            <span v-else style="opacity: 0.5">
                                &#x22A1;
                            </span>
                            {{ item.displayName }}
                            <span v-if="item.type === 'area' && item.isGlobal">(<?= tc('Area', 'sitewide') ?>)</span>
                        </a>
                        <a
                            v-if="item.openContextMenu"
                            style="text-decoration: none; display: inline; margin-left: 0.3rem"
                            href="#"
                            v-on:click.prevent.stop="item.openContextMenu()"
                        >
                            &#9776;
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
    el: '#blocks_cloner-page_structure',
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
        this.$nextTick(() => setTimeout(() => this.setupLaunchDialogElements(), 100));
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
            return result;
        },
    },
    methods: {
        refreshPageStructure(delayed) {
            this.refreshing = true;
            this.items.splice(0, this.items.length);
            if (delayed) {
                setTimeout(() => this.refreshPageStructure(), 100);
                return;
            }
            const items = window.ccmBlocksCloner.getPageStructure({});
            const walk = function(item, depth) {
                item.depth = depth;
                item.expanded = depth === 0 || item.children.length === 0;
                item.children.forEach((child) => walk(child, depth + 1));
            };
            items.forEach((item) => walk(item, 0));
            items.forEach(item => this.items.push(item));
            this.refreshing = false;
        },
        setupLaunchDialogElements() {
            $('#blocks_cloner-page_structure').find('.dialog-launch').dialog();
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
