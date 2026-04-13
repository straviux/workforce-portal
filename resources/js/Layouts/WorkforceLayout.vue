<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import SidebarLink from '@/Components/ui/navigation/SidebarLink.vue';

const page = usePage();
const toggleMenu = ref(false);
const userMenuRef = ref(null);

const user = computed(() => page.props.auth?.user);

const userRole = computed(() => {
    const roles = user.value?.roles;
    if (Array.isArray(roles) && roles.length > 0) {
        return roles[0]?.name ?? roles[0];
    }
    return 'Staff';
});

function toggleUserMenu(event) {
    userMenuRef.value.toggle(event);
}

function handleLogout() {
    router.post(route('logout'), {}, {
        onFinish: () => {
            window.location.href = '/login';
        }
    });
}

function toggleDark() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
}

const userMenuItems = ref([
    {
        label: 'Sign Out',
        icon: 'pi pi-sign-out',
        command: handleLogout,
    }
]);

function isActive(routeName) {
    try {
        return route().current(routeName + '*');
    } catch {
        return false;
    }
}
</script>

<template>
    <div class="min-h-screen flex bg-[#222831]">
        <!-- Mobile overlay -->
        <div v-if="toggleMenu" class="fixed inset-0 z-20 bg-black/40 lg:hidden" @click="toggleMenu = false" />

        <!-- Sidebar -->
        <aside :class="[
            'fixed lg:relative z-30 lg:z-auto inset-y-0 left-0 w-64 flex flex-col',
            'bg-[#222831]/90 backdrop-blur-xl rounded-4xl m-3',
            'transition-transform duration-300',
            toggleMenu ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        ]">
            <!-- Brand -->
            <div class="px-6 pt-6 pb-4 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-linear-to-br from-[#2b5876] to-[#4e4376] flex items-center justify-center shrink-0">
                    <i class="pi pi-briefcase text-white text-sm"></i>
                </div>
                <div>
                    <p class="text-white font-semibold text-sm leading-tight">Workforce Portal</p>
                    <p class="text-gray-400 text-xs">PGP – Office of the Governor</p>
                </div>
            </div>

            <div class="border-t border-white/10 mx-4 mb-3" />

            <!-- Navigation -->
            <nav class="flex-1 px-3 space-y-1 overflow-y-auto">
                <SidebarLink :href="route('dashboard')" :active="isActive('dashboard')">
                    <i class="pi pi-home mr-3 text-base"></i>
                    <span class="text-sm">Dashboard</span>
                </SidebarLink>
                <SidebarLink :href="route('employee_fund_transactions.index')" :active="isActive('employee_fund_transactions')">
                    <i class="pi pi-file-edit mr-3 text-base"></i>
                    <span class="text-sm">Fund Transactions</span>
                </SidebarLink>
            </nav>

            <!-- Bottom: user card -->
            <div class="border-t border-white/10 mx-4 my-3" />
            <div class="px-3 pb-4">
                <div class="flex items-center gap-3 px-3 py-2 rounded-2xl hover:bg-white/10 cursor-pointer transition" @click="toggleUserMenu">
                    <div class="w-8 h-8 rounded-full bg-linear-to-br from-blue-500 to-purple-600 flex items-center justify-center shrink-0">
                        <img v-if="user?.has_profile_photo && user?.profile_photo_url" :src="user.profile_photo_url" class="w-8 h-8 rounded-full object-cover" />
                        <i v-else class="pi pi-user text-white text-xs"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ user?.name ?? 'User' }}</p>
                        <p class="text-gray-400 text-xs truncate">{{ userRole }}</p>
                    </div>
                    <i class="pi pi-ellipsis-v text-gray-400 text-xs"></i>
                </div>
                <Menu ref="userMenuRef" :model="userMenuItems" popup />
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Mobile top bar -->
            <div class="lg:hidden flex items-center justify-between bg-[#222831]/80 backdrop-blur-sm px-4 py-3 mx-3 mt-3 rounded-4xl">
                <button class="text-white p-1" @click="toggleMenu = !toggleMenu">
                    <i :class="toggleMenu ? 'pi pi-times' : 'pi pi-bars'" class="text-lg"></i>
                </button>
                <span class="text-white font-semibold text-sm">Workforce Portal</span>
                <button class="text-gray-400 p-1" @click="toggleDark">
                    <i class="pi pi-sun text-sm dark:hidden"></i>
                    <i class="pi pi-moon text-sm hidden dark:block"></i>
                </button>
            </div>

            <!-- Desktop top-right actions -->
            <div class="hidden lg:flex justify-end items-center gap-2 px-6 pt-5 pr-6">
                <button class="text-gray-400 hover:text-white transition p-2 rounded-xl hover:bg-white/10" @click="toggleDark" title="Toggle dark mode">
                    <i class="pi pi-sun text-sm dark:hidden"></i>
                    <i class="pi pi-moon text-sm hidden dark:block"></i>
                </button>
            </div>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto px-6 pb-6 content-bg mx-3 mb-3 rounded-4xl" :class="{ 'mt-3': true }">
                <slot />
            </main>
        </div>

        <Toast />
    </div>
</template>
