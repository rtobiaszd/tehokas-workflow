<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    roles: { type: Array, default: () => ['user'] },
    tenants: { type: Array, default: () => [] },
    isRoot: { type: Boolean, default: false },
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: props.roles[0] ?? 'user',
    tenant_id: props.tenants[0]?.id ?? null,
});

const submit = () => {
    form.post(route('users.store'));
};
</script>

<template>
    <AppLayout>
        <div class="max-w-3xl">
            <form
                class="space-y-6 rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-sm"
                @submit.prevent="submit"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Usuários</p>
                        <h2 class="mt-2 text-2xl font-semibold text-[var(--color-text)]">Novo usuario</h2>
                        <p class="mt-2 text-sm text-[var(--color-muted)]">
                            Crie um novo usuario e atribua permissao.
                        </p>
                    </div>
                    <Link :href="route('users.index')" class="text-sm font-semibold text-[var(--color-primary)]">Back</Link>
                </div>

                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                    Name
                    <input
                        v-model="form.name"
                        type="text"
                        class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
                </label>

                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                    Email
                    <input
                        v-model="form.email"
                        type="email"
                        class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
                </label>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                        Password
                        <input
                            v-model="form.password"
                            type="password"
                            class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                        Confirm
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                        />
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                        Role
                        <select
                            v-model="form.role"
                            class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                        >
                            <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                        </select>
                    </label>
                    <label
                        v-if="isRoot"
                        class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]"
                    >
                        Tenant
                        <select
                            v-model="form.tenant_id"
                            class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                        >
                            <option :value="null">Global</option>
                            <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                                {{ tenant.name }}
                            </option>
                        </select>
                    </label>
                </div>

                <button
                    type="submit"
                    class="rounded-2xl bg-[var(--color-primary)] px-6 py-3 text-sm font-semibold text-white"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Saving...' : 'Create user' }}
                </button>
            </form>
        </div>
    </AppLayout>
</template>
