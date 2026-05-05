<template>
    <AppLayout>
        <div class="space-y-6">
            <h1 class="text-2xl font-bold text-gray-900">Gestione Candidati</h1>

            <!-- Flash messages -->
            <div v-if="flash.success" class="px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm font-medium">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm font-medium">
                {{ flash.error }}
            </div>

            <!-- Invite form -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Invita nuovo candidato</h2>
                <form @submit.prevent="sendInvite" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <input
                            v-model="inviteForm.name"
                            type="text"
                            placeholder="Nome completo"
                            required
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <p v-if="inviteForm.errors.name" class="mt-1 text-xs text-red-600">{{ inviteForm.errors.name }}</p>
                    </div>
                    <div class="flex-1">
                        <input
                            v-model="inviteForm.email"
                            type="email"
                            placeholder="Email"
                            required
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <p v-if="inviteForm.errors.email" class="mt-1 text-xs text-red-600">{{ inviteForm.errors.email }}</p>
                    </div>
                    <button
                        type="submit"
                        :disabled="inviteForm.processing"
                        class="px-5 py-2 bg-[#1F3864] text-white text-sm font-semibold rounded-lg hover:bg-[#162a4e] disabled:opacity-50 transition-colors whitespace-nowrap"
                    >
                        {{ inviteForm.processing ? 'Invio...' : 'Invia invito' }}
                    </button>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stato account</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Registrato il</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ user.name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ user.email }}</td>
                            <td class="px-6 py-4">
                                <span
                                    :class="user.is_suspended
                                        ? 'bg-red-100 text-red-800'
                                        : 'bg-green-100 text-green-800'"
                                    class="text-xs font-semibold px-2.5 py-0.5 rounded-full"
                                >
                                    {{ user.is_suspended ? 'Sospeso' : 'Attivo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(user.created_at) }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        v-if="user.cv"
                                        @click="resetAiImport(user)"
                                        class="text-xs px-3 py-1.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                                    >
                                        Reset AI ({{ user.cv?.ai_import_count ?? 0 }}/3)
                                    </button>
                                    <button
                                        v-if="!user.is_suspended"
                                        @click="confirm(`Sospendere l'account di ${user.name}?`) && suspend(user)"
                                        class="text-xs px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                                    >
                                        Sospendi
                                    </button>
                                    <button
                                        v-else
                                        @click="confirm(`Riattivare l'account di ${user.name}?`) && unsuspend(user)"
                                        class="text-xs px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                                    >
                                        Riattiva
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-400">
                                Nessun candidato trovato.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    users: Array,
});

const flash = computed(() => usePage().props.flash);

const inviteForm = useForm({
    name:  '',
    email: '',
});

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('it-IT', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function resetAiImport(user) {
    const count = user.cv?.ai_import_count ?? 0;
    if (!confirm(`Reset contatore AI per ${user.name}? (attuale: ${count}/3)`)) return;
    router.patch(route('admin.users.reset-ai-import', user.id));
}

function sendInvite() {
    inviteForm.post(route('admin.users.invite'), {
        preserveScroll: true,
        onSuccess: () => inviteForm.reset(),
    });
}

function suspend(user) {
    router.patch(route('admin.users.suspend', user.id));
}

function unsuspend(user) {
    router.patch(route('admin.users.unsuspend', user.id));
}
</script>
