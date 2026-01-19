<script setup>
import PayloadEditor from './PayloadEditor.vue';

const FIELD_SUGGESTIONS = [
    'data.project_id',
    'data.project_name',
    'data.old_status',
    'data.new_status',
    'data.manager_email',
];

const props = defineProps({
    modelValue: { type: Array, required: true },
    operators: { type: Array, default: () => [] },
    createCondition: { type: Function, required: true },
});

const emit = defineEmits(['update:modelValue']);

const addCondition = () => {
    emit('update:modelValue', [...props.modelValue, props.createCondition()]);
};

const updateCondition = (index, patch) => {
    const next = props.modelValue.map((condition, idx) =>
        idx === index ? { ...condition, ...patch } : condition
    );
    emit('update:modelValue', next);
};

const updatePayloadState = (index, { payload, raw, error }) => {
    const next = props.modelValue.map((condition, idx) => {
        if (idx !== index) {
            return condition;
        }

        return {
            ...condition,
            payload: payload !== undefined ? payload ?? null : condition.payload ?? null,
            payload_text:
                raw !== undefined
                    ? raw
                    : payload
                        ? JSON.stringify(payload, null, 2)
                        : condition.payload_text ?? '',
            payload_error: error ?? '',
        };
    });

    emit('update:modelValue', next);
};

const removeCondition = (index) => {
    emit('update:modelValue', props.modelValue.filter((_, idx) => idx !== index));
};

const moveCondition = (index, direction) => {
    const destination = index + direction;
    if (destination < 0 || destination >= props.modelValue.length) {
        return;
    }

    const next = [...props.modelValue];
    const [removed] = next.splice(index, 1);
    next.splice(destination, 0, removed);
    emit('update:modelValue', next);
};
</script>

<template>
    <div class="rounded-2xl border border-dashed border-[var(--color-border)] bg-[var(--color-surface)] p-5">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-[var(--color-text)]">Conditions</h3>
            <button
                type="button"
                class="rounded-full bg-[var(--color-primary)] px-3 py-1 text-[10px] font-semibold uppercase text-white"
                @click="addCondition"
            >
                Add condition
            </button>
        </div>

        <div v-if="!modelValue.length" class="mt-4 text-sm text-[var(--color-muted)]">
            No conditions. All events will pass.
        </div>

        <div v-else class="mt-4 space-y-3">
            <div
                v-for="(condition, index) in modelValue"
                :key="index"
                class="grid gap-3 rounded-xl border border-[var(--color-border)] bg-[var(--color-surface-muted)] p-4 md:grid-cols-[2fr_1fr_1fr_auto]"
            >
                <input
                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                    :value="condition.field"
                    placeholder="data.new_status"
                    @input="updateCondition(index, { field: $event.target.value })"
                />
                <select
                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                    :value="condition.operator"
                    @change="updateCondition(index, { operator: $event.target.value })"
                >
                    <option v-for="operator in operators" :key="operator.value" :value="operator.value">
                        {{ operator.label }}
                    </option>
                </select>
                <input
                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                    :value="condition.value"
                    placeholder="Expected value"
                    @input="updateCondition(index, { value: $event.target.value })"
                />
                <button
                    type="button"
                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-[10px] font-semibold uppercase text-[var(--color-muted)]"
                    @click="removeCondition(index)"
                >
                    Remove
                </button>
                <div class="flex flex-wrap gap-2 md:col-span-4">
                    <button
                        type="button"
                        class="rounded-full border border-[var(--color-border)] px-3 py-1 text-[10px] font-semibold uppercase text-[var(--color-muted)] disabled:opacity-40"
                        :disabled="index === 0"
                        @click="moveCondition(index, -1)"
                    >
                        &uarr;
                    </button>
                    <button
                        type="button"
                        class="rounded-full border border-[var(--color-border)] px-3 py-1 text-[10px] font-semibold uppercase text-[var(--color-muted)] disabled:opacity-40"
                        :disabled="index === modelValue.length - 1"
                        @click="moveCondition(index, 1)"
                    >
                        &darr;
                    </button>
                </div>
                <div class="md:col-span-4 space-y-2">
                    <PayloadEditor
                        :model-value="condition.payload ?? null"
                        :raw-value="condition.payload_text ?? ''"
                        label="Payload opcional para avaliar a condicao"
                        :suggested-fields="FIELD_SUGGESTIONS"
                        @update:modelValue="(value) => updatePayloadState(index, { payload: value })"
                        @update:rawValue="(value) => updatePayloadState(index, { raw: value })"
                        @error="(message) => updatePayloadState(index, { error: message })"
                    />
                    <p v-if="condition.payload_error" class="text-xs text-rose-600">
                        {{ condition.payload_error }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
