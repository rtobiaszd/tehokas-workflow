<script setup>
const props = defineProps({
    modelValue: { type: Array, required: true },
    options: { type: Array, default: () => [] },
    createAction: { type: Function, required: true },
    templates: { type: Object, required: true },
});

const emit = defineEmits(['update:modelValue']);

const payloadPlaceholder = `{
  "field": "status",
  "operator": "equals",
  "value": "Delayed"
}`;

const addAction = () => {
    emit('update:modelValue', [...props.modelValue, props.createAction()]);
};

const updateAction = (index, patch) => {
    const next = props.modelValue.map((action, idx) =>
        idx === index ? { ...action, ...patch } : action
    );
    emit('update:modelValue', next);
};

const updateActionConfig = (index, patch) => {
    const next = props.modelValue.map((action, idx) =>
        idx === index ? { ...action, config: { ...action.config, ...patch } } : action
    );
    emit('update:modelValue', next);
};

const updatePayload = (index, value) => {
    const trimmed = value.trim();
    const next = props.modelValue.map((action, idx) => {
        if (idx !== index) {
            return action;
        }

        if (!trimmed) {
            return { ...action, payload: null, payload_text: value, payload_error: '' };
        }

        try {
            const parsed = JSON.parse(trimmed);
            return { ...action, payload: parsed, payload_text: value, payload_error: '' };
        } catch (error) {
            return { ...action, payload_text: value, payload_error: 'JSON invalido. Verifique o formato.' };
        }
    });

    emit('update:modelValue', next);
};

const updateActionType = (index, type) => {
    const config = props.templates[type] ? { ...props.templates[type] } : {};
    updateAction(index, { type, config });
};

const removeAction = (index) => {
    emit('update:modelValue', props.modelValue.filter((_, idx) => idx !== index));
};

const isOptionBlocked = (type) => {
    const option = props.options.find((item) => item.value === type);
    return option ? option.enabled === false || option.configured === false : false;
};

const optionLabel = (option) => option.displayLabel ?? option.label;

const optionsForAction = (type) => {
    const enabledOptions = props.options.filter((option) => option.enabled !== false);
    if (enabledOptions.some((option) => option.value === type)) {
        return enabledOptions;
    }
    const current = props.options.find((option) => option.value === type);
    return current ? [...enabledOptions, current] : enabledOptions;
};
</script>

