import {getBlockTypeName} from './i18n';

enum Type {
  Area = 'area',
  Block = 'block',
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
  return {
    type: Type.Area,
    element,
    id,
    handle,
    displayName,
    isGlobal: element.classList.contains('ccm-global-area'),
    enableGridContainer: ['1', 'true'].includes(element.dataset.areaEnableGridContainer || ''),
    children: [],
  };
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
  return {
    type: Type.Block,
    element,
    id,
    handle,
    displayName: getBlockTypeName(handle) || handle,
    children: [],
  };
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
