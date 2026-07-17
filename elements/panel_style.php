<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\View\View $view
 * @var string $panelID
 */

?>
<style>
#ccm-panel-blocks_cloner-<?= $panelID ?> section:not(:first-child) {
    margin-top: 20px;
}
#ccm-panel-blocks_cloner-<?= $panelID ?> .section-body {
    margin-top: 0.9rem;
}
#ccm-panel-blocks_cloner-<?= $panelID ?> menu.blocks_cloner-ccmtree {
    margin: 0;
}
#ccm-panel-blocks_cloner-<?= $panelID ?> menu.blocks_cloner-ccmtree a {
    padding: 0;
    display: inline;
}
<?php
if (version_compare(APP_VERSION, '9') < 0) {
    ?>
    #ccm-panel-blocks_cloner-<?= $panelID ?> {
        background-color: #2a2c30;
        color: #999;
    }
    #ccm-panel-blocks_cloner-<?= $panelID ?> section header {
        color: #3baaf7;
        background-color: #202226;
    }
    #ccm-panel-blocks_cloner-<?= $panelID ?> .section-body {
        margin-left: 15px;
        margin-right: 15px;
    }
    #ccm-panel-blocks_cloner-<?= $panelID ?> menu.blocks_cloner-ccmtree li {
        color: #999;
    }
    <?php
} else {
    ?>
    #ccm-panel-blocks_cloner-<?= $panelID ?> section header {
        font-weight: 600;
        font-size: 1.25rem;
        line-height: 1.2;
        color: var(--bs-heading-color);
    }
    #ccm-panel-blocks_cloner-<?= $panelID ?> .section-body {
        border-top: 1px solid #e9ecef;
    }
    #ccm-panel-blocks_cloner-<?= $panelID ?> menu.blocks_cloner-ccmtree {
        border-top-width: 0;
    }
    <?php
}
?>
</style>
