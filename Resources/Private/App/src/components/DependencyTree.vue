<template>
    <div class="pp-treeview">
        <div class="pp-treeview__controls">
            <input type="search" v-model="treeFilter" placeholder="Filter">
            <button class="pp-treeview__controls__action" @click="expandAll">Expand all</button>
            <button class="pp-treeview__controls__action" @click="collapseAll">Collapse all</button>
        </div>
        <div class="pp-treeview__tree">
            <pp-dependencyTreeItem
                v-for="(pkg, index) in filteredPackages"
                :depth="0"
                :key="index"
                :pkg="pkg"
                :filterActive="filterActive"
            >
                <template slot="item" slot-scope="_">
                    <slot :props="_"></slot>
                </template>
            </pp-dependencyTreeItem>
        </div>
    </div>
</template>

<style lang="scss">
@import '../scss/settings.scss';

.pp-treeview {
    &__controls {
        &__action {
            cursor: pointer;
            border: none;
            background: none;
            color: $colorFontLink;
        }
        margin-bottom: 16px;
        input {
            min-width: 33%;
            max-width: 50%;
            @include input
        }
    }

    &__tree {
        padding-left: 24px;
    }
}
</style>


<script lang="ts">
import Vue from 'vue';
import { Component, Prop } from 'vue-property-decorator';
import DependencyTreeItem from './DependencyTreeItem.vue';
import EventBus from './EventBus';
import { PackageInformation } from '../types';

export const Actions = {
    CollapseAll: 'DependencyTree_CollapseAll',
    ExpandAll: 'DependencyTree_ExpandAll',
};

type DependencyTreeFilter = {
    name: RegExp|null,
};

const matchesFilter = (pkg: PackageInformation, filter: DependencyTreeFilter) =>
    filter.name === null || filter.name.test(pkg.name);

const filterDependencyTree = (packages: PackageInformation[], filter: DependencyTreeFilter): PackageInformation[] => {
    const result: PackageInformation[] = [];
    packages.forEach(pkg => {
        const pkg2 = (Object as any).assign({}, pkg);
        const filteredDependencies = pkg2.hasDependencies && !pkg2.duplicate
            ? filterDependencyTree(pkg2.dependencies, filter)
            : [];
        if (filteredDependencies.length > 0 || matchesFilter(pkg2, filter)) {
            pkg2.dependencies = filteredDependencies;
            pkg2.hasDependencies = filteredDependencies.length > 0;
            result.push(pkg2);
        }
    });
    return result;
};

@Component({
    components: {
        'pp-dependencyTreeItem': DependencyTreeItem,
    },
})
export default class DependencyTree extends Vue {
    treeFilter: string = '';
    @Prop({default: []})
    packages!: PackageInformation[];

    get filteredPackages() {
        const packages = this.packages.slice();
        return filterDependencyTree(packages, this.filter);
    }

    get filter() {
        return {
            name: this.treeFilter === '' ? null : new RegExp('.*?' + this.treeFilter + '.*?', 'i'),
        };
    }

    get filterActive() {
        return this.treeFilter !== '';
    }

    expandAll(): void {
        EventBus.$emit(Actions.ExpandAll, null);
    }

    collapseAll(): void {
        EventBus.$emit(Actions.CollapseAll, null);
    }
}
</script>
