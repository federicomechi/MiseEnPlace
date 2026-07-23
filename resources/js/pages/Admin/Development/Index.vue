<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

type DevelopmentEntry = {
    id: number;
    title: string;
    description: string | null;
    link: string | null;
    created_at: string;
};

type PaginatedEntries = {
    data: DevelopmentEntry[];
    links: { url: string | null; label: string; active: boolean }[];
    from: number | null;
    to: number | null;
    total: number;
};

const props = defineProps<{ entries: PaginatedEntries; search: string }>();
const search = ref(props.search);

function submitSearch(): void {
    router.get(
        '/admin/development',
        { search: search.value || undefined },
        { preserveState: true, replace: true },
    );
}

function removeEntry(entry: DevelopmentEntry): void {
    if (window.confirm(`Eliminare “${entry.title}”?`)) {
        router.delete(`/admin/development/${entry.id}`);
    }
}

function host(link: string): string {
    try {
        return new URL(link).host;
    } catch {
        return link;
    }
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Amministrazione', href: '/admin' },
            { title: 'Sviluppo', href: '/admin/development' },
        ],
    },
});
</script>

<template>
    <Head title="Sviluppo" />

    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-4 p-3 md:p-5">
        <header
            class="flex flex-wrap items-end justify-between gap-3 border-b border-border pb-3"
        >
            <div>
                <h1 class="font-serif text-2xl tracking-tight text-[#2c4133]">
                    Sviluppo
                </h1>
                <p class="mt-0.5 text-sm text-muted-foreground">
                    Riferimenti, decisioni e collegamenti utili al progetto.
                </p>
            </div>
            <Button
                as="a"
                href="/admin/development/create"
                label="Nuova voce"
                icon="pi pi-plus"
            />
        </header>

        <form class="flex gap-2" @submit.prevent="submitSearch">
            <InputText
                v-model="search"
                class="flex-1"
                placeholder="Cerca titolo, descrizione o link"
                aria-label="Cerca nello sviluppo"
            />
            <Button
                type="submit"
                label="Cerca"
                icon="pi pi-search"
                severity="secondary"
            />
        </form>

        <section
            class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
        >
            <div
                v-if="entries.data.length === 0"
                class="p-8 text-center text-sm text-muted-foreground"
            >
                Nessuna voce trovata.
                <Link
                    href="/admin/development/create"
                    class="font-semibold text-[#476246]"
                    >Aggiungi la prima</Link
                >.
            </div>
            <div v-else class="divide-y divide-border">
                <article
                    v-for="entry in entries.data"
                    :key="entry.id"
                    class="flex flex-col gap-2 p-4 sm:flex-row sm:items-start sm:justify-between"
                >
                    <div class="min-w-0">
                        <Link
                            :href="`/admin/development/${entry.id}/edit`"
                            class="font-semibold hover:text-[#476246]"
                            >{{ entry.title }}</Link
                        >
                        <p
                            v-if="entry.description"
                            class="mt-1 text-sm whitespace-pre-line text-muted-foreground"
                        >
                            {{ entry.description }}
                        </p>
                        <a
                            v-if="entry.link"
                            :href="entry.link"
                            class="mt-2 inline-flex max-w-full items-center gap-1 truncate text-xs font-semibold text-[#476246] hover:underline"
                            target="_blank"
                            rel="noopener noreferrer"
                            ><i class="pi pi-external-link text-[10px]" />{{
                                host(entry.link)
                            }}</a
                        >
                    </div>
                    <div class="flex shrink-0 gap-3 text-sm">
                        <Link
                            :href="`/admin/development/${entry.id}/edit`"
                            class="font-semibold text-[#476246]"
                            >Modifica</Link
                        ><button
                            type="button"
                            class="font-semibold text-destructive"
                            @click="removeEntry(entry)"
                        >
                            Elimina
                        </button>
                    </div>
                </article>
            </div>
        </section>

        <footer
            v-if="entries.data.length"
            class="flex flex-wrap items-center justify-between gap-2 text-sm text-muted-foreground"
        >
            <span
                >{{ entries.from }}–{{ entries.to }} di
                {{ entries.total }}</span
            >
            <div class="flex gap-1">
                <Link
                    v-for="link in entries.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    class="rounded-md px-2 py-1"
                    :class="[
                        link.active
                            ? 'bg-[#e7f0e6] font-bold text-[#294635]'
                            : 'hover:bg-muted',
                        !link.url ? 'pointer-events-none opacity-40' : '',
                    ]"
                    ><span v-html="link.label"
                /></Link>
            </div>
        </footer>
    </div>
</template>
