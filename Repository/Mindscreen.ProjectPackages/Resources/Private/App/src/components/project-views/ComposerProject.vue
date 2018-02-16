<template>
    <div>
        <h3>Packages</h3>
        <div class="pp-project__packages">
            <ul>
                <li v-for="pkg in packages" :key="pkg.name" class="pp-project__packages__item">
                    <a :href="`https://packagist.org/packages/${pkg.name}`" target="_blank">{{pkg.name}}</a> {{pkg.version}}
                    <pp-badge v-if="pkg.additional && pkg.additional.dist && pkg.additional.dist.type === 'path'">
                        Local
                    </pp-badge>
                    <pp-badge v-if="pkg.additional && pkg.additional.source" :iconUrl="getPkgIcon(pkg.additional.source.host)" :title="pkg.additional.source.host">
                        <a v-if="pkg.additional.source.url" target="_blank" :href="getPkgUrl(pkg.additional.source.url)">Source</a>
                    </pp-badge>
                </li>
            </ul>
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
import { ProjectInfo, PackageVersionInformation } from '../../types';
import { Prop, Component, Watch } from 'vue-property-decorator';

@Component
export default class ComposerProject extends Vue {

    @Prop()
    project: ProjectInfo;

    packages: PackageVersionInformation[] = [];

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
        return `http://www.google.com/s2/favicons?domain=${gitUri}`;
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
