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
  const id = Number(element.getAttribute('data-area-id')) || 0;
  if (id <= 0) {
    return null;
  }
  const handle = element.getAttribute('data-area-handle');
  if (!handle) {
    return null;
  }
  const displayName = element.getAttribute('data-area-display-name');
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
  const id = Number(element.getAttribute('data-block-id')) || 0;
  if (id <= 0) {
    return null;
  }
  const handle = element.getAttribute('data-block-type-handle');
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

/**
 * @param {BlocksClonerOptions|undefined} options
 *
 * @returns {BlocksClonerArea[]}
 */
export function getPageStructure(options?: GetPageStructureOptions): Area[] {
  options = Object.assign(
    {
      skipAreasWithoutBlocks: false,
      skipBlocksWithoutChildAreas: false,
    },
    options || {},
  );
  const container: Container = {children: []};
  parse(document.body, container, options);

  return container.children.filter((item) => item.type === Type.Area && (!options.skipAreasWithoutBlocks || item.children.length > 0)) as Area[];
}

/**
 * @param {HTMLElement} element
 * @param {BlocksCloner~Container} parent
 * @param {BlocksClonerOptions} options
 *
 * @returns {void}
 */
function parse(element: HTMLElement, parent: Container, options: GetPageStructureOptions): void {
  const area = parseArea(element);
  const block = area ? null : parseBlock(element);
  const itemForElement = area || block;
  let appendTo = parent;
  if (itemForElement) {
    parent.children.push(itemForElement);
    appendTo = itemForElement;
  }
  Array.from(element.children).forEach((child) => parse(child as HTMLElement, appendTo, options));
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
