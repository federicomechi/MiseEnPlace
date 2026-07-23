<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

type Supplier = { id: number; name: string };
type Price = { id?: number; supplier_id: number | null; supplier_code: string; package_name: string; package_quantity: number | string | null; package_unit: string | null; package_price: number | string | null; valid_from: string; is_current: boolean; notes: string };
type Ingredient = { id: number; code: string | null; name: string; category: string | null; unit: string | null; notes: string | null; available_for_bar: boolean; prices: Price[] };

const props = defineProps<{ ingredient: Ingredient | null; suppliers: Supplier[]; units: string[] }>();
const today = new Date().toISOString().slice(0, 10);

const form = useForm({
    code: props.ingredient?.code ?? '',
    name: props.ingredient?.name ?? '',
    category: props.ingredient?.category ?? '',
    unit: props.ingredient?.unit ?? null as string | null,
    notes: props.ingredient?.notes ?? '',
    available_for_bar: props.ingredient?.available_for_bar ?? false,
    prices: props.ingredient?.prices ?? [] as Price[],
});

function addPrice(): void {
    form.prices.push({ supplier_id: props.suppliers[0]?.id ?? null, supplier_code: '', package_name: '', package_quantity: null, package_unit: form.unit, package_price: null, valid_from: today, is_current: true, notes: '' });
}

function submit(): void {
    if (props.ingredient) {
        form.put(`/operativita/ingredients/${props.ingredient.id}`, { preserveScroll: true });
    } else {
        form.post('/operativita/ingredients', { preserveScroll: true });
    }
}

function remove(): void {
    if (props.ingredient && window.confirm(`Eliminare l’ingrediente “${props.ingredient.name}”?`)) router.delete(`/operativita/ingredients/${props.ingredient.id}`);
}

function formatUnitPrice(price: Price): string {
    const quantity = Number(price.package_quantity);
    const cost = Number(price.package_price);
    return quantity > 0 && cost >= 0 ? `${(cost / quantity).toLocaleString('it-IT', { style: 'currency', currency: 'EUR' })}${price.package_unit ? ` / ${price.package_unit}` : ''}` : '—';
}

defineOptions({ layout: { breadcrumbs: [{ title: 'Ingredienti', href: '/operativita/ingredients' }] } });
</script>

