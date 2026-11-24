/**
 * VoltCore Bootstrap
 * Configurações globais de AJAX e CSRF para o frontend.
 */

// CSRF token para fetch nativo
const csrfMeta = document.head.querySelector('meta[name="csrf-token"]');
if (csrfMeta) {
    window._csrfToken = csrfMeta.content;
}

// Configurar axios se disponível (opcional)
if (typeof window.axios !== 'undefined') {
    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    if (csrfMeta) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfMeta.content;
    }
}
