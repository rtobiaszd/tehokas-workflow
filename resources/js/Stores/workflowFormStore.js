import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';
import { useWorkflows } from '../Composables/useWorkflows';

export const useWorkflowFormStore = defineStore('workflowForm', () => {
    const integrationStatus = ref({});
    const workflowHelpers = ref(useWorkflows({}));

    const name = ref('');
    const description = ref('');
    const status = ref('draft');
    const is_active = ref(false);
    const trigger = ref(workflowHelpers.value.createTrigger());
    const conditions = ref([]);
    const actions = ref([]);

    const setIntegrationStatus = (statusPayload = {}) => {
        integrationStatus.value = statusPayload ?? {};
        workflowHelpers.value = useWorkflows(integrationStatus.value);
    };

    const applyMappedForm = (mapped) => {
        name.value = mapped.name ?? '';
        description.value = mapped.description ?? '';
        status.value = mapped.status ?? (mapped.is_active ? 'active' : 'draft');
        is_active.value = mapped.is_active ?? status.value === 'active';
        trigger.value = mapped.trigger ?? workflowHelpers.value.createTrigger();
        conditions.value = Array.isArray(mapped.conditions) ? mapped.conditions : [];
        actions.value = Array.isArray(mapped.actions) && mapped.actions.length
            ? mapped.actions
            : [workflowHelpers.value.createAction()];
    };

    const resetBuilder = () => {
        name.value = '';
        description.value = '';
        status.value = 'draft';
        is_active.value = false;
        trigger.value = workflowHelpers.value.createTrigger();
        conditions.value = [];
        actions.value = [workflowHelpers.value.createAction()];
    };

    const initialize = ({ workflow = null, integrations = {} } = {}) => {
        setIntegrationStatus(integrations);

        if (workflow) {
            const mapped = workflowHelpers.value.mapWorkflowToForm(workflow);
            applyMappedForm(mapped);
            return;
        }

        resetBuilder();
    };

    const snapshot = computed(() => ({
        name: name.value,
        description: description.value,
        status: status.value,
        is_active: is_active.value,
        trigger: trigger.value,
        conditions: conditions.value,
        actions: actions.value,
    }));

    watch(status, (value) => {
        is_active.value = value === 'active';
    }, { immediate: true });

    const hasPayloadErrors = computed(() => {
        const conditionErrors = conditions.value.some((condition) => Boolean(condition.payload_error));
        const actionErrors = actions.value.some((action) => Boolean(action.payload_error));

        return conditionErrors || actionErrors;
    });

    const serialize = () => ({
        name: name.value,
        description: description.value,
        status: status.value,
        is_active: is_active.value,
        trigger: trigger.value,
        conditions: conditions.value.map((condition) => ({
            ...condition,
            payload: condition.payload ?? null,
        })),
        actions: actions.value.map((action) => ({
            ...action,
            payload: action.payload ?? null,
        })),
    });

    return {
        name,
        description,
        status,
        is_active,
        trigger,
        conditions,
        actions,
        snapshot,
        integrationStatus,
        initialize,
        reset: resetBuilder,
        hasPayloadErrors,
        serialize,
    };
});
