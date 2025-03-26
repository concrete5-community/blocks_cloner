import {localize} from './i18n';
import {type Area, type Block, findParentArea, parseArea, parseBlock} from './page-structure';

interface Menu {
  $element?: JQuery;
}

function injectMenuItems(menu: Menu, menuElement: JQuery): void {
  const sourceElement = menu.$element?.length === 1 ? menu.$element[0] : null;
  if (!sourceElement) {
    return;
  }
  const area = parseArea(sourceElement);
  if (area !== null) {
    setupAreaMenu(menu, menuElement, area);
  } else {
    const block = parseBlock(sourceElement);
    if (block !== null) {
      setupBlockMenu(menu, menuElement, block);
    }
  }
}

function setupAreaMenu(menu: Menu, menuElement: JQuery, area: Area): void {
  if (menuElement.find('a[data-ccm-blocks-cloner]').length) {
    return;
  }
  const $after = menuElement.find('a:last');
  if ($after.length === 0) {
    return;
  }
  $after.after(
    $('<a data-ccm-blocks-cloner />')
      .attr('dialog-title', (localize('importBlockFromXmlIntoAreaName') || 'Import Block from XML into %s').replace('%s', area.displayName))
      .attr('class', 'dialog-launch dropdown-item')
      .attr('dialog-width', '90%')
      .attr('dialog-height', '80%')
      .attr('href', `${window.CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/import?cID=${window.CCM_CID}&aID=${area.id}&aHandle=${encodeURIComponent(area.handle)}`)
      .text(localize('importBlockFromXml') || 'Import Block from XML')
      .dialog(),
  );
}
function setupBlockMenu(menu: Menu, menuElement: JQuery, block: Block): void {
  if (menuElement.find('a[data-ccm-blocks-cloner]').length) {
    return;
  }
  const $after = menuElement.find('a:last');
  if ($after.length === 0) {
    return;
  }
  const area = findParentArea(block.element);
  if (area === null) {
    return;
  }
  $after.after(
    $('<a data-ccm-blocks-cloner />')
      .attr('dialog-title', (localize('exportBlockTypeNameAsXml') || 'Export %s block as XML').replace('%s', block.displayName))
      .attr('class', 'dialog-launch dropdown-item')
      .attr('dialog-width', '90%')
      .attr('dialog-height', '80%')
      .attr('href', `${window.CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/export?cID=${window.CCM_CID}&aHandle=${encodeURIComponent(area.handle)}&bID=${block.id}`)
      .text(localize('exportAsXml') || 'Export as XML')
      .dialog(),
  );
}

export function hook(): void {
  document.addEventListener('DOMContentLoaded', () => {
    window.ConcreteEvent?.subscribe('ConcreteMenuShow', function (e: any, args: any): void {
      const menu: Menu | undefined = args?.menu;
      const menuElement: JQuery | undefined = args?.menuElement;
      if (menu && menuElement) {
        injectMenuItems(menu, menuElement);
      }
    });
  });
}
