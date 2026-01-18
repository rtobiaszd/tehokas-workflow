<script setup>
import { computed } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    logs: { type: Object, required: true },
});

const logsList = computed(() => props.logs?.data ?? []);
</script>

<template>
    <AppLayout>
        <div class="flex flex-col gap-6">
            <section class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Logs</p>
                <h2 class="mt-2 text-2xl font-semibold text-[var(--color-text)]">Execucoes recentes</h2>
                <p class="mt-2 text-sm text-[var(--color-muted)]">
                    Eventos processados e status das automacoes.
                </p>
            </section>

            <section class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <div v-if="!logsList.length" class="rounded-2xl border border-dashed border-[var(--color-border)] px-6 py-10 text-center text-sm text-[var(--color-muted)]">
                    Nenhum log registrado ainda.
                </div>
                <div v-else class="overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-[var(--color-border)] text-xs uppercase tracking-[0.2em] text-[var(--color-muted)]">
                            <tr>
                                <th class="py-3 pr-4">Workflow</th>
                                <th class="py-3 pr-4">Event</th>
                                <th class="py-3 pr-4">Status</th>
                                <th class="py-3">Executed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="log in logsList"
                                :key="log.id"
                                class="border-b border-[var(--color-border)] last:border-transparent"
                            >
                                <td class="py-4 pr-4 text-[var(--color-text)]">#{{ log.workflow_id }}</td>
                                <td class="py-4 pr-4 text-[var(--color-muted)]">{{ log.event }}</td>
                                <td class="py-4 pr-4">
                                    <span
                                        class="rounded-full px-2 py-1 text-[10px] uppercase tracking-[0.2em]"
                                        :class="log.status === 'failed'
                                            ? 'bg-rose-100 text-rose-700'
                                            : log.status === 'running'
                                                ? 'bg-amber-100 text-amber-700'
                                                : 'bg-emerald-100 text-emerald-700'"
                                    >
                                        {{ log.status }}
                                    </span>
                                </td>
                                <td class="py-4 text-[var(--color-muted)]">
                                    {{ log.executed_at ?? log.created_at }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
