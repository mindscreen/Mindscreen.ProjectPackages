<template>
    <div v-if="pkg && (show || showFromChild)" :aria-expanded="expanded">
        <div class="pp-treeview__item">
            <button :class="{'pp-treeview__item__toggle': true, 'pp-treeview__item__toggle--open': expanded}"
                    v-if="pkg.hasDependencies"
                    :title="expanded ? 'collapse' : 'expand'"
                    @click="expand(!isExpanded)">
                <span class="pp-treeview__item__toggle--icon"></span>
                <span class="visuallyhidden" v-html="expanded ? 'collapse' : 'expand'"></span>
            </button>
            <div class="pp-treeview__item__label">
                <slot :pkg="pkg" name="item"></slot>
                <pp-badge v-if="pkg.duplicate" color="grey" title="This package already appeared higher up in the tree.">Duplicate</pp-badge>
            </div>
        </div>
        <div v-if="expanded" class="pp-treeview__item__children">
            <pp-dependencyTreeItem
                v-for="(dependency, index) in pkg.dependencies"
                :depth="depth + 1"
                :key="index"
                :pkg="dependency"
                :filterActive="filterActive"
                @expanded="expand(true)"
            >
                <template slot-scope="_" slot="item">
                    <slot :pkg="_.pkg" name="item"></slot>
                </template>
            </pp-dependencyTreeItem>
        </div>
    </div>
</template>

<style lang="scss">
@import '../scss/settings.scss';

.pp-treeview__item {

    > button + .pp-treeview__item__label {
        margin-left: 0;
    }

    &__label {
        display: inline-flex;
        align-items: baseline;
        line-height: 24px;
        color: $colorFont;
        margin-left: 20px;

        > div {
            margin-right: 4px;
        }
    }

    &__children {
        padding-left: 20px;
    }

    &__toggle {
        height: 16px;
        width: 16px;
        border: none;
        background: none;
        &--icon {
            position: relative;
            bottom: 2px;
            right: 5px;
            &:after {
                position: absolute;
                display: block;
                content: '';
                cursor: pointer;
                top: 0;
                left: 0;
                border-style: solid;
                border-width: 5px 0 5px 10px;
                border-color: transparent transparent transparent $colorFontInactive;
            }
        }
        &--open .pp-treeview__item__toggle--icon:after {
            left: 0;
            border-width: 0 0 10px 10px;
            border-color: transparent transparent $colorFont transparent;
        }
    }
}
</style>


<script lang="ts">
import Vue from 'vue';
import { Component, Prop } from 'vue-property-decorator';
import { PackageInformation } from '../types';
import EventBus from './EventBus';
import { Actions } from './DependencyTree.vue';

@Component({
    name: 'pp-dependencyTreeItem',
})
export default class DependencyTreeItem extends Vue {
    isExpanded: boolean = false;
    show: boolean = true;
    @Prop()
    depth!: number;
    @Prop()
    pkg!: PackageInformation;
    @Prop()
    filterActive!: boolean;

    get expanded(): boolean {
        return this.filterActive || this.isExpanded;
    }

    expand(force: boolean): void {
        this.isExpanded = force;
        if (force) {
            this.$emit('expanded');
        }
    }

    mounted(): void {
        EventBus.$on(Actions.CollapseAll, () => this.expand(false));
        EventBus.$on(Actions.ExpandAll, () => {
            this.expand(true);
            // EventBus.$emit(Actions.ExpandAll);
        });
    }
}
</script>

