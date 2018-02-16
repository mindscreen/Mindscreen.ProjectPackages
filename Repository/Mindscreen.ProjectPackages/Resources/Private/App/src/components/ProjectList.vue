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
    import { Component, Prop } from 'vue-property-decorator';
    import { ProjectInfo } from '../types';

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
        @Prop()
        projects: ProjectInfo[];
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
            EventBus.$emit('ProjectList_Filter', {
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

        mounted(): void {
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
    }
</script>