<template>
    <div class="rounded-2xl border border-dashed border-[var(--color-border)] bg-[var(--color-surface)] p-5">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-[var(--color-text)]">Actions</h3>
            <button
                type="button"
                class="rounded-full bg-[var(--color-primary)] px-3 py-1 text-[10px] font-semibold uppercase text-white"
                @click="addAction"
            >
                Add action
            </button>
        </div>

        <div v-if="!modelValue.length" class="mt-4 text-sm text-[var(--color-muted)]">
            No actions configured.
        </div>

        <div v-else class="mt-4 space-y-4">
            <div
                v-for="(action, index) in modelValue"
                :key="index"
                class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface-muted)] p-4"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <select
                        class="w-full rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm md:w-60"
                        :value="action.type"
                        @change="updateActionType(index, $event.target.value)"
                    >
                        <option
                            v-for="option in optionsForAction(action.type)"
                            :key="option.value"
                            :value="option.value"
                            :disabled="(option.enabled === false || option.configured === false) && option.value !== action.type"
                            :title="option.enabled === false ? option.disabledReason : option.configured === false ? option.unconfiguredReason : ''"
                        >
                            {{ optionLabel(option) }}
                        </option>
                    </select>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="rounded-full border border-[var(--color-border)] px-3 py-1 text-[10px] font-semibold uppercase text-[var(--color-muted)] disabled:opacity-50"
                            :disabled="modelValue.length === 1"
                            @click="removeAction(index)"
                        >
                            Remove
                        </button>
                    </div>
                </div>

                <p v-if="isOptionBlocked(action.type)" class="mt-3 text-xs text-amber-600">
                    Integracao indisponivel. Ative e configure nas Settings para executar.
                </p>

                <div v-if="action.type === 'send_email'" class="mt-4 space-y-4">
                    <div class="rounded-xl border border-[var(--color-border)] bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Configuracao tecnica</p>
                        <div class="mt-3 grid gap-3 md:grid-cols-2">
                            <input
                                class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                :value="action.config.to"
                                placeholder="Destinatario (email)"
                                @input="updateActionConfig(index, { to: $event.target.value })"
                            />
                            <input
                                class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                :value="action.config.subject"
                                placeholder="Assunto"
                                @input="updateActionConfig(index, { subject: $event.target.value })"
                            />
                        </div>
                    </div>
                    <div class="rounded-xl border border-[var(--color-border)] bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Conteudo da mensagem</p>
                        <textarea
                            class="mt-3 w-full rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                            rows="4"
                            :value="action.config.body"
                            placeholder="Escreva o corpo do email"
                            @input="updateActionConfig(index, { body: $event.target.value })"
                        ></textarea>
                        <p class="mt-2 text-xs text-[var(--color-muted)]">
                            Use {{ }} para acessar qualquer campo recebido no payload do evento.
                        </p>
                        <div v-pre class="mt-2 rounded-lg bg-[var(--color-surface-muted)] p-3 text-xs font-mono text-[var(--color-muted)]">
                            Hello {{data.manager_email}}, The project {{data.project_name}} is now {{data.new_status}}.
                        </div>
                    </div>
                </div>

                <div v-else-if="action.type === 'http_request'" class="mt-4 space-y-4">
                    <div class="rounded-xl border border-[var(--color-border)] bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Configuracao tecnica</p>
                        <div class="mt-3 grid gap-3 md:grid-cols-2">
                            <input
                                class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                :value="action.config.url"
                                placeholder="Endpoint HTTPS"
                                @input="updateActionConfig(index, { url: $event.target.value })"
                            />
                            <select
                                class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                :value="action.config.method"
                                @change="updateActionConfig(index, { method: $event.target.value })"
                            >
                                <option>POST</option>
                                <option>PUT</option>
                                <option>PATCH</option>
                            </select>
                            <input
                                class="md:col-span-2 rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                :value="action.config.headers"
                                placeholder='Headers (JSON)'
                                @input="updateActionConfig(index, { headers: $event.target.value })"
                            />
                        </div>
                    </div>
                    <div class="rounded-xl border border-[var(--color-border)] bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Conteudo da requisicao</p>
                        <textarea
                            class="mt-3 w-full rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                            rows="4"
                            :value="action.config.body"
                            placeholder="Body JSON"
                            @input="updateActionConfig(index, { body: $event.target.value })"
                        ></textarea>
                        <p class="mt-2 text-xs text-[var(--color-muted)]">
                            Use {{ }} para acessar qualquer campo recebido no payload do evento.
                        </p>
                    </div>
                </div>

                <div v-else-if="action.type === 'whatsapp_message'" class="mt-4 space-y-4">
                    <div class="rounded-xl border border-[var(--color-border)] bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Configuracao tecnica</p>
                        <input
                            class="mt-3 w-full rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                            :value="action.config.to"
                            placeholder="Telefone ou contato"
                            @input="updateActionConfig(index, { to: $event.target.value })"
                        />
                    </div>
                    <div class="rounded-xl border border-[var(--color-border)] bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Conteudo da mensagem</p>
                        <textarea
                            class="mt-3 w-full rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                            rows="3"
                            :value="action.config.message"
                            placeholder="Mensagem"
                            @input="updateActionConfig(index, { message: $event.target.value })"
                        ></textarea>
                        <p class="mt-2 text-xs text-[var(--color-muted)]">
                            Use {{ }} para acessar qualquer campo recebido no payload do evento.
                        </p>
                    </div>
                </div>

                <div v-else-if="action.type === 'google_sheets_append'" class="mt-4 space-y-4">
                    <div class="rounded-xl border border-[var(--color-border)] bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Configuracao tecnica</p>
                        <input
                            class="mt-3 w-full rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                            :value="action.config.sheet"
                            placeholder="Nome da aba"
                            @input="updateActionConfig(index, { sheet: $event.target.value })"
                        />
                    </div>
                    <div class="rounded-xl border border-[var(--color-border)] bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Conteudo da linha</p>
                        <textarea
                            class="mt-3 w-full rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                            rows="3"
                            :value="action.config.values"
                            placeholder="Valores em JSON"
                            @input="updateActionConfig(index, { values: $event.target.value })"
                        ></textarea>
                        <p class="mt-2 text-xs text-[var(--color-muted)]">
                            Use {{ }} para acessar qualquer campo recebido no payload do evento.
                        </p>
                    </div>
                </div>

                <div v-else class="mt-4 space-y-4">
                    <div class="rounded-xl border border-[var(--color-border)] bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Configuracao tecnica</p>
                        <input
                            class="mt-3 w-full rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                            :value="action.config.channel"
                            placeholder="Canal ou destino"
                            @input="updateActionConfig(index, { channel: $event.target.value })"
                        />
                    </div>
                    <div class="rounded-xl border border-[var(--color-border)] bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Conteudo da mensagem</p>
                        <textarea
                            class="mt-3 w-full rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                            rows="3"
                            :value="action.config.message"
                            placeholder="Mensagem"
                            @input="updateActionConfig(index, { message: $event.target.value })"
                        ></textarea>
                        <p class="mt-2 text-xs text-[var(--color-muted)]">
                            Use {{ }} para acessar qualquer campo recebido no payload do evento.
                        </p>
                       <div v-pre class="mt-2 rounded-lg bg-[var(--color-surface-muted)] p-3 text-xs font-mono text-[var(--color-muted)]">
                            Hello {{data.manager_email}}, The project {{data.project_name}} is now {{data.new_status}}.
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                        Payload JSON
                        <textarea
                            class="mt-2 w-full rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-xs font-mono"
                            rows="4"
                            :placeholder="payloadPlaceholder"
                            :value="action.payload_text ?? ''"
                            @input="updatePayload(index, $event.target.value)"
                        ></textarea>
                    </label>
                    <p v-if="action.payload_error" class="mt-1 text-xs text-rose-600">
                        {{ action.payload_error }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
