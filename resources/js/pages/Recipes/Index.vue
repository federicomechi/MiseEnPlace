<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
type Recipe = { id: number; name: string; tag: string | null; season: string | null; yield_quantity: string | null; yield_unit: string | null; total_minutes: number | null; steps_count: number };
type Page = { data: Recipe[]; links: { url: string | null; label: string; active: boolean }[]; from: number | null; to: number | null; total: number };
const props = defineProps<{ recipes: Page; search: string }>();
const search = ref(props.search);
let searchTimer: ReturnType<typeof setTimeout> | undefined;
function submit(): void {
 router.get('/operativita/recipes', { search: search.value || undefined }, { preserveState: true, replace: true }); 
}
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(submit, 300);
});
function remove(recipe: Recipe): void {
 if (window.confirm('Eliminare “' + recipe.name + '”?')) {
router.delete('/operativita/recipes/' + recipe.id);
} 
}
defineOptions({ layout: { breadcrumbs: [{ title: 'Ricette', href: '/operativita/recipes' }] } });
</script>
<template>
    <Head title="Ricette" />
    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-4 p-3 md:p-5">
        <header class="flex flex-wrap items-end justify-between gap-3 border-b border-border pb-3"><div><h1 class="font-serif text-2xl tracking-tight text-[#2c4133]">Ricette</h1><p class="mt-0.5 text-sm text-muted-foreground">Anagrafica, rese, tempi e fasi di preparazione.</p></div><Button as="a" href="/operativita/recipes/create" label="Nuova ricetta" icon="pi pi-plus" /></header>
        <form class="flex gap-2" @submit.prevent="submit"><InputText v-model="search" class="flex-1" placeholder="Cerca nome, tag o stagione" aria-label="Cerca ricette" /><Button type="submit" label="Cerca" icon="pi pi-search" severity="secondary" /></form>
        <section class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"><div v-if="recipes.data.length === 0" class="p-8 text-center text-sm text-muted-foreground">Nessuna ricetta presente. <Link href="/operativita/recipes/create" class="font-semibold text-[#476246]">Crea la prima</Link>.</div><div v-else class="divide-y divide-border"><article v-for="recipe in recipes.data" :key="recipe.id" class="flex flex-wrap items-center justify-between gap-3 p-4"><div class="min-w-0"><Link :href="'/operativita/recipes/' + recipe.id + '/edit'" class="font-semibold hover:text-[#476246]">{{ recipe.name }}</Link><p class="text-sm text-muted-foreground">{{ recipe.tag || 'Senza tag' }} · {{ recipe.season || 'Tutto l’anno' }} · {{ recipe.steps_count }} fasi</p></div><div class="flex items-center gap-4 text-right"><div><p class="font-semibold">{{ recipe.total_minutes ? recipe.total_minutes + ' min' : '—' }}</p><p class="text-xs text-muted-foreground">{{ recipe.yield_quantity ? recipe.yield_quantity + ' ' + (recipe.yield_unit || '') : 'resa n/d' }}</p></div><Link :href="'/operativita/recipes/' + recipe.id + '/edit'" class="text-sm font-semibold text-[#476246]">Modifica</Link><button type="button" class="text-sm font-semibold text-destructive" @click="remove(recipe)">Elimina</button></div></article></div></section>
        <footer v-if="recipes.data.length" class="flex flex-wrap items-center justify-between gap-2 text-sm text-muted-foreground"><span>{{ recipes.from }}–{{ recipes.to }} di {{ recipes.total }}</span><div class="flex gap-1"><Link v-for="link in recipes.links" :key="link.label" :href="link.url ?? '#'" class="rounded-md px-2 py-1" :class="[link.active ? 'bg-[#e7f0e6] font-bold text-[#294635]' : 'hover:bg-muted', !link.url ? 'pointer-events-none opacity-40' : '']"><span v-html="link.label" /></Link></div></footer>
    </div>
</template>
