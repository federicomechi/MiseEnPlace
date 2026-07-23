<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{ title: string; description: string }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Amministrazione', href: '/admin' }],
    },
});
</script>

<template>
    <Head :title="title" />
    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-4 p-3 md:p-5">
        <section class="rounded-xl border border-border bg-card p-5 shadow-sm">
            <p
                class="text-xs font-bold tracking-[0.08em] text-[#55714e] uppercase"
            >
                Area di lavoro
            </p>
            <h1 class="mt-1 font-serif text-2xl tracking-tight text-[#2c4133]">
                {{ title }}
            </h1>
            <p class="mt-2 text-sm leading-6 text-muted-foreground">
                {{ description }}
            </p>
            <Link
                href="/admin"
                class="mt-5 inline-flex rounded-md border border-border px-3 py-2 text-sm font-semibold hover:bg-muted"
                >Torna all’amministrazione</Link
            >
        </section>
    </div>
</template>
