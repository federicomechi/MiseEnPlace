<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
type Client = { id: number; filemaker_id: string | null; name: string; location_id: string | null; location_name: string | null; email: string | null; expires_at: string | null; users_count: number; users: { id: number; name: string; email: string }[] };
const props = defineProps<{ clients: Client[]; search: string }>();
const search = ref(props.search);
let searchTimer: ReturnType<typeof setTimeout> | undefined;
function filter(): void {
 router.get('/admin/clients', { search: search.value || undefined }, { preserveState: true, replace: true }); 
}
watch(search, () => {
 clearTimeout(searchTimer); searchTimer = setTimeout(filter, 300); 
});
defineOptions({ layout: { breadcrumbs: [{ title: 'Amministrazione', href: '/admin' }, { title: 'Azienda', href: '/admin/clients' }] } });
</script>
<template>
    <Head title="Azienda" />
    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-4 p-3 md:p-5">
        <header class="flex flex-wrap items-end justify-between gap-3 border-b border-border pb-3"><div><h1 class="font-serif text-2xl tracking-tight text-[#2c4133]">Azienda</h1><p class="mt-0.5 text-sm text-muted-foreground">Anagrafica DB_cliente e utenti associati.</p></div><div class="flex flex-wrap gap-2"><InputText v-model="search" placeholder="Cerca azienda, sede o email" aria-label="Cerca aziende" /><Button as="a" href="/admin" label="Amministrazione" severity="secondary" outlined /></div></header>
        <section class="grid gap-3 md:grid-cols-2">
            <article v-for="client in clients" :key="client.id" class="rounded-xl border border-border bg-card p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3"><div><h2 class="text-lg font-semibold">{{ client.name }}</h2><p class="text-sm text-muted-foreground">{{ client.location_name || 'Sede non indicata' }}<span v-if="client.location_id"> · ID {{ client.location_id }}</span></p></div><span class="rounded-full bg-[#e7f0e6] px-2 py-1 text-xs font-semibold text-[#294635]">Client {{ client.id }}</span></div>
                <dl class="mt-4 grid gap-2 text-sm sm:grid-cols-2"><div><dt class="text-xs text-muted-foreground">FileMaker ID</dt><dd class="truncate">{{ client.filemaker_id || '—' }}</dd></div><div><dt class="text-xs text-muted-foreground">Email</dt><dd>{{ client.email || '—' }}</dd></div><div><dt class="text-xs text-muted-foreground">Utenti</dt><dd>{{ client.users_count }}</dd></div><div><dt class="text-xs text-muted-foreground">Scadenza</dt><dd>{{ client.expires_at || '—' }}</dd></div></dl>
                <div class="mt-4 border-t border-border pt-3"><p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Utenti collegati</p><ul class="mt-2 space-y-1 text-sm"><li v-for="user in client.users" :key="user.id">{{ user.name }} <span class="text-muted-foreground">· {{ user.email }}</span></li></ul><Link href="/admin/users" class="mt-3 inline-block text-sm font-semibold text-[#476246]">Gestisci utenti →</Link></div>
            </article>
        </section>
    </div>
</template>
