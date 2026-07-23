<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

type Price = {
    id: number;
    ingredient: string | null;
    ingredient_id: number;
    unit: string | null;
    supplier: string | null;
    package_name: string | null;
    package_quantity: string | null;
    package_unit: string | null;
    package_price: string;
    valid_from: string;
    is_current: boolean;
};
type Pagination = { data: Price[]; links: { url: string | null; label: string; active: boolean }[]; from: number | null; to: number | null; total: number };
const props = defineProps<{ prices: Pagination; search: string }>();
const search = ref(props.search);
let searchTimer: ReturnType<typeof setTimeout> | undefined;
function submitSearch(): void {
    router.get('/operativita/ingredients/listini', { search: search.value || undefined }, { preserveState: true, replace: true });
}
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(submitSearch, 300);
});
defineOptions({ layout: { breadcrumbs: [{ title: 'Ingredienti', href: '/operativita/ingredients' }, { title: 'Listini', href: '/operativita/ingredients/listini' }] } });
</script>

<template>
    <Head title="Listini prezzi" />
    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-4 p-3 md:p-5">
        <header class="flex flex-wrap items-end justify-between gap-3 border-b border-border pb-3">
            <div>
                <h1 class="font-serif text-2xl tracking-tight text-[#2c4133]">Listini prezzi</h1>
                <p class="mt-0.5 text-sm text-muted-foreground">Prezzi importati da FileMaker, collegati a ingredienti e fornitori.</p>
            </div>
            <div class="flex gap-2">
                <Button as="a" href="/operativita/ingredients" label="Ingredienti" severity="secondary" outlined />
                <Button as="a" href="/operativita/ingredients/fornitori" label="Fornitori" severity="secondary" outlined />
            </div>
        </header>
        <form class="flex gap-2" @submit.prevent="submitSearch">
            <InputText v-model="search" class="flex-1" placeholder="Cerca ingrediente o fornitore" aria-label="Cerca listino" />
            <Button type="submit" label="Cerca" icon="pi pi-search" severity="secondary" />
        </form>
        <section class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
            <div v-if="prices.data.length === 0" class="p-8 text-center text-sm text-muted-foreground">Nessun prezzo trovato.</div>
            <div v-else class="divide-y divide-border">
                <article v-for="price in prices.data" :key="price.id" class="flex flex-wrap items-center justify-between gap-3 p-4">
                    <div class="min-w-0">
                        <Link :href="`/operativita/ingredients/${price.ingredient_id}/edit`" class="font-semibold hover:text-[#476246]">{{ price.ingredient || 'Ingrediente' }}</Link>
                        <p class="text-sm text-muted-foreground">{{ price.supplier || 'Fornitore' }} · {{ price.package_name || (price.package_quantity ? `${price.package_quantity} ${price.package_unit || ''}` : 'Confezione') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold">€ {{ Number(price.package_price).toFixed(2) }}</p>
                        <p class="text-xs text-muted-foreground">dal {{ price.valid_from }} <span v-if="price.is_current" class="font-semibold text-[#476246]">· attuale</span></p>
                    </div>
                </article>
            </div>
        </section>
        <footer v-if="prices.data.length" class="flex flex-wrap items-center justify-between gap-2 text-sm text-muted-foreground">
            <span>{{ prices.from }}–{{ prices.to }} di {{ prices.total }}</span>
            <div class="flex gap-1"><Link v-for="link in prices.links" :key="link.label" :href="link.url ?? '#'" class="rounded-md px-2 py-1" :class="[link.active ? 'bg-[#e7f0e6] font-bold text-[#294635]' : 'hover:bg-muted', !link.url ? 'pointer-events-none opacity-40' : '']"><span v-html="link.label" /></Link></div>
        </footer>
    </div>
</template>
