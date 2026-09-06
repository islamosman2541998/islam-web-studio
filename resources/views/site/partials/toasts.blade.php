<div
    class="studio-toasts"
    x-data="{ items: [], add(event) { const data = event.detail || {}; const toast = { id: Date.now() + Math.random(), type: ['success', 'warning', 'danger'].includes(data.type) ? data.type : 'success', title: data.title || '', message: data.message || '' }; this.items.push(toast); window.setTimeout(() => this.remove(toast.id), 5200); }, remove(id) { this.items = this.items.filter(item => item.id !== id); } }"
    x-on:studio-toast.window="add($event)"
    aria-live="polite"
    aria-atomic="false"
    x-cloak
>
    <template x-for="toast in items" :key="toast.id">
        <article class="studio-toast" :class="'studio-toast--' + toast.type" x-transition.opacity.duration.200ms>
            <span class="studio-toast__icon" aria-hidden="true" x-text="toast.type === 'success' ? '✓' : (toast.type === 'warning' ? '!' : '×')"></span>
            <span class="studio-toast__copy"><strong x-text="toast.title"></strong><small x-text="toast.message"></small></span>
            <button type="button" x-on:click="remove(toast.id)" aria-label="{{ \App\Support\Studio::text('close') }}">×</button>
        </article>
    </template>
</div>
