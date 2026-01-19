<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash ?? {});
const navigation = computed(() => page.props.navigation ?? []);
const currentTenant = computed(() => page.props.currentTenant);
const tenantSwitcher = computed(() => page.props.tenantSwitcher);

const companyName = computed(() => {
    return currentTenant.value?.name
        ?? tenantSwitcher.value?.company?.name
        ?? 'Empresa';
});

const permissions = computed(() => page.props.permissions ?? {});

const navigationRoutes = {
    '/dashboard': 'dashboard',
    '/companies': 'companies.index',
    '/workflows': 'workflows.index',
    '/logs': 'logs.index',
    '/users': 'users.index',
    '/settings': 'settings.index',
    '/webhooks': 'webhooks.index',
};

const resolveNavigationHref = (href) => {
    if (!href) {
        return href;
    }
    const routeName = navigationRoutes[href];
    return routeName ? route(routeName) : href;
};

const isActive = (href) => {
    if (href === '/dashboard') {
        return page.url === '/dashboard' || page.url === '/';
    }

    return page.url.startsWith(href);
};

const initials = computed(() => {
    const name = user.value?.name || 'WF';
    const parts = name.split(' ').filter(Boolean);
    return parts.slice(0, 2).map((part) => part[0]).join('').toUpperCase();
});



const iconPaths = {
    grid: 'M3 3h8v8H3V3zm10 0h8v5h-8V3zM3 13h5v8H3v-8zm7 6h11v2H10v-2zm0-6h11v2H10v-2z',
    flows: 'M4 6h7m3 0h6M4 18h6m4 0h7M11 6v12m2-6h3',
    log: 'M4 5h16M4 12h16M4 19h10',
    settings: 'M12 3v3m0 12v3m9-9h-3M6 12H3m14.4-6.4-2.1 2.1M8.7 15.3l-2.1 2.1m0-11.4 2.1 2.1m8.6 8.6 2.1 2.1',
    office: 'M3 21V5a2 2 0 0 1 2-2h9l5 5v13H3zm7-7h4m-4 4h4M6 10h4',
    users: 'M16 11c1.66 0 3-1.57 3-3.5S17.66 4 16 4s-3 1.57-3 3.5 1.34 3.5 3 3.5zM8 11c1.66 0 3-1.57 3-3.5S9.66 4 8 4 5 5.57 5 7.5 6.34 11 8 11zm8 2c-2.67 0-8 1.34-8 4v3h16v-3c0-2.66-5.33-4-8-4zM8 13c-.29 0-.62.02-.97.05C4.49 13.26 2 14.4 2 17v3h4v-3c0-1.54.8-2.74 2-3.5z',
    webhook: 'M4 7a3 3 0 1 1 6 0v1H8V7a1 1 0 1 0-2 0v1H4V7zm10 0a3 3 0 1 1 6 0v1h-2V7a1 1 0 1 0-2 0v1h-2V7zM6 11h12v2H6v-2zm2 4a3 3 0 1 0 6 0v-1h-2v1a1 1 0 1 1-2 0v-1H8v1z',
};
</script>

