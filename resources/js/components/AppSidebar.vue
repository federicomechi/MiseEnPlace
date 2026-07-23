<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    BookOpen,
    ClipboardList,
    CookingPot,
    FolderGit2,
    GlassWater,
    LayoutGrid,
    Salad,
    Settings,
    SlidersHorizontal,
    Store,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();
const role = computed(() => page.props.auth.user?.role ?? 'open');

const roleLabels: Record<string, string> = {
    full_access: 'Accesso completo',
    eart_admin: 'eARTadmin',
    client: 'Cliente',
    kitchen: 'Cucina',
    bar: 'Bar',
    all: 'All',
    first: 'Primo avvio',
    open: 'Open',
};

const workspaceItems: Record<string, NavItem[]> = {
    full_access: [
        { title: 'Ricette', href: '/operativita/recipes', icon: BookOpen },
        { title: 'Ingredienti', href: '/operativita/ingredients', icon: Salad },
        { title: 'Menu', href: '/operativita/menus', icon: ClipboardList },
        {
            title: 'Produzione',
            href: '/operativita/production',
            icon: CookingPot,
        },
        { title: 'Bar e bevande', href: '/operativita/bar', icon: GlassWater },
        { title: 'Fornitori', href: '/operativita/suppliers', icon: Store },
        {
            title: 'Impostazioni',
            href: '/operativita/settings',
            icon: SlidersHorizontal,
        },
    ],
    eart_admin: [],
    all: [
        { title: 'Ricette', href: '/operativita/recipes', icon: BookOpen },
        { title: 'Ingredienti', href: '/operativita/ingredients', icon: Salad },
        { title: 'Menu', href: '/operativita/menus', icon: ClipboardList },
        {
            title: 'Produzione',
            href: '/operativita/production',
            icon: CookingPot,
        },
        { title: 'Bar e bevande', href: '/operativita/bar', icon: GlassWater },
    ],
    kitchen: [
        { title: 'Ricette', href: '/operativita/recipes', icon: BookOpen },
        { title: 'Ingredienti', href: '/operativita/ingredients', icon: Salad },
        {
            title: 'Produzione',
            href: '/operativita/production',
            icon: CookingPot,
        },
    ],
    bar: [
        { title: 'Bar e bevande', href: '/operativita/bar', icon: GlassWater },
    ],
    client: [
        { title: 'Menu', href: '/operativita/menus', icon: ClipboardList },
    ],
    first: [
        {
            title: 'Configurazione iniziale',
            href: '/operativita/setup',
            icon: SlidersHorizontal,
        },
    ],
    open: [],
};

workspaceItems.eart_admin = workspaceItems.full_access;

const mainNavItems = computed<NavItem[]>(() => [
    { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
    ...(workspaceItems[role.value] ?? workspaceItems.open),
    ...(role.value === 'full_access' || role.value === 'eart_admin'
        ? [{ title: 'Amministrazione', href: '/admin', icon: Settings }]
        : []),
]);

const navigationLabel = computed(() => roleLabels[role.value] ?? 'Accesso');

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/federicomechi/MiseEnApPlace',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" :label="navigationLabel" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
