<template>
    <AppLayout>
        <!-- Auto-save status bar -->
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold text-gray-900">Il tuo CV — DIGIT-TM II</h1>
            <div class="flex items-center gap-2 text-sm">
                <span v-if="saveStatus === 'saved'" class="text-green-600 font-medium">
                    ✓ Salvato alle {{ lastSavedAt ?? '—' }}
                </span>
                <span v-else-if="saveStatus === 'saving'" class="text-blue-500 font-medium animate-pulse">
                    Salvataggio...
                </span>
                <span v-else-if="saveStatus === 'pending'" class="text-gray-400 font-medium">
                    In attesa...
                </span>
                <span v-else-if="saveStatus === 'error'" class="text-orange-500 font-medium" :title="saveErrorMessage">
                    ⚠ Errore validazione
                </span>
                <span v-else-if="saveStatus === 'offline'" class="text-red-500 font-medium">
                    ⚠ Offline — dati locali
                </span>
            </div>
        </div>

        <!-- Locked banner -->
        <div v-if="isLocked" class="mb-4 px-4 py-3 bg-yellow-50 border border-yellow-300 rounded-lg text-yellow-800 text-sm font-medium">
            CV bloccato — solo lettura. Contatta l'amministratore per richiedere modifiche.
        </div>

        <!-- Draft recovery banner -->
        <div v-if="showRecovery" class="mb-4 px-4 py-3 bg-blue-50 border border-blue-300 rounded-lg text-blue-800 text-sm flex items-center justify-between">
            <span>Trovata una bozza locale più recente. Vuoi ripristinarla?</span>
            <div class="flex gap-2">
                <button @click="recoverDraft" class="text-xs px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Ripristina</button>
                <button @click="showRecovery = false" class="text-xs px-3 py-1 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Ignora</button>
            </div>
        </div>

        <!-- Two-column layout -->
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Left column: form -->
            <div class="w-full lg:w-1/2">
                <!-- Tab navigation -->
                <div class="flex gap-1 mb-4 bg-gray-100 p-1 rounded-lg overflow-x-auto">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="[
                            'flex-shrink-0 px-4 py-2 text-sm font-medium rounded-md transition-colors',
                            activeTab === tab.key
                                ? 'bg-white text-blue-700 shadow-sm'
                                : 'text-gray-500 hover:text-gray-700',
                        ]"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <!-- Tab content -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <PersonalDataForm
                        v-if="activeTab === 'personal'"
                        v-model="cvData"
                        :disabled="isLocked"
                    />
                    <EducationTrainingSection
                        v-else-if="activeTab === 'education'"
                        v-model="cvData"
                        :disabled="isLocked"
                    />
                    <ProjectList
                        v-else-if="activeTab === 'projects'"
                        :cv-id="cv.id"
                        :projects="cvData.projects"
                        :disabled="isLocked"
                        @project-saved="onProjectSaved"
                        @project-deleted="onProjectDeleted"
                    />
                    <ExpertiseSummary
                        v-else-if="activeTab === 'expertise'"
                        :projects="cvData.projects"
                    />
                </div>
            </div>

            <!-- Right column: live preview -->
            <div class="w-full lg:w-1/2 lg:sticky lg:top-4 lg:self-start lg:max-h-[calc(100vh-6rem)] lg:overflow-y-auto">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-3 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Preview — DIGIT-TM II</span>
                    </div>
                    <CvPreview :cv="cvData" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PersonalDataForm from './Components/PersonalDataForm.vue';
import ProjectList from './Components/ProjectList.vue';
import ExpertiseSummary from './Components/ExpertiseSummary.vue';
import CvPreview from './Components/CvPreview.vue';
import { useAutoSave } from '@/Composables/useAutoSave.js';

const props = defineProps({
    cv: Object,
});

// Inline education/training form — simple enough to not need a separate component
import EducationTrainingSection from './Components/EducationTrainingSection.vue';

const tabs = [
    { key: 'personal',   label: 'Dati Personali' },
    { key: 'education',  label: 'Formazione' },
    { key: 'projects',   label: 'Progetti' },
    { key: 'expertise',  label: 'Expertise' },
];

const activeTab = ref('personal');

const isLocked = computed(() => props.cv.status === 'locked');

// Reactive copy of cv data (projects/educations/trainings included)
const cvData = ref({ ...props.cv });

const { saveStatus, lastSavedAt, saveErrorMessage } = useAutoSave(
    cvData,
    route('cv.update'),
    props.cv.id,
);

// Draft recovery
const showRecovery = ref(false);
const recoveryKey = `cv_draft_${props.cv.id}`;

onMounted(() => {
    window.addEventListener('draft-recovery-available', () => {
        showRecovery.value = true;
    });
});

function recoverDraft() {
    try {
        const stored = localStorage.getItem(recoveryKey);
        if (stored) {
            cvData.value = JSON.parse(stored).data;
        }
    } catch { /* ignore */ }
    showRecovery.value = false;
}

function onProjectSaved(project) {
    const idx = cvData.value.projects.findIndex(p => p.id === project.id);
    if (idx !== -1) {
        cvData.value.projects[idx] = project;
    } else {
        cvData.value.projects.push(project);
    }
}

function onProjectDeleted(projectId) {
    cvData.value.projects = cvData.value.projects.filter(p => p.id !== projectId);
}
</script>
