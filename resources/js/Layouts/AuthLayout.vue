<template>
    <div class="min-h-screen bg-[var(--color-bg)] text-[var(--color-text)]">
        <div class="mx-auto flex min-h-screen max-w-5xl flex-col justify-center px-6 py-12 lg:flex-row lg:items-center lg:gap-16">
            <div class="mb-10 max-w-md lg:mb-0">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[var(--color-primary)] text-white font-semibold">
                    WF
                </div>
                <h1 class="mt-6 text-3xl font-semibold">Workflow Automation Engine</h1>
                <p class="mt-3 text-sm text-[var(--color-muted)]">
                    Enterprise SaaS for multi-tenant workflow orchestration. Build, monitor, and scale automations with confidence.
                </p>
                <ul class="mt-6 space-y-2 text-sm text-[var(--color-muted)]">
                    <li class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-[var(--color-primary)]"></span>
                        Secure webhooks and async execution
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-[var(--color-primary)]"></span>
                        Tenant-aware governance and logs
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-[var(--color-primary)]"></span>
                        Visual workflow builder
                    </li>
                </ul>
            </div>

            <div class="w-full max-w-md rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-8 shadow-sm">
                <slot />
            </div>
        </div>
    </div>
</template>
