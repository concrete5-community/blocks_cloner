<?php
/**
 * @var Concrete\Package\BlocksCloner\Controller\Panel\Copy $controller
 * @var array $scopeItems
 * @var Concrete\Core\View\View $view
 */
$view->markHeaderAssetPosition();

?>
<div id="blocks_cloner-copy" v-cloak>
    <ul class="list-unstyled">
        <li v-for="item in flatItems" :style="{'margin-left': (item.depth * 10) + 'px'}">
            <a v-if="item.children.length" href="#" @click.prevent="item.expanded = !item.expanded" style="text-decoration: none">{{ item.expanded ? '\u25bc' : '\u25b6' }}</a>
            <a href="#" @click.prevent="pick(item)">{{ item.displayName || item.typeHandle }}</a>
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
        const items = window.blocksCloner.getPageStructure();
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
    methods: {
        pick(item) {
            window.alert(JSON.stringify(item));
        },
    },
});

});</script>