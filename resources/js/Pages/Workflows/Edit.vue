<script setup>
import { computed, onUnmounted, ref, watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import TriggerSelect from '../../Components/WorkflowBuilder/TriggerSelect.vue';
import ConditionBuilder from '../../Components/WorkflowBuilder/ConditionBuilder.vue';
import ActionBuilder from '../../Components/WorkflowBuilder/ActionBuilder.vue';
import { useWorkflows } from '../../Composables/useWorkflows';
import { useWorkflowFormStore } from '../../Stores/workflowFormStore';

const props = defineProps({
    workflow: { type: Object, required: true },
    integrations: { type: Object, default: () => ({}) },
});

const workflowStore = useWorkflowFormStore();
const inertiaForm = useForm({});
const form = inertiaForm;

const {
    statusOptions,
    triggerOptions,
    operatorOptions,
    actionOptions,
    actionTemplates,
    createCondition,
    createAction,
} = useWorkflows(props.integrations);

const payloadValidationError = ref('');
const integrationValidationError = ref('');

const isActionBlocked = (type) => {
    const option = actionOptions.find((item) => item.value === type);
    return option ? option.enabled === false || option.configured === false : false;
};

const initializeStore = () => {
    workflowStore.initialize({
        workflow: props.workflow,
        integrations: props.integrations,
    });
};

initializeStore();

watch(
    () => [props.workflow, props.integrations],
    () => initializeStore(),
    { deep: true }
);

onUnmounted(() => {
    workflowStore.reset();
});

const submit = () => {
    payloadValidationError.value = '';
    integrationValidationError.value = '';

    const hasPayloadErrors = workflowStore.hasPayloadErrors.value;

    if (hasPayloadErrors) {
        payloadValidationError.value = 'Existem payloads invalidos. Corrija o JSON antes de salvar.';
        return;
    }

    if (workflowStore.actions.some((action) => isActionBlocked(action.type))) {
        integrationValidationError.value = 'Existem acoes com integracoes desativadas ou sem credenciais.';
        return;
    }

    inertiaForm.clearErrors();
    inertiaForm
        .transform(() => workflowStore.serialize())
        .put(route('workflows.update', props.workflow.id));
};

const payloadPreview = computed(() =>
    JSON.stringify(
        {
            name: workflowStore.name || props.workflow.name,
            trigger: workflowStore.trigger.type === 'webhook'
                ? workflowStore.trigger.config.event
                : workflowStore.trigger.type,
            conditions: workflowStore.conditions,
            actions: workflowStore.actions,
        },
        null,
        2
    )
);
</script>

<template>
    <AppLayout>
        <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
            <form
                class="space-y-6 rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm"
                @submit.prevent="submit"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Workflow builder</p>
                        <h2 class="mt-2 text-2xl font-semibold text-[var(--color-text)]">Edit workflow</h2>
                        <p class="mt-2 text-sm text-[var(--color-muted)]">
                            Ajuste regras e mantenha o fluxo alinhado com o tenant.
                        </p>
                    </div>
                    <Link :href="route('workflows.index')" class="text-sm font-semibold text-[var(--color-primary)]">
                        Back to list
                    </Link>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                        Name
                        <input
                            v-model="workflowStore.name"
                            type="text"
                            class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                        Status
                        <select
                            v-model="workflowStore.status"
                            class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                        >
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </label>
                </div>

                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                    Description
                    <textarea
                        v-model="workflowStore.description"
                        rows="3"
                        class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                    ></textarea>
                </label>

                <TriggerSelect v-model="workflowStore.trigger" :options="triggerOptions" />
                <ConditionBuilder v-model="workflowStore.conditions" :operators="operatorOptions" :create-condition="createCondition" />
                <ActionBuilder
                    v-model="workflowStore.actions"
                    :options="actionOptions"
                    :templates="actionTemplates"
                    :create-action="createAction"
                />
                <p v-if="form.errors.actions" class="text-xs text-rose-600">
                    {{ form.errors.actions }}
                </p>
                <p v-if="payloadValidationError" class="text-xs text-rose-600">
                    {{ payloadValidationError }}
                </p>
                <p v-if="integrationValidationError" class="text-xs text-rose-600">
                    {{ integrationValidationError }}
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="submit"
                        class="rounded-2xl bg-[var(--color-primary)] px-6 py-3 text-sm font-semibold text-white"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>Update workflow</span>
                    </button>
                    <p class="text-xs text-[var(--color-muted)]">Changes are applied immediately.</p>
                </div>
            </form>

            <aside class="space-y-4 rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Payload preview</p>
                    <p class="mt-2 text-sm text-[var(--color-text)]">
                        Snapshot of current configuration.
                    </p>
                </div>
                <pre class="max-h-[32rem] overflow-auto rounded-2xl bg-slate-900 p-4 text-xs text-slate-100">
{{ payloadPreview }}
                </pre>
            </aside>
        </div>
    </AppLayout>
</template>
