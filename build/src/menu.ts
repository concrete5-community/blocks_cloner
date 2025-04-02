import {localize} from './i18n';
import {type Area, type Block, findParentArea, getEditingStackID, getPageStructure, parseArea, parseBlock} from './page-structure';

interface Menu {
  $element?: JQuery;
}

function getEditingCollectionID(): number {
  return getEditingStackID() || window.CCM_CID;
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
    const block = parseBlock(sourceElement) || findCoreContainerBlock(sourceElement);
    if (block !== null) {
      setupBlockMenu(menu, menuElement, block);
    }
  }
}

function findCoreContainerBlock(menuElement: HTMLElement): Block | null {
  const containerElement = menuElement.parentElement?.parentElement;
  if (containerElement?.dataset.container !== 'block') {
    return null;
  }
  const blockElement = containerElement.querySelector(':scope>div[data-block-type-handle="core_container"]') as HTMLElement | null;
  return blockElement ? parseBlock(blockElement) : null;
}

function setupAreaMenu(menu: Menu, menuElement: JQuery, area: Area): void {
  if (menuElement.find('a[data-ccm-blocks-cloner]').length) {
    return;
  }
  const $after = menuElement.find('a:last');
  if ($after.length === 0) {
    return;
  }
  $after
    .after(
      $('<a data-ccm-blocks-cloner />')
        .attr('dialog-title', localize('importFromXmlIntoAreaName', 'Import from XML into %s').replace('%s', area.displayName))
        .attr('class', 'dialog-launch dropdown-item')
        .attr('dialog-width', '90%')
        .attr('dialog-height', '80%')
        .attr('href', `${window.CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/import?cID=${getEditingCollectionID()}&aID=${area.id}&aHandle=${encodeURIComponent(area.handle)}`)
        .text(localize('importFromXml', 'Import from XML'))
        .dialog(),
    )
    .after(
      $('<a data-ccm-blocks-cloner />')
        .attr('dialog-title', localize('exportAreaNameAsXmlName', 'Export %s area as XML').replace('%s', area.displayName))
        .attr('class', 'dialog-launch dropdown-item')
        .attr('dialog-width', '90%')
        .attr('dialog-height', '80%')
        .attr('href', `${window.CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/export/area?cID=${getEditingCollectionID()}&aID=${area.id}&aHandle=${encodeURIComponent(area.handle)}`)
        .text(localize('exportAreaAsXml', 'Export Area as XML'))
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
      .attr('dialog-title', localize('exportBlockTypeNameAsXml', 'Export %s block as XML').replace('%s', block.displayName))
      .attr('class', 'dialog-launch dropdown-item')
      .attr('dialog-width', '90%')
      .attr('dialog-height', '80%')
      .attr('href', `${window.CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/export/block?cID=${getEditingCollectionID()}&aHandle=${encodeURIComponent(area.handle)}&bID=${block.id}`)
      .text(localize('exportBlockAsXml', 'Export Block as XML'))
      .dialog(),
  );
}

function injectStackMenuItems(stackID: number): void {
  let menuElement: HTMLElement | null = null;
  let version: number;

  if ((menuElement = document.querySelector('#ccm-dashboard-content-regular nav.navbar ul')) !== null) {
    version = 9;
  } else if ((menuElement = document.querySelector('#ccm-dashboard-content-inner nav.navbar ul')) !== null) {
    version = 8;
  } else {
    return;
  }
  if (menuElement.querySelector('li[data-ccm-blocks-cloner]')) {
    return;
  }
  const area = getPageStructure()[0];
  if (!area) {
    return;
  }
  $(menuElement)
    .append(
      $(`<li${version >= 9 ? ' class="nav-item"' : ''} />`).append(
        $('<a data-ccm-blocks-cloner />')
          .attr('dialog-title', localize('importFromXml', 'Import from XML'))
          .attr('class', `dialog-launch${version >= 9 ? ' nav-link' : ''}`)
          .attr('dialog-width', '90%')
          .attr('dialog-height', '80%')
          .attr('href', `${window.CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/import?cID=${stackID}&aID=${area.id}&aHandle=${encodeURIComponent(area.handle)}`)
          .text(localize('importFromXml', 'Import from XML'))
          .dialog(),
      ),
    )
    .append(
      $(`<li${version >= 9 ? ' class="nav-item"' : ''} />`).append(
        $('<a data-ccm-blocks-cloner />')
          .attr('dialog-title', localize('exportStackAsXml', 'Export Stack as XML'))
          .attr('class', `dialog-launch${version >= 9 ? ' nav-link' : ''}`)
          .attr('dialog-width', '90%')
          .attr('dialog-height', '80%')
          .attr('href', `${window.CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/export/area?cID=${stackID}&aID=${area.id}&aHandle=${encodeURIComponent(area.handle)}`)
          .text(localize('exportStackAsXml', 'Export Stack as XML'))
          .dialog(),
      ),
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
    const stackID = getEditingStackID();
    if (stackID !== null) {
      injectStackMenuItems(stackID);
    }
  });
}
