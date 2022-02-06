import { Dictionary } from 'vue-router/types/router';

function getHostIcon(url: string): string|null {
    const host = url.match(/:\/\/([^/]+)/);
    if (host !== null) {
        return `https://www.google.com/s2/favicons?domain=${host[1]}`;
    }
    return null;
}

function uuidv4() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
        const r = Math.random() * 16 | 0;
        const v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

type QueryArgumentOption = {
    key: string,
    value: string|null,
    condition: boolean,
};

function buildQueryObject(oldQuery: Dictionary<string | (string | null)[]>, args: QueryArgumentOption[]) {
    const newQuery = Object.assign({}, oldQuery);
    args.forEach(arg => {
        if (arg.value !== null && arg.condition) {
            newQuery[arg.key] = arg.value;
        } else {
            delete newQuery[arg.key];
        }
    });
    return newQuery;
}

export {
    getHostIcon,
    uuidv4,
    buildQueryObject,
};
