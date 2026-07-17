import type jQuery from 'jquery';

import {getBlockTypeName} from './i18n';
import {tryScrollIntoView} from './highlighter';

enum Type {
  Area = 'area',
  Block = 'block',
}

let editMode = null;
function getEditMode() {
  return (editMode ??= window.Concrete.getEditMode());
}

type CCMMenuOpener = () => void;

function getCCMObject(item: Area | Block): any {
  switch (item.type) {
    case Type.Area:
      return getEditMode().getAreaByID(item.id);
    case Type.Block:
      return getEditMode().getBlockByID(item.id);
  }
}

function createCCMMenuOpener(item: Area | Block): CCMMenuOpener | undefined {
  const ccmObject = getCCMObject(item);
  if (!ccmObject) {
    return;
  }
  const ccmMenu = ccmObject.getMenu?.();
  if (ccmMenu?.hoverProxy && ccmMenu?.$launcher?.length) {
    return () => standardCCMMenuOpener(item);
  }
}

function standardCCMMenuOpener(item: Area | Block): void {
  if (!window.ConcreteMenuManager?.enabled) {
    return;
  }
  const ccmObject = getCCMObject(item);
  const ccmMenu = ccmObject?.getMenu?.();
  const numLaunchers = ccmMenu?.$launcher?.length || 0;
  if (numLaunchers === 0) {
    return;
  }
  const activeMenu = window.ConcreteMenuManager.getActiveMenu();
  if (activeMenu) {
    activeMenu.hide();
  }
  const launcher = ccmMenu?.$launcher[0] as HTMLElement;
  const rect = launcher.getBoundingClientRect();
  const centerX = rect.left + rect.width / 2;
  const centerY = rect.top + rect.height / 2;
  const mouseMoveEvent = $.Event('mousemove', {
    clientX: centerX,
    clientY: centerY,
    pageX: centerX + window.scrollX,
    pageY: centerY + window.scrollY,
  });
  if (ccmMenu.hoverProxy(mouseMoveEvent, $(launcher)) === false) {
    return;
  }
  setTimeout(() => {
    const clickEvent = $.Event('click', {
      clientX: centerX,
      clientY: centerY,
      pageX: centerX + window.scrollX,
      pageY: centerY + window.scrollY,
    });
    window.ConcreteMenuManager.$clickProxy.trigger(clickEvent);
    setTimeout(() => {
      const menuEl = document.querySelector('#ccm-popover-menu-container .popover') as HTMLElement | null;
      if (menuEl) {
        tryScrollIntoView(menuEl);
      }
    }, 200);
  }, 10);
}

interface GetPageStructureOptions {
  skipAreasWithoutBlocks?: boolean;
  skipBlocksWithoutChildAreas?: boolean;
}

interface Container {
  children: Array<Area | Block>;
}
interface BaseItem extends Container {
  element: HTMLElement;
  type: Type;
  id: number;
  handle: string;
  displayName: string;
  openContextMenu?: () => void;
}
export interface Area extends BaseItem {
  type: Type.Area;
  isGlobal: boolean;
  enableGridContainer: boolean;
}
export interface Block extends BaseItem {
  type: Type.Block;
}

export function parseArea(element: HTMLElement): Area | null {
  if (element.tagName !== 'DIV') {
    return null;
  }
  const id = Number(element.dataset.areaId) || 0;
  if (id <= 0) {
    return null;
  }
  const handle = element.dataset.areaHandle;
  if (!handle) {
    return null;
  }
  const displayName = element.dataset.areaDisplayName;
  if (!displayName) {
    return null;
  }
  const result: Area = {
    type: Type.Area,
    element,
    id,
    handle,
    displayName,
    isGlobal: element.classList.contains('ccm-global-area'),
    enableGridContainer: ['1', 'true'].includes(element.dataset.areaEnableGridContainer || ''),
    children: [],
  };
  const openContextMenu = createCCMMenuOpener(result);
  if (openContextMenu) {
    result.openContextMenu = openContextMenu;
  }
  return result;
}

export function parseBlock(element: HTMLElement): Block | null {
  if (element.tagName !== 'DIV') {
    return null;
  }
  const id = Number(element.dataset.blockId) || 0;
  if (id <= 0) {
    return null;
  }
  const handle = element.dataset.blockTypeHandle;
  if (!handle) {
    return null;
  }
  const result: Block = {
    type: Type.Block,
    element,
    id,
    handle,
    displayName: getBlockTypeName(handle) || handle,
    children: [],
  };
  const openContextMenu = createCCMMenuOpener(result);
  if (openContextMenu) {
    result.openContextMenu = openContextMenu;
  }
  return result;
}

export function getPageStructure(options?: GetPageStructureOptions): Area[] {
  let rootElement = getEditingStackID() ? (document.querySelector('#ccm-stack-container') as HTMLElement) : null;
  return getPageStructureStartingAt(rootElement || document.body, options).filter((item) => item.type === Type.Area) as Area[];
}

export function getPageStructureStartingAt(element: HTMLElement, options?: GetPageStructureOptions): Array<Area | Block> {
  options = Object.assign(
    {
      skipAreasWithoutBlocks: false,
      skipBlocksWithoutChildAreas: false,
    },
    options || {},
  );
  const container: Container = {children: []};
  parse(element, container, options);

  return container.children.filter((item) => {
    switch (item.type) {
      case Type.Area:
        return !options.skipAreasWithoutBlocks || item.children.length > 0;
      case Type.Block:
        return !options.skipBlocksWithoutChildAreas || item.children.length > 0;
    }
  });
}

function parse(element: HTMLElement, parent: Container, options: GetPageStructureOptions): void {
  const area = parseArea(element);
  const block = area ? null : parseBlock(element);
  const itemForElement = area || block;
  let appendTo = parent;
  if (itemForElement) {
    parent.children.push(itemForElement);
    appendTo = itemForElement;
  }
  (Array.from(element.children) as HTMLElement[]).forEach((child) => parse(child, appendTo, options));
  if (block !== null && options.skipBlocksWithoutChildAreas) {
    if (!itemHasChildrenOfType(block, Type.Area)) {
      parent.children.splice(parent.children.indexOf(block), 1);
    }
  }
  if (area !== null && options.skipAreasWithoutBlocks) {
    if (!itemHasChildrenOfType(area, Type.Block)) {
      parent.children.splice(parent.children.indexOf(area), 1);
    }
  }
}

function itemHasChildrenOfType(item: Area | Block, type: Type): boolean {
  if (item.children.some((child) => child.type === type)) {
    return true;
  }
  return item.children.some((child) => itemHasChildrenOfType(child, type));
}

export function findParentArea(element: HTMLElement): Area | null {
  let parent = element.parentElement;
  while (parent !== null) {
    const area = parseArea(parent);
    if (area !== null) {
      return area;
    }
    parent = parent.parentElement;
  }
  return null;
}

export function getEditingStackID(): number | null {
  if (!window.CCM_CID || window.CCM_CID != window.ccmBlocksClonerDynamicData?.stackEditPageID) {
    return null;
  }
  const editContainer = document.querySelector('#ccm-stack-container');
  const mainAreaElement = editContainer?.querySelector('[data-area-handle="Main"][data-cID]');
  const cID = Number(mainAreaElement?.getAttribute('data-cID'));

  return cID || null;
}
