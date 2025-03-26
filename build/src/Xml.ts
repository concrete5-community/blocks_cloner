import * as xmlFormatter from 'xml-formatter';

const parser = new DOMParser();
const xmlSerializer = new XMLSerializer();

/**
 * Builds an XML document (from an XML which may have more than one root element is wrap is true).
 * If wrap is true, rhe built document will have a single root element wrapping the input XML.
 *
 * @throws Error if the input XML is invalid (if so, the error message is in HTML format)
 */
function parse(xml: string, wrap?: boolean): XMLDocument
{
    if (wrap) {
        xml = `<root>${xml.trim().replace(/^<\?xml[^>]*>\s*/i, '')}</root>`;
    } else {
        xml = xml.trim();
    }
    const doc = parser.parseFromString(xml, 'application/xml');
    const errorNode = doc.querySelector('parsererror');
    if (errorNode) {
        throw new Error(errorNode.textContent || 'Unknown XML parsing error');
    }
    return doc;

}
function normalizeXml(xml: string, wrap?: boolean): string
{
    const doc = parse(xml, wrap);
    return normalizeDoc(doc, wrap);
}

function normalizeDoc(doc: XMLDocument, isWrapped?: boolean): string
{
    const xml = isWrapped ? doc.documentElement.innerHTML : xmlSerializer.serializeToString(doc.documentElement);

    return (xmlFormatter as any)(xml, {
        indentation: '   ',
        collapseContent: true,
        lineSeparator: '\n',
        whiteSpaceAtEndOfSelfclosingTag: true,
        throwOnFailure: true,
        strictMode: true,
        forceSelfClosingEmptyTag: true,
    });
}

export default {
    parse,
    normalizeXml,
    normalizeDoc,
};