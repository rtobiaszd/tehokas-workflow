<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], default: '--' },
    caption: { type: String, default: '' },
    trend: { type: String, default: '' },
    loading: { type: Boolean, default: false },
});
</script>

<template>
    <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">
                    {{ label }}
                </p>
                <div v-if="loading" class="mt-4 h-7 w-24 rounded-full bg-[var(--color-surface-muted)]"></div>
                <p v-else class="mt-4 text-2xl font-semibold text-[var(--color-text)]">
                    {{ value }}
                </p>
                <p v-if="caption" class="mt-2 text-xs text-[var(--color-muted)]">
                    {{ caption }}
                </p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-surface-muted)] text-[var(--color-primary)]">
                <slot name="icon">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M4 12h16M4 6h16M4 18h10" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </slot>
            </div>
        </div>
        <p v-if="trend" class="mt-4 text-xs font-medium text-[var(--color-success)]">
            {{ trend }}
        </p>
    </div>
</template>
