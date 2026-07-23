<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from '@lucide/vue';
import { ref } from 'vue';
import {
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

defineOptions({ name: 'NavMenuItem' });
defineProps<{ item: NavItem }>();
const { isCurrentUrl } = useCurrentUrl();
const open = ref(true);
</script>

<template>
    <SidebarMenuItem>
        <SidebarMenuButton
            v-if="item.children?.length"
            :is-active="item.children.some((child) => isCurrentUrl(child.href))"
            :tooltip="item.title"
            @click="open = !open"
        >
            <component :is="item.icon" v-if="item.icon" /><span>{{
                item.title
            }}</span
            ><ChevronRight
                class="ml-auto transition-transform"
                :class="open ? 'rotate-90' : ''"
            />
        </SidebarMenuButton>
        <SidebarMenuButton
            v-else
            as-child
            :is-active="isCurrentUrl(item.href)"
            :tooltip="item.title"
        >
            <Link :href="item.href"
                ><component :is="item.icon" v-if="item.icon" /><span>{{
                    item.title
                }}</span></Link
            >
        </SidebarMenuButton>
        <SidebarMenuSub v-if="item.children?.length && open">
            <SidebarMenuSubItem
                v-for="child in item.children"
                :key="child.title"
            >
                <SidebarMenuSubButton
                    as-child
                    :is-active="isCurrentUrl(child.href)"
                    ><Link :href="child.href"
                        ><component
                            :is="child.icon"
                            v-if="child.icon"
                        /><span>{{ child.title }}</span></Link
                    ></SidebarMenuSubButton
                >
            </SidebarMenuSubItem>
        </SidebarMenuSub>
    </SidebarMenuItem>
</template>
