<template>
    <span
        :is="link ? 'a' : 'span'"
        :href="link"
        :class="{'badge': true, 'badge_icon': icon || iconUrl}"
        :title="title || null"
        :target="link ? '_blank' : false"
        >
        <span :class="iconClass" :style="{backgroundImage: iconUrl ? `url('${iconUrl}')` : false}"></span>
        <slot></slot>
    </span>
</template>

<style lang="scss">
    @import "../scss/settings";

    $border-radius: 4px;

    a.badge {
        &:focus,
        &:active,
        &:hover {
            color: $colorFontInactive !important;
        }
    }

    .badge {
        box-sizing: border-box;
        display: inline-block;
        height: 22px;
        padding: 4px 6px;
        position: relative;
        top: -3px;
        color: $colorFont !important;
        font-size: 12px;
        line-height: 12px;
        text-decoration: none !important;
        border-radius: $border-radius;
        background: linear-gradient(0deg, $colorBgDark 0%, $colorBgLight 5%, $colorBgLight 95%, $colorBgHover 100%);

        &.badge_icon {
            padding-left: 28px;
            .badge_icon__icon {
                height: 22px;
                width: 24px;
                background-color: rgba(255,255,255,0.1);
                background-repeat: no-repeat;
                background-size: 16px 16px;
                background-position: 4px 3px;
                display: block;
                position: absolute;
                left: 0;
                top: 0;
                border-radius: $border-radius 0 0 $border-radius;
            }

            .badge_icon-- {
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
        @Prop() link?: string;

        get iconClass(): string {
            let className = 'badge_icon__icon';
            if (this.icon) {
                className += ` badge_icon badge_icon--${this.icon}`;
            }
            return className;
        }
    }
</script>
