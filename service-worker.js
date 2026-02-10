/**
 * Service Worker - SGT Propostas
 * Estratégia: Network First + Cache Fallback + Background Sync
 */

const CACHE_NAME = 'sgt-propostas-v1';
const STATIC_ASSETS = [
    '/',
    '/Cli_Pro.php',
    '/iniciar_nova_proposta.php',
    '/painel.php',
    '/meus_clientes.php',
    '/criar_proposta.php',
    '/calculos.js',
    '/manifest.json',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css',
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
];

// Instalação - Pré-cache assets estáticos
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('[SW] Cache aberto');
                return cache.addAll(STATIC_ASSETS);
            })
            .catch(err => console.error('[SW] Erro ao cachear:', err))
    );
    self.skipWaiting();
});

// Ativação - Limpa caches antigos
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(name => name !== CACHE_NAME)
                    .map(name => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

// Fetch - Estratégia híbrida
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Estratégia 1: API/PHP - Network First
    if (url.pathname.includes('.php') || url.pathname.includes('ajax')) {
        event.respondWith(networkFirst(request));
        return;
    }

    // Estratégia 2: Assets estáticos - Cache First
    if (request.destination.match(/(style|script|image|font)/)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Estratégia 3: Padrão - Stale While Revalidate
    event.respondWith(staleWhileRevalidate(request));
});

// Network First: Tenta rede, fallback para cache
async function networkFirst(request) {
    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) return cached;
        throw error;
    }
}

// Cache First: Cache primeiro, atualiza em background
async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        // Atualiza cache em background (stale-while-revalidate)
        fetch(request).then(response => {
            if (response.ok) {
                caches.open(CACHE_NAME).then(cache => cache.put(request, response));
            }
        }).catch(() => { });
        return cached;
    }
    return fetch(request);
}

// Stale While Revalidate: Cache imediato + atualização silenciosa
async function staleWhileRevalidate(request) {
    const cached = await caches.match(request);

    const fetchPromise = fetch(request).then(response => {
        if (response.ok) {
            caches.open(CACHE_NAME).then(cache => cache.put(request, response.clone()));
        }
        return response;
    }).catch(() => cached);

    return cached || fetchPromise;
}

// Background Sync - Salva propostas pendentes
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-propostas') {
        event.waitUntil(syncPropostasPendentes());
    }
});

// Notificações Push (opcional - para lembretes)
self.addEventListener('push', (event) => {
    const data = event.data.json();
    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/icons/icon-192x192.png',
            badge: '/icons/badge-72x72.png',
            tag: data.tag,
            requireInteraction: true,
            actions: data.actions || []
        })
    );
});

// Clique na notificação
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data?.url || '/')
    );
});

// ==================== FUNÇÕES AUXILIARES ====================

// Sincroniza propostas salvas offline
async function syncPropostasPendentes() {
    const db = await openDB('SGT-Offline', 1);
    const propostas = await db.getAll('propostas-pendentes');

    for (const proposta of propostas) {
        try {
            const response = await fetch('salvar_proposta.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(proposta)
            });

            if (response.ok) {
                await db.delete('propostas-pendentes', proposta.id);
                // Notifica sucesso
                self.registration.showNotification('Proposta Sincronizada', {
                    body: 'Proposta salva com sucesso!',
                    icon: '/icons/icon-192x192.png'
                });
            }
        } catch (error) {
            console.error('[SW] Falha ao sincronizar:', error);
        }
    }
}

// IndexedDB helper
function openDB(name, version) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(name, version);
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('propostas-pendentes')) {
                db.createObjectStore('propostas-pendentes', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}
