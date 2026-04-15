<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import SidebarLink from '@/Components/ui/navigation/SidebarLink.vue';

const page = usePage();
const toggleMenu = ref(false);
const userMenuRef = ref(null);
const sidebarMinimized = ref(localStorage.getItem('sidebarMinimized') === 'true');
const isDark = ref(document.documentElement.classList.contains('dark'));

const user = computed(() => page.props.auth?.user);

const userRole = computed(() => {
    const roles = user.value?.roles;
    if (Array.isArray(roles) && roles.length > 0) {
        return roles[0]?.name ?? roles[0];
    }
    return 'Staff';
});

function toggleSidebarMinimized() {
    sidebarMinimized.value = !sidebarMinimized.value;
    localStorage.setItem('sidebarMinimized', sidebarMinimized.value ? 'true' : 'false');
}

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
    isDark.value = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
}

function isActive(routeName) {
    try {
        return route().current(routeName + '*');
    } catch {
        return false;
    }
}
</script>

<template>
    <Toast position="top-right" :life="3500" />

    <div class="w-full h-full flex">
        <!-- Mobile Backdrop -->
        <div v-if="toggleMenu" @click="toggleMenu = false" class="fixed inset-0 bg-black/50 z-20 md:hidden" />

        <!-- Floating Sidebar -->
        <aside
            class="fixed z-30 md:z-20 top-0 left-0 md:top-20 md:left-4 flex flex-col bg-[#222831] shadow-xl backdrop-blur-xl transition-[width,transform] duration-300 rounded-4xl min-w-0 h-full md:h-[calc(100vh-96px)]"
            :class="[
                sidebarMinimized ? 'md:w-[110px]' : 'md:w-[220px]',
                toggleMenu ? 'w-[280px] translate-x-0' : '-translate-x-full md:translate-x-0',
            ]">
            <div class="flex-1 flex flex-col min-h-0 min-w-0 overflow-hidden rounded-4xl">
                <!-- Minimize toggle (desktop only) -->
                <button @click="toggleSidebarMinimized"
                    class="hidden md:flex w-full pt-4 px-4 text-gray-500 hover:text-gray-300 cursor-pointer justify-end transition">
                    <i :class="sidebarMinimized ? 'pi pi-window-maximize' : 'pi pi-window-minimize'"
                        style="font-size: 0.7rem; margin-right: 8px;"></i>
                </button>

                <!-- User profile (expanded) -->
                <div v-if="!sidebarMinimized" class="px-4 py-2 border-b border-white/10">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-linear-to-br from-blue-500 to-purple-600 flex items-center justify-center shrink-0 overflow-hidden">
                            <img v-if="user?.has_profile_photo && user?.profile_photo_url" :src="user.profile_photo_url"
                                class="w-10 h-10 object-cover" />
                            <i v-else class="pi pi-user text-white text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-white text-sm font-medium truncate">{{ user?.name ?? 'User' }}</p>
                            <p class="text-gray-400 text-xs truncate">{{ userRole }}</p>
                        </div>
                    </div>
                </div>

                <!-- User profile (minimized) -->
                <div v-else class="flex items-center justify-center py-3 border-b border-white/10">
                    <div
                        class="w-9 h-9 rounded-full bg-linear-to-br from-blue-500 to-purple-600 flex items-center justify-center shrink-0 overflow-hidden">
                        <img v-if="user?.has_profile_photo && user?.profile_photo_url" :src="user.profile_photo_url"
                            class="w-9 h-9 object-cover" />
                        <i v-else class="pi pi-user text-white text-xs"></i>
                    </div>
                </div>

                <!-- Navigation (expanded) -->
                <nav v-if="!sidebarMinimized" class="flex-1 px-3 pt-3 space-y-1 overflow-y-auto pb-6">
                    <SidebarLink :href="route('dashboard')" :active="isActive('dashboard')">
                        <i class="pi pi-home mr-3 text-base"></i>
                        <span class="text-sm font-medium">Dashboard</span>
                    </SidebarLink>
                    <SidebarLink :href="route('employees.index')" :active="isActive('employees')">
                        <i class="pi pi-id-card mr-3 text-base"></i>
                        <span class="text-sm font-medium">Employees</span>
                    </SidebarLink>
                    <SidebarLink :href="route('employee_fund_transactions.index')"
                        :active="isActive('employee_fund_transactions')">
                        <i class="pi pi-file-edit mr-3 text-base"></i>
                        <span class="text-sm font-medium">Fund Transactions</span>
                    </SidebarLink>
                    <SidebarLink :href="route('responsibility_centers.index')"
                        :active="isActive('responsibility_centers')">
                        <i class="pi pi-building mr-3 text-base"></i>
                        <span class="text-sm font-medium">Responsibility Centers</span>
                    </SidebarLink>
                    <SidebarLink :href="route('settings.signatories')" :active="isActive('settings')">
                        <i class="pi pi-pen-to-square mr-3 text-base"></i>
                        <span class="text-sm font-medium">Signatories</span>
                    </SidebarLink>
                </nav>

                <!-- Navigation (minimized) -->
                <nav v-else class="flex-1 px-2 pt-3 space-y-2 overflow-y-auto pb-6">
                    <SidebarLink :href="route('dashboard')" :active="isActive('dashboard')">
                        <span class="w-full flex justify-center">
                            <i class="pi pi-home text-xl"></i>
                        </span>
                    </SidebarLink>
                    <SidebarLink :href="route('employees.index')" :active="isActive('employees')">
                        <span class="w-full flex justify-center">
                            <i class="pi pi-id-card text-xl"></i>
                        </span>
                    </SidebarLink>
                    <SidebarLink :href="route('employee_fund_transactions.index')"
                        :active="isActive('employee_fund_transactions')">
                        <span class="w-full flex justify-center">
                            <i class="pi pi-file-edit text-xl"></i>
                        </span>
                    </SidebarLink>
                    <SidebarLink :href="route('responsibility_centers.index')"
                        :active="isActive('responsibility_centers')">
                        <span class="w-full flex justify-center">
                            <i class="pi pi-building text-xl"></i>
                        </span>
                    </SidebarLink>
                    <SidebarLink :href="route('settings.signatories')" :active="isActive('settings')">
                        <span class="w-full flex justify-center">
                            <i class="pi pi-pen-to-square text-xl"></i>
                        </span>
                    </SidebarLink>
                </nav>

                <!-- Mobile: logout at bottom -->
                <div class="md:hidden border-t border-white/10 p-3">
                    <button @click="handleLogout"
                        class="flex items-center gap-3 px-3 py-2 rounded-2xl text-red-400 hover:text-red-300 hover:bg-white/10 transition w-full text-sm">
                        <i class="pi pi-sign-out"></i>
                        <span>Sign Out</span>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main content wrapper -->
        <div class="w-full min-w-0 flex flex-col h-screen overflow-hidden">
            <!-- Top Navbar -->
            <div class="flex-shrink-0 relative z-20 h-16 bg-[#222831] border-b border-white/5">
                <div class="px-4 md:px-6 flex items-center justify-between h-full gap-4">
                    <!-- Mobile hamburger -->
                    <button @click="toggleMenu = !toggleMenu"
                        class="md:hidden text-gray-300 hover:text-white p-2 cursor-pointer">
                        <i class="pi text-lg" :class="toggleMenu ? 'pi-times' : 'pi-bars'"></i>
                    </button>

                    <!-- Brand -->
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div
                            class="w-8 h-8 rounded-xl bg-linear-to-br from-[#2b5876] to-[#4e4376] flex items-center justify-center shrink-0">
                            <i class="pi pi-briefcase text-white text-sm"></i>
                        </div>
                        <span class="text-white font-semibold text-sm hidden sm:inline">Workforce Portal</span>
                        <div class="hidden md:block w-px h-5 bg-white/10"></div>
                        <span class="text-gray-500 text-xs hidden md:inline">PGP – Office of the Governor</span>
                    </div>

                    <!-- Right actions -->
                    <div class="flex items-center gap-1">
                        <Button :icon="isDark ? 'pi pi-sun' : 'pi pi-moon'" severity="secondary" variant="text"
                            size="large" rounded @click="toggleDark"
                            :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'" />

                        <Button icon="pi pi-cog" severity="secondary" variant="text" size="large" rounded
                            @click="toggleUserMenu" v-tooltip.bottom="`User Menu`" />
                        <Popover ref="userMenuRef" class="w-52 !rounded-2xl">
                            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                <p class="text-sm font-semibold">{{ user?.name ?? 'User' }}</p>
                                <p class="text-xs opacity-60 mt-0.5">{{ userRole }}</p>
                            </div>
                            <div class="px-4 py-2 bg-gray-50 dark:bg-gray-800 rounded-b-2xl">
                                <Button @click="handleLogout" label="Sign Out" icon="pi pi-sign-out" severity="danger"
                                    variant="text" class="w-full" />
                            </div>
                        </Popover>
                    </div>
                </div>
            </div>

            <!-- Scrollable page content -->
            <div class="flex-1 overflow-y-auto px-4 md:px-6 pt-6 pb-10 content-bg transition-[margin-left] duration-300"
                :class="sidebarMinimized ? 'md:ml-[130px]' : 'md:ml-[240px]'">
                <slot />
            </div>
        </div>
    </div>
</template>
