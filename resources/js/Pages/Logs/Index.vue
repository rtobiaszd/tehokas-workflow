<script setup>
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    logs: { type: Object, required: true },
});

const items = ref([]);
const nextPageUrl = ref(null);
const isLoadingMore = ref(false);
const loadError = ref('');
const sentinel = ref(null);
let observer;

watch(
    () => props.logs,
    () => {
        items.value = [...(props.logs?.data ?? [])];
        nextPageUrl.value = props.logs?.next_page_url ?? null;
        loadError.value = '';
    },
    { immediate: true }
);

const hasLogs = computed(() => items.value.length > 0);
const hasMore = computed(() => Boolean(nextPageUrl.value));
const skeletonRows = computed(() => Array.from({ length: 3 }, (_, index) => index));

const formatTimestamp = (value) => {
    if (!value) {
        return '--';
    }

    const parsed = new Date(value);
    return Number.isNaN(parsed.getTime()) ? value : parsed.toLocaleString();
};

const loadMore = async () => {
    if (!nextPageUrl.value || isLoadingMore.value) {
        return;
    }

    isLoadingMore.value = true;
    loadError.value = '';

    try {
        const { data } = await axios.get(nextPageUrl.value);
        items.value = [...items.value, ...(data?.data ?? [])];
        nextPageUrl.value = data?.next_page_url ?? null;
    } catch (error) {
        loadError.value = 'Nao foi possivel carregar mais logs. Tente novamente.';
    } finally {
        isLoadingMore.value = false;
    }
};

onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            if (!entries.length) {
                return;
            }

            if (entries[0].isIntersecting) {
                loadMore();
            }
        },
        { rootMargin: '0px 0px 200px 0px' }
    );

    if (sentinel.value) {
        observer.observe(sentinel.value);
    }
});

watch(
    () => sentinel.value,
    (element) => {
        if (!observer) {
            return;
        }

        observer.disconnect();

        if (element) {
            observer.observe(element);
        }
    }
);

onUnmounted(() => {
    observer?.disconnect();
});
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
                <div v-if="!hasLogs" class="rounded-2xl border border-dashed border-[var(--color-border)] px-6 py-10 text-center text-sm text-[var(--color-muted)]">
                    Nenhum log registrado ainda.
                </div>
                <div v-else class="space-y-4">
                    <div class="overflow-hidden">
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
                                    v-for="log in items"
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
                                        {{ formatTimestamp(log.executed_at ?? log.created_at) }}
                                    </td>
                                </tr>
                                <tr
                                    v-for="placeholder in skeletonRows"
                                    v-if="isLoadingMore"
                                    :key="`skeleton-${placeholder}`"
                                    class="animate-pulse border-b border-[var(--color-border)] last:border-transparent"
                                >
                                    <td class="py-4 pr-4">
                                        <div class="h-4 rounded-full bg-[var(--color-surface-muted)]"></div>
                                    </td>
                                    <td class="py-4 pr-4">
                                        <div class="h-4 rounded-full bg-[var(--color-surface-muted)]"></div>
                                    </td>
                                    <td class="py-4 pr-4">
                                        <div class="h-4 rounded-full bg-[var(--color-surface-muted)]"></div>
                                    </td>
                                    <td class="py-4">
                                        <div class="h-4 rounded-full bg-[var(--color-surface-muted)]"></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p v-if="loadError" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-700">
                        {{ loadError }}
                    </p>

                    <div v-if="hasMore" class="flex flex-col items-center gap-3">
                        <button
                            type="button"
                            class="rounded-full border border-[var(--color-border)] px-4 py-2 text-xs font-semibold text-[var(--color-muted)] hover:bg-[var(--color-surface-muted)]"
                            :disabled="isLoadingMore"
                            @click="loadMore"
                        >
                            {{ isLoadingMore ? 'Carregando...' : 'Carregar mais' }}
                        </button>
                        <div ref="sentinel" class="h-1 w-full opacity-0"></div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
