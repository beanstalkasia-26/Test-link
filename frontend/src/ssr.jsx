import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { renderToString } from 'react-dom/server';

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        resolve: (name) => {
            const pages = import.meta.glob(['./pages/site/**/*.jsx', './pages/Error.jsx'], { eager: true });
            const module = pages[`./pages/${name}.jsx`];
            if (!module) throw new Error(`Unknown page component "${name}"`);
            return module.default;
        },
        setup: ({ App, props }) => <App {...props} />,
    }),
);
