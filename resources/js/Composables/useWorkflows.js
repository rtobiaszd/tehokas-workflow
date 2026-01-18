const triggerTemplates = {
    webhook: { event: '' },
    schedule: { cron: '0 9 * * 1-5' },
    manual: {},
};

const actionTemplates = {
    send_email: {
        to: '',
        subject: '',
        body: '',
    },
    teams_message: {
        channel: '',
        message: '',
    },
    whatsapp_message: {
        to: '',
        message: '',
    },
    google_sheets_append: {
        sheet: '',
        values: '',
    },
    http_request: {
        url: '',
        method: 'POST',
        headers: '',
        body: '',
    },
    slack_message: {
        channel: '',
        message: '',
    },
};

export const useWorkflows = (integrationStatus = {}) => {
    const statusOptions = [
        { label: 'Active', value: 'active' },
        { label: 'Draft', value: 'draft' },
    ];

    const filterOptions = [
        { label: 'All', value: 'all' },
        { label: 'Active', value: 'active' },
        { label: 'Inactive', value: 'inactive' },
        { label: 'Draft', value: 'draft' },
    ];

    const triggerOptions = [
        { label: 'Webhook', value: 'webhook' },
        { label: 'Schedule', value: 'schedule' },
        { label: 'Manual', value: 'manual' },
    ];

    const operatorOptions = [
        { label: 'Equals', value: 'equals' },
        { label: 'Not equals', value: 'not_equals' },
        { label: 'Contains', value: 'contains' },
        { label: 'Greater than', value: 'gt' },
        { label: 'Less than', value: 'lt' },
        { label: 'In list', value: 'in' },
        { label: 'Not in list', value: 'not_in' },
    ];

    const buildOption = (label, value, integrationKey) => {
        const state = integrationKey ? integrationStatus[integrationKey] ?? {} : {};
        const enabled = integrationKey ? state.enabled !== false : true;
        const configured = integrationKey ? state.configured !== false : true;
        const displayLabel = enabled
            ? configured ? label : `${label} (Configurar)`
            : `${label} (Desativado)`;

        return {
            label,
            value,
            enabled,
            configured,
            disabledReason: enabled ? '' : 'Desativado nas Settings',
            unconfiguredReason: configured ? '' : 'Credenciais pendentes nas Settings',
            displayLabel,
        };
    };

    const actionOptions = [
        buildOption('Send Email', 'send_email', 'email'),
        buildOption('Send Webhook', 'http_request', 'webhook'),
        buildOption('Send Slack Message', 'slack_message', 'slack'),
        buildOption('Send Teams Message', 'teams_message', 'teams'),
        buildOption('Send WhatsApp Message', 'whatsapp_message', 'whatsapp'),
        buildOption('Append Google Sheet Row', 'google_sheets_append', 'google_sheets'),
    ];

    const createTrigger = (type = 'webhook') => ({
        type,
        config: { ...(triggerTemplates[type] ?? {}) },
    });

    const createCondition = () => ({
        field: '',
        operator: 'equals',
        value: '',
        payload: null,
        payload_text: '',
        payload_error: '',
    });

    const defaultActionType = actionOptions.find((option) => option.enabled && option.configured)?.value ?? 'send_email';

    const createAction = (type = defaultActionType) => ({
        type,
        is_active: true,
        config: { ...(actionTemplates[type] ?? {}) },
        payload: null,
        payload_text: '',
        payload_error: '',
    });

    const formatPayloadText = (payload) => {
        if (payload === null || payload === undefined) {
            return '';
        }
        try {
            return JSON.stringify(payload, null, 2);
        } catch (error) {
            return '';
        }
    };

    const normalizeTrigger = (trigger) => {
        if (!trigger) {
            return createTrigger();
        }

        if (typeof trigger === 'string') {
            return { type: 'webhook', config: { event: trigger } };
        }

        return {
            type: trigger.type ?? 'webhook',
            config: trigger.config ?? { ...(triggerTemplates[trigger.type] ?? {}) },
        };
    };

    const normalizeAction = (action) => {
        if (!action) {
            return createAction();
        }

        if (action.type) {
            return {
                type: action.type,
                is_active: action.is_active ?? true,
                config: action.config ?? {},
            };
        }

        return createAction();
    };

    const mapWorkflowToForm = (workflow) => {
        const definition = workflow?.definition ?? {};
        const trigger = definition.trigger
            ? normalizeTrigger(definition.trigger)
            : workflow?.triggers?.[0]
                ? { type: workflow.triggers[0].type, config: workflow.triggers[0].config ?? {} }
                : createTrigger();

        const conditions = definition.conditions
            ? definition.conditions.map((condition) => ({
                field: condition.field ?? '',
                operator: condition.operator ?? 'equals',
                value: condition.value ?? '',
                payload: condition.payload ?? null,
                payload_text: formatPayloadText(condition.payload),
                payload_error: '',
            }))
            : (workflow?.conditions ?? []).map((condition) => ({
                field: condition.config?.field ?? '',
                operator: condition.config?.operator ?? 'equals',
                value: condition.config?.value ?? '',
                payload: condition.payload ?? null,
                payload_text: formatPayloadText(condition.payload),
                payload_error: '',
            }));

        const actions = definition.actions
            ? definition.actions.map((action) => ({
                ...normalizeAction(action),
                payload: action.payload ?? null,
                payload_text: formatPayloadText(action.payload),
                payload_error: '',
            }))
            : (workflow?.actions ?? []).map((action) => ({
                type: action.type,
                is_active: action.is_active,
                config: action.config ?? {},
                payload: action.payload ?? null,
                payload_text: formatPayloadText(action.payload),
                payload_error: '',
            }));

        return {
            name: workflow?.name ?? '',
            description: workflow?.description ?? '',
            status: workflow?.is_active ? 'active' : 'draft',
            is_active: workflow?.is_active ?? false,
            trigger,
            conditions: conditions.length ? conditions : [],
            actions: actions.length ? actions : [createAction()],
        };
    };

    const getStatusLabel = (workflow) => {
        if (workflow?.is_active) {
            return 'Active';
        }

        if (workflow?.definition?.status === 'draft') {
            return 'Draft';
        }

        return 'Inactive';
    };

    const formatDate = (value) => {
        if (!value) {
            return '--';
        }
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return '--';
        }
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
    };

    return {
        statusOptions,
        filterOptions,
        triggerOptions,
        operatorOptions,
        actionOptions,
        actionTemplates,
        triggerTemplates,
        createTrigger,
        createCondition,
        createAction,
        mapWorkflowToForm,
        formatPayloadText,
        getStatusLabel,
        formatDate,
    };
};
