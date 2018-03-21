
type ProjectInfo = {
    repository: {
        id: string,
        url: string,
        source: string,
        namespace: string,
        name: string,
        full_name: string,
    }
    key: string,
    name: string,
    packageManager: string,
    type?: string,
    additional: any[],
    description?: string,
};

type ProjectFilter = {
    name: RegExp,
    type: string|null,
    packageManager: string|null,
    repositorySource: string|null,
};

export {
    ProjectInfo,
    ProjectFilter,
};
