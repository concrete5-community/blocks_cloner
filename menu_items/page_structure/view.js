;(function() {
'use strict';
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ConcretePanelManager === 'undefined') {
        return;
    }
    ConcretePanelManager.register({
        overlay: false,
        identifier: 'blocks_cloner-page_structure',
        position: 'left',
        url: CCM_DISPATCHER_FILENAME + '/ccm/blocks_cloner/panels/page_structure',
        pinable: false,
    });
});
})();
