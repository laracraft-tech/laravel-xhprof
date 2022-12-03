export default [
    { path: '/', redirect: '/requests' },

    {
        path: '/jobs/:id',
        name: 'job-preview',
        component: require('./screens/jobs/preview').default,
    },

    {
        path: '/jobs',
        name: 'jobs',
        component: require('./screens/jobs/index').default,
    },

    {
        path: '/requests/:id',
        name: 'request-preview',
        component: require('./screens/requests/preview').default,
    },

    {
        path: '/requests',
        name: 'requests',
        component: require('./screens/requests/index').default,
    },

    {
        path: '/commands/:id',
        name: 'command-preview',
        component: require('./screens/commands/preview').default,
    },

    {
        path: '/commands',
        name: 'commands',
        component: require('./screens/commands/index').default,
    },

    {
        path: '/schedule/:id',
        name: 'schedule-preview',
        component: require('./screens/schedule/preview').default,
    },

    {
        path: '/schedule',
        name: 'schedule',
        component: require('./screens/schedule/index').default,
    },

    {
        path: '/monitored-tags',
        name: 'monitored-tags',
        component: require('./screens/monitoring/index').default,
    },
];
