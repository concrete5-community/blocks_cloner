import {Range, SemVer} from 'semver';
import {Environment} from './environment';
import type {ApplicableToCore, ApplicableToPackage, ApplicableToPackages, Converter} from './Conversion/Converter';

const converters: Converter[] = [];

function getConverterByHandle(handle: string): Converter | null {
  return converters.find((converter) => converter.handle === handle) || null;
}

function registerConverter(converter: Converter): void {
  if (getConverterByHandle(converter.handle) !== null) {
    throw new Error(`Converter with handle "${converter.handle}" is already registered`);
  }
  converters.push(converter);
  sortConverters();
}

function getConverters(): readonly Converter[] {
  return converters;
}

function getConvertersForEvironments(sourceEnvironment: Environment | null, destinationEnvironment: Environment | null): readonly Converter[] {
  if (sourceEnvironment === null && destinationEnvironment === null) {
    return converters;
  }
  return converters.filter((converter) => converterIsForEnvironments(converter, sourceEnvironment, destinationEnvironment));
}

function isSemVer(version: string, constraint: string): boolean {
  try {
    const v = new SemVer(version, {loose: true});
    const c = new Range(constraint, {includePrerelease: true, loose: true});
    return c.test(v);
  } catch {
    return false;
  }
}
function converterIsForEnvironments(converter: Converter, sourceEnvironment: Environment | null, destinationEnvironment: Environment | null): boolean {
  const applicableTo = converter.applicableTo;
  if (typeof (applicableTo as ApplicableToPackage).packageHandle === 'string') {
    return environmentMatchesPackage(applicableTo as ApplicableToPackage, sourceEnvironment, destinationEnvironment);
  }
  if (typeof (applicableTo as ApplicableToPackages).sourcePackageHandle === 'string') {
    return environmentMatchesPackages(applicableTo as ApplicableToPackages, sourceEnvironment, destinationEnvironment);
  }
  return environmentMatchesCore(applicableTo, sourceEnvironment, destinationEnvironment);
}

function environmentMatchesCore(applicableTo: ApplicableToCore, sourceEnvironment: Environment | null, destinationEnvironment: Environment | null): boolean {
  if (sourceEnvironment !== null) {
    if (!isSemVer(sourceEnvironment.core, applicableTo.sourceVersionConstraint)) {
      return false;
    }
  }
  if (destinationEnvironment !== null) {
    if (!isSemVer(destinationEnvironment.core, applicableTo.destinationVersionConstraint)) {
      return false;
    }
  }
  return true;
}

function environmentMatchesPackage(applicableTo: ApplicableToPackage, sourceEnvironment: Environment | null, destinationEnvironment: Environment | null): boolean {
  if (sourceEnvironment !== null) {
    const sourceVersion = sourceEnvironment.packages[applicableTo.packageHandle];
    if (sourceVersion === undefined || !isSemVer(sourceVersion, applicableTo.sourceVersionConstraint)) {
      return false;
    }
  }
  if (destinationEnvironment !== null) {
    const destinationVersion = destinationEnvironment.packages[applicableTo.packageHandle];
    if (destinationVersion === undefined || !isSemVer(destinationVersion, applicableTo.destinationVersionConstraint)) {
      return false;
    }
  }
  return true;
}

function environmentMatchesPackages(applicableTo: ApplicableToPackages, sourceEnvironment: Environment | null, destinationEnvironment: Environment | null): boolean {
  if (sourceEnvironment !== null) {
    const sourceVersion = sourceEnvironment.packages[applicableTo.sourcePackageHandle];
    if (sourceVersion === undefined || !isSemVer(sourceVersion, applicableTo.sourceVersionConstraint)) {
      return false;
    }
  }
  if (destinationEnvironment !== null) {
    const destinationVersion = destinationEnvironment.packages[applicableTo.destinationPackageHandle];
    if (destinationVersion === undefined || !isSemVer(destinationVersion, applicableTo.destinationVersionConstraint)) {
      return false;
    }
  }
  return true;
}

function sortConverters(): void {
  converters.sort((a: Converter, b: Converter) => {
    const aIsPackage = typeof (a.applicableTo as ApplicableToPackage).packageHandle === 'string' || typeof (a.applicableTo as ApplicableToPackages).sourcePackageHandle === 'string';
    const bIsPackage = typeof (b.applicableTo as ApplicableToPackage).packageHandle === 'string' || typeof (b.applicableTo as ApplicableToPackages).sourcePackageHandle === 'string';
    if (aIsPackage !== bIsPackage) {
      return aIsPackage ? 1 : -1;
    }
    return a.name.localeCompare(b.name, 'en', {sensitivity: 'base'});
  });
}

export default {
  registerConverter,
  getConverters,
  getConvertersForEvironments,
  getConverterByHandle,
};
