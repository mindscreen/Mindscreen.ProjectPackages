# Mindscreen.ProjectPackages
Goal of this project is to offer an overview of packages used in your various projects. Projects are registered from configured repository sources like a [GitLab](https://about.gitlab.com/) instance.

Addtionally projects can be searched by filtering packages e.g. to find all your projects you have to update as some package has received an update or got deprecated.

## Requirements
This project is a [Flow](https://flow.neos.io) application, so [it's requirements](http://flowframework.readthedocs.io/en/stable/Quickstart/index.html#installing-flow) apply:
* a webserver
* PHP 7.1
* a [Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html)-supported database such as MySql
* command-line access

## Installation
1. Setup your database
2. Store your credentials in your [Settings.yaml](http://flowframework.readthedocs.io/en/stable/TheDefinitiveGuide/PartII/Configuration.html#database-setup)
3. Configure your repository-sources
4. Create tables: `./flow doctrine:migrate`
5. [Load data](#cli)

## Configuration
You can configure multiple package sources in your [application's settings](http://flowframework.readthedocs.io/en/stable/TheDefinitiveGuide/PartII/Configuration.html), e.g. `Configuration/Settings.yaml`:
```yaml
# ...
Mindscreen:
  ProjectPackages:
    clients:
      myClient:
        type: Mindscreen\ProjectPackages\Strategy\RepositorySource\Gitlab
        options:
          url: 'https://git.example.com/'
          token: '*****'
```
Note that `myClient` is a (unique) identifier of your choosing used when updating repositories and to differentiate between your projects.
See [repository-sources](#repository-sources) for details on the client type. Different repository-sources may have different options.

## Repository Sources
A repository source can be anything that provides repositories where you host your projects, e.g. a [GitLab](https://about.gitlab.com/) instance, [GitHub](https://github.com/) or maybe even a SVN provider. The given `type` is a class implementing `\Mindscreen\ProjectPackages\Strategy\RepositorySource\RepositorySourceInterface` to grant access to repositories and files.

### Mindscreen\ProjectPackages\Strategy\RepositorySource\Gitlab
Load repositories from a GitLab instance.  
**Options:**
* url: the gitlab url
* token: a valid api token for that gitlab

### Mindscreen\ProjectPackages\Strategy\RepositorySource\Github
Load repositories from [GitHub.com](https://github.com).  
**Options:**  
* user: a github user to load repositories from
* org: a github organisation to load repositories from (Note that only one of these will be used by a source.)
* authorization: optional, but will drastically increase your rate-limit. See *Authorization* below.

**Authorization:**  
An array that at least requires the setting `type` with the following possible values and additional fields:
* `value`: The value in `header` will be set as `Authorization` header
* `basic`: Requires either a [personal access token](https://github.com/blog/1509-personal-api-tokens) as `token` or a combination of `user` and `password`.
* `token`: Requires a `token`

## Project Strategies
For a repository multiple project strategies will be tested and evaluated if applicable.
Applicability is generally determined by the existence of certain files like a `composer.json`.
During evaluation packages are identified and associated to the current project.
A project-strategy should extend the `FallbackStrategy` (or a fitting other base class) as strategy execution is not repeated for child classes of already evaluated strategies (e.g. a (hypothetical) LavarelProjectStrategy extending a PhpProjectStrategy is assumed to add value, but should not repeat the same behaviour).  
Project-Strategies are applied ordered by their `protected static $priority` with higher values executed first. Strategies with values <= 0 are excluded from automatic execution. Thus when implementing a custom project-strategy don't forget to set this value accordingly.  
Keep in mind that some repository-source methods might take a long time: e.g. searching a file deep within a repository to determine applicability (or other features) might be extremely slow in big repositories.

## CLI
* `project:updateall` Update all repositories from all sources
* `project:updatefromsource --source-identifier` Update all repositories from the specified source
* `project:deletebysource --source-identifier` Delete all repositories (and associated information) originating the specified source (e.g. after renaming a source)
* `project:listsources` List all configured repository-sources

## UI
The application serves a single vue app and several json [endpoints](#endpoints).

### Building the UI
1. Install dependencies with `npm install`
2. Build with `npm run build`
3. Develop with `npm run build -- --watch`

When running the UI (or a new, better UI) in standalone development, you might want to enable the setting `Mindscreen.ProjectPackages.enableCrossOriginRequests` to turn on CORS-headers.

## Endpoints
### /packages
#### /packages/list
Returns packages including `usages` counting the projects depending on this package.  
**Parameters**  
`grouped` (bool, optional): Group package-information by package-name  
**Return-type**  
```ts
PackageVersionInformation: {
  name: string,
  version: string,
  packageManager: string,
  usages: number,
}[]
```
If `grouped`, returns `[[PackageVersionInformation]]`.

#### /packages/packagemanagers
Get all distinct package-managers used in packages.  
**Return-type**  
```ts
string[]
```

#### /packages/projects
Returns all projects using the given set of packages.  
**Parameters**  
`packages` (string[]): Can either be the package-name (e.g. `neos/flow`) or accompanied by the version (`neos/flow:~4.3`)  
**Return-type**  
```ts
ProjectInfo: {
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
}[]
```

### /projects
#### /projects/list
Get all projects  
**Return-type**  
see [/packages/projects](#-packages-projects)  

#### /projects/projecttypes
Get all distinct project types  
**Return-type**  
```ts
string[]
```

#### /projects/packagemanagers
Get all distinct package-managers  
**Return-type**  
```ts
string[]
```

#### /projects/repositorysources
Get all distinct repository-source identifiers  
**Return-type**  
```ts
string[]
```

#### /projects/packages/{packageKey}
Get all packages found for the given project  
**Parameters**  
`packageKey` (string): the `key` property returned by `/projects/list` or `/packages/projects`  
**Return-type**  
```ts
PackageInformation: {
  name: string,
  version: string,
  packageManager: string,
  additional?: any[],
  depth: number,
  renderDepth: number|null,
  hasDependencies: boolean,
  duplicate: boolean,
  dependencies: PackageInformation[],
}[]
```
Note the difference in this return type having the additional field `additional` with arbitrary data defined by a [project strategy](#project-strategies) and is missing the field `usages`.

#### /projects/messages/{packageKey}
Get all messages found for the given project  
**Parameters**  
`packageKey` (string): the `key` property returned by `/projects/list` or `/packages/projects`  
**Return-type**  
```ts
Message: {
  code: string|null,
  message: string,
  severity: number,
  title: string|null,
}[]
```

## Appendix
### Terminology
**Repository** A storage for project files, e.g. a git repository. A repository can contain multiple *projects*: e.g. your application might contain a frontend-build besides being a [composer](https://getcomposer.org/)-managed project.  
**Repository-Source** A interface to fetch repositories and repository-files from a configured storage.  
**Project** E.g. a composer-project, meaning a (php) project managing it's dependencies with a `composer.json` file. Projects are usually identified by the existence of certain files like a `composer.json`, `package.json` or `Vagrantfile` (the latter not indicating a package-manager of course).  
**Package** A project (which is identified to have dependencies) will be associated with (multiple) *packages*. A package usually has a name and a version and is part of a certain package-manager.  
**Message** During a evaluation messages can be created and assigned to projects e.g. to notify about suggested but missing `*lock` files.  
