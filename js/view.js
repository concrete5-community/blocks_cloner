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
 * @typedef {Object} Block
 * @property {'block'} type
 * @property {number} id
 * @property {string} typeHandle
 * @property {Array<Area|Block>} children
 */

/**
 * @typedef {Object} Area
 * @property {'area'} type
 * @property {number} id
 * @property {string} handle
 * @property {string} displayName
 * @property {Array<Area|Block>} children
 */

/**
 * 
 * @returns {Area[]}
 */
function getPageStructure()
{
    if (!document.body) {
        return [];
    }
    const container = {children: []};
    parse(document.body, container);
    return container.children.filter(item => item.type === 'area');
}

/**
 * @param {HTMLElement} element 
 * @param {Area|Block|Container} parent 
 */
function parse(element, parent)
{
    const item = parseBlock(element) || parseArea(element);
    if (item) {
        parent.children.push(item);
        parent = item;
    }
    for (const childElement of element.children) {
        parse(childElement, parent);
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
    const typeHandle = element.getAttribute('data-block-type-handle');
    if (!typeHandle) {
        return null;
    }
    return {
        type: 'block',
        id,
        typeHandle,
        children: [],
    };
}

global.blocksCloner = global.blocksCloner || {};
global.blocksCloner.getPageStructure = getPageStructure;

})(window);
