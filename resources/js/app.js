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

// PWA Service Worker + Push subscription
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(reg => {
            window.swRegistration = reg;
            // Auto-subscribe to push if permission already granted
            if (Notification.permission === 'granted') {
                subscribeToPush(reg);
            }
        }).catch(err => {
            console.warn('SW registration failed:', err);
        });
    });
}

// PWA install prompt — captured globally for Alpine $persist usage
window.deferredInstallPrompt = null;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    window.deferredInstallPrompt = e;
    window.dispatchEvent(new CustomEvent('pwa-installable'));
});

async function subscribeToPush(registration) {
    const vapidKey = document.querySelector('meta[name="vapid-public-key"]')?.content;
    if (!vapidKey) return;

    try {
        const sub = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidKey),
        });

        await fetch('/api/push/subscribe', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            body: JSON.stringify(sub.toJSON()),
        });
    } catch (err) {
        console.warn('Push subscribe failed:', err);
    }
}

window.requestPushPermission = async function () {
    const perm = await Notification.requestPermission();
    if (perm === 'granted' && window.swRegistration) {
        subscribeToPush(window.swRegistration);
    }
    return perm;
};

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = atob(base64);
    return Uint8Array.from([...rawData].map(c => c.charCodeAt(0)));
}
