<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AuthLayout from '../../Layouts/AuthLayout.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'));
};
</script>

<template>
    <AuthLayout>
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Login</p>
            <h2 class="mt-2 text-2xl font-semibold">Welcome back</h2>
            <p class="mt-2 text-sm text-[var(--color-muted)]">
                Access your workflows and monitor executions.
            </p>
        </div>

        <form class="mt-6 space-y-4" @submit.prevent="submit">
            <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                Email
                <input
                    v-model="form.email"
                    type="email"
                    class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                    autocomplete="email"
                />
                <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
            </label>

            <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                Password
                <input
                    v-model="form.password"
                    type="password"
                    class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                    autocomplete="current-password"
                />
                <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
            </label>

            <label class="flex items-center gap-2 text-xs text-[var(--color-muted)]">
                <input v-model="form.remember" type="checkbox" class="rounded border-[var(--color-border)]" />
                Remember me
            </label>

            <button
                type="submit"
                class="w-full rounded-2xl bg-[var(--color-primary)] px-6 py-3 text-sm font-semibold text-white"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Signing in...' : 'Sign in' }}
            </button>
        </form>

        <p class="mt-6 text-center text-xs text-[var(--color-muted)]">
            New here?
            <Link :href="route('register')" class="font-semibold text-[var(--color-primary)]">Create an account</Link>
        </p>
    </AuthLayout>
</template>
