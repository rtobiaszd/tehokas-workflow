<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    endpoint: { type: String, required: true },
    tokenConfigured: { type: Boolean, default: false },
});
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl space-y-6">
            <section class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Webhooks</p>
                <h2 class="mt-2 text-2xl font-semibold text-[var(--color-text)]">Endpoint seguro</h2>
                <p class="mt-2 text-sm text-[var(--color-muted)]">
                    Configure seus sistemas externos para enviar eventos de workflow.
                </p>
            </section>

            <section class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface-muted)] p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-[var(--color-muted)]">Endpoint</p>
                        <p class="mt-2 text-sm font-mono text-[var(--color-text)]">
                            {{ endpoint }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface-muted)] p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-[var(--color-muted)]">Token</p>
                        <p class="mt-2 text-sm font-mono text-[var(--color-text)]">
                            {{ tokenConfigured ? 'configurado' : 'nao configurado' }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-dashed border-[var(--color-border)] bg-white p-4 text-xs text-[var(--color-muted)]">
                    Envie o header <strong>X-WEBHOOK-TOKEN</strong> em todas as requisicoes.
                </div>
            </section>
        </div>
    </AppLayout>
</template>
