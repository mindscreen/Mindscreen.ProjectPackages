<template>
    <header class="pp-header">
        <h1>ProjectPackages</h1>
    </header>
</template>

<style lang="scss">
@import '../scss/settings.scss';

.pp-header {
    background-color: $colorBgLight;
    line-height: 44px;
    padding: 8px;
    h1 {
        margin: 0;
        color: $colorFont;
    }
}
</style>


<script lang="ts">
import Vue from 'vue';

export default Vue.extend({
    name: 'pp-header',
});
</script>
