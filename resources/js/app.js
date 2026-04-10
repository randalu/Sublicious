import './bootstrap';
import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';
import focus from '@alpinejs/focus';
import Sortable from 'sortablejs';
import Chart from 'chart.js/auto';

Alpine.plugin(persist);
Alpine.plugin(focus);

window.Alpine = Alpine;
window.Sortable = Sortable;
window.Chart = Chart;

Alpine.start();

// PWA Service Worker registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(reg => {
            window.swRegistration = reg;
        }).catch(err => {
            console.warn('SW registration failed:', err);
        });
    });
}
