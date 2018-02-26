type PackageInformation = {
  name: string,
  version: string,
  packageManager: string,
  additional?: null | {
    type?: string,
    source?: {
      type: string,
      url?: string,
      host: string,
    },
  },
  depth: number,
  renderDepth: number|null,
  hasDependencies: boolean,
  duplicate: boolean,
  dependencies: PackageInformation[],
};

type PackageVersionInformation = {
  name: string,
  version: string,
  packageManager: string,
  usages: number,
};

type PackageFilter = {
  name: RegExp,
  packageManager: string|null,
};

export {
  PackageInformation,
  PackageVersionInformation,
  PackageFilter,
};
