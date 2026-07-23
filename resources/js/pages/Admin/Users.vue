<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Select from 'primevue/select';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type User = {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    role: string;
    created_at: string;
};

type RoleOption = { label: string; value: string };

const props = defineProps<{ users: User[]; roleOptions: RoleOption[]; search: string }>();
const page = usePage();
const search = ref(props.search);
let searchTimer: ReturnType<typeof setTimeout> | undefined;
const currentUserId = computed(() => page.props.auth.user.id);
const editingId = ref<number | null>(null);

const createForm = useForm({
    name: '',
    email: '',
    password: '',
    role: 'open',
});

const editForm = useForm({
    name: '',
    email: '',
    password: '',
    role: 'open',
});

function createUser(): void {
    createForm.post('/admin/users', {
        preserveScroll: true,
        onSuccess: () => createForm.reset(),
    });
}

function startEditing(user: User): void {
    editingId.value = user.id;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.password = '';
    editForm.role = user.role;
    editForm.clearErrors();
}

function updateUser(user: User): void {
    editForm.put(`/admin/users/${user.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingId.value = null;
            editForm.reset();
        },
    });
}

function deleteUser(user: User): void {
    if (window.confirm(`Eliminare definitivamente l'utente ${user.name}?`)) {
        router.delete(`/admin/users/${user.id}`, { preserveScroll: true });
    }
}
function filter(): void {
    router.get('/admin/users', { search: search.value || undefined }, { preserveState: true, replace: true });
}
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(filter, 300);
});

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Amministrazione', href: '/admin' },
            { title: 'Utenti e permessi', href: '/admin/users' },
        ],
    },
});
</script>