<template>
    <div class="min-h-screen bg-[var(--color-bg)] text-[var(--color-text)]">
        <div class="flex">
            <aside class="hidden w-64 flex-col border-r border-[var(--color-border)] bg-[var(--color-surface)] p-6 lg:flex">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--color-primary)] text-white font-semibold">
                        WF
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-[var(--color-muted)]">Workflow Engine</p>
                        <p class="text-lg font-semibold">Enterprise Hub</p>
                    </div>
                </div>

                <nav class="mt-10 space-y-2 text-sm font-medium">
                    <Link
                        v-for="item in navigation"
                        :key="item.href"
                        :href="resolveNavigationHref(item.href)"
                        class="flex items-center gap-3 rounded-xl px-3 py-2 transition"
                        :class="isActive(item.href)
                            ? 'bg-[var(--color-surface-muted)] text-[var(--color-primary)]'
                            : 'text-[var(--color-muted)] hover:bg-[var(--color-surface-muted)]'"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <path :d="iconPaths[item.icon]" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="mt-auto rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface-muted)] p-4 text-xs text-[var(--color-muted)]">
                    <p class="font-semibold text-[var(--color-text)]">Status do tenant</p>
                    <div class="mt-3 space-y-3 text-[var(--color-muted)]">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.2em]">Filas processadas 24h</p>
                            <p class="mt-1 text-base font-semibold text-[var(--color-text)]">{{ page.props.stats?.executions_last_24h ?? 0 }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.2em]">Falhas detectadas</p>
                            <p class="mt-1 text-base font-semibold text-[var(--color-text)]">
                                {{ page.props.stats?.failures_last_24h ?? 0 }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.2em]">Taxa de sucesso</p>
                            <p class="mt-1 text-base font-semibold text-[var(--color-success)]">
                                {{ page.props.stats?.success_percent ?? 0 }}%
                            </p>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="flex min-h-screen flex-1 flex-col">
                <header class="sticky top-0 z-20 border-b border-[var(--color-border)] bg-[var(--color-surface)]/90 backdrop-blur">
                    <div class="flex flex-col gap-4 px-6 py-4 lg:flex-row lg:items-center lg:justify-between lg:px-10">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Enterprise Console</p>
                            <h1 class="text-lg font-semibold">Workflow Automation</h1>
                        </div>
                        <div class="flex items-center justify-between gap-6 lg:justify-end">
                            <nav class="flex items-center gap-3 text-sm font-medium lg:hidden">
                                <Link
                                    v-for="item in navigation"
                                    :key="item.href"
                                    :href="resolveNavigationHref(item.href)"
                                    class="rounded-full px-3 py-2 text-[var(--color-muted)] hover:bg-[var(--color-surface-muted)]"
                                >
                                    {{ item.label }}
                                </Link>
                                <Link
                                    v-if="permissions.canManageWorkflows"
                                    :href="route('workflows.create')"
                                    class="rounded-full bg-[var(--color-primary)] px-3 py-2 text-white"
                                >
                                    Novo
                                </Link>
                            </nav>
                            <div class="flex items-center gap-3">
                             <!--   <div class="hidden flex-col text-right text-xs text-[var(--color-muted)] lg:flex">
                                    <span class="uppercase tracking-[0.2em]">Empresa</span>
                                    <span class="font-semibold text-[var(--color-text)]">
                                        {{ companyName }}
                                    </span>
                                </div>
                              <div v-if="tenantSwitcher" class="hidden lg:block">
                                    <select
                                        class="rounded-xl border border-[var(--color-border)] bg-white px-3 py-2 text-xs"
                                        :value="tenantSwitcher.current ?? ''"
                                        @change="switchTenant"
                                    >
                                        <option value="">Global</option>
                                        <option v-for="tenant in tenantSwitcher.tenants" :key="tenant.id" :value="tenant.id">
                                            {{ tenant.name }}
                                        </option>
                                    </select>
                                </div>-->
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--color-surface-muted)] text-sm font-semibold text-[var(--color-primary)]">
                                    {{ initials }}
                                </div>
                                <div class="text-sm">
                                    <p class="font-semibold text-[var(--color-text)]">
                                        {{ user?.name ?? 'Guest User' }}
                                    </p>
                                    <p class="text-xs text-[var(--color-muted)]">
                                        {{ user?.email ?? 'no-email@tenant.local' }}
                                    </p>
                                </div>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="rounded-full border border-[var(--color-border)] px-3 py-2 text-xs font-semibold text-[var(--color-muted)] hover:bg-[var(--color-surface-muted)]"
                                >
                                    Sair
                                </Link>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="flex-1 px-6 py-6 lg:px-10">
                    <div v-if="flash.success" class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ flash.success }}
                    </div>
                    <div v-if="flash.error" class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ flash.error }}
                    </div>
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
