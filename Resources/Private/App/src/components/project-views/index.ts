import ComposerProject from './ComposerProject.vue';
import NodeProject from './NodeProject.vue';
import { VueConstructor } from 'vue';

const views: {[packageManager: string]: VueConstructor} = {
    composer: ComposerProject,
    npm: NodeProject,
    yarn: NodeProject,
};

export {
    views,
};
