;(function(global) {
'use strict';

if (global.ccmBlocksCloner) {
    return;
}

const TYPE_AREA = 'area';
const TYPE_BLOCK = 'block';

/**
 * @typedef {Object} BlocksClonerOptions
 * @property {boolean|undefined} skipAreasWithoutBlocks
 * @property {boolean|undefined} skipBlocksWithoutChildAreas
 */

/**
 * @typedef {Object} BlocksCloner~Container
 * @property {Array<BlocksClonerArea|BlocksClonerBlock>} children
 */

/**
 * @typedef {Object} BlocksCloner~BaseItem
 * @extends {BlocksCloner~Container}
 * @property {HTMLElement} element
 * @property {string} type
 * @property {number} id
 * @property {string} handle
 * @property {string} displayName
 */

/**
 * @typedef {Object} BlocksClonerBlock
 * @extends {BlocksCloner~BaseItem}
 * @property {TYPE_BLOCK} type
 */

/**
 * @typedef {Object} BlocksClonerArea
 * @extends {BlocksCloner~BaseItem}
 * @property {TYPE_AREA} type
 * @property {boolean} isGlobal
 */

/**
 * @param {string} key 
 *
 * @returns {string|null}
 */
function localize(key)
{
    if (global.ccmBlocksClonerI18N && global.ccmBlocksClonerI18N.hasOwnProperty(key)) {
        return global.ccmBlocksClonerI18N[key];
    }
    return null;
}

/**
 * @param {string} handle
 *
 * @returns {string|null}
 */
function getBlockTypeName(handle)
{
    if (global.ccmBlocksClonerI18N && global.ccmBlocksClonerI18N && global.ccmBlocksClonerI18N.blockTypeNames && global.ccmBlocksClonerI18N.blockTypeNames.hasOwnProperty(handle)) {
        return global.ccmBlocksClonerI18N.blockTypeNames[handle];
    }
    return null;

}
/**
 * @param {BlocksClonerOptions|undefined} options
 *
 * @returns {BlocksClonerArea[]}
 */
function getPageStructure(options)
{
    options = Object.assign({
        skipAreasWithoutBlocks: false,
        skipBlocksWithoutChildAreas: false,
    }, options || {});
    const container = {children: []};
    parse(document.body, container, options);
    return container.children.filter((item) => item.type === TYPE_AREA && (!options.skipAreasWithoutBlocks || item.children.length > 0));
}

/**
 * @param {HTMLElement} element
 * @param {BlocksCloner~Container} parent
 * @param {BlocksClonerOptions} options
 *
 * @returns {void}
 */
function parse(element, parent, options)
{
    const area = parseArea(element, options);
    const block = area ? null : parseBlock(element, options);
    const itemForElement = area || block;
    let appendTo = parent;
    if (itemForElement) {
        parent.children.push(itemForElement);
        appendTo = itemForElement;
    }
    for (const childElement of element.children) {
        parse(childElement, appendTo, options);
    }
    if (block !== null && options.skipBlocksWithoutChildAreas) {
        if (!itemHasChildrenOfType(block, TYPE_AREA)) {
            parent.children.splice(parent.children.indexOf(block), 1);
        }
    }
    if (area !== null && options.skipAreasWithoutBlocks) {
        if (!itemHasChildrenOfType(area, TYPE_BLOCK)) {
            parent.children.splice(parent.children.indexOf(area), 1);
        }
    }
}

function itemHasChildrenOfType(item, type)
{
    if (item.children.some((child) => child.type === type)) {
        return true;
    }
    return item.children.some((child) => itemHasChildrenOfType(child, type));
}

/**
 * @param {HTMLElement} element
 * @param {BlocksClonerOptions|undefined} options
 *
 * @returns {BlocksClonerArea|null}
 */
function parseArea(element, options)
{
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
        type: TYPE_AREA,
        element,
        id,
        handle,
        displayName,
        isGlobal: element.classList.contains('ccm-global-area'),
        children: [],
    };
}


/**
 * @param {HTMLElement} element
 * @param {BlocksClonerOptions|undefined} options
 *
 * @returns {BlocksClonerBlock|null}
 */
function parseBlock(element, options)
{
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
    const displayName = getBlockTypeName(handle) || handle;
    return {
        type: TYPE_BLOCK,
        element,
        id,
        handle,
        displayName,
        children: [],
    };
}

/**
 * @type {HTMLElement|null}
 */
let currentItemHighlighed = null;

/**
 * @param {HTMLElement} el
 */
function checkElementInViewport(el, callback) {
    const onIntersection = function(entries, observer) {
        callback(entries.some((entry) => entry.isIntersecting));
        observer.disconnect();
    };
    const options = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1,
    };
    const observer = new IntersectionObserver(onIntersection, options);
    observer.observe(el);
}
/**
 * @param {HTMLElement} el
 * @param {boolean} highlight
 *
 * @returns {void}
 */
