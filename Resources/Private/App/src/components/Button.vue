<template>
    <button :class="`pp-button ${className}`" :title="title || null" @click="onClick">
        <slot></slot>
    </button>
</template>

<style lang="scss">
    @import '../scss/settings.scss';

    .pp-button {
        cursor: pointer;
        background: $colorBgDark;
        border: none;
        outline: 2px solid $colorBgBlack;
        outline-offset: -2px;
        color: $colorFontInactive;
        font-weight: bold;
        line-height: 56px;
        padding: 0 16px;
        min-width: 50px;
        &:active, &:focus {
            color: $colorFont;
            outline-color: $colorFontLink;
        }
        &:hover {
            color: $colorFont;
            background: $colorBgHover;
        }
    }
</style>


<script lang="ts">
import Vue from 'vue';
import { Prop, Component } from 'vue-property-decorator';

@Component
export default class Button extends Vue {
    @Prop()
    title!: string;
    @Prop({default: ''})
    className!: string;

    onClick() {
        this.$emit('click');
    }
}
</script>
