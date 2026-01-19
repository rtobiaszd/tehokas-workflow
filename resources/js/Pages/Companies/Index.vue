<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    tenants: { type: [Array, Object], default: () => [] },
    canCreate: { type: Boolean, default: false },
});

const tenantsList = computed(() => {
    if (Array.isArray(props.tenants)) {
        return props.tenants;
    }

    return props.tenants?.data ?? [];
});
</script>

<template>
    <AppLayout>
        <div class="flex flex-col gap-6">
            <section class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Companies</p>
                        <h2 class="mt-2 text-2xl font-semibold text-[var(--color-text)]">Empresas e tenants</h2>
                        <p class="mt-2 text-sm text-[var(--color-muted)]">
                            Gerencie empresas ativas e seus ambientes.
                        </p>
                    </div>
                    <Link
                        v-if="canCreate"
                        :href="route('companies.create')"
                        class="rounded-2xl bg-[var(--color-primary)] px-5 py-3 text-sm font-semibold text-white"
                    >
                        New company
                    </Link>
                </div>
            </section>

            <section class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <div v-if="!tenantsList.length" class="rounded-2xl border border-dashed border-[var(--color-border)] px-6 py-10 text-center text-sm text-[var(--color-muted)]">
                    Nenhuma empresa cadastrada.
                </div>

                <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="tenant in tenantsList"
                        :key="tenant.id"
                        class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface-muted)] p-5"
                    >
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-[var(--color-text)]">{{ tenant.name }}</h3>
                            <span
                                class="rounded-full px-2 py-1 text-[10px] uppercase tracking-[0.2em]"
                                :class="tenant.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'"
                            >
                                {{ tenant.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-[var(--color-muted)]">Slug: {{ tenant.slug }}</p>
                        <div class="mt-4 flex items-center justify-between">
                            <Link
                                :href="route('companies.edit', tenant.id)"
                                class="text-xs font-semibold text-[var(--color-primary)]"
                            >
                                Manage
                            </Link>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
