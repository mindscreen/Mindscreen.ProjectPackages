<template>
    <div class="pp-project">
        <div v-if="name != ''" class="pp-project-view">
            <div class="pp-project__header">
                <h2>{{name}}</h2>
                <span class="text-secondary">
                    <a :href="project.repository.url" target="_blank">
                        <img alt="" :src="getHostIcon(project.repository.url)" v-if="getHostIcon(project.repository.url)">
                        {{project.repository.full_name}}</a>
                    ({{project.repository.source}})
                </span>

                <p v-if="description">{{description}}</p>

                <div v-if="messages.length > 0">
                    <h3>Messages</h3>
                    <div class="pp-project__messages__container">
                        <div v-for="msg in messages" :key="msg.code" :class="'pp-project-message pp-project-message--' + msg.severity">
                            {{msg.message}}
                        </div>
                    </div>
                </div>
            </div>

            <div class="pp-project__other--container">
                <component :is="projectView" :project="project" class="pp-project__other"></component>
            </div>
        </div>
        <div v-if="name == ''" class="pp-project-empty">
            <h2>No project selected</h2>
        </div>
    </div>
</template>

<style lang="scss">
@import '../scss/settings.scss';

.pp-project {
    min-width: 30%;
    flex: 1 0 auto;
    height: 100%;
    background-color: $colorBgBlack;
    color: $colorFont;
    &-view {
        height: 100%;
        display: flex;
        flex-direction: column;
        padding: 0 8px;
    }
    &-empty {
        height: 100%;
        padding: 0 8px;
    }
    &__other--container {
        height: 100px;
        flex: 1 0 auto;
    }
    &__other {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    h2 {
        margin: 0;
        line-height: 55px;
    }
    a {
        color: $colorFontLink;
        text-decoration: none;
        &:hover, &:focus, &:active {
            text-decoration: underline;
        }
    }
}
.pp-project-message {
    border-left: 5px solid;
    padding: 8px;
    margin-bottom: 8px;
    background-color: $colorBgLight;
    color: $colorFont;
    &--1 {
        // debug
        border-color: rgb(202, 65, 230);
    }
    &--2 {
        // notice
        border-color: rgb(14.4%, 50.8%, 92.5%);
    }
    &--3 {
        // warning
        border-color: rgb(240, 162, 46);
    }
    &--4 {
        // error
        border-color: rgb(212, 20, 20);
    }
}
</style>


<script lang="ts">
import Vue from 'vue';
import Component from 'vue-class-component';
import EventBus from './EventBus';
import { Message, ProjectInfo } from '../types';
import { views } from './project-views';
import { getHostIcon } from '../util';

export const Actions = {
    Load: 'Project_Load',
    Loaded: 'Project_Loaded',
};

@Component
export default class Project extends Vue {

    name: string = '';
    description?: string|null = null;
    url: string = '';
    packageManager: string = '';
    messages: Message[] = [];
    project!: ProjectInfo;

    get projectView(): any {
        return views[this.packageManager];
    }

    getHostIcon(host: string): string|null {
        return getHostIcon(host);
    }

    loadProject(project: ProjectInfo) {
        this.project = project;
        this.name = project.name;
        this.url = project.repository.url;
        this.packageManager = project.packageManager;
        this.description = project.description;
        EventBus.$emit(Actions.Loaded, project);
        fetch(`/projects/messages/${project.key}`)
            .then(r => r.json())
            .then(j => this.messages = j);
    }

    mounted(): void {
        const arg = 'project[key]';
        if (arg in this.$route.query) {
            fetch(`projects/show/${this.$route.query[arg]}`)
                .then(r => r.json())
                .then(p => this.loadProject(p as ProjectInfo));
        }
        EventBus.$on(Actions.Load, (project: ProjectInfo) => {
            this.loadProject(project);
            if (this.project !== undefined && arg in this.$route.query && this.$route.query[arg] !== this.project.key) {
                this.$router.push({
                    query: (Object as any).assign({}, this.$route.query, { [arg]: this.project.key }),
                });
            }
        });
    }
}
</script>
