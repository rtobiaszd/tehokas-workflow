<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AuthLayout from '../../Layouts/AuthLayout.vue';

const form = useForm({
    name: '',
    email: '',
    company: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register');
};
</script>

<template>
    <AuthLayout>
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Register</p>
            <h2 class="mt-2 text-2xl font-semibold">Create your workspace</h2>
            <p class="mt-2 text-sm text-[var(--color-muted)]">
                Your company and admin user are created automatically.
            </p>
        </div>

        <form class="mt-6 space-y-4" @submit.prevent="submit">
            <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                Full name
                <input
                    v-model="form.name"
                    type="text"
                    class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                />
                <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
            </label>

            <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                Work email
                <input
                    v-model="form.email"
                    type="email"
                    class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                />
                <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
            </label>

            <label class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">
                Company name
                <input
                    v-model="form.company"
                    type="text"
                    class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                />
                <p v-if="form.errors.company" class="mt-1 text-xs text-rose-600">{{ form.errors.company }}</p>
            </label>

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
                Confirm password
                <input
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                />
            </label>

            <button
                type="submit"
                class="w-full rounded-2xl bg-[var(--color-primary)] px-6 py-3 text-sm font-semibold text-white"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Creating...' : 'Create account' }}
            </button>
        </form>

        <p class="mt-6 text-center text-xs text-[var(--color-muted)]">
            Already have an account?
            <Link href="/login" class="font-semibold text-[var(--color-primary)]">Sign in</Link>
        </p>
    </AuthLayout>
</template>
