;(function() {
'use strict';
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ConcretePanelManager === 'undefined') {
        return;
    }
    ConcretePanelManager.register({
        overlay: false,
        identifier: 'blocks_cloner-copy',
        position: 'left',
        url: CCM_DISPATCHER_FILENAME + '/ccm/blocks_cloner/panels/copy?cID=' + CCM_CID,
        pinable: true,
    });
});
})();