<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const form = useForm({
    name: '',
    slug: '',
    is_active: true,
});

const submit = () => {
    form.post('/companies');
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
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-muted)]">Companies</p>
                        <h2 class="mt-2 text-2xl font-semibold text-[var(--color-text)]">New company</h2>
                        <p class="mt-2 text-sm text-[var(--color-muted)]">
                            Crie um novo tenant e defina seu status.
                        </p>
                    </div>
                    <Link href="/companies" class="text-sm font-semibold text-[var(--color-primary)]">Back</Link>
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
                    Slug
                    <input
                        v-model="form.slug"
                        type="text"
                        placeholder="optional"
                        class="mt-2 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm"
                    />
                    <p v-if="form.errors.slug" class="mt-1 text-xs text-rose-600">{{ form.errors.slug }}</p>
                </label>

                <label class="flex items-center gap-2 text-xs text-[var(--color-muted)]">
                    <input v-model="form.is_active" type="checkbox" class="rounded border-[var(--color-border)]" />
                    Active company
                </label>

                <button
                    type="submit"
                    class="rounded-2xl bg-[var(--color-primary)] px-6 py-3 text-sm font-semibold text-white"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Saving...' : 'Create company' }}
                </button>
            </form>
        </div>
    </AppLayout>
</template>
