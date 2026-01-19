<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import StatsCard from '../Components/StatsCard.vue';

const props = defineProps({
    total_workflows: { type: Number, default: 0 },
    active_workflows: { type: Number, default: 0 },
    executions_last_24h: { type: Number, default: 0 },
    failures_last_24h: { type: Number, default: 0 },
    recent_executions: { type: Array, default: () => [] },
    recent_failures: { type: Array, default: () => [] },
});

const successPercent = computed(() => {
    const executions = props.executions_last_24h ?? 0;
    if (!executions) {
        return 0;
    }
    return Math.round(((executions - (props.failures_last_24h ?? 0)) / executions) * 100);
});

const stats = computed(() => [
    {
        label: 'Total workflows',
        value: props.total_workflows ?? 0,
        caption: 'Base ativa por tenant',
    },
    {
        label: 'Workflows ativos',
        value: props.active_workflows ?? 0,
        caption: 'Disponiveis em tempo real',
    },
    {
        label: 'Execucoes diarias',
        value: props.executions_last_24h ?? 0,
        caption: 'Ultimas 24 horas',
    },
    {
        label: 'Falhas',
        value: props.failures_last_24h ?? 0,
        caption: 'Eventos com alerta',
    },
]);

const formattedExecutions = computed(() =>
    props.recent_executions.map((item) => ({
        ...item,
        time: item.executed_at ? new Date(item.executed_at).toLocaleString() : '--',
    }))
);

const formattedFailures = computed(() =>
    props.recent_failures.map((item) => ({
        ...item,
        time: item.executed_at ? new Date(item.executed_at).toLocaleString() : '--',
    }))
);
</script>

<template>
    <AppLayout>
        <div class="grid gap-6">
            <section class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Dashboard</p>
                        <h2 class="mt-2 text-2xl font-semibold text-[var(--color-text)]">
                            Controle central de automacao
                        </h2>
                        <p class="mt-2 text-sm text-[var(--color-muted)]">
                            Acompanhe a saude dos workflows e priorize execucoes criticas.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface-muted)] px-4 py-3 text-xs text-[var(--color-muted)]">
                        <p class="font-semibold text-[var(--color-text)]">Execucoes 24h</p>
                        <p class="mt-1">{{ props.executions_last_24h ?? 0 }} eventos processados</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <StatsCard
                        v-for="card in stats"
                        :key="card.label"
                        :label="card.label"
                        :value="card.value"
                        :caption="card.caption"
                    >
                        <template #icon>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <path d="M4 13h4v7H4v-7zm6-6h4v13h-4V7zm6 3h4v10h-4V10z" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </template>
                    </StatsCard>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-[2fr_1fr]">
                <div class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Success vs Fail</p>
                        <h3 class="mt-2 text-lg font-semibold">Performance diaria</h3>
                    </div>
                    <p class="text-sm font-semibold text-[var(--color-success)]">
                        {{ successPercent }}% success
                    </p>
                </div>
                <div class="mt-6 space-y-4">
                    <div class="rounded-full bg-[var(--color-surface-muted)]">
                        <div
                            class="h-3 rounded-full bg-[var(--color-primary)]"
                            :style="{ width: `${successPercent}%` }"
                        ></div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-[var(--color-muted)]">
                        <span>{{ (props.executions_last_24h ?? 0) - (props.failures_last_24h ?? 0) }} success</span>
                        <span>{{ props.failures_last_24h ?? 0 }} failed</span>
                    </div>
                </div>

                    <div class="mt-6 rounded-2xl border border-dashed border-[var(--color-border)] px-4 py-4 text-xs text-[var(--color-muted)]">
                        Sem dados adicionais de fila ou latencia para este tenant.
                    </div>
                </div>

                <div class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Recent runs</p>
                            <h3 class="mt-2 text-lg font-semibold">Execucoes recentes</h3>
                        </div>
                        <Link href="/logs" class="text-xs font-semibold text-[var(--color-primary)]">View logs</Link>
                    </div>
                    <div class="mt-6 space-y-4">
                        <div
                            v-for="run in formattedExecutions"
                            :key="run.id"
                            class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface-muted)] p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-[var(--color-text)]">{{ run.workflow }}</p>
                                    <p class="mt-1 text-xs text-[var(--color-muted)]">Execucao</p>
                                </div>
                                <span
                                    class="rounded-full px-2 py-1 text-[10px] uppercase tracking-[0.2em]"
                                    :class="run.status === 'failed'
                                        ? 'bg-rose-100 text-rose-700'
                                        : run.status === 'running'
                                            ? 'bg-amber-100 text-amber-700'
                                            : 'bg-emerald-100 text-emerald-700'"
                                >
                                    {{ run.status }}
                                </span>
                            </div>
                            <p class="mt-2 text-xs text-[var(--color-muted)]">{{ run.time }}</p>
                        </div>
                        <div
                            v-if="!formattedExecutions.length"
                            class="rounded-2xl border border-dashed border-[var(--color-border)] px-4 py-6 text-center text-xs text-[var(--color-muted)]"
                        >
                            Nenhuma execucao recente.
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Failures</p>
                        <h3 class="mt-2 text-lg font-semibold">Falhas recentes</h3>
                    </div>
                </div>
                <div class="mt-6 space-y-3">
                    <div
                        v-for="failure in formattedFailures"
                        :key="failure.id"
                        class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface-muted)] p-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-[var(--color-text)]">{{ failure.workflow }}</p>
                                <p class="mt-1 text-xs text-rose-600">{{ failure.error || 'Erro nao informado.' }}</p>
                            </div>
                            <span class="text-xs text-[var(--color-muted)]">{{ failure.time }}</span>
                        </div>
                    </div>
                    <div
                        v-if="!formattedFailures.length"
                        class="rounded-2xl border border-dashed border-[var(--color-border)] px-4 py-6 text-center text-xs text-[var(--color-muted)]"
                    >
                        Nenhuma falha registrada.
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
