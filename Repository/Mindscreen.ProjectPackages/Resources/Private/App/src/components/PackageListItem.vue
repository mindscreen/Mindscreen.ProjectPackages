<template>
    <div v-if="shown" class="pp-packageList__item">
        <input type="checkbox" v-model="selected" :id="itemId()"
               @change="toggleSelected">
        <div>
            <button @click="collapsed = !collapsed">{{collapsed ? '▶' : '▼'}}
            </button>
            <label :for="itemId()">
                <img :alt="packageManager" :title="packageManager" :src="`/_Resources/Static/Packages/Mindscreen.ProjectPackages/Build/assets/${packageManager}.png`">
                {{name}}
            </label>
            <div v-if="!collapsed" v-for="(packageVersion, index) in packageVersions"
                 :key="packageVersion.version + '_' + index"
                 class="pp-packageList__item-versionList">
                <div class="pp-packageList__item__version">
                    <input type="checkbox"
                           :checked="isSelected(packageVersion.version)"
                           @change="toggleVersion(packageVersion.version)"
                           :id="versionId(packageVersion.version + '_' + index)">
                    <label :for="versionId(packageVersion.version + '_' + index)">{{packageVersion.version}}</label>
                    <span class="pp-packageList__item__version--badge">{{packageVersion.usages}}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss">
    @import '../scss/settings.scss';

    .pp-packageList__item {
        width: 100%;
        line-height: 36px;
        img {
            margin-bottom: -3px;
            margin-right: 3px;
        }
        label {
            cursor: pointer;
        }
        input[type=checkbox] {
            opacity: 0;
            position: absolute;
            &:checked ~ div {
                background-color: $colorBgHover;
            }
        }
        button {
            cursor: pointer;
            border: none;
            background: none;
            color: $colorFontInactive;
        }
        &-versionList {
            padding-left: 16px;
        }
        &__version {
            position: relative;
            label {
                cursor: pointer;
                display: block;
                padding: 0 16px;
            }
            input[type=checkbox]:checked ~ label {
                background-color: $colorBgSelected;
            }
            input[type=checkbox]:focus ~ label {
                background-color: $colorBgHover;
            }
            .pp-packageList__item__version--badge {
                position: absolute;
                right: 16px;
                top: 8px;
                line-height: 20px;
                font-size: 12px;
                border-radius: 10px;
                min-width: 17px;
                height: 16px;
                background: $colorBgLight;
                color: $colorFont;
                padding: 2px;
                text-align: center;
            }
        }
    }
</style>


<script lang="ts">
    import Vue from 'vue';
    import EventBus from './EventBus';
    import { PackageVersionInformation, PackageFilter } from '../types';
    import { Component, Prop } from 'vue-property-decorator';

    @Component
    export default class PackageListItem extends Vue {

        collapsed: boolean = false;

        selected: boolean = false;

        selectedVersions: string[] = [];

        shown: boolean = true;

        @Prop()
        name: string;

        @Prop()
        packageVersions: PackageVersionInformation[];

        constructor() {
            super();
        }

        get packageManager() {
            return this.packageVersions[0].packageManager;
        }

        toggleSelected(): void {
            if (this.selected) {
                this.selectedVersions = this.packageVersions.map((v: PackageVersionInformation) => v.version);
            } else {
                this.selectedVersions = [];
            }
            this.updatePackages();
        }

        isSelected(versionName: string): boolean {
            return this.selectedVersions.indexOf(versionName) >= 0;
        }

        toggleVersion(versionName: string) {
            if (this.isSelected(versionName)) {
                this.selectedVersions = this.selectedVersions.filter((version: string) => version !== versionName);
                if (this.selectedVersions.length === 0) {
                    this.selected = false;
                }
            } else {
                this.selectedVersions.push(versionName);
            }
            this.updatePackages();
        }

        getFilterParameters(): string[] {
            if (this.selectedVersions.length === 0) {
                return [];
            }
            if (this.selectedVersions.length === this.packageVersions.length) {
                return [this.name + ':ANY'];
            }
            return this.selectedVersions.map((versionName: string) => this.name + ':' + versionName);
        }

        updatePackages(): void {
            const parameters = this.getFilterParameters();
            EventBus.$emit('PackageList_Changed', {
                name: this.name,
                parameters: parameters,
            });
        }

        versionId(versionName: string): string {
            return this.itemId() + versionName;
        }

        itemId(): string {
            return this.name.toLowerCase().replace('/', '_');
        }

        mounted() {
            EventBus.$on('PackageList_Filter', (filter: PackageFilter) => {
                this.shown =
                    (filter.packageManager === null || filter.packageManager === this.packageManager) &&
                    filter.name.test(this.name);
            });
            EventBus.$on('PackageList_Reset', () => {
                this.selected = false;
                this.shown = true;
                this.selectedVersions = [];
            });
        }
    }
</script>
