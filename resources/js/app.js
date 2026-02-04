import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

createInertiaApp({
    resolve: name => {
        // Handle namespaced component names (watchtower::ComponentName)
        const cleanName = name.replace('watchtower::', '');
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        const page = pages[`./Pages/${cleanName}.vue`];
        
        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }
        
        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#3b82f6',
    },
});
