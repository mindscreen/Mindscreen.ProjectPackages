type PackageVersionInformation = {
  name: string,
  version: string,
  packageManager: string,
  usages?: number,
  additional?: null | {
    type?: string,
    source?: {
      type: string,
      url?: string,
      host: string,
    },
  },
  depth: number,
  hasDependencies: boolean,
  duplicate: boolean,
  dependencies: PackageVersionInformation[],
};

type PackageFilter = {
  name: RegExp,
  packageManager: string|null,
};

export { PackageVersionInformation, PackageFilter };
