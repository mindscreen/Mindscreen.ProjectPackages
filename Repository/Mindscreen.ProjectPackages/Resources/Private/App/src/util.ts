function getHostIcon(url: string): string|null {
    const host = url.match(/:\/\/([^/]+)/);
    if (host !== null) {
        return `http://www.google.com/s2/favicons?domain=${host[1]}`;
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

export {
    getHostIcon,
    uuidv4,
};
