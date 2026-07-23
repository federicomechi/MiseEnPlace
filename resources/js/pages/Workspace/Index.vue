<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
    section: string;
    title: string;
    roleLabel: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Operatività', href: '/dashboard' }],
    },
});
</script>

<template>
    <Head :title="title" />

    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-4 p-3 md:p-5">
        <header
            class="flex flex-wrap items-end justify-between gap-2 border-b border-border pb-3"
        >
            <div>
                <h1 class="font-serif text-2xl tracking-tight text-[#2c4133]">
                    {{ title }}
                </h1>
                <p class="mt-0.5 text-sm text-muted-foreground">
                    Area disponibile per il profilo {{ roleLabel }}.
                </p>
            </div>
        </header>

        <section
            class="rounded-xl border border-dashed border-border bg-card p-4 text-sm text-muted-foreground"
        >
            Questa area è pronta per ricevere le funzioni di gestione e i dati
            importati da FileMaker.
        </section>
    </div>
</template>
