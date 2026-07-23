<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

type Supplier = {
    id: number;
    name: string;
    contact_name: string | null;
    email: string | null;
    phone: string | null;
    notes: string | null;
    is_active: boolean;
    prices_count: number;
};
const props = defineProps<{ suppliers: Supplier[]; search: string }>();
const editing = ref<Supplier | null>(null);
const search = ref(props.search);
const form = useForm({
    name: '',
    contact_name: '',
    email: '',
    phone: '',
    notes: '',
    is_active: true,
});

function edit(supplier: Supplier): void {
    editing.value = supplier;
    form.name = supplier.name;
    form.contact_name = supplier.contact_name ?? '';
    form.email = supplier.email ?? '';
    form.phone = supplier.phone ?? '';
    form.notes = supplier.notes ?? '';
    form.is_active = supplier.is_active;
    form.clearErrors();
}
function reset(): void {
    editing.value = null;
    form.reset();
    form.is_active = true;
}
function submit(): void {
    if (editing.value) {
        form.put(`/operativita/ingredients/fornitori/${editing.value.id}`, {
            preserveScroll: true,
            onSuccess: reset,
        });
    } else {
        form.post('/operativita/ingredients/fornitori', {
            preserveScroll: true,
            onSuccess: reset,
        });
    }
}
function remove(supplier: Supplier): void {
    if (window.confirm(`Eliminare il fornitore “${supplier.name}”?`)) {
        router.delete(`/operativita/ingredients/fornitori/${supplier.id}`, {
            preserveScroll: true,
        });
    }
}
function filter(): void {
    router.get(
        '/operativita/ingredients/fornitori',
        { search: search.value || undefined },
        { preserveState: true, replace: true },
    );
}
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Ingredienti', href: '/operativita/ingredients' },
            { title: 'Fornitori', href: '/operativita/ingredients/fornitori' },
        ],
    },
});
</script>

<template>
    <Head title="Fornitori" />
    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-4 p-3 md:p-5">
        <header
            class="flex flex-wrap items-end justify-between gap-3 border-b border-border pb-3"
        >
            <div>
                <h1 class="font-serif text-2xl tracking-tight text-[#2c4133]">
                    Fornitori
                </h1>
                <p class="mt-0.5 text-sm text-muted-foreground">
                    Anagrafiche collegate ai listini degli ingredienti.
                </p>
            </div>
            <Button
                as="a"
                href="/operativita/ingredients"
                label="Ingredienti"
                severity="secondary"
                outlined
            />
        </header>
        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <section class="rounded-xl border border-border bg-card shadow-sm">
                <form
                    class="border-b border-border p-3"
                    @submit.prevent="filter"
                >
                    <InputText
                        v-model="search"
                        class="w-full"
                        placeholder="Cerca fornitore"
                    />
                </form>
                <div
                    v-if="suppliers.length === 0"
                    class="p-6 text-center text-sm text-muted-foreground"
                >
                    Nessun fornitore trovato.
                </div>
                <div v-else class="divide-y divide-border">
                    <article
                        v-for="supplier in suppliers"
                        :key="supplier.id"
                        class="flex flex-wrap items-center justify-between gap-3 p-4"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="font-semibold">
                                    {{ supplier.name }}
                                </h2>
                                <span
                                    v-if="!supplier.is_active"
                                    class="rounded-full bg-muted px-2 py-0.5 text-[10px] font-bold text-muted-foreground"
                                    >INATTIVO</span
                                >
                            </div>
                            <p class="text-sm text-muted-foreground">
                                {{
                                    supplier.contact_name ||
                                    supplier.email ||
                                    supplier.phone ||
                                    'Nessun contatto'
                                }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ supplier.prices_count }} voci di listino
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button
                                class="text-sm font-semibold text-[#476246]"
                                type="button"
                                @click="edit(supplier)"
                            >
                                Modifica</button
                            ><button
                                class="text-sm font-semibold text-destructive"
                                type="button"
                                @click="remove(supplier)"
                            >
                                Elimina
                            </button>
                        </div>
                    </article>
                </div>
            </section>
            <aside
                class="h-fit rounded-xl border border-border bg-card p-4 shadow-sm"
            >
                <h2 class="font-semibold">
                    {{ editing ? 'Modifica fornitore' : 'Nuovo fornitore' }}
                </h2>
                <form class="mt-4 grid gap-3" @submit.prevent="submit">
                    <div>
                        <Label for="supplier-name">Ragione sociale</Label
                        ><InputText
                            id="supplier-name"
                            v-model="form.name"
                            class="mt-1 w-full"
                            required
                        /><InputError :message="form.errors.name" />
                    </div>
                    <div>
                        <Label for="contact-name">Referente</Label
                        ><InputText
                            id="contact-name"
                            v-model="form.contact_name"
                            class="mt-1 w-full"
                        />
                    </div>
                    <div>
                        <Label for="supplier-email">Email</Label
                        ><InputText
                            id="supplier-email"
                            v-model="form.email"
                            class="mt-1 w-full"
                            type="email"
                        />
                    </div>
                    <div>
                        <Label for="supplier-phone">Telefono</Label
                        ><InputText
                            id="supplier-phone"
                            v-model="form.phone"
                            class="mt-1 w-full"
                        />
                    </div>
                    <div>
                        <Label for="supplier-notes">Note</Label
                        ><Textarea
                            id="supplier-notes"
                            v-model="form.notes"
                            class="mt-1 w-full"
                            rows="3"
                        />
                    </div>
                    <label class="flex items-center gap-2 text-sm"
                        ><Checkbox v-model="form.is_active" binary /> Fornitore
                        attivo</label
                    >
                    <div class="flex gap-2">
                        <Button
                            type="submit"
                            class="flex-1"
                            :label="editing ? 'Salva' : 'Crea'"
                            :loading="form.processing"
                        /><Button
                            v-if="editing"
                            type="button"
                            severity="secondary"
                            outlined
                            label="Annulla"
                            @click="reset"
                        />
                    </div>
                </form>
            </aside>
        </div>
    </div>
</template>
