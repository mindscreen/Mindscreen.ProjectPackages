<template>
    <div :class="`pp-checkbox ${className}`">
        <input
            type="checkbox"
            :title="title || null"
            :disabled="disabled"
            :checked="value"
            :id="id"
            @change="onChange">
        <label :for="id" class="pp-checkbox__label">{{label}}</label>
    </div>
</template>

<style lang="scss">
    @import '../scss/settings.scss';

    $size: 32px;
    .pp-checkbox {
        height: $size;
        position: relative;
        input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            &:active + label, &:focus + label {
                color: $colorFont;
                outline-color: $colorFontLink;
            }
            &[disabled] + label {
                cursor: not-allowed;
            }
            &:checked + label {
                &:before {
                    background: $colorFontLink;
                }
                &:after {
                    content: '✓';
                }
                &:hover:after {
                    color: $colorBgHover;
                }
            }
        }
        label {
            cursor: pointer;
            line-height: $size;
            margin-left: $size + 16px;
            &:before {
                content: '';
                width: $size;
                display: block;
                position: absolute;
                height: $size;
                @include input;
                padding: 0;
                top: 0;
                left: 0;
            }
            &:hover:before {
                background: $colorBgHover;
            }
            &:after {
                content: '';
                font-size: $size;
                position: absolute;
                left: 0;
                top: 0;
                width: $size;
                line-height: $size;
                text-align: center;
                color: $colorBgLight;
            }
        }
    }
</style>


<script lang="ts">
import Vue from 'vue';
import { Prop, Component } from 'vue-property-decorator';
import { uuidv4 } from '../util';

@Component
export default class Checkbox extends Vue {
    @Prop()
    title!: string;
    @Prop()
    label!: string;
    @Prop({default: ''})
    className!: string;
    @Prop({default: false})
    disabled!: boolean;
    @Prop()
    value!: boolean;

    onChange(e: Event) {
        this.$emit('change', (e.target as HTMLInputElement).checked);
        this.$emit('input', (e.target as HTMLInputElement).checked);
    }

    onClick() {
        this.$emit('click');
    }

    get id() {
        return uuidv4();
    }
}
</script>
