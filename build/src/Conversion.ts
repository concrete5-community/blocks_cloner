export interface Converter {
  readonly handle: string;
  readonly name: string;
}

const converters: Converter[] = [];

function registerConverters(converter: Converter | Converter[]): void {
  if (converter instanceof Array) {
    converter.forEach((c) => registerConverters(c));
  } else {
    converters.push(converter);
  }
  sortConverters();
}

function getConverters(): readonly Converter[] {
  return converters;
}

function sortConverters(): void {
  converters.sort((a: Converter, b: Converter) => {
    return a.name.localeCompare(b.name, 'en', {sensitivity: 'base'}) || a.handle.localeCompare(b.handle, 'en', {sensitivity: 'base'});
  });
}

export default {
  registerConverters,
  getConverters,
};