<template>
    <Head :title="ingredient ? `Modifica ${ingredient.name}` : 'Nuovo ingrediente'" />
    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-4 p-3 md:p-5">
        <header class="flex flex-wrap items-end justify-between gap-3 border-b border-border pb-3"><div><p class="text-xs font-bold uppercase tracking-wide text-[#6b7e6c]">Ingredienti</p><h1 class="font-serif text-2xl tracking-tight text-[#2c4133]">{{ ingredient ? ingredient.name : 'Nuovo ingrediente' }}</h1></div><div class="flex gap-2"><Button v-if="ingredient" label="Elimina" severity="danger" text @click="remove" /><Button as="a" href="/operativita/ingredients" label="Elenco" severity="secondary" outlined /></div></header>

        <form class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(20rem,.8fr)]" @submit.prevent="submit">
            <section class="rounded-xl border border-border bg-card p-4 shadow-sm"><h2 class="font-semibold">Dati ingrediente</h2><div class="mt-4 grid gap-4 sm:grid-cols-2"><div><Label for="name">Nome</Label><InputText id="name" v-model="form.name" class="mt-2 w-full" required autofocus /><InputError :message="form.errors.name" /></div><div><Label for="code">Codice interno</Label><InputText id="code" v-model="form.code" class="mt-2 w-full" /><InputError :message="form.errors.code" /></div><div><Label for="category">Categoria</Label><InputText id="category" v-model="form.category" class="mt-2 w-full" placeholder="es. Latticini" /><InputError :message="form.errors.category" /></div><div><Label for="unit">Unità base</Label><Select id="unit" v-model="form.unit" class="mt-2 w-full" :options="units" placeholder="Seleziona" show-clear /><InputError :message="form.errors.unit" /></div></div><div class="mt-4"><Label for="notes">Note</Label><Textarea id="notes" v-model="form.notes" class="mt-2 w-full" rows="4" auto-resize /></div><label class="mt-4 flex items-center gap-2 text-sm font-medium"><Checkbox v-model="form.available_for_bar" binary /> Disponibile anche per il bar</label></section>

            <aside class="h-fit rounded-xl border border-border bg-card p-4 shadow-sm"><h2 class="font-semibold">Azioni</h2><p class="mt-1 text-sm text-muted-foreground">Il costo unitario viene calcolato dall’offerta corrente.</p><Button type="submit" class="mt-4 w-full" :label="ingredient ? 'Salva modifiche' : 'Crea ingrediente'" :loading="form.processing" /><Link href="/operativita/ingredients/fornitori" class="mt-3 block text-center text-sm font-semibold text-[#476246]">Gestisci fornitori</Link></aside>

            <section class="xl:col-span-2 rounded-xl border border-border bg-card p-4 shadow-sm"><div class="flex flex-wrap items-center justify-between gap-2"><div><h2 class="font-semibold">Listino fornitori</h2><p class="text-sm text-muted-foreground">Conserva prezzi e formati di acquisto nel tempo.</p></div><Button type="button" label="Aggiungi prezzo" icon="pi pi-plus" severity="secondary" outlined :disabled="suppliers.length === 0" @click="addPrice" /></div><p v-if="suppliers.length === 0" class="mt-4 rounded-lg bg-[#fbecd8] p-3 text-sm text-[#805021]">Crea prima almeno un <Link href="/operativita/ingredients/fornitori" class="font-bold underline">fornitore</Link>.</p><div v-else-if="form.prices.length === 0" class="mt-4 text-sm text-muted-foreground">Nessuna offerta registrata.</div><div v-else class="mt-4 grid gap-3"> <article v-for="(price, index) in form.prices" :key="price.id ?? `new-${index}`" class="rounded-lg border border-border bg-muted/20 p-3"><div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4"><div><Label :for="`supplier-${index}`">Fornitore</Label><Select :id="`supplier-${index}`" v-model="price.supplier_id" class="mt-1 w-full" :options="suppliers" option-label="name" option-value="id" required /><InputError :message="form.errors[`prices.${index}.supplier_id`]" /></div><div><Label :for="`supplier-code-${index}`">Codice fornitore</Label><InputText :id="`supplier-code-${index}`" v-model="price.supplier_code" class="mt-1 w-full" /></div><div><Label :for="`package-name-${index}`">Formato</Label><InputText :id="`package-name-${index}`" v-model="price.package_name" class="mt-1 w-full" placeholder="es. Cartone" /></div><div><Label :for="`valid-from-${index}`">Valido dal</Label><InputText :id="`valid-from-${index}`" v-model="price.valid_from" class="mt-1 w-full" type="date" required /></div><div><Label :for="`quantity-${index}`">Quantità collo</Label><InputText :id="`quantity-${index}`" v-model="price.package_quantity" class="mt-1 w-full" type="number" min="0.001" step="0.001" /></div><div><Label :for="`price-unit-${index}`">Unità collo</Label><Select :id="`price-unit-${index}`" v-model="price.package_unit" class="mt-1 w-full" :options="units" show-clear /></div><div><Label :for="`package-price-${index}`">Prezzo collo (€)</Label><InputText :id="`package-price-${index}`" v-model="price.package_price" class="mt-1 w-full" type="number" min="0" step="0.0001" required /></div><div class="flex items-end justify-between gap-2"><label class="flex items-center gap-2 pb-2 text-sm"><Checkbox v-model="price.is_current" binary /> Corrente</label><button type="button" class="pb-2 text-sm font-semibold text-destructive" @click="form.prices.splice(index, 1)">Rimuovi</button></div></div><p class="mt-2 text-xs text-muted-foreground">Costo unitario: <strong class="text-foreground">{{ formatUnitPrice(price) }}</strong></p></article></div></section>
        </form>
    </div>
</template>