<template>
    <Head title="Utenti e permessi" />

    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-4 p-3 md:p-5">
        <section
            class="flex flex-wrap items-end justify-between gap-2 border-b border-border pb-3"
        >
            <div>
                <h1 class="font-serif text-2xl tracking-tight text-[#2c4133]">
                    Utenti e permessi
                </h1>
                <p class="mt-0.5 text-sm text-muted-foreground">
                    Gestisci accessi e ruoli operativi.
                </p>
            </div>
            <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto">
                <InputText v-model="search" class="min-w-56 flex-1" placeholder="Cerca utente, email o ruolo" aria-label="Cerca utenti" />
                <span class="text-xs font-semibold text-muted-foreground">{{ users.length }} utenti registrati</span>
            </div>
        </section>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_20rem]">
            <section class="rounded-xl border border-border bg-card shadow-sm">
                <div class="border-b border-border px-4 py-3">
                    <h2 class="font-semibold">Account</h2>
                </div>
                <div
                    v-if="users.length === 0"
                    class="p-6 text-center text-muted-foreground"
                >
                    Nessun utente presente.
                </div>
                <div v-else class="divide-y divide-border">
                    <article v-for="user in users" :key="user.id" class="p-4">
                        <template v-if="editingId === user.id">
                            <form
                                class="grid gap-4"
                                @submit.prevent="updateUser(user)"
                            >
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <Label :for="`name-${user.id}`"
                                            >Nome</Label
                                        ><Input
                                            :id="`name-${user.id}`"
                                            v-model="editForm.name"
                                            class="mt-2"
                                            required
                                        /><InputError
                                            :message="editForm.errors.name"
                                        />
                                    </div>
                                    <div>
                                        <Label :for="`email-${user.id}`"
                                            >Email</Label
                                        ><Input
                                            :id="`email-${user.id}`"
                                            v-model="editForm.email"
                                            class="mt-2"
                                            type="email"
                                            required
                                        /><InputError
                                            :message="editForm.errors.email"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <Label :for="`password-${user.id}`"
                                        >Nuova password</Label
                                    ><Input
                                        :id="`password-${user.id}`"
                                        v-model="editForm.password"
                                        class="mt-2"
                                        type="password"
                                        placeholder="Lascia vuoto per non modificarla"
                                    /><InputError
                                        :message="editForm.errors.password"
                                    />
                                </div>
                                <div>
                                    <Label :for="`role-${user.id}`"
                                        >Profilo di accesso</Label
                                    ><Select
                                        :id="`role-${user.id}`"
                                        v-model="editForm.role"
                                        class="mt-2 w-full"
                                        :options="roleOptions"
                                        option-label="label"
                                        option-value="value"
                                        :disabled="user.id === currentUserId"
                                    />
                                    <p
                                        v-if="user.id === currentUserId"
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        Il tuo profilo non può essere ridotto da
                                        questa schermata.
                                    </p>
                                    <InputError
                                        :message="editForm.errors.role"
                                    />
                                </div>
                                <div class="flex gap-3">
                                    <button
                                        type="submit"
                                        :disabled="editForm.processing"
                                        class="rounded-xl bg-[#284a38] px-4 py-2 text-sm font-semibold text-white"
                                    >
                                        Salva</button
                                    ><button
                                        type="button"
                                        class="rounded-xl border border-border px-4 py-2 text-sm font-semibold"
                                        @click="editingId = null"
                                    >
                                        Annulla
                                    </button>
                                </div>
                            </form>
                        </template>
                        <template v-else>
                            <div
                                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="flex min-w-0 items-center gap-3">
                                    <span
                                        class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-[#e7f0e6] font-serif text-lg text-[#476246]"
                                        >{{
                                            user.name.slice(0, 1).toUpperCase()
                                        }}</span
                                    >
                                    <div class="min-w-0">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <h3 class="font-bold">
                                                {{ user.name }}
                                            </h3>
                                            <span
                                                class="rounded-full bg-[#e5edf1] px-2 py-0.5 text-[11px] font-bold text-[#476774]"
                                                >{{
                                                    roleOptions.find(
                                                        (role) =>
                                                            role.value ===
                                                            user.role,
                                                    )?.label ?? user.role
                                                }}</span
                                            >
                                        </div>
                                        <p
                                            class="truncate text-sm text-muted-foreground"
                                        >
                                            {{ user.email }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="rounded-lg border border-border px-3 py-2 text-sm font-semibold hover:bg-muted"
                                        @click="startEditing(user)"
                                    >
                                        Modifica</button
                                    ><button
                                        v-if="user.id !== currentUserId"
                                        type="button"
                                        class="rounded-lg px-3 py-2 text-sm font-semibold text-destructive hover:bg-destructive/10"
                                        @click="deleteUser(user)"
                                    >
                                        Elimina
                                    </button>
                                </div>
                            </div>
                        </template>
                    </article>
                </div>
            </section>

            <aside
                class="h-fit rounded-xl border border-border bg-card p-4 shadow-sm"
            >
                <h2 class="font-semibold">Nuovo utente</h2>
                <p class="mt-0.5 text-sm text-muted-foreground">
                    L'account sarà subito attivo.
                </p>
                <form class="mt-4 grid gap-4" @submit.prevent="createUser">
                    <div>
                        <Label for="new-name">Nome</Label
                        ><InputText
                            id="new-name"
                            v-model="createForm.name"
                            class="mt-2 w-full"
                            required
                            autocomplete="name"
                        /><InputError :message="createForm.errors.name" />
                    </div>
                    <div>
                        <Label for="new-email">Email</Label
                        ><InputText
                            id="new-email"
                            v-model="createForm.email"
                            class="mt-2 w-full"
                            type="email"
                            required
                            autocomplete="email"
                        /><InputError :message="createForm.errors.email" />
                    </div>
                    <div>
                        <Label for="new-password">Password</Label
                        ><Password
                            id="new-password"
                            v-model="createForm.password"
                            class="mt-2 w-full"
                            input-class="w-full"
                            :feedback="false"
                            toggle-mask
                            required
                            autocomplete="new-password"
                        />
                        <p class="mt-1 text-xs text-muted-foreground">
                            Almeno 8 caratteri.
                        </p>
                        <InputError :message="createForm.errors.password" />
                    </div>
                    <div>
                        <Label for="new-role">Profilo di accesso</Label
                        ><Select
                            id="new-role"
                            v-model="createForm.role"
                            class="mt-2 w-full"
                            :options="roleOptions"
                            option-label="label"
                            option-value="value"
                        /><InputError :message="createForm.errors.role" />
                    </div>
                    <Button
                        type="submit"
                        label="Crea utente"
                        icon="pi pi-user-plus"
                        :loading="createForm.processing"
                        class="w-full"
                    />
                </form>
            </aside>
        </div>
    </div>
</template>
