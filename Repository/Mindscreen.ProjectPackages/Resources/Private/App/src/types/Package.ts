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
};

type PackageFilter = {
  name: RegExp,
  packageManager: string|null,
};

export { PackageVersionInformation, PackageFilter };
