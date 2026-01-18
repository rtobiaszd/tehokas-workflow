<script setup>
const props = defineProps({
    modelValue: { type: Array, required: true },
    operators: { type: Array, default: () => [] },
    createCondition: { type: Function, required: true },
});

const emit = defineEmits(['update:modelValue']);

const payloadPlaceholder = `{
  "field": "status",
  "operator": "equals",
  "value": "Delayed"
}`;

const addCondition = () => {
    emit('update:modelValue', [...props.modelValue, props.createCondition()]);
};

const updateCondition = (index, patch) => {
    const next = props.modelValue.map((condition, idx) =>
        idx === index ? { ...condition, ...patch } : condition
    );
    emit('update:modelValue', next);
};

const updatePayload = (index, value) => {
    const trimmed = value.trim();
    const next = props.modelValue.map((condition, idx) => {
        if (idx !== index) {
            return condition;
        }

        if (!trimmed) {
            return { ...condition, payload: null, payload_text: value, payload_error: '' };
        }

        try {
            const parsed = JSON.parse(trimmed);
            return { ...condition, payload: parsed, payload_text: value, payload_error: '' };
        } catch (error) {
            return { ...condition, payload_text: value, payload_error: 'JSON invalido. Verifique o formato.' };
        }
    });

    emit('update:modelValue', next);
};

const removeCondition = (index) => {
    emit('update:modelValue', props.modelValue.filter((_, idx) => idx !== index));
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
                <div class="md:col-span-4">
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                        Payload JSON
                        <textarea
                            class="mt-2 w-full rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-xs font-mono"
                            rows="4"
                            :placeholder="payloadPlaceholder"
                            :value="condition.payload_text ?? ''"
                            @input="updatePayload(index, $event.target.value)"
                        ></textarea>
                    </label>
                    <p v-if="condition.payload_error" class="mt-1 text-xs text-rose-600">
                        {{ condition.payload_error }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
