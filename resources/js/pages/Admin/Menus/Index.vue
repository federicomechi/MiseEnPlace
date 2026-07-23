<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import { ref, watch } from 'vue';

type Item = {
    id: number;
    title: string;
    href: string | null;
    icon: string | null;
    parent?: { title: string } | null;
    sort_order: number;
    is_active: boolean;
};
const props = defineProps<{
    items: { data: Item[]; links: unknown[] };
    role: string;
    roles: Record<string, string>;
    search: string;
}>();
const role = ref(props.role);
const search = ref(props.search);
let searchTimer: ReturnType<typeof setTimeout> | undefined;

function changeRole(): void {
    router.get(
        '/admin/menus',
        { role: role.value, search: search.value || undefined },
        { preserveState: true, replace: true },
    );
}
function filter(): void {
    router.get('/admin/menus', { role: role.value, search: search.value || undefined }, { preserveState: true, replace: true });
}
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(filter, 300);
});
function remove(item: Item): void {
    if (window.confirm(`Eliminare “${item.title}”?`)) {
        router.delete(`/admin/menus/${item.id}`);
    }
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Amministrazione', href: '/admin' },
            { title: 'Menu sidebar', href: '/admin/menus' },
        ],
    },
});
</script>

<template>
    <Head title="Menu sidebar" />
    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-4 p-3 md:p-5">
        <header
            class="flex flex-wrap items-end justify-between gap-3 border-b border-border pb-3"
        >
            <div>
                <h1 class="font-serif text-2xl tracking-tight text-[#2c4133]">
                    Menu sidebar
                </h1>
                <p class="text-sm text-muted-foreground">
                    Configura voci e sottogruppi per ogni ruolo.
                </p>
            </div>
            <Button
                as="a"
                href="/admin/menus/create"
                label="Nuova voce"
                icon="pi pi-plus"
                size="small"
            />
        </header>
        <div
            class="flex flex-wrap items-end gap-2 rounded-lg border border-border bg-card p-3"
        >
            <div class="min-w-56">
                <label class="text-xs font-semibold text-muted-foreground"
                    >Ruolo</label
                ><Select
                    v-model="role"
                    :options="
                        Object.entries(roles).map(([value, label]) => ({
                            value,
                            label,
                        }))
                    "
                    option-label="label"
                    option-value="value"
                    class="mt-1 w-full"
                    @change="changeRole"
                />
            </div>
            <InputText v-model="search" class="min-w-64 flex-1" placeholder="Cerca voce o collegamento" aria-label="Cerca voci menu" />
            <Link href="/admin" class="text-sm text-muted-foreground underline"
                >Torna all’amministrazione</Link
            >
        </div>
        <div class="overflow-hidden rounded-xl border border-border bg-card">
            <div
                v-if="items.data.length === 0"
                class="p-8 text-center text-sm text-muted-foreground"
            >
                Nessuna voce configurata per questo ruolo. La sidebar utilizza
                il menu predefinito.
            </div>
            <div
                v-for="item in items.data"
                :key="item.id"
                class="flex flex-wrap items-center gap-3 border-b border-border p-3 last:border-b-0"
            >
                <span
                    class="grid h-8 w-8 place-items-center rounded-md bg-[#e7f0e6] text-[#476246]"
                    ><i
                        :class="
                            item.icon ? `pi pi-${item.icon}` : 'pi pi-bars'
                        "
                /></span>
                <div class="min-w-0 flex-1">
                    <div class="font-semibold">{{ item.title }}</div>
                    <div class="text-xs text-muted-foreground">
                        {{
                            item.parent
                                ? `Sottogruppo di ${item.parent.title}`
                                : 'Voce principale'
                        }}
                        · {{ item.href || 'Gruppo senza collegamento' }}
                    </div>
                </div>
                <span
                    :class="
                        item.is_active
                            ? 'text-emerald-700'
                            : 'text-muted-foreground'
                    "
                    class="text-xs"
                    >{{ item.is_active ? 'Attivo' : 'Nascosto' }}</span
                >
                <Link
                    :href="`/admin/menus/${item.id}/edit`"
                    class="text-sm font-semibold text-[#55714e]"
                    >Modifica</Link
                >
                <button
                    type="button"
                    class="text-sm text-destructive"
                    @click="remove(item)"
                >
                    Elimina
                </button>
            </div>
        </div>
    </div>
</template>
