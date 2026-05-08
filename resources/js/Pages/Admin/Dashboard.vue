<template>
    <AppLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h1 class="text-2xl font-bold text-gray-900">CV Dashboard</h1>

                <!-- Filtro status con contatori -->
                <div class="flex gap-2 flex-wrap">
                    <button
                        v-for="f in filters"
                        :key="f.value"
                        @click="setFilter(f.value)"
                        :class="[
                            'flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border',
                            statusFilter === f.value
                                ? 'bg-blue-600 text-white border-blue-600'
                                : 'bg-white text-gray-600 border-gray-300 hover:border-gray-400',
                        ]"
                    >
                        {{ f.label }}
                        <span
                            v-if="f.count !== null"
                            :class="statusFilter === f.value ? 'bg-blue-500' : 'bg-gray-100 text-gray-500'"
                            class="px-1.5 py-0.5 rounded-full text-xs font-semibold"
                        >{{ f.count }}</span>
                    </button>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Candidate</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Updated</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr v-for="cv in cvs" :key="cv.user.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ cv.first_name || cv.user.name }}
                                {{ cv.last_name ?? '' }}
                                <span v-if="cv.user.role === 'admin'" class="ml-1 text-xs font-normal text-purple-600">(admin)</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ cv.user.email }}</td>
                            <td class="px-6 py-4">
                                <span v-if="cv.status" :class="statusBadge(cv.status)" class="text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                    {{ cv.status }}
                                </span>
                                <span v-else class="text-xs text-gray-400">no CV</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ formatDate(cv.updated_at) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div v-if="cv.cv_id" class="flex items-center justify-end gap-2">
                                    <button
                                        v-if="cv.status !== 'locked' && cv.status !== 'archived'"
                                        @click="requestAction(cv, lock, 'Lock this CV?')"
                                        class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                                    >
                                        Lock
                                    </button>
                                    <button
                                        v-if="cv.status === 'locked'"
                                        @click="requestAction(cv, unlock, 'Unlock this CV?')"
                                        class="text-xs px-3 py-1.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors"
                                    >
                                        Unlock
                                    </button>
                                    <button
                                        v-if="cv.status !== 'archived'"
                                        @click="requestAction(cv, archive, 'Archive this CV? This action cannot be undone.')"
                                        class="text-xs px-3 py-1.5 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors"
                                    >
                                        Archive
                                    </button>
                                    <a
                                        :href="`/admin/cvs/${cv.cv_id}/export`"
                                        target="_blank"
                                        class="text-xs px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                                        title="Download DOCX DIGIT-TM II"
                                    >
                                        Download
                                    </a>
                                </div>
                                <span v-else class="text-xs text-gray-400">—</span>
                            </td>
                        </tr>
                        <tr v-if="cvs.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-400">
                                No CVs found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Confirm dialog -->
        <div
            v-if="pending"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
            @click.self="cancelPending"
        >
            <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full mx-4">
                <p class="text-sm text-gray-700 mb-6">{{ pending.label }}</p>
                <div class="flex justify-end gap-3">
                    <button
                        @click="cancelPending"
                        class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 transition-colors"
                    >Cancel</button>
                    <button
                        @click="confirmPending"
                        class="px-4 py-2 text-sm rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors"
                    >Confirm</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    cvs:    Array,
    counts: Object,
});

const pending = ref(null); // { cv, action, label }

function requestAction(cv, action, label) {
    pending.value = { cv, action, label };
}

function confirmPending() {
    if (!pending.value) return;
    const { cv, action } = pending.value;
    pending.value = null;
    action(cv);
}

function cancelPending() {
    pending.value = null;
}

const statusFilter = ref('');

const filters = computed(() => [
    { value: '',         label: 'All',      count: null },
    { value: 'draft',    label: 'Draft',    count: props.counts?.draft    ?? 0 },
    { value: 'locked',   label: 'Locked',   count: props.counts?.locked   ?? 0 },
    { value: 'archived', label: 'Archived', count: props.counts?.archived ?? 0 },
]);

function setFilter(value) {
    statusFilter.value = value;
    router.get(route('admin.dashboard'), { status: value }, { preserveState: true, replace: true });
}

function statusBadge(status) {
    return {
        draft:    'bg-yellow-100 text-yellow-800',
        locked:   'bg-blue-100 text-blue-800',
        archived: 'bg-gray-100 text-gray-600',
    }[status] ?? 'bg-gray-100 text-gray-600';
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function lock(cv) {
    router.patch(route('admin.cvs.lock', cv.cv_id));
}

function unlock(cv) {
    router.patch(route('admin.cvs.unlock', cv.cv_id));
}

function archive(cv) {
    router.patch(route('admin.cvs.archive', cv.cv_id));
}
</script>
