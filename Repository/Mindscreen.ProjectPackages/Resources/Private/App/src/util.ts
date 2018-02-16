function getHostIcon(url: string): string|null {
    const host = url.match(/:\/\/([^/]+)/);
    if (host !== null) {
        return `http://www.google.com/s2/favicons?domain=${host[1]}`;
    }
    return null;
}

export {
    getHostIcon,
};
