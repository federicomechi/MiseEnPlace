<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';

type Ingredient = {
    id: number;
    code: string | null;
    name: string;
    category: string | null;
    unit: string | null;
    unit_cost: string | null;
    cost_date: string | null;
    available_for_bar: boolean;
    supplier: string | null;
};

type IngredientsPage = {
    data: Ingredient[];
    links: { url: string | null; label: string; active: boolean }[];
    from: number | null;
    to: number | null;
    total: number;
};

const props = defineProps<{
    ingredients: IngredientsPage;
    filters: { search: string; bar: boolean };
}>();

const search = ref(props.filters.search);
const barOnly = ref(props.filters.bar);
const hasIngredients = computed(() => props.ingredients.data.length > 0);

function applyFilters(): void {
    router.get('/operativita/ingredients', { search: search.value || undefined, bar: barOnly.value || undefined }, { preserveState: true, replace: true });
}

function removeIngredient(ingredient: Ingredient): void {
    if (window.confirm(`Eliminare l’ingrediente “${ingredient.name}”?`)) {
        router.delete(`/operativita/ingredients/${ingredient.id}`);
    }
}

function formatCost(cost: string | null, unit: string | null): string {
    return cost ? `${Number(cost).toLocaleString('it-IT', { style: 'currency', currency: 'EUR' })}${unit ? ` / ${unit}` : ''}` : '—';
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Ingredienti', href: '/operativita/ingredients' },
        ],
    },
});
</script>

<template>
    <Head title="Ingredienti" />

    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-4 p-3 md:p-5">
        <header class="flex flex-wrap items-end justify-between gap-3 border-b border-border pb-3">
            <div>
                <h1 class="font-serif text-2xl tracking-tight text-[#2c4133]">Ingredienti</h1>
                <p class="mt-0.5 text-sm text-muted-foreground">Catalogo, disponibilità bar e costi di acquisto.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button as="a" href="/operativita/ingredients/fornitori" label="Fornitori" icon="pi pi-building" severity="secondary" outlined />
                <Button as="a" href="/operativita/ingredients/create" label="Nuovo ingrediente" icon="pi pi-plus" />
            </div>
        </header>

        <form class="flex flex-wrap items-center gap-2" @submit.prevent="applyFilters">
            <InputText v-model="search" class="min-w-56 flex-1" placeholder="Cerca nome, codice o categoria" aria-label="Cerca ingredienti" />
            <label class="flex h-10 items-center gap-2 rounded-lg border border-border px-3 text-sm">
                <Checkbox v-model="barOnly" binary /> Solo bar
            </label>
            <Button type="submit" label="Cerca" icon="pi pi-search" severity="secondary" />
        </form>

        <section class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
            <div v-if="!hasIngredients" class="p-8 text-center text-sm text-muted-foreground">
                Nessun ingrediente trovato. <Link href="/operativita/ingredients/create" class="font-semibold text-[#476246]">Crea il primo ingrediente</Link>.
            </div>
            <template v-else>
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-xs uppercase tracking-wide text-muted-foreground">
                            <tr><th class="px-4 py-3">Ingrediente</th><th class="px-3 py-3">Categoria</th><th class="px-3 py-3">Fornitore corrente</th><th class="px-3 py-3 text-right">Costo unitario</th><th class="px-4 py-3" /></tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="ingredient in ingredients.data" :key="ingredient.id" class="hover:bg-muted/30">
                                <td class="px-4 py-3"><Link :href="`/operativita/ingredients/${ingredient.id}/edit`" class="font-semibold hover:text-[#476246]">{{ ingredient.name }}</Link><p v-if="ingredient.code" class="mt-0.5 text-xs text-muted-foreground">{{ ingredient.code }}</p></td>
                                <td class="px-3 py-3 text-muted-foreground">{{ ingredient.category ?? '—' }}</td>
                                <td class="px-3 py-3 text-muted-foreground"><span>{{ ingredient.supplier ?? 'Nessun listino' }}</span><span v-if="ingredient.available_for_bar" class="ml-2 rounded-full bg-[#e9e6f7] px-2 py-0.5 text-[10px] font-bold text-[#67548e]">BAR</span></td>
                                <td class="px-3 py-3 text-right font-medium tabular-nums">{{ formatCost(ingredient.unit_cost, ingredient.unit) }}</td>
                                <td class="px-4 py-3 text-right"><Link :href="`/operativita/ingredients/${ingredient.id}/edit`" class="text-xs font-bold text-[#476246]">Modifica</Link><button type="button" class="ml-3 text-xs font-bold text-destructive" @click="removeIngredient(ingredient)">Elimina</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="divide-y divide-border md:hidden">
                    <article v-for="ingredient in ingredients.data" :key="ingredient.id" class="p-3">
                        <div class="flex items-start justify-between gap-3"><div><Link :href="`/operativita/ingredients/${ingredient.id}/edit`" class="font-semibold">{{ ingredient.name }}</Link><p class="text-xs text-muted-foreground">{{ ingredient.code ?? ingredient.category ?? 'Senza categoria' }}</p></div><span class="text-xs font-semibold tabular-nums">{{ formatCost(ingredient.unit_cost, ingredient.unit) }}</span></div>
                        <p class="mt-1 text-xs text-muted-foreground">{{ ingredient.supplier ?? 'Nessun fornitore/listino corrente' }}</p>
                    </article>
                </div>
            </template>
        </section>

        <footer v-if="hasIngredients" class="flex flex-wrap items-center justify-between gap-2 text-sm text-muted-foreground">
            <span>{{ ingredients.from }}–{{ ingredients.to }} di {{ ingredients.total }}</span>
            <div class="flex gap-1"><Link v-for="link in ingredients.links" :key="link.label" :href="link.url ?? '#'" class="rounded-md px-2 py-1" :class="[link.active ? 'bg-[#e7f0e6] font-bold text-[#294635]' : 'hover:bg-muted', !link.url ? 'pointer-events-none opacity-40' : '']"><span v-html="link.label" /></Link></div>
        </footer>
    </div>
</template>
