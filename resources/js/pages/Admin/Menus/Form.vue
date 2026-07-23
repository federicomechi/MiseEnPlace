<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';

type Item = {
    id: number;
    role: string;
    parent_id: number | null;
    title: string;
    href: string | null;
    icon: string | null;
    sort_order: number;
    is_active: boolean;
};
type Parent = { id: number; title: string; depth: number };
const props = defineProps<{
    item: Item | null;
    role: string;
    roles: Record<string, string>;
    parents: Parent[];
}>();
const form = useForm({
    role: props.item?.role ?? props.role,
    parent_id: props.item?.parent_id ?? null,
    title: props.item?.title ?? '',
    href: props.item?.href ?? '',
    icon: props.item?.icon ?? '',
    sort_order: props.item?.sort_order ?? 0,
    is_active: props.item?.is_active ?? true,
});
function submit(): void {
    if (props.item) {
        form.put(`/admin/menus/${props.item.id}`);
    } else {
        form.post('/admin/menus');
    }
}
function remove(): void {
    if (props.item && window.confirm(`Eliminare “${props.item.title}”?`)) {
        router.delete(`/admin/menus/${props.item.id}`);
    }
}
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Amministrazione', href: '/admin' },
            { title: 'Menu sidebar', href: '/admin/menus' },
            { title: 'Modifica', href: '/admin/menus' },
        ],
    },
});
</script>

<template>
    <Head :title="item ? `Modifica ${item.title}` : 'Nuova voce menu'" />
    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-4 p-3 md:p-5">
        <header class="border-b border-border pb-3">
            <h1 class="font-serif text-2xl tracking-tight text-[#2c4133]">
                {{ item ? 'Modifica voce menu' : 'Nuova voce menu' }}
            </h1>
            <p class="text-sm text-muted-foreground">
                Le voci senza collegamento funzionano come gruppi contenitore.
            </p>
        </header>
        <form
            class="grid gap-4 rounded-xl border border-border bg-card p-4"
            @submit.prevent="submit"
        >
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-xs font-semibold">Ruolo</label
                    ><Select
                        v-model="form.role"
                        :options="
                            Object.entries(roles).map(([value, label]) => ({
                                value,
                                label,
                            }))
                        "
                        option-label="label"
                        option-value="value"
                        class="mt-1 w-full"
                    />
                </div>
                <div>
                    <label class="text-xs font-semibold">Gruppo superiore</label
                    ><Select
                        v-model="form.parent_id"
                        :options="parents"
                        option-label="title"
                        option-value="id"
                        show-clear
                        placeholder="Voce principale"
                        class="mt-1 w-full"
                    />
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold">Titolo</label
                ><InputText v-model="form.title" class="mt-1 w-full" required />
            </div>
            <div class="grid gap-4 sm:grid-cols-[1fr_10rem]">
                <div>
                    <label class="text-xs font-semibold">Collegamento</label
                    ><InputText
                        v-model="form.href"
                        class="mt-1 w-full"
                        placeholder="/operativita/ingredients"
                    />
                </div>
                <div>
                    <label class="text-xs font-semibold">Icona PrimeIcons</label
                    ><InputText
                        v-model="form.icon"
                        class="mt-1 w-full"
                        placeholder="salad"
                    />
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-4">
                <div>
                    <label class="text-xs font-semibold">Ordine</label
                    ><InputNumber
                        v-model="form.sort_order"
                        :min="0"
                        class="mt-1 w-32"
                    />
                </div>
                <label class="mt-5 flex items-center gap-2 text-sm"
                    ><Checkbox v-model="form.is_active" binary /> Voce
                    attiva</label
                >
            </div>
            <div
                class="flex flex-wrap justify-between gap-2 border-t border-border pt-3"
            >
                <button
                    v-if="item"
                    type="button"
                    class="text-sm text-destructive"
                    @click="remove"
                >
                    Elimina</button
                ><span v-else />
                <div class="flex gap-2">
                    <Link
                        href="/admin/menus"
                        class="rounded-md border border-border px-3 py-2 text-sm"
                        >Annulla</Link
                    ><Button
                        type="submit"
                        :label="item ? 'Salva modifiche' : 'Crea voce'"
                        :loading="form.processing"
                    />
                </div>
            </div>
        </form>
    </div>
</template>
