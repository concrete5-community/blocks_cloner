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

interface ExtendedTemplateRemapping {
  readonly newTemplate?: string;
  readonly newCustomClasses?: string;
}

interface BlockTypeConversion {
  readonly newBlockTypeHandle?: string;
  readonly templateRemapping?: Readonly<Record<string, string | ExtendedTemplateRemapping>>;
  readonly addRecordFields?: Readonly<Record<string, Readonly<Record<string, string>>>>;
  readonly removeRecordFields?: Readonly<Record<string, ReadonlyArray<string>>>;
  readonly fontAwesome4to5Fields?: Readonly<Record<string, ReadonlyArray<string>>>;
  readonly renameDataTables?: Readonly<Record<string, string>>;
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
  listBlockNodes(doc).forEach((xBlock) => {
    const blockTypeHandle = xBlock.getAttribute('type');
    const blockTypeConversion = blockTypeHandle ? blockTypes[blockTypeHandle] : undefined;
    if (!blockTypeConversion) {
      return;
    }
    if (blockTypeConversion.newBlockTypeHandle !== undefined) {
      xBlock.setAttribute('type', blockTypeConversion.newBlockTypeHandle);
    }
    if (blockTypeConversion.templateRemapping !== undefined) {
      convertTemplate(xBlock, blockTypeConversion.templateRemapping);
    }
    if (blockTypeConversion.addRecordFields !== undefined) {
      addRecordFields(xBlock, blockTypeConversion.addRecordFields);
    }
    if (blockTypeConversion.removeRecordFields !== undefined) {
      removeRecordFields(xBlock, blockTypeConversion.removeRecordFields);
    }
    if (blockTypeConversion.fontAwesome4to5Fields !== undefined) {
      convertFontAwesome4to5(xBlock, blockTypeConversion.fontAwesome4to5Fields);
    }
    if (blockTypeConversion.renameDataTables !== undefined) {
      convertDataTables(xBlock, blockTypeConversion.renameDataTables);
    }
    if (blockTypeConversion.customConversion !== undefined) {
      blockTypeConversion.customConversion(xBlock);
    }
  });
}

function convertTemplate(xBlock: Element, templateRemapping: Readonly<Record<string, string | ExtendedTemplateRemapping>>): void {
  let currentTemplateHandle = xBlock.getAttribute('template') || '';
  if (currentTemplateHandle && !/.\.php$/.test(currentTemplateHandle)) {
    currentTemplateHandle += '.php';
  }
  const remapTo = templateRemapping[currentTemplateHandle];
  let newTemplateHandle: string | null = null;
  let newCustomClasses: string[] = [];
  if (typeof remapTo === 'string') {
    newTemplateHandle = remapTo;
  } else if (remapTo) {
    newTemplateHandle = remapTo.newTemplate || null;
    newCustomClasses = remapTo.newCustomClasses?.split(/\s+/).filter((c) => c.length > 0) || [];
  }
  if (newTemplateHandle === '') {
    xBlock.removeAttribute('template');
  } else if (newTemplateHandle !== null) {
    xBlock.setAttribute('template', newTemplateHandle.replace(/\.php$/, '') + '.php');
  }
  if (newCustomClasses.length > 0) {
    let xStyle = listChildElements(xBlock, 'style').shift();
    if (!xStyle) {
      xStyle = xBlock.ownerDocument.createElement('style');
      xBlock.insertBefore(xStyle, xBlock.firstChild);
    }
    let xCustomClass = listChildElements(xStyle, 'customClass').shift();
    if (!xCustomClass) {
      xCustomClass = xStyle.ownerDocument.createElement('customClass');
      xStyle.insertBefore(xCustomClass, xStyle.firstChild);
    }
    const oldCustomClasses = (xCustomClass.textContent || '').split(/\s+/).filter((c) => c.length > 0);
    const customClassesToBeAdded = newCustomClasses.filter((c) => !oldCustomClasses.includes(c));
    if (customClassesToBeAdded.length > 0) {
      xCustomClass.textContent = [...oldCustomClasses, ...customClassesToBeAdded].join(' ');
    }
  }
}

function convertDataTables(xBlock: Element, map: Readonly<Record<string, string>>): void {
  listChildElements(xBlock, 'data').forEach((xData) => {
    const currentDataTableName = xBlock.getAttribute('table') || '';
    const newDataTableName = map[currentDataTableName];
    if (newDataTableName !== undefined) {
      xBlock.setAttribute('table', newDataTableName);
    }
  });
}

function addRecordFields(xBlock: Element, fieldList: Readonly<Record<string, Readonly<Record<string, string>>>>): void {
  listChildElements(xBlock, 'data').forEach((xData) => {
    const tableName = xData.getAttribute('table') || '';
    const fields = fieldList[tableName];
    if (!fields) {
      return;
    }
    listChildElements(xData, 'record').forEach((xRecord) => {
      const existingFields = (Array.from(xRecord.children) as Element[]).map((xField) => xField.tagName);
      Object.keys(fields).forEach((fieldName) => {
        if (existingFields.includes(fieldName)) {
          return;
        }
        const value = fields[fieldName];
        const xField = xRecord.ownerDocument.createElement(fieldName);
        xField.textContent = value;
        xRecord.appendChild(xField);
      });
    });
  });
}

function removeRecordFields(xBlock: Element, fieldList: Readonly<Record<string, ReadonlyArray<string>>>): void {
  listChildElements(xBlock, 'data').forEach((xData) => {
    const tableName = xData.getAttribute('table') || '';
    const fields = fieldList[tableName];
    if (!fields) {
      return;
    }
    listChildElements(xData, 'record').forEach((xRecord) => {
      (Array.from(xRecord.children) as Element[]).forEach((xField) => {
        if (fields.includes(xField.tagName)) {
          xRecord.removeChild(xField);
        }
      });
    });
  });
}

function convertFontAwesome4to5(xBlock: Element, fontAwesome4to5Fields: Readonly<Record<string, ReadonlyArray<string>>>): void {
  listChildElements(xBlock, 'data').forEach((xData) => {
    const tableName = xData.getAttribute('table') || '';
    const fields = fontAwesome4to5Fields[tableName];
    if (!fields) {
      return;
    }
    listChildElements(xData, 'record').forEach((xRecord) => {
      (Array.from(xRecord.children) as Element[]).forEach((xField) => {
        if (!fields.includes(xField.tagName)) {
          return;
        }
        const currentIconClass = xField.textContent;
        if (!currentIconClass) {
          return;
        }
        const newIconClass = convertFontAwesomeIcon4To5(currentIconClass);
        if (newIconClass !== '') {
          xField.textContent = newIconClass;
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

function listChildElements(el: Element, name: string): Element[] {
  return (Array.from(el.children) as Element[]).filter((el) => el.tagName === name);
}
