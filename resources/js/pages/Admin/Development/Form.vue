<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

type DevelopmentEntry = {
    id: number;
    title: string;
    description: string | null;
    link: string | null;
};

const props = defineProps<{ entry: DevelopmentEntry | null }>();
const form = useForm({
    title: props.entry?.title ?? '',
    description: props.entry?.description ?? '',
    link: props.entry?.link ?? '',
});

function submit(): void {
    if (props.entry) {
        form.put(`/admin/development/${props.entry.id}`);
    } else {
        form.post('/admin/development');
    }
}

function removeEntry(): void {
    if (props.entry && window.confirm(`Eliminare “${props.entry.title}”?`)) {
        router.delete(`/admin/development/${props.entry.id}`);
    }
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Amministrazione', href: '/admin' },
            { title: 'Sviluppo', href: '/admin/development' },
        ],
    },
});
</script>

<template>
    <Head :title="entry ? `Modifica ${entry.title}` : 'Nuova voce sviluppo'" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-4 p-3 md:p-5">
        <header
            class="flex flex-wrap items-end justify-between gap-3 border-b border-border pb-3"
        >
            <div>
                <p
                    class="text-xs font-bold tracking-wide text-[#6b7e6c] uppercase"
                >
                    Sviluppo
                </p>
                <h1 class="font-serif text-2xl tracking-tight text-[#2c4133]">
                    {{ entry ? 'Modifica voce' : 'Nuova voce' }}
                </h1>
            </div>
            <div class="flex gap-2">
                <Button
                    v-if="entry"
                    label="Elimina"
                    severity="danger"
                    text
                    @click="removeEntry"
                /><Button
                    as="a"
                    href="/admin/development"
                    label="Elenco"
                    severity="secondary"
                    outlined
                />
            </div>
        </header>

        <form
            class="rounded-xl border border-border bg-card p-4 shadow-sm"
            @submit.prevent="submit"
        >
            <div>
                <Label for="title">Titolo</Label
                ><InputText
                    id="title"
                    v-model="form.title"
                    class="mt-2 w-full"
                    required
                    autofocus
                /><InputError :message="form.errors.title" />
            </div>
            <div class="mt-4">
                <Label for="description">Descrizione</Label
                ><Textarea
                    id="description"
                    v-model="form.description"
                    class="mt-2 w-full"
                    rows="7"
                    auto-resize
                /><InputError :message="form.errors.description" />
            </div>
            <div class="mt-4">
                <Label for="link">Link</Label
                ><InputText
                    id="link"
                    v-model="form.link"
                    class="mt-2 w-full"
                    type="url"
                    placeholder="https://…"
                />
                <p class="mt-1 text-xs text-muted-foreground">
                    Sono accettati solo link http o https.
                </p>
                <InputError :message="form.errors.link" />
            </div>
            <Button
                type="submit"
                class="mt-5"
                :label="entry ? 'Salva modifiche' : 'Crea voce'"
                :loading="form.processing"
            />
        </form>
    </div>
</template>
