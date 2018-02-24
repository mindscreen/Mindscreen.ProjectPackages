<template>
    <div class="pp-treeview">
        <div class="pp-treeview__controls">
            <input type="search" @keyup="filterTree" @change="filterTree" v-model="treeFilter">
            <button class="pp-treeview__controls__action" @click="expandAll">Expand all</button>
            <button class="pp-treeview__controls__action" @click="collapseAll">Collapse all</button>
        </div>
        <div class="pp-treeview__tree">
            <pp-dependencyTreeItem
                v-for="(pkg, index) in packages"
                :depth="0"
                :key="index"
                :pkg="pkg"
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
import { PackageVersionInformation } from '../types/Package';

export const Actions = {
    CollapseAll: 'DependencyTree_CollapseAll',
    ExpandAll: 'DependencyTree_ExpandAll',
    Filter: 'DependencyTree_Filter',
};

@Component({
    components: {
        'pp-dependencyTreeItem': DependencyTreeItem,
    },
})
export default class DependencyTree extends Vue {
    treeFilter: string = '';
    filterTimeout: number|null = null;
    @Prop()
    packages: PackageVersionInformation[];

    filterTree(): void {
        if (this.filterTimeout !== null) {
            clearTimeout(this.filterTimeout);
        }
        this.filterTimeout = setTimeout(() => {
            const pattern = this.treeFilter === '' ? null : new RegExp('.*?' + this.treeFilter + '.*?', 'i');
            EventBus.$emit(Actions.Filter, {
                name: pattern,
            });
            this.filterTimeout = null;
        }, 1500);
    }

    expandAll(): void {
        EventBus.$emit(Actions.ExpandAll, null);
    }

    collapseAll(): void {
        EventBus.$emit(Actions.CollapseAll, null);
    }
}
</script>
