<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

const areas = [
    {
        group: 'Cataloghi di cucina',
        items: [
            { icon: '◒', title: 'Ricette', text: 'Anagrafica, rese, tempi, fasi e costi.', tone: 'bg-[#e7f0e6] text-[#476246]' },
            { icon: '◈', title: 'Ingredienti', text: 'Unità di misura, costi, fornitori e allergeni.', tone: 'bg-[#fbecd8] text-[#9a5c2a]' },
            { icon: '⌁', title: 'Allergeni', text: 'Catalogo e regole di evidenza per gli ingredienti.', tone: 'bg-[#f7e8e9] text-[#a2545d]' },
            { icon: '⌘', title: 'Attrezzature e supporti', text: 'Dotazioni necessarie per le preparazioni.', tone: 'bg-[#e9edf9] text-[#5264a1]' },
        ],
    },
    {
        group: 'Pianificazione e servizio',
        items: [
            { icon: '☀', title: 'Menu', text: 'Componi menu, portate e proposte stagionali.', tone: 'bg-[#f8efcf] text-[#9a7922]' },
            { icon: '↗', title: 'Produzione', text: 'Organizza preparazioni, quantità e attività giornaliere.', tone: 'bg-[#e1f0ee] text-[#3d7770]' },
            { icon: '◌', title: 'Bar e bevande', text: 'Ricette beverage, ingredienti e composizione.', tone: 'bg-[#e9e6f7] text-[#67548e]' },
            { icon: '▤', title: 'Listini e fornitori', text: 'Prezzi, fornitori e aggiornamenti dei costi.', tone: 'bg-[#f5e5d6] text-[#95603a]' },
        ],
    },
    {
        group: 'Sistema',
        items: [
            { icon: '♙', title: 'Utenti e permessi', text: 'Accessi, ruoli e autorizzazioni operative.', tone: 'bg-[#e5edf1] text-[#476774]', href: '/admin/users' },
            { icon: '⌘', title: 'Sviluppo', text: 'Appunti, riferimenti e collegamenti per il progetto.', tone: 'bg-[#e9edf9] text-[#5264a1]', href: '/admin/development' },
            { icon: '⇩', title: 'Importazione dati', text: 'Passaggio controllato dei dati da FileMaker.', tone: 'bg-[#edf0dc] text-[#68773d]' },
            { icon: '▣', title: 'Impostazioni', text: 'Preferenze dell’applicazione e valori condivisi.', tone: 'bg-[#eeeeea] text-[#60645e]' },
        ],
    },
];

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Amministrazione', href: '/admin' },
        ],
    },
});
</script>

<template>
    <Head title="Amministrazione" />

    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-5 p-3 md:p-5">
        <h1 class="sr-only">Amministrazione</h1>
        <section v-for="area in areas" :key="area.group">
            <div class="mb-2 flex items-center gap-2">
                <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#526352]">{{ area.group }}</h2>
                <div class="h-px flex-1 bg-border" />
            </div>
            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                <article v-for="item in area.items" :key="item.title" class="group flex min-h-20 items-center gap-3 rounded-xl border border-border bg-card p-3 shadow-sm transition hover:border-[#97aa99] hover:shadow-md">
                    <div :class="['grid h-9 w-9 shrink-0 place-items-center rounded-lg text-base', item.tone]">{{ item.icon }}</div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-bold text-foreground">{{ item.title }}</h3>
                        <p class="mt-0.5 text-xs leading-4 text-muted-foreground">{{ item.text }}</p>
                    </div>
                    <Link v-if="item.href" :href="item.href" class="shrink-0 rounded-md px-2 py-1 text-xs font-bold text-[#55714e] transition hover:bg-[#e7f0e6]" :aria-label="`Gestisci ${item.title}`">Gestisci</Link>
                    <span v-else class="shrink-0 text-[10px] font-bold uppercase tracking-[0.08em] text-[#91a094]">In arrivo</span>
                </article>
            </div>
        </section>
    </div>
</template>
