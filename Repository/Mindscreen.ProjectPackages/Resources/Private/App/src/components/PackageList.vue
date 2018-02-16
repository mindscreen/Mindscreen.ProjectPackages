<template>
    <div class="pp-packageList">
        <div class="pp-packageList-filter">
            <div class="pp-packageList-filter__row">
                <input type="search" v-model="filter" @keyup="updateFilter"
                       class="pp-packageList-filter__input"
                       placeholder="vendor/package">
                <pp-button @click="showFilters = !showFilters"
                    :aria-expanded="showFilters"
                    :aria-label="`Show more filters${filtersUsed?' (filters active)':''}`"
                    className="pp-packageList-filter__showFilters"
                    :title="`More${filtersUsed?' (modified)':''}`">...<span class="text-highlight" v-if="filtersUsed">*</span>
                </pp-button>
                <pp-button @click="reset" className="pp-packageList-filter__reset"
                        title="Reset">X
                </pp-button>
            </div>
            <div v-if="showFilters">
                <v-select :options="packageManagers"
                        aria-label="Package-manager"
                        placeholder="Package-manager"
                        :value="packageManagerFilter"
                        :on-change="setPackageManagerFilter" />
            </div>
        </div>
        <div class="pp-packageList-list">
            <PackageListItem v-for="(packageVersions, index) in packages"
                             :key="index"
                             :name="packageVersions[0].name"
                             :packageVersions="packageVersions"/>
        </div>
    </div>
</template>

<style lang="scss">
    @import '../scss/settings.scss';

    .pp-packageList {
        height: 100%;
        width: 30%;
        max-width: 400px;
        display: flex;
        flex-flow: column;
        background-color: $colorBgDark;
        color: $colorFont;
        &-filter {
            width: 100%;
            flex: 0 1 auto;
            &__row {
                display: flex;
            }
            &__input {
                flex: 1 0 auto;
                @include input;
            }
            &__reset {
                width: 80px;
            }
            &__showFilters {
                width: 80px;
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
    import PackageListItem from './PackageListItem.vue';
    import EventBus from './EventBus';
    import { PackageVersionInformation } from '../types';
    import { Component } from 'vue-property-decorator';

    @Component({
        components: {
            PackageListItem,
        },
    })
    export default class PackageList extends Vue {

        filter: string = '';
        packageManagerFilter: string|null = null;

        packages: PackageVersionInformation[][] = [];

        showFilters: boolean = false;

        private packageManagerList: string[] = [];

        get packageManagers() {
            return this.packageManagerList;
        }

        get filtersUsed() {
            return this.packageManagerFilter !== null;
        }

        setPackageManagerFilter(value: string): void {
            this.packageManagerFilter = value;
            this.updateFilter();
        }

        updateFilter(): void {
            const pattern = new RegExp('.*?' + this.filter + '.*?', 'i');
            EventBus.$emit('PackageList_Filter', {
                name: pattern,
                packageManager: this.packageManagerFilter,
            });
        }

        reset(): void {
            this.filter = '';
            this.packageManagerFilter = null;
            EventBus.$emit('PackageList_Reset', null);
        }

        created(): void {
            fetch('/packages/list?grouped=true')
                .then(r => r.json())
                .then(p => this.packages = p);
            fetch('/packages/packagemanagers')
                .then(r => r.json())
                .then(t => this.packageManagerList = t);
        }
    }
</script>
