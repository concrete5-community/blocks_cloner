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

interface ConverterBase {
  readonly name: string;
}

export interface CoreConverter extends ConverterBase {
  readonly applicableTo: ApplicableToCore;
}

export interface PackageConverter extends ConverterBase {
  readonly applicableTo: ApplicableToPackage | ApplicableToPackages;
}

export type Converter = CoreConverter | PackageConverter;
