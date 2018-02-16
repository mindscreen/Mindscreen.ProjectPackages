<template>
    <span :class="className" :title="title || null" :style="{backgroundImage: iconUrl ? `url('${iconUrl}')` : false}">
        <slot></slot>
    </span>
</template>

<style lang="scss">
    @import "../scss/settings";

    .badge {
        color: $colorFont;
        border-radius: 8px;
        padding: 6px;
        background: no-repeat 6px 7px;
        background-color: $colorBgLight;
        background-size: auto 16px;

        a {
            color: $colorFontInactive !important;
            &:focus,
            &:active,
            &:hover {
                color: $colorFont !important;
            }
        }

        &.badge_icon {
            padding-left: 28px;

            &.badge_icon-- {
                &composer {
                    background-image: url("/_Resources/Static/Packages/Mindscreen.ProjectPackages/Build/assets/composer.png");
                }

                &vagrant {
                    background-image: url("/_Resources/Static/Packages/Mindscreen.ProjectPackages/Build/assets/vagrant.png");
                }

                &gitlab {
                    background-image: url("/_Resources/Static/Packages/Mindscreen.ProjectPackages/Build/assets/gitlab.png");
                }

                &npm {
                    background-image: url("/_Resources/Static/Packages/Mindscreen.ProjectPackages/Build/assets/npm.png");
                }

                &yarn {
                    background-image: url("/_Resources/Static/Packages/Mindscreen.ProjectPackages/Build/assets/yarn.png");
                }

                &github {
                    background-image: url("/_Resources/Static/Packages/Mindscreen.ProjectPackages/Build/assets/github.png");
                }

                &bitbucket {
                    background-image: url("/_Resources/Static/Packages/Mindscreen.ProjectPackages/Build/assets/bitbucket.png");
                }
            }
        }
    }
</style>

<script lang="ts">
    import Vue from 'vue';
    import { Component, Prop } from 'vue-property-decorator';

    @Component
    export default class Badge extends Vue {
        @Prop() icon?: string;
        @Prop() iconUrl?: string;
        @Prop() title?: string;

        get className(): string {
            let className = 'badge';
            if (this.icon) {
                className += ` badge_icon badge_icon--${this.icon}`;
            } else if (this.iconUrl) {
                className += ' badge_icon';
            }
            return className;
        }
    }
</script>
