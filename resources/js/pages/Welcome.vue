<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login } from '@/routes';
/* @chisel-registration */
import { register } from '@/routes';
/* @end-chisel-registration */

const modules = [
    {
        title: 'Ricette',
        description: 'Ingredienti, fasi e costi sempre sotto controllo.',
        color: 'bg-[#e8f0e6] text-[#476246]',
        icon: '✦',
    },
    {
        title: 'Menu',
        description: 'Componi proposte stagionali in pochi passaggi.',
        color: 'bg-[#f9ead7] text-[#a35e24]',
        icon: '⌘',
    },
    {
        title: 'Produzione',
        description: 'Trasforma la pianificazione in una lista operativa.',
        color: 'bg-[#e9edf9] text-[#5264a1]',
        icon: '↗',
    },
];
</script>

<template>
    <Head title="MiseEnPlace" />

    <main class="min-h-screen overflow-hidden bg-[#f7f4ed] text-[#243129]">
        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-6 lg:px-10">
            <div class="pointer-events-none absolute -top-48 -right-32 h-[30rem] w-[30rem] rounded-full bg-[#d9e8cb] blur-3xl opacity-80" />
            <div class="pointer-events-none absolute bottom-0 -left-48 h-96 w-96 rounded-full bg-[#f3cf9c] blur-3xl opacity-40" />

            <header class="relative z-10 flex items-center justify-between">
                <Link :href="dashboard()" class="group flex items-center gap-3" aria-label="MiseEnPlace">
                    <span class="grid h-10 w-10 place-items-center rounded-2xl bg-[#284a38] text-xl text-[#f8f4ea] shadow-lg shadow-[#284a38]/15">M</span>
                    <span class="leading-none">
                        <span class="block font-serif text-2xl tracking-tight">MiseEnPlace</span>
                        <span class="mt-1 block text-[10px] font-bold uppercase tracking-[0.19em] text-[#718077]">cucina organizzata</span>
                    </span>
                </Link>

                <nav class="flex items-center gap-2 text-sm font-medium">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="rounded-full bg-[#284a38] px-5 py-2.5 text-[#f8f4ea] transition hover:bg-[#1b3829]"
                    >
                        Vai al pannello
                    </Link>
                    <Link
                        v-if="$page.props.auth.user"
                        href="/admin"
                        class="rounded-full border border-[#284a38]/20 bg-white/60 px-4 py-2.5 text-[#405248] transition hover:bg-white"
                    >
                        Admin
                    </Link>
                    <template v-else>
                        <Link :href="login()" class="rounded-full px-4 py-2.5 text-[#405248] transition hover:bg-white/70">
                            Accedi
                        </Link>
                        <!-- @chisel-registration -->
                        <Link :href="register()" class="rounded-full bg-[#284a38] px-5 py-2.5 text-[#f8f4ea] shadow-lg shadow-[#284a38]/15 transition hover:-translate-y-0.5 hover:bg-[#1b3829]">
                            Inizia ora
                        </Link>
                        <!-- @end-chisel-registration -->
                    </template>
                </nav>
            </header>

            <section class="relative z-10 grid flex-1 items-center gap-14 py-16 lg:grid-cols-[1.03fr_.97fr] lg:py-20">
                <div class="max-w-2xl">
                    <p class="mb-6 inline-flex items-center gap-2 rounded-full border border-[#284a38]/10 bg-white/60 px-3 py-1.5 text-xs font-semibold tracking-wide text-[#496050]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#e07c3d]" />
                        DALLE RICETTE AL SERVIZIO
                    </p>
                    <h1 class="font-serif text-5xl leading-[.98] tracking-[-0.045em] text-[#1d3227] sm:text-6xl lg:text-7xl">
                        Ogni servizio<br />comincia con<br /><em class="font-normal text-[#c76837]">una buona base.</em>
                    </h1>
                    <p class="mt-7 max-w-lg text-lg leading-8 text-[#536258]">
                        MiseEnPlace riunisce ricette, ingredienti, menu e produzione in un unico spazio di lavoro, pensato per la cucina.
                    </p>
                    <div class="mt-9 flex flex-wrap gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="rounded-full bg-[#284a38] px-6 py-3.5 text-sm font-semibold text-[#f8f4ea] shadow-xl shadow-[#284a38]/20 transition hover:-translate-y-0.5 hover:bg-[#1b3829]"
                        >
                            Apri il pannello →
                        </Link>
                        <Link
                            v-else
                            :href="login()"
                            class="rounded-full bg-[#284a38] px-6 py-3.5 text-sm font-semibold text-[#f8f4ea] shadow-xl shadow-[#284a38]/20 transition hover:-translate-y-0.5 hover:bg-[#1b3829]"
                        >
                            Accedi all'area riservata →
                        </Link>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-xl">
                    <div class="absolute -inset-5 rounded-[2.5rem] bg-white/45 blur-xl" />
                    <div class="relative overflow-hidden rounded-[2rem] border border-white/80 bg-[#fffdf8] p-5 shadow-2xl shadow-[#40593f]/15 sm:p-7">
                        <div class="flex items-center justify-between border-b border-[#e9e4db] pb-5">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#829087]">Oggi in cucina</p>
                                <h2 class="mt-1 font-serif text-2xl text-[#263b2d]">Preparazioni</h2>
                            </div>
                            <span class="rounded-full bg-[#edf4e9] px-3 py-1.5 text-xs font-bold text-[#4f6c4d]">in corso</span>
                        </div>
                        <div class="mt-6 space-y-3">
                            <div class="flex items-center gap-4 rounded-2xl bg-[#f5f1e8] p-4">
                                <span class="grid h-11 w-11 place-items-center rounded-xl bg-[#e6c66e] text-lg">☀</span>
                                <div class="min-w-0 flex-1"><p class="font-semibold text-[#304333]">Menu del giorno</p><p class="text-sm text-[#758078]">4 portate · 18 coperti</p></div>
                                <span class="text-sm font-semibold text-[#55714e]">Pronto</span>
                            </div>
                            <div class="flex items-center gap-4 rounded-2xl bg-[#f5f1e8] p-4">
                                <span class="grid h-11 w-11 place-items-center rounded-xl bg-[#d9b390] text-lg">◒</span>
                                <div class="min-w-0 flex-1"><p class="font-semibold text-[#304333]">Fondo vegetale</p><p class="text-sm text-[#758078]">Cottura · 45 min</p></div>
                                <span class="h-2 w-2 rounded-full bg-[#e07c3d]" />
                            </div>
                        </div>
                        <div class="mt-6 grid grid-cols-3 gap-3 border-t border-[#e9e4db] pt-5">
                            <div v-for="module in modules" :key="module.title" :class="['rounded-2xl p-3.5', module.color]">
                                <span class="text-lg">{{ module.icon }}</span>
                                <p class="mt-4 text-sm font-bold">{{ module.title }}</p>
                                <p class="mt-1 hidden text-[11px] leading-4 opacity-80 sm:block">{{ module.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <footer class="relative z-10 flex flex-wrap items-center justify-between gap-3 border-t border-[#284a38]/10 py-5 text-xs text-[#718077]">
                <span>Una cucina ordinata lavora meglio.</span>
                <span>Ricette · Menu · Produzione</span>
            </footer>
        </div>
    </main>
</template>
