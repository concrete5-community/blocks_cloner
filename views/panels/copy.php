<?php
/**
 * @var Concrete\Package\BlocksCloner\Controller\Panel\Copy $controller
 * @var Concrete\Core\View\View $view
 * @var int $cID
 */
$view->markHeaderAssetPosition();

?>
<div id="blocks_cloner-copy" v-cloak>
    <ul class="list-unstyled">
        <li v-for="item in flatItems" :style="{'margin-left': (item.depth * 2) + 'rem'}">
            <a
                v-if="item.type === 'area' || item.children.length > 0"
                style="text-decoration: none"
                href="#"
                @click.prevent="item.expanded = !item.expanded"
            >
                {{ item.expanded ? '\u25bc' : '\u25b6' }}
                <span v-if="item.type !== 'block'">
                    {{ item.displayName }}
                </span>
            </a>
            <a
                v-if="item.type === 'block'"
                style="text-decoration: none"
                dialog-title="<?= t('Export')?>"
                class="dialog-launch"
                dialog-width="90%"
                dialog-height="70%"
                :href="`${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/panels/copy/export?cID=<?= $cID ?>&&bId=${item.id}`"
            ><strong>{{ item.displayName }}</strong></a>
        </li>
    </ul>
</div>
<?php
$view->markFooterAssetPosition();
?>
<script>$(document).ready(function() {
new Vue({
    el: '#blocks_cloner-copy',
    data() {
        const items = window.blocksCloner.getPageStructure(true);
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
            return result;
        },
    },
});

});</script>