import { router as inertia } from '@inertiajs/react';

export function crossesShell(target) {
    if (typeof window === 'undefined' || !/^\//.test(target)) return false;
    const isAdmin = (path) => /^\/admin(\/|$|\?)/.test(path) && !/^\/admin\/preview\//.test(path);
    return isAdmin(window.location.pathname) !== isAdmin(target);
}

function fullLoad(url) {
    window.location.assign(url);
    return Promise.resolve();
}

export const router = {
    visit(url, options = {}) {
        const isGet = !options.method || options.method === 'get';
        return isGet && crossesShell(url) ? fullLoad(url) : inertia.visit(url, options);
    },
    get(url, data = {}, options = {}) {
        return crossesShell(url) ? fullLoad(url) : inertia.get(url, data, options);
    },
    post: (url, data, options) => inertia.post(url, data, options),
    put: (url, data, options) => inertia.put(url, data, options),
    patch: (url, data, options) => inertia.patch(url, data, options),
    delete: (url, options) => inertia.delete(url, options),
    reload: (options) => inertia.reload(options),
    on: (event, callback) => inertia.on(event, callback),
};
