import Vue from 'vue';
import VueRouter from 'vue-router';
import PackageList, { Actions as PackageListActions } from './components/PackageList.vue';
import ProjectList, { Actions as ProjectListActions } from './components/ProjectList.vue';
import Project from './components/Project.vue';
import SiteHeader from './components/SiteHeader.vue';
import Badge from './components/Badge.vue';
import Button from './components/Button.vue';
import Checkbox from './components/Checkbox.vue';
import EventBus from './components/EventBus';
import { ProjectInfo } from './types';
import { Component, Watch } from 'vue-property-decorator';
import vSelect from 'vue-select';

Vue.use(VueRouter);

const router = new VueRouter();

Vue.component('pp-badge', Badge);
Vue.component('pp-button', Button);
Vue.component('pp-checkbox', Checkbox);
Vue.component('v-select', vSelect);

@Component({
  components: {
    PackageList,
    Project,
    ProjectList,
    SiteHeader,
  },
  router: router,
  template: `
    <div>
        <site-header />
        <div class="pp-app">
            <div class="pp-row">
                <package-list />
                <project-list :projects="projects" />
                <project />
            </div>
        </div>
    </div>
    `,
})
class App extends Vue {
  allProjects: ProjectInfo[] = [];
  projects: ProjectInfo[] = [];
  packageFilter: {[p: string]: string[]} = {};
  created(): void {
    fetch('/projects/list')
      .then(r => r.json())
      .then(p => {
        this.allProjects = p;
        this.projects = p;
        return p;
      });
  }
  @Watch('projects')
  onProjectsChanged(): void {
    EventBus.$emit(ProjectListActions.ProjectsUpdated);
  }
  mounted() {
    EventBus.$on(PackageListActions.PackageChanged, (filterArgument: { name: string, parameters: string[] }) => {
      if (filterArgument.parameters.length === 0) {
        if (this.packageFilter[filterArgument.name] !== undefined) {
          delete this.packageFilter[filterArgument.name];
        }
      } else {
        this.packageFilter[filterArgument.name] = filterArgument.parameters;
      }
      this.loadFilteredPackageList();
    });
    EventBus.$on(PackageListActions.FilterReset, () => {
      this.projects = this.allProjects;
      this.packageFilter = {};
    });
  }
  loadFilteredPackageList() {
    if (Object.keys(this.packageFilter).length === 0) {
      this.projects = this.allProjects;
    } else {
      const initial: string[] = [];
      const flattened = Object.keys(this.packageFilter).reduce((p, c) => p.concat(this.packageFilter[c]), initial);
      const queryString = flattened.map(s => `packages[]=${s}`).join('&');
      fetch('/packages/projects?' + queryString)
        .then(r => r.json())
        .then(p => {
          this.projects = p;
          EventBus.$emit(ProjectListActions.ProjectsUpdated);
          return p;
        });
    }
  }
}
/* tslint:disable */
new App({
  el: '#app'
});
