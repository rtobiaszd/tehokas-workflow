<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    settings: { type: Object, default: () => ({}) },
    integrationsStatus: { type: Object, default: () => ({}) },
    canEdit: { type: Boolean, default: false },
    hasTenant: { type: Boolean, default: true },
});

const tabs = [
    { key: 'general', label: 'General & Policies' },
    { key: 'integrations', label: 'Integrations' },
    { key: 'credentials', label: 'Credenciais & Conexoes' },
    { key: 'security', label: 'Seguranca & Conta' }, 
];

const activeTab = ref('general');
const page = usePage();
const tokenPreview = computed(() => page.props.flash?.tokenPreview ?? '');
const canEdit = computed(() => props.canEdit);
const hasTenant = computed(() => props.hasTenant);
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});
const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};
const form = useForm({
    settings: JSON.parse(JSON.stringify(props.settings ?? {})),
    webhook_token: '',
});

const webhookHeadersText = ref(
    JSON.stringify(form.settings.integrations?.webhook?.credentials?.headers ?? {}, null, 2)
);
const webhookHeadersError = ref('');

const integrations = computed(() => props.integrationsStatus ?? {});
const isLoading = ref(false);
const isHydrated = ref(false);
const showSkeleton = computed(() => isLoading.value || !isHydrated.value);

const handleStart = (event) => {
    const method = event.detail?.visit?.method?.toLowerCase?.() ?? 'get';
    if (method !== 'get') {
        return;
    }
    isLoading.value = true;
};

const handleFinish = (event) => {
    const method = event.detail?.visit?.method?.toLowerCase?.() ?? 'get';
    if (method !== 'get') {
        return;
    }
    isLoading.value = false;
};

onMounted(() => {
    isHydrated.value = true;
    router.on('start', handleStart);
    router.on('finish', handleFinish);
});

onUnmounted(() => {
    router.off('start', handleStart);
    router.off('finish', handleFinish);
});

const submit = () => {
    webhookHeadersError.value = '';

    if (webhookHeadersText.value) {
        try {
            const parsed = JSON.parse(webhookHeadersText.value);
            form.settings.integrations.webhook.credentials.headers = parsed;
        } catch (error) {
            webhookHeadersError.value = 'Headers devem estar em JSON valido.';
            return;
        }
    }

    if (!canEdit.value) {
        return;
    }

    form.put(route('settings.update'));

};

const testIntegration = (integration) => {
    if (!canEdit.value) {
        return;
    }
    router.post(route('settings.integrations.test'), { integration }, { preserveScroll: true });

};

const regenerateToken = () => {
    if (!canEdit.value) {
        return;
    }
    router.post(
        route('settings.webhook-token'),
        {},
        { preserveScroll: true }
    );
};


const copyToken = async () => {
    if (!tokenPreview.value) {
        return;
    }
    try {
        await navigator.clipboard.writeText(tokenPreview.value);
    } catch (error) {
        // noop: fallback to manual copy
    }
};
</script>

