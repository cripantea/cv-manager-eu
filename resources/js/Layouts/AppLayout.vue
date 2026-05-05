<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Navbar -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex items-center gap-3">
                        <span class="text-blue-700 font-bold text-lg tracking-tight">
                            CV Manager EU
                        </span>
                        <span class="text-xs text-gray-400 hidden sm:inline">DIGIT-TM II</span>
                    </div>

                    <!-- Admin nav links -->
                    <div v-if="isAdmin" class="hidden sm:flex items-center gap-1">
                        <Link
                            :href="route('admin.dashboard')"
                            class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                            :class="{ 'bg-gray-100 text-gray-900': $page.url.startsWith('/admin/dashboard') }"
                        >CV</Link>
                        <Link
                            :href="route('admin.users')"
                            class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                            :class="{ 'bg-gray-100 text-gray-900': $page.url.startsWith('/admin/users') }"
                        >Utenti</Link>
                    </div>

                    <!-- User info + logout -->
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-700 font-medium hidden sm:block">
                            {{ $page.props.auth.user.name }}
                        </span>
                        <span
                            :class="isAdmin
                                ? 'bg-blue-100 text-blue-800'
                                : 'bg-green-100 text-green-800'"
                            class="text-xs font-semibold px-2.5 py-0.5 rounded-full"
                        >
                            {{ isAdmin ? 'Admin' : 'Candidato' }}
                        </span>
                        <button
                            @click="logout"
                            class="text-sm text-gray-500 hover:text-red-600 transition-colors"
                        >
                            Esci
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Flash messages -->
        <div v-if="flash.success || flash.error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div
                v-if="flash.success"
                class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3"
            >
                <span>{{ flash.success }}</span>
            </div>
            <div
                v-if="flash.error"
                class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg px-4 py-3"
            >
                <span>{{ flash.error }}</span>
            </div>
        </div>

        <!-- Page content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';

const page = usePage();

const isAdmin = computed(() => page.props.auth.user.role === 'admin');

const flash = computed(() => page.props.flash ?? {});

function logout() {
    router.post(route('logout'));
}
</script>
