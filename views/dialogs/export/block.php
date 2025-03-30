<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\BlocksCloner\Controller\Dialog\Export\Block $controller
 * @var Concrete\Core\View\View $view
 * @var int $cID
 * @var string $xml
 * @var array $references
 * @var Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface $resolverManager
 */

$view->element('vue/export_viewer', null, 'blocks_cloner');
?>
<div id="ccm-blockscloker-export-block" style="display: flex; flex-direction: column; height: 100%;" v-cloak>
    <blocks-cloner-export-viewer
        v-bind:cid="<?= $cID ?>"
        v-bind:references="<?= h(json_encode($references)) ?>"
        v-bind:xml="<?= h(json_encode($xml)) ?>"
    ></blocks-cloner-references-viewer>
</div>

<script>$(document).ready(function() {

new Vue({
    el: '#ccm-blockscloker-export-block',
});

});</script>
