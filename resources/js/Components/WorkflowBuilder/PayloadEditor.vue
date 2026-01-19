<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: [Object, Array, null], default: null },
    rawValue: { type: String, default: '' },
    label: { type: String, default: 'Payload' },
    suggestedFields: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue', 'update:rawValue', 'error']);

const mode = ref('visual');
const entries = ref([]);
const rawState = ref(props.rawValue ?? '');

const typeOptions = [
    { label: 'Texto', value: 'string' },
    { label: 'Numero', value: 'number' },
    { label: 'Boolean', value: 'boolean' },
    { label: 'JSON', value: 'json' },
];

watch(
    () => props.modelValue,
    (value) => {
        entries.value = flattenPayload(value);
    },
    { immediate: true }
);

watch(
    () => props.rawValue,
    (value) => {
        if (mode.value === 'json') {
            rawState.value = value ?? '';
        }
    },
    { immediate: true }
);

const uid = () => (typeof crypto !== 'undefined' && crypto.randomUUID ? crypto.randomUUID() : `payload-${Date.now()}-${Math.random()}`);

const emptyVisualState = () => ({
    id: uid(),
    path: '',
    value: '',
    type: 'string',
});

const flattenPayload = (value) => {
    if (!value || typeof value !== 'object') {
        return [emptyVisualState()];
    }

    const rows = [];

    const traverse = (node, prefix = '') => {
        if (node !== null && typeof node === 'object' && !Array.isArray(node)) {
            Object.entries(node).forEach(([key, child]) => {
                traverse(child, prefix ? `${prefix}.${key}` : key);
            });
            return;
        }

        rows.push({
            id: uid(),
            path: prefix,
            value: formatValue(childValue(node)),
            type: detectType(node),
        });
    };

    traverse(value);

    return rows.length ? rows : [emptyVisualState()];
};

const detectType = (value) => {
    if (typeof value === 'number') {
        return 'number';
    }

    if (typeof value === 'boolean') {
        return 'boolean';
    }

    if (typeof value === 'object') {
        return 'json';
    }

    return 'string';
};

const childValue = (value) => {
    if (typeof value === 'object') {
        return JSON.stringify(value);
    }

    return value ?? '';
};

const formatValue = (value) => (value ?? '').toString();

const parseEntryValue = (entry) => {
    if (entry.type === 'number') {
        const asNumber = Number(entry.value);
        return Number.isNaN(asNumber) ? entry.value : asNumber;
    }

    if (entry.type === 'boolean') {
        return entry.value === 'true' || entry.value === true;
    }

    if (entry.type === 'json') {
        return JSON.parse(entry.value);
    }

    return entry.value;
};

const setByPath = (target, path, value) => {
    const parts = path.split('.').filter(Boolean);
    if (!parts.length) {
        return;
    }

    let cursor = target;
    for (let index = 0; index < parts.length; index += 1) {
        const key = parts[index];
        if (index === parts.length - 1) {
            cursor[key] = value;
            return;
        }

        if (typeof cursor[key] !== 'object' || cursor[key] === null) {
            cursor[key] = {};
        }

        cursor = cursor[key];
    }
};

const entriesToObject = () => {
    const result = {};
    const validEntries = entries.value.filter((entry) => entry.path.trim() !== '');

    validEntries.forEach((entry) => {
        try {
            setByPath(result, entry.path.trim(), parseEntryValue(entry));
        } catch (error) {
            throw error;
        }
    });

    return Object.keys(result).length ? result : null;
};

const emitVisualChange = () => {
    try {
        const payload = entriesToObject();
        emit('error', '');
        emit('update:modelValue', payload);
        emit('update:rawValue', payload ? JSON.stringify(payload, null, 2) : '');
        rawState.value = payload ? JSON.stringify(payload, null, 2) : '';
    } catch (error) {
        emit('error', 'JSON invalido em um dos campos visual.');
    }
};

const handleRawChange = (value) => {
    rawState.value = value;
    const trimmed = value.trim();

    if (!trimmed) {
        emit('error', '');
        emit('update:modelValue', null);
        emit('update:rawValue', '');
        entries.value = [emptyVisualState()];
        return;
    }

    try {
        const parsed = JSON.parse(trimmed);
        emit('update:modelValue', parsed);
        emit('update:rawValue', JSON.stringify(parsed, null, 2));
        entries.value = flattenPayload(parsed);
        emit('error', '');
    } catch (error) {
        emit('error', 'JSON invalido. Verifique o formato.');
    }
};

