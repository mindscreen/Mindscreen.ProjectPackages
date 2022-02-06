<template>
    <div>
        <h3>Packages</h3>
        <div class="pp-project__packages">
            <pp-dependencyTree :packages="packages">
                <div slot-scope="{props}">
                    <a :href="`https://packagist.org/packages/${props.pkg.name}`" target="_blank">{{props.pkg.name}}</a> {{props.pkg.version}}
                    <pp-badge v-if="props.pkg.additional && props.pkg.additional.dist && props.pkg.additional.dist.type === 'path'">
                        Local
                    </pp-badge>
                    <pp-badge
                        v-if="props.pkg.additional && props.pkg.additional.source"
                        :iconUrl="getPkgIcon(props.pkg.additional.source.host)"
                        :title="props.pkg.additional.source.host"
                        :link="props.pkg.additional.source.url ? getPkgUrl(props.pkg.additional.source.url) : null"
                        >Source</pp-badge>
                </div>
            </pp-dependencyTree>
        </div>
    </div>
</template>

<style lang="scss">
@import '../../scss/settings.scss';

.pp-project {
    &__packages {
        overflow-y: auto;
        &__item {
            line-height: 34px;
        }
    }
    a {
        color: $colorFontLink;
        text-decoration: none;
        &:hover, &:focus, &:active {
            text-decoration: underline;
        }
    }
}
</style>

<script lang="ts">
import Vue from 'vue';
import EventBus from '../EventBus';
import { ProjectInfo, PackageInformation } from '../../types';
import { Prop, Component, Watch } from 'vue-property-decorator';
import DependencyTree from '../DependencyTree.vue';
import {getHostIcon} from "../../util";

@Component({
    components: {
        'pp-dependencyTree': DependencyTree,
    },
})
export default class ComposerProject extends Vue {

    @Prop()
    project!: ProjectInfo;

    packages: PackageInformation[] = [];

    @Watch('project')
    loadPackages(): void {
        fetch(`/projects/packages/${this.project.key}`)
            .then(r => r.json())
            .then(j => this.packages = j);
    }

    mounted() {
        this.loadPackages();
    }

    getPkgIcon(gitUri: string) {
        return getHostIcon(gitUri);
    }

    getPkgUrl(gitUri: string): string {
        if (/\S+@[^:]+:.+/.test(gitUri)) {
            const host = gitUri.split('@')[1];
            return 'https://' + host.replace(':', '/');
        }
        return gitUri;
    }
}
</script>
