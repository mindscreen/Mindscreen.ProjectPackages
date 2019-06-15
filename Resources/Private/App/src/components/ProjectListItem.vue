<template>
    <div v-if="shown"
         :class="{ 'pp-projectList-item': true, 'pp-projectList-item--active': active}">
        <span class="pp-projectList-item__title">{{project.name}}</span>
        <span class="pp-projectList-item__subtitle">{{project.repository.full_name}}</span>
        <div>
            <pp-badge
                v-if="project.packageManager !== 'unknown'"
                :icon="project.packageManager"
                :title="project.packageManager">{{project.packageManager}}</pp-badge>
            <pp-badge :iconUrl="getVcsIcon(project.repository.url)" :link="project.repository.url" >
                Project<span class="visuallyhidden"> on VCS</span>
            </pp-badge>
            <pp-badge v-if="project.type && project.type !== ''">{{project.type}}</pp-badge>
        </div>
        <button class="pp-projectList-item__action--show" @click="show(project.id)" aria-label="Load Project">
            &raquo;
        </button>
    </div>
</template>

<style lang="scss">
    @import '../scss/settings.scss';

    .pp-projectList-item {
        padding: 8px 8px 16px 8px;
        position: relative;
        &:not(:last-child) {
            margin-bottom: 8px;
        }
        &--active {
            background-color: $colorBgSelected;
            .pp-projectList-item__action--show {
                color: $colorFont;
            }
        }

        &__title {
            font-weight: bold;
            color: $colorFont;
            display: block;
        }

        &__subtitle {
            color: $colorFontInactive;
            display: block;
        }
        > div {
            margin-top: 8px;
        }
        &__action {
            &--show {
                border: none;
                background: none;
                cursor: pointer;
                position: absolute;
                height: 100%;
                right: 0;
                top: 0;
                font-size: 2em;
                color: $colorFontInactive;
                &:focus, &:active, &:hover {
                    color: $colorFont;
                }
            }
        }
    }
</style>


<script lang="ts">
    import Vue from 'vue';
    import EventBus from './EventBus';
    import { Actions } from './ProjectList.vue';
    import { Actions as ProjectActions } from './Project.vue';
    import { Component, Prop } from 'vue-property-decorator';
    import { ProjectInfo, ProjectFilter } from '../types';
    import { getHostIcon } from '../util';

    @Component
    export default class ProjectListItem extends Vue {

        shown: boolean = true;

        active: boolean = false;
        @Prop()
        project!: ProjectInfo;

        mounted(): void {
            EventBus.$on(Actions.Filter, (filter: ProjectFilter) => {
                this.shown = (filter.type === null || this.project.type === filter.type) &&
                    (filter.packageManager === null || this.project.packageManager === filter.packageManager) &&
                    (filter.repositorySource === null || this.project.repository.source === filter.repositorySource) &&
                    filter.name.test(this.project.name);
            });
            EventBus.$on([ProjectActions.Load, ProjectActions.Loaded], (project: ProjectInfo) => {
                this.active = project.key === this.project.key;
            });
        }

        show(projectId: number): void {
            this.active = true;
            EventBus.$emit(ProjectActions.Load, this.project);
        }

        getVcsIcon(url: string): string|null {
            return getHostIcon(url);
        }
    }
</script>
