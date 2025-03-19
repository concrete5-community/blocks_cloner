;(function(global) {
'use strict';

if (global.blocksCloner?.getPageStructure) {
    return;
}

/**
 * @typedef {Object} Container
 * @property {Array<Area|Block>} children
 */

/**
 * @typedef {Container} BaseItem
 * @property {string} type
 * @property {number} id
 * @property {string} handle
 * @property {string} displayName
 */

/**
 * @typedef {BaseItem} Block
 * @property {'block'} type
 */

/**
 * @typedef {BaseItem} Area
 * @property {'area'} type
 */

/**
 * @param {boolean|undefined} omitEmptyAreas
 * @returns {Area[]}
 */
function getPageStructure(omitEmptyAreas)
{
    if (!document.body) {
        return [];
    }
    omitEmptyAreas = omitEmptyAreas ? true : false;
    const container = {children: []};
    parse(document.body, container, omitEmptyAreas);
    return container.children.filter(item => item.type === 'area' && (!omitEmptyAreas || item.children.length > 0));
}

/**
 * @param {HTMLElement} element 
 * @param {Container} parent 
 * @param {boolean} omitEmptyAreas
 */
function parse(element, parent, omitEmptyAreas)
{
    const area = parseArea(element);
    const itemForElement = area || parseBlock(element);
    if (itemForElement) {
        parent.children.push(itemForElement);
    }
    const appendTo = itemForElement || parent;
    for (const childElement of element.children) {
        parse(childElement, appendTo, omitEmptyAreas);
    }
    if (omitEmptyAreas && area !== null && area.children.length === 0) {
        parent.children.splice(parent.children.indexOf(area, 1));
    }
}

/**
 * @param {HTMLElement} element
 * 
 * @returns {Area|null} 
 */
function parseArea(element)
{
    if (element.tagName !== 'DIV') {
        return null;
    }
    const id = parseInt(element.getAttribute('data-area-id'), 10);
    if (!id || isNaN(id) || id <= 0) {
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
        type: 'area',
        id,
        handle,
        displayName,
        children: [],
    };
}


/**
 * @param {HTMLElement} element
 * 
 * @returns {Block|null} 
 */
function parseBlock(element)
{
    if (element.tagName !== 'DIV') {
        return null;
    }
    const id = parseInt(element.getAttribute('data-block-id'), 10);
    if (!id || isNaN(id) || id <= 0) {
        return null;
    }
    const handle = element.getAttribute('data-block-type-handle');
    if (!handle) {
        return null;
    }
    return {
        type: 'block',
        id,
        handle,
        displayName: handle,
        children: [],
    };
}

global.blocksCloner = global.blocksCloner || {};
global.blocksCloner.getPageStructure = getPageStructure;

})(window);
