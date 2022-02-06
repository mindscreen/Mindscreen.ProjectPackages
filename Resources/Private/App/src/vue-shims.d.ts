declare module '*.vue' {
    import Vue from 'vue';
    // TODO: workaround; should be propperly fixed
    export const Actions: Record<string, string>;
    export default Vue;
}

declare module '*.png';

declare module 'vue-select';
