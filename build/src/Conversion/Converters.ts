import {satisfies} from 'semver';
import {Environment} from '../environment';
import type {ApplicableToCore, ApplicableToPackage, ApplicableToPackages, Converter} from './Converter';

const converters: Converter[] = [];

export function registerConverter(converter: Converter): void {
  converters.push(converter);
  sortConverters();
}

export function getAllConverters(): readonly Converter[] {
  return converters;
}

export function getConvertersForEvironments(sourceEnvironment: Environment, destinationEnvironment: Environment): readonly Converter[] {
  return converters.filter((converter) => converterIsForEnvironments(converter, sourceEnvironment, destinationEnvironment));
}

function converterIsForEnvironments(converter: Converter, sourceEnvironment: Environment, destinationEnvironment: Environment): boolean {
  const applicableTo = converter.applicableTo;
  if (typeof (applicableTo as ApplicableToPackage).packageHandle === 'string') {
    return environmentMatchesPackage(applicableTo as ApplicableToPackage, sourceEnvironment, destinationEnvironment);
  }
  if (typeof (applicableTo as ApplicableToPackages).sourcePackageHandle === 'string') {
    return environmentMatchesPackages(applicableTo as ApplicableToPackages, sourceEnvironment, destinationEnvironment);
  }
  return environmentMatchesCore(applicableTo, sourceEnvironment, destinationEnvironment);
}

function environmentMatchesCore(applicableTo: ApplicableToCore, sourceEnvironment: Environment, destinationEnvironment: Environment): boolean {
  const sourceVersion = sourceEnvironment.core;
  const destinationVersion = destinationEnvironment.core;
  return satisfies(sourceVersion, applicableTo.sourceVersionConstraint) && satisfies(destinationVersion, applicableTo.destinationVersionConstraint);
}

function environmentMatchesPackage(applicableTo: ApplicableToPackage, sourceEnvironment: Environment, destinationEnvironment: Environment): boolean {
  const sourceVersion = sourceEnvironment.packages[applicableTo.packageHandle];
  if (sourceVersion === undefined) {
    return false;
  }
  const destinationVersion = destinationEnvironment.packages[applicableTo.packageHandle];
  if (destinationVersion === undefined) {
    return false;
  }
  return satisfies(sourceVersion, applicableTo.sourceVersionConstraint) && satisfies(destinationVersion, applicableTo.destinationVersionConstraint);
}

function environmentMatchesPackages(applicableTo: ApplicableToPackages, sourceEnvironment: Environment, destinationEnvironment: Environment): boolean {
  const sourceVersion = sourceEnvironment.packages[applicableTo.sourcePackageHandle];
  if (sourceVersion === undefined) {
    return false;
  }
  const destinationVersion = destinationEnvironment.packages[applicableTo.destinationPackageHandle];
  if (destinationVersion === undefined) {
    return false;
  }
  return satisfies(sourceVersion, applicableTo.sourceVersionConstraint) && satisfies(destinationVersion, applicableTo.destinationVersionConstraint);
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
