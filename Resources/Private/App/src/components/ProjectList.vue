<template>
    <div class="pp-projectList">
        <div class="pp-projectList-filter">
            <div class="pp-projectList-filter__row">
                <input type="search" v-model="filter" @keyup="updateFilter"
                    class="pp-projectList-filter__input" placeholder="Project">
                <pp-button @click="showFilters = !showFilters"
                    :aria-expanded="showFilters"
                    :aria-label="`Show more filters${filtersUsed?' (filters active)':''}`"
                    className="pp-projectList-filter__showFilters"
                    :title="`More${filtersUsed?' (modified)':''}`">...<span class="text-highlight" v-if="filtersUsed">*</span>
                </pp-button>
                <pp-button @click="reset" className="pp-projectList-filter__reset"
                        title="Reset">X
                </pp-button>
            </div>
            <div v-if="showFilters">
                <v-select :options="projectTypes"
                        aria-label="Project-type"
                        placeholder="Project-type"
                        :value="projectType"
                        :on-change="setProjectTypeFilter" />
                <v-select :options="packageManagers"
                        aria-label="Package-manager"
                        placeholder="Package-manager"
                        :value="packageManager"
                        :on-change="setPackageManagerFilter" />
                <v-select :options="repositorySources"
                        aria-label="Repository-source"
                        placeholder="Repository-source"
                        :value="repositorySource"
                        :on-change="setRepositorySourceFilter" />
            </div>
        </div>
        <div class="pp-projectList-list">
            <ProjectListItem v-for="project in projects" :key="project.id"
                             :project="project"/>
        </div>
    </div>
</template>

<style lang="scss">
    @import '../scss/settings.scss';
    @import '../scss/dropdown.scss';

    .pp-projectList {
        background-color: $colorBgDark;
        height: 100%;
        width: 30%;
        display: flex;
        flex-flow: column;
        &-filter {
            width: 100%;
            flex: 0 1 auto;
            &__input {
                flex: 1 0 auto;
                @include input
            }
            &__row {
                display: flex;
            }
            &__reset {
                min-width: 80px;
            }
            &__showFilters {
                min-width: 80px;
            }
        }
        &-list {
            flex: 1 1 auto;
            overflow-y: scroll;
        }
    }
</style>

<script lang="ts">
    import Vue from 'vue';
    import ProjectListItem from './ProjectListItem.vue';
    import EventBus from './EventBus';
    import { Component, Prop, Watch } from 'vue-property-decorator';
    import { ProjectInfo } from '../types';
    import { buildQueryObject } from '../util';

    export const Actions = {
        Filter: 'ProjectList_Filter',
        ProjectsUpdated: 'ProjectList_ProjectsUpdated',
    };

    @Component({
        components: {
            ProjectListItem,
        },
    })
    export default class ProjectList extends Vue {
        filter: string = '';
        showFilters: boolean = false;
        projectType: string|null = null;
        packageManager: string|null = null;
        repositorySource: string|null = null;
        @Prop({default: []})
        readonly projects!: ProjectInfo[];
        private projectTypesList: string[] = [];
        private packageManagerList: string[] = [];
        private repositorySourceList: string[] = [];

        get projectTypes() {
            return this.projectTypesList.filter(t => t !== null);
        }

        get packageManagers() {
            return this.packageManagerList;
        }

        get repositorySources() {
            return this.repositorySourceList;
        }

        get filtersUsed(): boolean {
            return this.projectType !== null ||
                this.packageManager !== null ||
                this.repositorySource !== null;
        }

        setProjectTypeFilter(value: string|null) {
            this.projectType = value;
            this.updateFilter();
        }

        setPackageManagerFilter(value: string|null) {
            this.packageManager = value;
            this.updateFilter();
        }

        setRepositorySourceFilter(value: string|null) {
            this.repositorySource = value;
            this.updateFilter();
        }

        updateFilter() {
            const pattern = new RegExp('.*?' + this.filter + '.*?', 'i');
            const newQuery = buildQueryObject(this.$route.query, [
                {
                    condition: this.filter !== '',
                    key: 'projects[name]',
                    value: this.filter,
                },
                {
                    condition: this.projectType !== '',
                    key: 'projects[type]',
                    value: this.projectType,
                },
                {
                    condition: this.packageManager !== '',
                    key: 'projects[pkgmgr]',
                    value: this.packageManager,
                },
                {
                    condition: this.repositorySource !== '',
                    key: 'projects[src]',
                    value: this.repositorySource,
                },
            ]);
            this.$router.replace({ query: newQuery })
                .catch(() => {});
            EventBus.$emit(Actions.Filter, {
                name: pattern,
                packageManager: this.packageManager,
                repositorySource: this.repositorySource,
                type: this.projectType,
            });
        }

        reset() {
            this.filter = '';
            this.projectType = null;
            this.packageManager = null;
            this.repositorySource = null;
            this.updateFilter();
        }

        created(): void {
            fetch('/projects/projecttypes')
                .then(r => r.json())
                .then(t => this.projectTypesList = t);
            fetch('/projects/packagemanagers')
                .then(r => r.json())
                .then(t => this.packageManagerList = t);
            fetch('/projects/repositorysources')
                .then(r => r.json())
                .then(t => this.repositorySourceList = t);
        }

        mounted(): void {
            const filterNameFromQuery = this.$route.query['projects[name]'];
            if (filterNameFromQuery !== undefined) {
                this.filter = filterNameFromQuery.toString();
            }
            const filtersourceFromQuery = this.$route.query['projects[src]'];
            if (filtersourceFromQuery !== undefined) {
                this.repositorySource = filtersourceFromQuery.toString();
            }
            const filterTypeFromQuery = this.$route.query['projects[type]'];
            if (filterTypeFromQuery !== undefined) {
                this.projectType = filterTypeFromQuery.toString();
            }
            const filterPkgMgrFromQuery = this.$route.query['projects[pkgmgr]'];
            if (filterPkgMgrFromQuery !== undefined) {
                this.packageManager = filterPkgMgrFromQuery.toString();
            }
            setTimeout(this.updateFilter, 500);
            EventBus.$on(Actions.ProjectsUpdated, () => {
                this.updateFilter();
            });
        }
    }
</script>
