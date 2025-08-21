const parser = new DOMParser();

/**
 * Builds an XML document (from an XML which may have more than one root element is wrap is true).
 * If wrap is true, rhe built document will have a single root element wrapping the input XML.
 *
 * @throws Error if the input XML is invalid (if so, the error message is in HTML format)
 */
function parse(xml: string): XMLDocument {
  xml = xml.trim();
  const doc = parser.parseFromString(xml, 'application/xml');
  const errorNode = doc.querySelector('parsererror');
  if (errorNode) {
    throw new Error(errorNode.textContent || 'Unknown XML parsing error');
  }
  return doc;
}

export default {
  parse,
};
