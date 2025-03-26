import {convertFontAwesomeIcon4To5} from './Service/FontAwesome';

interface ApplicableTo {
  readonly sourceVersionConstraint: string;
  readonly destinationVersionConstraint: string;
}

export interface ApplicableToCore extends ApplicableTo {}

export interface ApplicableToPackage extends ApplicableTo {
  readonly packageHandle: string;
}

export interface ApplicableToPackages extends ApplicableTo {
  readonly sourcePackageHandle: string;
  readonly destinationPackageHandle: string;
}

interface BlockTypeConversion {
  readonly newBlockTypeHandle?: string;
  readonly templateRemapping?: Readonly<Record<string, string>>;
  readonly dataTableNameRemapping?: Readonly<Record<string, string>>;
  readonly addMissingFields?: Readonly<Record<string, Readonly<Record<string, string>>>>;
  readonly fontAwesome4to5Fields?: Readonly<Record<string, ReadonlyArray<string>>>;
  readonly customConversion?: (blockElement: Element) => void;
}

interface ConverterBase {
  readonly name: string;
  readonly blockTypes?: Readonly<Record<string, Readonly<BlockTypeConversion>>>;
}

export interface CoreConverter extends ConverterBase {
  readonly applicableTo: ApplicableToCore;
}

export interface PackageConverter extends ConverterBase {
  readonly applicableTo: ApplicableToPackage | ApplicableToPackages;
}

export type Converter = CoreConverter | PackageConverter;

export function applyConverter(doc: XMLDocument, converter: Converter): void {
  if (converter.blockTypes) {
    applyBlockTypeConversions(doc, converter.blockTypes);
  }
}

function applyBlockTypeConversions(doc: XMLDocument, blockTypes: Readonly<Record<string, Readonly<BlockTypeConversion>>>): void {
  listBlockNodes(doc).forEach((blockNode) => {
    const blockTypeHandle = blockNode.getAttribute('type');
    const blockTypeConversion = blockTypeHandle ? blockTypes[blockTypeHandle] : undefined;
    if (!blockTypeConversion) {
      return;
    }
    if (blockTypeConversion.newBlockTypeHandle !== undefined) {
      blockNode.setAttribute('type', blockTypeConversion.newBlockTypeHandle);
    }
    if (blockTypeConversion.templateRemapping !== undefined) {
      convertTemplate(blockNode, blockTypeConversion.templateRemapping);
    }
    if (blockTypeConversion.dataTableNameRemapping !== undefined) {
      convertDataTables(blockNode, blockTypeConversion.dataTableNameRemapping);
    }
    if (blockTypeConversion.addMissingFields !== undefined) {
      addMissingFields(blockNode, blockTypeConversion.addMissingFields);
    }
    if (blockTypeConversion.fontAwesome4to5Fields !== undefined) {
      convertFontAwesome4to5(blockNode, blockTypeConversion.fontAwesome4to5Fields);
    }
    if (blockTypeConversion.customConversion !== undefined) {
      blockTypeConversion.customConversion(blockNode);
    }
  });
}

function convertTemplate(blockNode: Element, templateRemapping: Readonly<Record<string, string>>): void {
  const currentTemplateHandle = blockNode.getAttribute('template') || '';
  const newTemplateHandle = templateRemapping[currentTemplateHandle];
  if (newTemplateHandle !== undefined) {
    if (newTemplateHandle === '') {
      blockNode.removeAttribute('template');
    } else {
      blockNode.setAttribute('template', newTemplateHandle);
    }
  }
}

function convertDataTables(blockNode: Element, dataTableNameRemapping: Readonly<Record<string, string>>): void {
  listDataNodes(blockNode).forEach((dataNode) => {
    const currentDataTableName = blockNode.getAttribute('table') || '';
    const newDataTableName = dataTableNameRemapping[currentDataTableName];
    if (newDataTableName !== undefined) {
      blockNode.setAttribute('table', newDataTableName);
    }
  });
}
function addMissingFields(blockNode: Element, missingFields: Readonly<Record<string, Readonly<Record<string, string>>>>): void {
  listDataNodes(blockNode).forEach((dataNode) => {
    const tableName = dataNode.getAttribute('table') || '';
    const fields = missingFields[tableName];
    if (!fields) {
      return;
    }
    listRecordNodes(dataNode).forEach((recordNode) => {
      const existingFields = (Array.from(recordNode.children) as Element[]).map((fieldNode) => fieldNode.tagName);
      Object.keys(fields).forEach((fieldName) => {
        if (existingFields.includes(fieldName)) {
          return;
        }
        const value = fields[fieldName];
        const fieldNode = recordNode.ownerDocument.createElement(fieldName);
        fieldNode.textContent = value;
        recordNode.appendChild(fieldNode);
      });
    });
  });
}

function convertFontAwesome4to5(blockNode: Element, fontAwesome4to5Fields: Readonly<Record<string, ReadonlyArray<string>>>): void {
  listDataNodes(blockNode).forEach((dataNode) => {
    const tableName = dataNode.getAttribute('table') || '';
    const fields = fontAwesome4to5Fields[tableName];
    if (!fields) {
      return;
    }
    listRecordNodes(dataNode).forEach((recordNode) => {
      (Array.from(recordNode.children) as Element[]).forEach((fieldNode) => {
        if (!fields.includes(fieldNode.tagName)) {
          return;
        }
        const currentIconClass = fieldNode.textContent;
        if (!currentIconClass) {
          return;
        }
        const newIconClass = convertFontAwesomeIcon4To5(currentIconClass);
        if (newIconClass !== '') {
          fieldNode.textContent = newIconClass;
        }
      });
    });
  });
}

function listBlockNodes(doc: XMLDocument): Element[] {
  const result: Element[] = [];
  const walk = function (el: Element, parentEl?: Element): void {
    switch (el.tagName) {
      case 'block':
        result.push(el);
      case 'data':
        if (parentEl?.tagName === 'block') {
          return;
        }
        break;
    }
    (Array.from(el.children) as Element[]).forEach((child) => {
      walk(child, el);
    });
  };
  walk(doc.documentElement);
  return result;
}

function listDataNodes(blockNode: Element): Element[] {
  return (Array.from(blockNode.children) as Element[]).filter((el) => el.tagName === 'data');
}

function listRecordNodes(dataNode: Element): Element[] {
  return (Array.from(dataNode.children) as Element[]).filter((el) => el.tagName === 'record');
}
