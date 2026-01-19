<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    endpoint: { type: String, required: true },
    tokenConfigured: { type: Boolean, default: false },
    webhookToken: { type: String, default: '' },
    tenantId: { type: [Number, String, null], default: null },
});

const page = usePage();
const endpointPath = computed(() => props.endpoint || '/api/webhooks/workflows');
const tenantIdentifier = computed(() =>
    props.tenantId ?? page.props.currentTenant?.id ?? 'Global'
);
const tokenLabel = computed(() =>
    props.webhookToken ? props.webhookToken : 'Configure o token nas Settings'
);
const absoluteEndpoint = computed(() => {
    const ziggyUrl = page.props.ziggy?.url ?? (typeof window !== 'undefined' ? window.location.origin : '');
    if (!ziggyUrl) {
        return endpointPath.value;
    }

    try {
        return new URL(endpointPath.value, ziggyUrl).toString();
    } catch (error) {
        return endpointPath.value;
    }
});
const curlExample = computed(() => {
    return [
        `curl -X POST ${absoluteEndpoint.value} \\`,
        '  -H "Content-Type: application/json" \\',
        `  -H "X-WEBHOOK-TOKEN: ${tokenLabel.value}" \\`,
        `  -H "X-Tenant-ID: ${tenantIdentifier.value}" \\`,
        "  -d '{ ... }'",
    ].join('\n');
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

            <section class="space-y-6 rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface-muted)] p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-[var(--color-muted)]">Endpoint</p>
                        <p class="mt-2 text-sm font-mono text-[var(--color-text)]">
                            POST {{ endpointPath }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface-muted)] p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-[var(--color-muted)]">Headers obrigatórios</p>
                        <div class="mt-2 space-y-1 text-sm font-mono text-[var(--color-text)]">
                            <p>X-WEBHOOK-TOKEN: {{ tokenLabel }}</p>
                            <p>X-Tenant-ID: {{ tenantIdentifier }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-dashed border-[var(--color-border)] bg-white p-4 text-xs text-[var(--color-muted)]">
                    Inclua sempre os headers <strong>X-WEBHOOK-TOKEN</strong> e <strong>X-Tenant-ID</strong> para que o
                    evento seja autenticado e roteado para o tenant correto.
                </div>

                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-[var(--color-muted)]">Exemplo de curl</p>
                    <pre class="mt-2 overflow-x-auto rounded-2xl bg-slate-900 p-4 text-xs text-slate-100">
{{ curlExample }}
                    </pre>
                    <p class="mt-2 text-xs text-[var(--color-muted)]">
                        Mantenha este token em sigilo. Regere-o nas Settings caso suspeite de vazamento.
                    </p>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
