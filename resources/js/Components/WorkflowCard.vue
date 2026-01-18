<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    workflow: { type: Object, required: true },
    statusLabel: { type: String, default: 'Inactive' },
    lastExecution: { type: String, default: '--' },
    createdBy: { type: String, default: 'System' },
    canManage: { type: Boolean, default: true },
});

const emit = defineEmits(['toggle']);
</script>

<template>
    <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-[var(--color-text)]">
                    {{ workflow.name }}
                </h3>
                <p class="mt-1 text-xs text-[var(--color-muted)]">
                    {{ workflow.description || 'No description provided' }}
                </p>
            </div>
            <span
                class="rounded-full px-2 py-1 text-[10px] uppercase tracking-[0.2em]"
                :class="workflow.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'"
            >
                {{ statusLabel }}
            </span>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3 text-xs text-[var(--color-muted)]">
            <div>
                <p class="uppercase tracking-[0.2em]">Last execution</p>
                <p class="mt-1 text-[var(--color-text)]">{{ lastExecution }}</p>
            </div>
            <div>
                <p class="uppercase tracking-[0.2em]">Created by</p>
                <p class="mt-1 text-[var(--color-text)]">{{ createdBy }}</p>
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between">
            <button
                type="button"
                class="rounded-full border border-[var(--color-border)] px-3 py-1 text-xs font-semibold text-[var(--color-muted)] hover:bg-[var(--color-surface-muted)]"
                :disabled="!canManage"
                @click="canManage ? $emit('toggle', workflow) : null"
                role="switch"
                :aria-checked="workflow.is_active ? 'true' : 'false'"
            >
                Toggle status
            </button>
            <Link
                v-if="canManage"
                :href="`/workflows/${workflow.id}/edit`"
                class="text-xs font-semibold text-[var(--color-primary)] hover:text-[var(--color-primary-dark)]"
            >
                Edit
            </Link>
        </div>
    </div>
</template>