const addEntry = () => {
    entries.value = [...entries.value, emptyVisualState()];
};

const removeEntry = (entryId) => {
    if (entries.value.length === 1) {
        entries.value = [emptyVisualState()];
        emitVisualChange();
        return;
    }

    entries.value = entries.value.filter((entry) => entry.id !== entryId);
    emitVisualChange();
};

const applySuggestedField = (path) => {
    if (!path) {
        return;
    }

    const existing = entries.value.find((entry) => entry.path === path);
    if (existing) {
        existing.value = existing.value || '';
        return;
    }

    entries.value = [
        ...entries.value,
        {
            id: uid(),
            path,
            value: '',
            type: 'string',
        },
    ];
};

const toggleMode = (value) => {
    mode.value = value;
    if (value === 'json') {
        rawState.value = props.rawValue ?? JSON.stringify(props.modelValue ?? {}, null, 2);
    } else {
        entries.value = flattenPayload(props.modelValue);
    }
};

const hasSuggestedFields = computed(() => (props.suggestedFields ?? []).length > 0);
</script>

<template>
    <div class="rounded-xl border border-[var(--color-border)] bg-white p-4">
        <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
            <span>{{ label }}</span>
            <div class="flex gap-2">
                <button
                    type="button"
                    class="rounded-full border border-[var(--color-border)] px-3 py-1 text-[10px] font-semibold uppercase"
                    :class="mode === 'visual' ? 'bg-[var(--color-primary)] text-white' : 'text-[var(--color-muted)]'"
                    @click="toggleMode('visual')"
                >
                    Assistido
                </button>
                <button
                    type="button"
                    class="rounded-full border border-[var(--color-border)] px-3 py-1 text-[10px] font-semibold uppercase"
                    :class="mode === 'json' ? 'bg-[var(--color-primary)] text-white' : 'text-[var(--color-muted)]'"
                    @click="toggleMode('json')"
                >
                    JSON
                </button>
            </div>
        </div>

        <div v-if="mode === 'visual'" class="mt-4 space-y-3">
            <div
                v-for="entry in entries"
                :key="entry.id"
                class="grid gap-3 md:grid-cols-[2fr_1fr_1fr_auto]"
            >
                <input
                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                    :value="entry.path"
                    placeholder="data.manager_email"
                    @input="entry.path = $event.target.value; emitVisualChange();"
                />
                <select
                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                    :value="entry.type"
                    @change="entry.type = $event.target.value; emitVisualChange();"
                >
                    <option v-for="option in typeOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
                <input
                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                    :value="entry.value"
                    placeholder="Valor"
                    @input="entry.value = $event.target.value; emitVisualChange();"
                />
                <button
                    type="button"
                    class="rounded-lg border border-[var(--color-border)] px-3 py-2 text-[10px] font-semibold uppercase text-[var(--color-muted)]"
                    @click="removeEntry(entry.id)"
                >
                    Remover
                </button>
            </div>

            <button
                type="button"
                class="rounded-full border border-[var(--color-border)] px-4 py-2 text-[10px] font-semibold uppercase text-[var(--color-muted)]"
                @click="addEntry"
            >
                Adicionar campo
            </button>

            <div v-if="hasSuggestedFields" class="rounded-lg bg-[var(--color-surface-muted)] p-3 text-xs text-[var(--color-muted)]">
                <p class="font-semibold text-[var(--color-text)]">Campos sugeridos</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button
                        v-for="field in suggestedFields"
                        :key="field"
                        type="button"
                        class="rounded-full border border-[var(--color-border)] px-3 py-1 text-[10px] uppercase text-[var(--color-muted)]"
                        @click="applySuggestedField(field)"
                    >
                        {{ field }}
                    </button>
                </div>
            </div>
        </div>

        <div v-else class="mt-4">
            <textarea
                class="min-h-[160px] w-full rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-xs font-mono"
                :value="rawState"
                @input="handleRawChange($event.target.value)"
            ></textarea>
            <p class="mt-2 text-xs text-[var(--color-muted)]">
                Cole um JSON pronto se preferir editar diretamente.
            </p>
        </div>
    </div>
</template>
