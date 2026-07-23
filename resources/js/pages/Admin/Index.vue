<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

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
            { icon: '♙', title: 'Utenti e permessi', text: 'Accessi, ruoli e autorizzazioni operative.', tone: 'bg-[#e5edf1] text-[#476774]' },
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

    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-8 p-4 md:p-8">
        <section class="overflow-hidden rounded-3xl bg-[#284a38] px-6 py-8 text-[#f8f4ea] shadow-xl shadow-[#284a38]/15 md:px-9 md:py-10">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#c7ddc2]">MiseEnPlace · Centro di controllo</p>
            <div class="mt-4 flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <div>
                    <h1 class="font-serif text-4xl tracking-tight md:text-5xl">Amministrazione</h1>
                    <p class="mt-3 max-w-xl text-[#d7e5d3]">Da qui prenderanno forma tutti gli strumenti di gestione della cucina. Scegli un’area per iniziare a configurare il progetto.</p>
                </div>
                <span class="w-fit rounded-full border border-[#c7ddc2]/30 bg-white/10 px-3 py-1.5 text-xs font-semibold text-[#e1eedc]">Struttura iniziale</span>
            </div>
        </section>

        <section v-for="area in areas" :key="area.group">
            <div class="mb-4 flex items-center gap-3">
                <h2 class="font-serif text-2xl text-[#2c4133]">{{ area.group }}</h2>
                <div class="h-px flex-1 bg-border" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article v-for="item in area.items" :key="item.title" class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    <div :class="['grid h-11 w-11 place-items-center rounded-xl text-lg', item.tone]">{{ item.icon }}</div>
                    <h3 class="mt-5 text-base font-bold text-foreground">{{ item.title }}</h3>
                    <p class="mt-2 min-h-10 text-sm leading-5 text-muted-foreground">{{ item.text }}</p>
                    <button type="button" class="mt-5 text-xs font-bold uppercase tracking-[0.12em] text-[#55714e] transition group-hover:text-[#284a38]">
                        Configura →
                    </button>
                </article>
            </div>
        </section>

        <section class="rounded-2xl border border-dashed border-[#97aa99] bg-[#f6f7ef] p-5 text-sm text-[#526352]">
            <strong class="font-semibold text-[#294635]">Prossimo passo:</strong> iniziare dalla gestione di utenti e ricette, quindi collegare ingredienti, fasi e allergeni.
        </section>
    </div>
</template>
