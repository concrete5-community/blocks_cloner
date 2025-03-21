;(function() {
'use strict';
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ConcretePanelManager === 'undefined') {
        return;
    }
    ConcretePanelManager.register({
        overlay: false,
        identifier: 'blocks_cloner-export',
        position: 'left',
        url: CCM_DISPATCHER_FILENAME + '/ccm/blocks_cloner/panels/export?cID=' + CCM_CID,
        pinable: false,
    });
});
})();
