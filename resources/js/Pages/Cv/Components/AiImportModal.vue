<template>
    <!-- Backdrop -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="$emit('close')">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-900">⚡ Import from LinkedIn / PDF</h2>
                <button @click="$emit('close')" type="button" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            <!-- Tab bar -->
            <div class="flex gap-1 px-6 pt-4">
                <button
                    v-for="t in tabs"
                    :key="t.key"
                    @click="activeTab = t.key; reset()"
                    type="button"
                    :class="[
                        'px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 transition-colors',
                        activeTab === t.key
                            ? 'border-blue-600 text-blue-700 bg-blue-50'
                            : 'border-transparent text-gray-500 hover:text-gray-700',
                    ]"
                >
                    {{ t.label }}
                </button>
            </div>

            <!-- Body -->
            <div class="flex-1 overflow-y-auto px-6 pb-6 pt-4">

                <!-- Loading state -->
                <div v-if="loading" class="flex flex-col items-center justify-center py-12 gap-3 text-blue-600">
                    <svg class="animate-spin h-8 w-8" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                    </svg>
                    <span class="text-sm font-medium">Analysing...</span>
                </div>

                <!-- Results state -->
                <div v-else-if="extracted.length > 0" class="space-y-4">
                    <p class="text-sm text-gray-600">
                        Found <strong>{{ extracted.length }}</strong> project{{ extracted.length === 1 ? '' : 's' }}.
                        Select the ones to add to your CV.
                    </p>

                    <div class="space-y-3">
                        <label
                            v-for="(project, idx) in extracted"
                            :key="idx"
                            class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition-colors"
                            :class="selected.has(idx) ? 'border-blue-400 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
                        >
                            <input
                                type="checkbox"
                                :checked="selected.has(idx)"
                                @change="toggleSelection(idx)"
                                class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600"
                            />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ project.employer || project.project_name || '(untitled)' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ project.start_date ?? '?' }} — {{ project.end_date ?? 'Ongoing' }}
                                    <span v-if="project.roles?.length" class="ml-2 text-gray-400">
                                        · {{ project.roles.join(', ') }}
                                    </span>
                                </p>
                                <p v-if="project.description" class="text-xs text-gray-600 mt-1 line-clamp-2">
                                    {{ project.description }}
                                </p>
                                <div v-if="project.technologies?.length" class="flex flex-wrap gap-1 mt-2">
                                    <span
                                        v-for="tech in normalizeTechnologies(project.technologies)"
                                        :key="tech"
                                        class="text-xs px-2 py-0.5 bg-gray-100 text-gray-700 rounded-full"
                                    >{{ tech }}</span>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <button @click="reset()" type="button" class="text-sm text-gray-500 hover:text-gray-700">
                            ← Start over
                        </button>
                        <button
                            @click="confirmImport"
                            :disabled="selected.size === 0"
                            type="button"
                            class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            Add to CV ({{ selected.size }})
                        </button>
                    </div>
                </div>

                <!-- Error state -->
                <div v-else-if="errorMessage" class="space-y-3">
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 whitespace-pre-wrap">
                        {{ errorMessage }}
                    </div>
                    <div class="flex justify-start">
                        <button @click="reset()" type="button" class="text-sm text-gray-500 hover:text-gray-700">
                            ← Start over
                        </button>
                    </div>
                </div>

                <!-- Input state: LinkedIn Text -->
                <div v-else-if="activeTab === 'text'" class="space-y-4">
                    <p class="text-sm text-gray-500">
                        Copy the text from the <strong>Experience</strong> section of your LinkedIn profile and paste it below.
                    </p>
                    <textarea
                        v-model="inputText"
                        rows="10"
                        placeholder="Paste here the text copied from the LinkedIn Experience section..."
                        class="w-full text-sm border border-gray-300 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    />
                    <div class="flex justify-end">
                        <button
                            @click="importText"
                            :disabled="!inputText.trim()"
                            type="button"
                            class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            Import
                        </button>
                    </div>
                </div>

                <!-- Input state: CV PDF -->
                <div v-else-if="activeTab === 'pdf'" class="space-y-4">
                    <p class="text-sm text-gray-500">
                        Upload your CV as a PDF. The AI will automatically extract your work experience.
                    </p>
                    <label class="flex flex-col items-center justify-center gap-3 border-2 border-dashed border-gray-300 rounded-xl p-8 cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-colors">
                        <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-sm text-gray-500">
                            {{ selectedFile ? selectedFile.name : 'Click to select a PDF (max 5 MB)' }}
                        </span>
                        <input type="file" accept="application/pdf" class="hidden" @change="onFileChange" />
                    </label>
                    <div class="flex justify-end">
                        <button
                            @click="importPdf"
                            :disabled="!selectedFile"
                            type="button"
                            class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            Import
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';

const emit = defineEmits(['close', 'imported']);

const tabs = [
    { key: 'text', label: 'LinkedIn Text' },
    { key: 'pdf',  label: 'CV PDF' },
];

const activeTab    = ref('text');
const inputText    = ref('');
const selectedFile = ref(null);
const loading      = ref(false);
const extracted    = ref([]);
const errorMessage = ref('');
const selected     = reactive(new Set());

function reset() {
    extracted.value    = [];
    errorMessage.value = '';
    selected.clear();
}

function toggleSelection(idx) {
    if (selected.has(idx)) {
        selected.delete(idx);
    } else {
        selected.add(idx);
    }
}

function onFileChange(e) {
    selectedFile.value = e.target.files[0] ?? null;
}

async function importText() {
    if (!inputText.value.trim()) return;
    loading.value = true;
    errorMessage.value = '';
    try {
        const { data } = await axios.post(route('cv.ai-import.text'), {
            text: inputText.value,
        });
        extracted.value = data.projects ?? [];
        extracted.value.forEach((_, idx) => selected.add(idx));
    } catch (err) {
        const data = err.response?.data;
        errorMessage.value = data?.error
            ?? data?.message
            ?? `HTTP error ${err.response?.status ?? 'network error'}. Check the console.`;
        console.error('[AiImport] fromText error:', err.response?.status, data);
    } finally {
        loading.value = false;
    }
}

async function importPdf() {
    if (!selectedFile.value) return;
    loading.value = true;
    errorMessage.value = '';
    const formData = new FormData();
    formData.append('file', selectedFile.value);
    try {
        const { data } = await axios.post(route('cv.ai-import.pdf'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        extracted.value = data.projects ?? [];
        extracted.value.forEach((_, idx) => selected.add(idx));
    } catch (err) {
        const data = err.response?.data;
        errorMessage.value = data?.error
            ?? data?.message
            ?? `HTTP error ${err.response?.status ?? 'network error'}. Check the console.`;
        console.error('[AiImport] fromPdf error:', err.response?.status, data);
    } finally {
        loading.value = false;
    }
}

function normalizeTechnologies(technologies) {
    if (!Array.isArray(technologies)) return [];
    return technologies.map(t => (typeof t === 'string' ? t : t.technology_name)).filter(Boolean);
}

function confirmImport() {
    const toImport = [...selected].map(idx => {
        const p = extracted.value[idx];
        // Normalize technologies to the format expected by storeProject
        const normalizedTechs = normalizeTechnologies(p.technologies ?? []).map(name => ({
            technology_name: name,
            competence: null,
        }));
        return { ...p, technologies: normalizedTechs };
    });
    emit('imported', toImport);
}
</script>
