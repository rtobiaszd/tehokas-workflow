<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import WorkflowCard from '../../Components/WorkflowCard.vue';
import { useWorkflows } from '../../Composables/useWorkflows';

const props = defineProps({
    workflows: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const { filterOptions, formatDate, getStatusLabel } = useWorkflows();
const page = usePage();
const permissions = computed(() => page.props.permissions ?? {});

const form = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? 'all',
});

const isLoading = ref(false);

const applyFilters = () => {
    router.get('/workflows', form, {
        preserveState: true,
        replace: true,
        onStart: () => {
            isLoading.value = true;
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const clearFilters = () => {
    form.search = '';
    form.status = 'all';
    applyFilters();
};

const toggleWorkflow = (workflowId) => {
    router.patch(`/workflows/${workflowId}/toggle`, {}, { preserveScroll: true });
};

const workflowsData = computed(() => props.workflows.data ?? []);
const skeletonRows = Array.from({ length: 5 }, (_, index) => index);

const lastExecution = (workflow) => formatDate(workflow.last_executed_at ?? workflow.updated_at);
const createdBy = (workflow) => workflow.definition?.created_by ?? 'System';
</script>

<template>
    <AppLayout>
        <div class="flex flex-col gap-6">
            <section class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Workflows</p>
                        <h2 class="mt-2 text-2xl font-semibold text-[var(--color-text)]">
                            Catalogo de automacoes
                        </h2>
                        <p class="mt-2 text-sm text-[var(--color-muted)]">
                            Filtre por status, ajuste configuracoes e acompanhe desempenho.
                        </p>
                    </div>
                    <Link
                        v-if="permissions.canManageWorkflows"
                        href="/workflows/create"
                        class="rounded-2xl bg-[var(--color-primary)] px-5 py-3 text-sm font-semibold text-white"
                    >
                        New workflow
                    </Link>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-[2fr_1fr_auto_auto]">
                    <input
                        v-model="form.search"
                        type="text"
                        placeholder="Search workflows"
                        class="w-full rounded-2xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                        @keyup.enter="applyFilters"
                    />
                    <select
                        v-model="form.status"
                        class="w-full rounded-2xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                    >
                        <option v-for="option in filterOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                    <button
                        type="button"
                        class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface-muted)] px-4 py-3 text-sm font-semibold text-[var(--color-text)]"
                        @click="applyFilters"
                    >
                        Filter
                    </button>
                    <button
                        type="button"
                        class="rounded-2xl border border-transparent px-4 py-3 text-sm font-semibold text-[var(--color-muted)]"
                        @click="clearFilters"
                    >
                        Clear
                    </button>
                </div>
            </section>

            <section class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <div class="hidden overflow-hidden lg:block">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-[var(--color-border)] text-xs uppercase tracking-[0.2em] text-[var(--color-muted)]">
                            <tr>
                                <th class="py-3 pr-4">Name</th>
                                <th class="py-3 pr-4">Status</th>
                                <th class="py-3 pr-4">Last execution</th>
                                <th class="py-3 pr-4">Created by</th>
                                <th class="py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="isLoading">
                                <tr v-for="row in skeletonRows" :key="row" class="border-b border-[var(--color-border)]">
                                    <td class="py-4 pr-4">
                                        <div class="h-4 w-32 rounded-full bg-[var(--color-surface-muted)]"></div>
                                    </td>
                                    <td class="py-4 pr-4">
                                        <div class="h-4 w-20 rounded-full bg-[var(--color-surface-muted)]"></div>
                                    </td>
                                    <td class="py-4 pr-4">
                                        <div class="h-4 w-24 rounded-full bg-[var(--color-surface-muted)]"></div>
                                    </td>
                                    <td class="py-4 pr-4">
                                        <div class="h-4 w-28 rounded-full bg-[var(--color-surface-muted)]"></div>
                                    </td>
                                    <td class="py-4">
                                        <div class="ml-auto h-4 w-16 rounded-full bg-[var(--color-surface-muted)]"></div>
                                    </td>
                                </tr>
                            </template>

                            <template v-else>
                                <tr
                                    v-for="workflow in workflowsData"
                                    :key="workflow.id"
                                    class="border-b border-[var(--color-border)] last:border-transparent"
                                >
                                <td class="py-4 pr-4">
                                    <div class="font-semibold text-[var(--color-text)]">{{ workflow.name }}</div>
                                    <div class="text-xs text-[var(--color-muted)]">
                                        {{ workflow.description || 'No description' }}
                                    </div>
                                </td>
                                <td class="py-4 pr-4">
                                    <button
                                        type="button"
                                        class="flex items-center gap-2 rounded-full border border-[var(--color-border)] px-3 py-1 text-xs font-semibold"
                                        :class="workflow.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-600'"
                                        :disabled="!permissions.canManageWorkflows"
                                        @click="permissions.canManageWorkflows ? toggleWorkflow(workflow.id) : null"
                                        role="switch"
                                        :aria-checked="workflow.is_active ? 'true' : 'false'"
                                    >
                                        <span
                                            class="h-2 w-2 rounded-full"
                                            :class="workflow.is_active ? 'bg-emerald-500' : 'bg-slate-400'"
                                        ></span>
                                        {{ getStatusLabel(workflow) }}
                                    </button>
                                </td>
                                <td class="py-4 pr-4 text-[var(--color-muted)]">
                                    {{ lastExecution(workflow) }}
                                </td>
                                <td class="py-4 pr-4 text-[var(--color-muted)]">
                                    {{ createdBy(workflow) }}
                                </td>
                                <td class="py-4 text-right">
                                    <Link
                                        v-if="permissions.canManageWorkflows"
                                        :href="`/workflows/${workflow.id}/edit`"
                                        class="text-sm font-semibold text-[var(--color-primary)] hover:text-[var(--color-primary-dark)]"
                                    >
                                        Edit
                                    </Link>
                                </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <div v-if="!workflowsData.length && !isLoading" class="rounded-2xl border border-dashed border-[var(--color-border)] px-6 py-10 text-center text-sm text-[var(--color-muted)]">
                        No workflows found. Create your first automation.
                    </div>
                </div>

                <div class="grid gap-4 lg:hidden">
                    <WorkflowCard
                        v-for="workflow in workflowsData"
                        :key="workflow.id"
                        :workflow="workflow"
                        :status-label="getStatusLabel(workflow)"
                        :last-execution="lastExecution(workflow)"
                        :created-by="createdBy(workflow)"
                        :can-manage="permissions.canManageWorkflows"
                        @toggle="toggleWorkflow(workflow.id)"
                    />
                    <div
                        v-if="!workflowsData.length && !isLoading"
                        class="rounded-2xl border border-dashed border-[var(--color-border)] px-6 py-10 text-center text-sm text-[var(--color-muted)]"
                    >
                        No workflows found. Create your first automation.
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 text-xs text-[var(--color-muted)]">
                    <p>Pagination simulated for demo purposes.</p>
                    <div class="flex flex-wrap gap-2">
                        <template v-for="link in workflows.links ?? []" :key="link.label">
                            <span
                                v-if="!link.url"
                                v-html="link.label"
                                class="rounded-full border border-[var(--color-border)] px-3 py-1 text-xs font-semibold text-[var(--color-muted)] opacity-60"
                            />
                            <Link
                                v-else
                                :href="link.url"
                                v-html="link.label"
                                class="rounded-full border border-[var(--color-border)] px-3 py-1 text-xs font-semibold"
                                :class="link.active
                                    ? 'bg-[var(--color-primary)] text-white'
                                    : 'bg-white text-[var(--color-muted)]'"
                            />
                        </template>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
