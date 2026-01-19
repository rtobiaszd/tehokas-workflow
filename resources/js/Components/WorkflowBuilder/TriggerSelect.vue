<script setup>
import { computed, watch } from 'vue';

const props = defineProps({
    modelValue: { type: Object, required: true },
    options: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const config = computed(() => props.modelValue?.config ?? {});

const templates = {
    webhook: { event: '' },
    schedule: { cron: '0 9 * * 1-5' },
    manual: {},
};

const updateType = (type) => {
    const baseConfig = templates[type] ?? {};
    emit('update:modelValue', {
        ...props.modelValue,
        type,
        config: { ...baseConfig, ...props.modelValue.config },
    });
};

const updateConfig = (key, value) => {
    emit('update:modelValue', {
        ...props.modelValue,
        config: { ...props.modelValue.config, [key]: value },
    });
};

watch(
    () => props.modelValue.type,
    (type) => {
        if (!type) {
            updateType('webhook');
            return;
        }
        if (!props.modelValue.config || Object.keys(props.modelValue.config).length === 0) {
            updateType(type);
        }
    },
    { immediate: true }
);
</script>

<template>
    <div class="rounded-2xl border border-dashed border-[var(--color-border)] bg-[var(--color-surface)] p-5">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-[var(--color-text)]">Trigger</h3>
            <span class="rounded-full bg-[var(--color-surface-muted)] px-2 py-1 text-[10px] uppercase text-[var(--color-muted)]">
                Required
            </span>
        </div>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                Type
                <select
                    class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                    :value="modelValue.type"
                    @change="updateType($event.target.value)"
                >
                    <option v-for="option in options" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </label>

            <label v-if="modelValue.type === 'webhook'" class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                Event key
                <input
                    class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                    :value="config.event || ''"
                    placeholder="project.status_changed"
                    @input="updateConfig('event', $event.target.value)"
                />
            </label>

            <label v-else-if="modelValue.type === 'schedule'" class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                Cron
                <input
                    class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-3 py-2 text-sm font-mono"
                    :value="config.cron || ''"
                    placeholder="0 9 * * 1-5"
                    @input="updateConfig('cron', $event.target.value)"
                />
            </label>

            <div v-else class="text-xs text-[var(--color-muted)]">
                Trigger manual sera disparado via API interna.
            </div>
        </div>
    </div>
</template>