function tryScrollIntoView(el)
{
    if (!el.scrollIntoView) {
        return false;
    }
    checkElementInViewport(el, (isVisible) => {
        if (!isVisible) {
            try {
                el.scrollIntoView({behavior: 'smooth', block: 'nearest', inline: 'start'});
            } catch (e) {
            }
        }
    });
}

/**
 * @param {BlocksClonerArea|BlocksClonerBlock} item
 * @param {boolean} highlight
 * @param {boolean|undefined} highliensureVisibleght
 *
 * @returns {void}
 */
function setItemHighlighted(item, highlight, ensureVisible)
{
    const el = item.element;
    highlight = !!highlight;
    if (highlight) {
        if (currentItemHighlighed) {
            if (ensureVisible) {
                tryScrollIntoView(currentItemHighlighed);
            }
            return;
        }
        if (currentItemHighlighed) {
            setElementHighlighted(currentItemHighlighed, false);
        }
    }
    const wasHighlighed = !!el.dataset.blocksClonerHighlighted;
    if (highlight !== wasHighlighed) {
        if (highlight) {
            el.dataset.blocksClonerRestoreOutline = el.style.outline || '';
            el.style.outline = '2px solid red';
            el.dataset.blocksClonerHighlighted = '1';
            currentItemHighlighed = el;
        } else {
            el.style.outline = el.dataset.blocksClonerRestoreOutline || '';
            delete el.dataset.blocksClonerHighlighted;
            delete el.dataset.blocksClonerRestoreOutline;
            currentItemHighlighed = null;
        }
    }
    if (ensureVisible) {
        tryScrollIntoView(currentItemHighlighed);
    }
}

function injectMenuItems(menu, menuElement)
{
    const sourceElement = menu.$element && menu.$element.length === 1 ? menu.$element[0] : null;
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

function setupAreaMenu(menu, menuElement, area)
{
    if (menuElement.find('a[data-ccm-blocks-cloner]').length) {
        return;
    }
    const $after = menuElement.find('a:last');
    if (!$after.length) {
        return;
    }
    $after.after($('<a data-ccm-blocks-cloner />')
        .attr('dialog-title', `Import to ${area.displayName}`)
        .attr('class', 'dialog-launch dropdown-item')
        .attr('dialog-width', '90%')
        .attr('dialog-height', '80%')
        .attr('href', `${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/paste/import?cID=${CCM_CID}&aID=${area.id}&aHandle=${encodeURIComponent(area.handle)}`)
        .text(localize('import') || 'Import')
        .dialog()
    );
}

function setupBlockMenu(menu, menuElement, block)
{
    if (menuElement.find('a[data-ccm-blocks-cloner]').length) {
        return;
    }
    const $after = menuElement.find('a:last');
    if (!$after.length) {
        return;
    }
    $after.after($('<a data-ccm-blocks-cloner />')
        .attr('dialog-title', `Export ${block.displayName}`)
        .attr('class', 'dialog-launch dropdown-item')
        .attr('dialog-width', '90%')
        .attr('dialog-height', '80%')
        .attr('href', `${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/copy/export?cID=${CCM_CID}&bID=${block.id}`)
        .text(localize('export')|| 'Export')
        .dialog()
    );
}

global.ccmBlocksCloner = {
    getPageStructure,
    setItemHighlighted,
};

document.addEventListener('DOMContentLoaded', () => {
    ConcreteEvent.subscribe('ConcreteMenuShow', function(e, args) {
        if (args && args.menu && args.menuElement) {
            injectMenuItems(args.menu, args.menuElement);
        }
    });  
});

})(window);