<template>
    <AppLayout>
        <div class="max-w-5xl space-y-6">
            <section class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Settings</p>
                        <h2 class="mt-2 text-2xl font-semibold text-[var(--color-text)]">Preferencias do tenant</h2>
                        <p class="mt-2 text-sm text-[var(--color-muted)]">
                            Governanca, politicas e integracoes da operacao.
                        </p>
                    </div>
                    <span class="rounded-full border border-[var(--color-border)] px-3 py-1 text-xs text-[var(--color-muted)]">
                        {{ canEdit ? 'Edicao habilitada' : 'Somente leitura' }}
                    </span>
                </div>
            </section>

            <section
                v-if="!hasTenant"
                class="rounded-3xl border border-dashed border-[var(--color-border)] bg-[var(--color-surface)] p-6 text-sm text-[var(--color-muted)]"
            >
                Selecione um tenant no header para visualizar as configuracoes.
            </section>

            <div v-else class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <div class="flex flex-wrap gap-3 border-b border-[var(--color-border)] pb-4">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="rounded-full px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em]"
                        :class="activeTab === tab.key
                            ? 'bg-[var(--color-primary)] text-white'
                            : 'bg-[var(--color-surface-muted)] text-[var(--color-muted)]'"
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <form class="mt-6 space-y-6" @submit.prevent="submit">
                    <div v-if="activeTab === 'general'" class="space-y-6">
                        <section class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                            <h3 class="text-sm font-semibold text-[var(--color-text)]">Notifications</h3>
                            <p class="mt-1 text-xs text-[var(--color-muted)]">Canais de alerta para eventos criticos.</p>
                            <div class="mt-4 grid gap-3 md:grid-cols-3">
                                <label class="flex items-center gap-2 text-sm">
                                    <input
                                        v-model="form.settings.notifications.email.enabled"
                                        type="checkbox"
                                        class="rounded border-[var(--color-border)]"
                                        :disabled="!canEdit"
                                    />
                                    Email alerts
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input
                                        v-model="form.settings.notifications.slack.enabled"
                                        type="checkbox"
                                        class="rounded border-[var(--color-border)]"
                                        :disabled="!canEdit"
                                    />
                                    Slack alerts
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input
                                        v-model="form.settings.notifications.whatsapp.enabled"
                                        type="checkbox"
                                        class="rounded border-[var(--color-border)]"
                                        :disabled="!canEdit"
                                    />
                                    WhatsApp alerts
                                </label>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                            <h3 class="text-sm font-semibold text-[var(--color-text)]">Policies</h3>
                            <p class="mt-1 text-xs text-[var(--color-muted)]">Limites e resiliencia do workflow engine.</p>
                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                                    Rate limit (por minuto)
                                    <input
                                        v-model.number="form.settings.policies.workflow.rate_limit"
                                        type="number"
                                        min="0"
                                        class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                                        :disabled="!canEdit"
                                    />
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input
                                        v-model="form.settings.policies.workflow.retry_on_fail"
                                        type="checkbox"
                                        class="rounded border-[var(--color-border)]"
                                        :disabled="!canEdit"
                                    />
                                    Retry on fail
                                </label>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                            <h3 class="text-sm font-semibold text-[var(--color-text)]">Observability</h3>
                            <p class="mt-1 text-xs text-[var(--color-muted)]">Controle de logs e monitoramento externo.</p>
                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                <label class="flex items-center gap-2 text-sm">
                                    <input
                                        v-model="form.settings.observability.logs.enabled"
                                        type="checkbox"
                                        class="rounded border-[var(--color-border)]"
                                        :disabled="!canEdit"
                                    />
                                    Logs estruturados
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input
                                        v-model="form.settings.observability.external.enabled"
                                        type="checkbox"
                                        class="rounded border-[var(--color-border)]"
                                        :disabled="!canEdit"
                                    />
                                    Monitoramento externo
                                </label>
                            </div>
                        </section>
                    </div>

                    <div v-if="activeTab === 'integrations'" class="space-y-6">
                        <section class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                            <p class="text-xs text-[var(--color-muted)]">
                                Ativar uma integracao permite que ela seja usada nos workflows.
                                A configuracao tecnica e feita na aba Credenciais & Conexoes.
                            </p>
                            <div class="mt-5 grid gap-4 md:grid-cols-2">
                                <label class="flex items-center justify-between gap-3 text-sm">
                                    <span>Email</span>
                                    <input
                                        v-model="form.settings.integrations.email.enabled"
                                        type="checkbox"
                                        class="rounded border-[var(--color-border)]"
                                        :disabled="!canEdit"
                                    />
                                </label>
                                <label class="flex items-center justify-between gap-3 text-sm">
                                    <span>Webhook</span>
                                    <input
                                        v-model="form.settings.integrations.webhook.enabled"
                                        type="checkbox"
                                        class="rounded border-[var(--color-border)]"
                                        :disabled="!canEdit"
                                    />
                                </label>
                                <label class="flex items-center justify-between gap-3 text-sm">
                                    <span>Slack</span>
                                    <input
                                        v-model="form.settings.integrations.slack.enabled"
                                        type="checkbox"
                                        class="rounded border-[var(--color-border)]"
                                        :disabled="!canEdit"
                                    />
                                </label>
                                <label class="flex items-center justify-between gap-3 text-sm">
                                    <span>WhatsApp</span>
                                    <input
                                        v-model="form.settings.integrations.whatsapp.enabled"
                                        type="checkbox"
                                        class="rounded border-[var(--color-border)]"
                                        :disabled="!canEdit"
                                    />
                                </label>
                                <label class="flex items-center justify-between gap-3 text-sm">
                                    <span>Teams</span>
                                    <input
                                        v-model="form.settings.integrations.teams.enabled"
                                        type="checkbox"
                                        class="rounded border-[var(--color-border)]"
                                        :disabled="!canEdit"
                                    />
                                </label>
                                <label class="flex items-center justify-between gap-3 text-sm">
                                    <span>Google Sheets</span>
                                    <input
                                        v-model="form.settings.integrations.google_sheets.enabled"
                                        type="checkbox"
                                        class="rounded border-[var(--color-border)]"
                                        :disabled="!canEdit"
                                    />
                                </label>
                                <label class="flex items-center justify-between gap-3 text-sm">
                                    <span>Salesforce</span>
                                    <input
                                        v-model="form.settings.integrations.salesforce.enabled"
                                        type="checkbox"
                                        class="rounded border-[var(--color-border)]"
                                        :disabled="!canEdit"
                                    />
                                </label>
                                <label class="flex items-center justify-between gap-3 text-sm">
                                    <span>HubSpot</span>
                                    <input
                                        v-model="form.settings.integrations.hubspot.enabled"
                                        type="checkbox"
                                        class="rounded border-[var(--color-border)]"
                                        :disabled="!canEdit"
                                    />
                                </label>
                            </div>
                        </section>
                    </div>

                    <div v-if="activeTab === 'credentials'" class="space-y-6">
                        <section class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-[var(--color-text)]">Webhook (Incoming)</h3>
                                    <p class="mt-1 text-xs text-[var(--color-muted)]">
                                        Este token deve ser enviado no header <strong>X-WEBHOOK-TOKEN</strong>.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    class="rounded-full border border-[var(--color-border)] px-3 py-1 text-xs font-semibold text-[var(--color-muted)]"
                                    :disabled="!canEdit"
                                    @click="regenerateToken"
                                >
                                    Gerar novo token
                                </button>
                            </div>

                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                                    Webhook token
                                    <input
                                        v-model="form.webhook_token"
                                        type="password"
                                        class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                                        placeholder="Token seguro"
                                        :disabled="!canEdit"
                                    />
                                    <p class="mt-1 text-[10px] text-[var(--color-muted)]">
                                        {{ integrations.webhook?.configured ? 'Token configurado.' : 'Token nao configurado.' }}
                                    </p>
                                </label>
                                <div class="rounded-xl border border-dashed border-[var(--color-border)] p-4 text-xs text-[var(--color-muted)]">
                                    O token atual nao e exibido por seguranca. Gere um novo token para copiar.
                                </div>
                            </div>

                            <div v-if="tokenPreview" class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs">
                                <p class="font-semibold text-emerald-700">Token gerado</p>
                                <p class="mt-2 font-mono text-emerald-700">{{ tokenPreview }}</p>
                                <div class="mt-3 flex gap-2">
                                    <button
                                        type="button"
                                        class="rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white"
                                        @click="copyToken"
                                    >
                                        Copiar token
                                    </button>
                                </div>
                            </div>
                        </section>
                        <section class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-[var(--color-text)]">Email (SMTP)</h3>
                                <button
                                    type="button"
                                    class="text-xs font-semibold text-[var(--color-primary)]"
                                    :disabled="!canEdit"
                                    @click="testIntegration('email')"
                                >
                                    Test Connection
                                </button>
                            </div>
                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                <input
                                    v-model="form.settings.integrations.email.credentials.host"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="SMTP Host"
                                    :disabled="!canEdit"
                                />
                                <input
                                    v-model="form.settings.integrations.email.credentials.port"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Port"
                                    :disabled="!canEdit"
                                />
                                <select
                                    v-model="form.settings.integrations.email.credentials.encryption"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    :disabled="!canEdit"
                                >
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                </select>
                                <input
                                    v-model="form.settings.integrations.email.credentials.username"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Username"
                                    :disabled="!canEdit"
                                />
                                <input
                                    v-model="form.settings.integrations.email.credentials.password"
                                    type="password"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Password"
                                    :disabled="!canEdit"
                                />
                                <input
                                    v-model="form.settings.integrations.email.credentials.from_name"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="From Name"
                                    :disabled="!canEdit"
                                />
                                <input
                                    v-model="form.settings.integrations.email.credentials.from_email"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="From Email"
                                    :disabled="!canEdit"
                                />
                            </div>
                        </section>
                        <section class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-[var(--color-text)]">Slack</h3>
                                <button
                                    type="button"
                                    class="text-xs font-semibold text-[var(--color-primary)]"
                                    :disabled="!canEdit"
                                    @click="testIntegration('slack')"
                                >
                                    Test Connection
                                </button>
                            </div>
                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                <input
                                    v-model="form.settings.integrations.slack.credentials.bot_token"
                                    type="password"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Bot Token"
                                    :disabled="!canEdit"
                                />
                                <input
                                    v-model="form.settings.integrations.slack.credentials.default_channel"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Default Channel"
                                    :disabled="!canEdit"
                                />
                                <input
                                    v-model="form.settings.integrations.slack.credentials.webhook_url"
                                    class="md:col-span-2 rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Webhook URL"
                                    :disabled="!canEdit"
                                />
                            </div>
                        </section>
                        <section class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-[var(--color-text)]">WhatsApp</h3>
                                <button
                                    type="button"
                                    class="text-xs font-semibold text-[var(--color-primary)]"
                                    :disabled="!canEdit"
                                    @click="testIntegration('whatsapp')"
                                >
                                    Test Connection
                                </button>
                            </div>
                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                <input
                                    v-model="form.settings.integrations.whatsapp.credentials.provider"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Provider"
                                    :disabled="!canEdit"
                                />
                                <input
                                    v-model="form.settings.integrations.whatsapp.credentials.api_token"
                                    type="password"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="API Token"
                                    :disabled="!canEdit"
                                />
                                <input
                                    v-model="form.settings.integrations.whatsapp.credentials.phone_number_id"
                                    class="md:col-span-2 rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Phone Number ID"
                                    :disabled="!canEdit"
                                />
                            </div>
                        </section>
                        <section class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-[var(--color-text)]">Teams</h3>
                                <button
                                    type="button"
                                    class="text-xs font-semibold text-[var(--color-primary)]"
                                    :disabled="!canEdit"
                                    @click="testIntegration('teams')"
                                >
                                    Test Connection
                                </button>
                            </div>
                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                <input
                                    v-model="form.settings.integrations.teams.credentials.webhook_url"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Webhook URL"
                                    :disabled="!canEdit"
                                />
                                <input
                                    v-model="form.settings.integrations.teams.credentials.tenant_id"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Tenant ID (optional)"
                                    :disabled="!canEdit"
                                />
                            </div>
                        </section>
                        <section class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-[var(--color-text)]">Webhook Outgoing</h3>
                                <button
                                    type="button"
                                    class="text-xs font-semibold text-[var(--color-primary)]"
                                    :disabled="!canEdit"
                                    @click="testIntegration('webhook')"
                                >
                                    Test Connection
                                </button>
                            </div>
                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                <textarea
                                    v-model="webhookHeadersText"
                                    rows="4"
                                    class="md:col-span-2 rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Headers JSON"
                                    :disabled="!canEdit"
                                ></textarea>
                                <input
                                    v-model.number="form.settings.integrations.webhook.credentials.timeout"
                                    type="number"
                                    min="1"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Timeout (s)"
                                    :disabled="!canEdit"
                                />
                                <input
                                    v-model.number="form.settings.integrations.webhook.credentials.retries"
                                    type="number"
                                    min="0"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Retry policy"
                                    :disabled="!canEdit"
                                />
                            </div>
                            <p v-if="webhookHeadersError" class="mt-2 text-xs text-rose-600">
                                {{ webhookHeadersError }}
                            </p>
                        </section>
                        <section class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-[var(--color-text)]">Google Sheets</h3>
                                <button
                                    type="button"
                                    class="text-xs font-semibold text-[var(--color-primary)]"
                                    :disabled="!canEdit"
                                    @click="testIntegration('google_sheets')"
                                >
                                    Test Connection
                                </button>
                            </div>
                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                <textarea
                                    v-model="form.settings.integrations.google_sheets.credentials.service_account_json"
                                    rows="4"
                                    class="md:col-span-2 rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Service Account JSON"
                                    :disabled="!canEdit"
                                ></textarea>
                                <input
                                    v-model="form.settings.integrations.google_sheets.credentials.spreadsheet_id"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Spreadsheet ID"
                                    :disabled="!canEdit"
                                />
                                <input
                                    v-model="form.settings.integrations.google_sheets.credentials.default_sheet"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Default Sheet"
                                    :disabled="!canEdit"
                                />
                            </div>
                        </section>
                        <section class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-[var(--color-text)]">Salesforce</h3>
                                <button
                                    type="button"
                                    class="text-xs font-semibold text-[var(--color-primary)]"
                                    :disabled="!canEdit"
                                    @click="testIntegration('salesforce')"
                                >
                                    Test Connection
                                </button>
                            </div>
                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                <input
                                    v-model="form.settings.integrations.salesforce.credentials.client_id"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Client ID"
                                    :disabled="!canEdit"
                                />
                                <input
                                    v-model="form.settings.integrations.salesforce.credentials.client_secret"
                                    type="password"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Client Secret"
                                    :disabled="!canEdit"
                                />
                                <input
                                    v-model="form.settings.integrations.salesforce.credentials.refresh_token"
                                    type="password"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Refresh Token"
                                    :disabled="!canEdit"
                                />
                                <input
                                    v-model="form.settings.integrations.salesforce.credentials.instance_url"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Instance URL"
                                    :disabled="!canEdit"
                                />
                            </div>
                        </section>
                        <section class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-[var(--color-text)]">HubSpot</h3>
                                <button
                                    type="button"
                                    class="text-xs font-semibold text-[var(--color-primary)]"
                                    :disabled="!canEdit"
                                    @click="testIntegration('hubspot')"
                                >
                                    Test Connection
                                </button>
                            </div>
                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                <input
                                    v-model="form.settings.integrations.hubspot.credentials.access_token"
                                    type="password"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Access Token"
                                    :disabled="!canEdit"
                                />
                                <input
                                    v-model="form.settings.integrations.hubspot.credentials.refresh_token"
                                    type="password"
                                    class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm"
                                    placeholder="Refresh Token"
                                    :disabled="!canEdit"
                                />
                            </div>
                        </section>
                    </div>

                    <div v-if="activeTab === 'security'" class="space-y-6">
                        <section class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                            <h3 class="text-sm font-semibold text-[var(--color-text)]">
                                Alterar senha
                            </h3>
                            <p class="mt-1 text-xs text-[var(--color-muted)]">
                                Atualize sua senha de acesso. Esta ação afeta apenas seu usuário.
                            </p>

                            <form class="mt-6 grid gap-4 max-w-md" @submit.prevent="updatePassword">
                                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                                    Senha atual
                                    <input
                                        v-model="passwordForm.current_password"
                                        type="password"
                                        class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                                    />
                                </label>

                                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                                    Nova senha
                                    <input
                                        v-model="passwordForm.password"
                                        type="password"
                                        class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                                    />
                                </label>

                                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                                    Confirmar nova senha
                                    <input
                                        v-model="passwordForm.password_confirmation"
                                        type="password"
                                        class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                                    />
                                </label>

                                <div class="flex items-center gap-3">
                                    <button
                                        type="submit"
                                        class="rounded-2xl bg-[var(--color-primary)] px-6 py-3 text-sm font-semibold text-white disabled:opacity-60"
                                        :disabled="passwordForm.processing"
                                    >
                                        {{ passwordForm.processing ? 'Atualizando...' : 'Atualizar senha' }}
                                    </button>

                                    <span
                                        v-if="passwordForm.recentlySuccessful"
                                        class="text-xs text-emerald-600"
                                    >
                                        Senha atualizada com sucesso.
                                    </span>
                                </div>

                                <div v-if="passwordForm.errors.current_password" class="text-xs text-rose-600">
                                    {{ passwordForm.errors.current_password }}
                                </div>
                                <div v-if="passwordForm.errors.password" class="text-xs text-rose-600">
                                    {{ passwordForm.errors.password }}
                                </div>
                            </form>
                        </section>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="submit"
                            class="rounded-2xl bg-[var(--color-primary)] px-6 py-3 text-sm font-semibold text-white disabled:opacity-60"
                            :disabled="!canEdit || form.processing"
                        >
                            {{ form.processing ? 'Saving...' : 'Save settings' }}
                        </button>
                        <p v-if="!canEdit" class="text-xs text-[var(--color-muted)]">
                            Somente administradores do tenant podem editar.
                        </p>
                        <Link :href="route('webhooks.index')" class="text-xs font-semibold text-[var(--color-primary)]">
                            Ver endpoint de webhook
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
