<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button'; import InputText from 'primevue/inputtext'; import Textarea from 'primevue/textarea';
type Item = { id: number; name: string; code: string | null; category: string | null; description: string | null; is_active: boolean };
const props = defineProps<{ section: string; title: string; items: Item[]; search: string }>();
const search = ref(props.search); const editing = ref<Item | null>(null);
const form = useForm({ name: '', code: '', category: '', description: '', is_active: true });
function edit(item: Item): void {
 editing.value = item; form.name = item.name; form.code = item.code ?? ''; form.category = item.category ?? ''; form.description = item.description ?? ''; form.is_active = item.is_active; 
}
function reset(): void {
 editing.value = null; form.reset(); form.is_active = true; 
}
function submit(): void {
 if (editing.value) {
  form.put('/operativita/' + props.section + '/' + editing.value.id, { onSuccess: reset });
 } else {
  form.post('/operativita/' + props.section, { onSuccess: reset });
 }
}
function remove(item: Item): void {
 if (window.confirm('Eliminare “' + item.name + '”?')) {
router.delete('/operativita/' + props.section + '/' + item.id);
} 
}
function filter(): void {
 router.get('/operativita/' + props.section, { search: search.value || undefined }, { preserveState: true, replace: true }); 
}
defineOptions({ layout: { breadcrumbs: [{ title: 'Operatività', href: '/dashboard' }] } });
</script>
<template><Head :title="title" /><div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-4 p-3 md:p-5"><header class="flex flex-wrap items-end justify-between gap-3 border-b border-border pb-3"><div><h1 class="font-serif text-2xl text-[#2c4133]">{{ title }}</h1><p class="text-sm text-muted-foreground">Catalogo operativo dell’azienda attiva.</p></div></header><div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_22rem]"><section class="rounded-xl border border-border bg-card shadow-sm"><form class="border-b border-border p-3" @submit.prevent="filter"><div class="flex gap-2"><InputText v-model="search" class="flex-1" placeholder="Cerca nome o categoria" /><Button type="submit" icon="pi pi-search" label="Cerca" severity="secondary" /></div></form><div v-if="items.length === 0" class="p-8 text-center text-sm text-muted-foreground">Nessun elemento presente.</div><div v-else class="divide-y divide-border"><article v-for="item in items" :key="item.id" class="flex items-center justify-between gap-3 p-4"><div><h2 class="font-semibold">{{ item.name }}</h2><p class="text-sm text-muted-foreground">{{ item.category || 'Senza categoria' }}<span v-if="item.code"> · {{ item.code }}</span></p></div><div class="flex gap-3 text-sm"><button type="button" class="font-semibold text-[#476246]" @click="edit(item)">Modifica</button><button type="button" class="font-semibold text-destructive" @click="remove(item)">Elimina</button></div></article></div></section><aside class="h-fit rounded-xl border border-border bg-card p-4 shadow-sm"><h2 class="font-semibold">{{ editing ? 'Modifica elemento' : 'Nuovo elemento' }}</h2><form class="mt-4 grid gap-3" @submit.prevent="submit"><InputText v-model="form.name" placeholder="Nome" /><InputText v-model="form.code" placeholder="Codice" /><InputText v-model="form.category" placeholder="Categoria" /><Textarea v-model="form.description" rows="3" placeholder="Descrizione" /><div class="flex gap-2"><Button type="submit" :label="editing ? 'Salva' : 'Crea'" :loading="form.processing" /><Button v-if="editing" type="button" label="Annulla" severity="secondary" outlined @click="reset" /></div></form></aside></div></div></template>
