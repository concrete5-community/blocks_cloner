<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\BlocksCloner\Controller\Dialog\Export\Area $controller
 * @var Concrete\Core\View\View $view
 * @var int $cID
 * @var array $variants
 */

$view->element('vue/export_viewer', null, 'blocks_cloner');
?>
<div id="ccm-blockscloker-export-area" style="display: flex; flex-direction: column; height: 100%;" v-cloak>
    <div v-if="variants.length === 0" class="alert alert-info">
        <?= t('The area is empty') ?>
    </div>
    <div v-else style="display: flex; flex-direction: column; height: 100%;">
        <div style="display: flex; align-items: center; justify-content: center">
            <select v-if="variants.length > 1" v-model="selectedVariant" class="form-control text-center" style="max-width: 500px; font-weight: bold">
                <option v-for="v in variants" v-bind:value="v">{{ v.name }}</option>
            </select>
        </div>
        <blocks-cloner-export-viewer
            v-if="selectedVariant"
            v-bind:cid="<?= $cID ?>"
            v-bind:references="selectedVariant.data.references"
            v-bind:xml="selectedVariant.data.xml"
        ></blocks-cloner-references-viewer>

    </div>
</div>
<script>$(document).ready(function() {

new Vue({
    el: '#ccm-blockscloker-export-area',
    data() {
        return {
            variants: <?= json_encode($variants) ?>,
            selectedVariant: null,
            copyHighlighed: false,
        };
    },
    mounted() {
        if (this.variants.length) {
            this.selectedVariant = this.variants[0];
        }
    },
});

});</script>
