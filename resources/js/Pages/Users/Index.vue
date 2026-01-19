<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    users: { type: Object, required: true },
    canCreate: { type: Boolean, default: false },
});

const usersList = computed(() => props.users?.data ?? []);
</script>

<template>
    <AppLayout>
        <div class="flex flex-col gap-6">
            <section class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Users</p>
                        <h2 class="mt-2 text-2xl font-semibold text-[var(--color-text)]">Usuarios do tenant</h2>
                        <p class="mt-2 text-sm text-[var(--color-muted)]">
                            Gerencie permissoes e acessos do time.
                        </p>
                    </div>
                    <Link
                        v-if="canCreate"
                        :href="route('users.create')"
                        class="rounded-2xl bg-[var(--color-primary)] px-5 py-3 text-sm font-semibold text-white"
                    >
                        New user
                    </Link>
                </div>
            </section>

            <section class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm">
                <div v-if="!usersList.length" class="rounded-2xl border border-dashed border-[var(--color-border)] px-6 py-10 text-center text-sm text-[var(--color-muted)]">
                    Nenhum usuario cadastrado.
                </div>
                <div v-else class="overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-[var(--color-border)] text-xs uppercase tracking-[0.2em] text-[var(--color-muted)]">
                            <tr>
                                <th class="py-3 pr-4">Name</th>
                                <th class="py-3 pr-4">Email</th>
                                <th class="py-3 pr-4">Role</th>
                                <th class="py-3">Tenant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in usersList"
                                :key="user.id"
                                class="border-b border-[var(--color-border)] last:border-transparent"
                            >
                                <td class="py-4 pr-4 font-semibold text-[var(--color-text)]">{{ user.name }}</td>
                                <td class="py-4 pr-4 text-[var(--color-muted)]">{{ user.email }}</td>
                                <td class="py-4 pr-4 text-[var(--color-muted)]">{{ user.role }}</td>
                                <td class="py-4 text-[var(--color-muted)]">{{ user.tenant?.name ?? 'Global' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
