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

let iconUrlMap: Record<string, string>|null = null;
const getIconMap = (): Record<string, string> => {
    if (iconUrlMap === null) {
        const configElement = document.getElementById('configuration');
        if (configElement) {
            const iconUrls = JSON.parse(configElement.getAttribute('data-icons') || '{}');
            iconUrlMap = flattenObject(iconUrls);
        }
        if (!iconUrlMap) {
            iconUrlMap = {};
        }
    }
    return iconUrlMap;
};
function getIcon(iconName: string): string|null {
    const iconMap = getIconMap();
    return iconMap[iconName] || null;
}

type ValueOrItem<T = string, K extends string|number = string> = T | Record<K, T>;
type NestedRecord<T = string, K extends string|number = string> = Record<K, ValueOrItem<T, K>>;
function flattenObject(input: NestedRecord, keyDelimiter = '.', path = ''): Record<string, string> {
    let result: Record<string, string> = {};
    Object.keys(input).forEach(k => {
        const v = input[k];
        const key = (path ? path + keyDelimiter : '') + k;
        if (typeof v === 'string') {
            result[key] = v;
            return;
        }
        const nested = flattenObject(v, keyDelimiter, key);
        result = { ...result, ...nested };
    });
    return result;
}

export {
    getHostIcon,
    getIcon,
    uuidv4,
    buildQueryObject,
    flattenObject,
};
