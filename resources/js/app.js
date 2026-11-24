import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// -------------------------------------------------------------------------
// Helpers globais
// -------------------------------------------------------------------------

// Formatar moeda BRL
window.formatBRL = (val) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val || 0);

// Busca de CEP
window.buscarCep = async (cep) => {
    const c = cep.replace(/\D/g, '');
    if (c.length !== 8) return null;
    try {
        const r = await fetch(`/api/v1/cep/${c}`);
        return r.ok ? r.json() : null;
    } catch { return null; }
};

// CSRF token para AJAX
window.csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// Fetch com CSRF
window.apiFetch = (url, options = {}) =>
    fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken(),
            'Accept': 'application/json',
            ...(options.headers || {}),
        },
        ...options,
    });

// -------------------------------------------------------------------------
// Componente: busca de cliente (typeahead)
// -------------------------------------------------------------------------
window.clienteBusca = () => ({
    query: '',
    resultados: [],
    selecionado: null,
    loading: false,
    async buscar() {
        if (this.query.length < 2) { this.resultados = []; return; }
        this.loading = true;
        try {
            const r = await fetch(`/api/v1/clientes/buscar?q=${encodeURIComponent(this.query)}`);
            this.resultados = await r.json();
        } finally { this.loading = false; }
    },
    selecionar(cliente) {
        this.selecionado = cliente;
        this.query = cliente.nome;
        this.resultados = [];
    },
    limpar() {
        this.selecionado = null;
        this.query = '';
        this.resultados = [];
    }
});

// -------------------------------------------------------------------------
// Componente: modal de confirmação
// -------------------------------------------------------------------------
window.confirmar = (mensagem, callback) => {
    if (confirm(mensagem)) callback();
};

// -------------------------------------------------------------------------
// Inicializar tooltips simples
// -------------------------------------------------------------------------
document.addEventListener('alpine:init', () => {
    Alpine.directive('tooltip', (el, { expression }) => {
        el.title = expression;
    });
});

// -------------------------------------------------------------------------
// Flash auto-hide
// -------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-flash]').forEach(el => {
        setTimeout(() => el.remove(), 4000);
    });
});
