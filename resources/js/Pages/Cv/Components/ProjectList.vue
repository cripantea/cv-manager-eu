<template>
    <div class="space-y-4">
        <!-- Show form when adding/editing -->
        <ProjectForm
            v-if="editingProject !== undefined"
            :project="editingProject"
            :cv-id="cvId"
            @saved="onSaved"
            @cancel="editingProject = undefined"
        />

        <template v-else>
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900">Progetti ({{ projects.length }})</h2>
                <div class="flex gap-2">
                    <button
                        v-if="!disabled"
                        @click="showAiModal = true"
                        type="button"
                        class="text-xs px-3 py-1.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
                    >
                        ⚡ Importa da LinkedIn / PDF
                    </button>
                    <button
                        v-if="!disabled"
                        @click="editingProject = null"
                        type="button"
                        class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        + Nuovo Progetto
                    </button>
                </div>
            </div>

            <!-- AI Import Modal -->
            <AiImportModal
                v-if="showAiModal"
                @close="showAiModal = false"
                @imported="onAiImported"
            />

            <div v-if="projects.length" class="space-y-3">
                <div
                    v-for="project in projects"
                    :key="project.id"
                    class="p-4 bg-gray-50 border border-gray-200 rounded-xl hover:border-gray-300 transition-colors"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                {{ project.project_name || '(senza titolo)' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ formatDate(project.start_date) }} —
                                {{ project.end_date ? formatDate(project.end_date) : 'In corso' }}
                                <span v-if="project.employer" class="ml-2 text-gray-400">· {{ project.employer }}</span>
                            </p>
                            <!-- Technology badges -->
                            <div v-if="project.technologies?.length" class="flex flex-wrap gap-1 mt-2">
                                <span
                                    v-for="tech in project.technologies"
                                    :key="tech.id ?? tech.technology_name"
                                    class="text-xs px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full font-medium"
                                >
                                    {{ tech.technology_name }}
                                    <span v-if="tech.competence" class="text-blue-500">({{ tech.competence }}/5)</span>
                                </span>
                            </div>
                        </div>

                        <div class="flex gap-2 flex-shrink-0">
                            <button
                                v-if="!disabled"
                                @click="editingProject = project"
                                type="button"
                                class="text-xs px-3 py-1.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                Modifica
                            </button>
                            <button
                                v-if="!disabled"
                                @click="deleteProject(project)"
                                type="button"
                                class="text-xs px-3 py-1.5 bg-white border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition-colors"
                            >
                                Elimina
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <p v-else class="text-sm text-gray-400 italic">Nessun progetto aggiunto. Clicca "+ Nuovo Progetto" per iniziare.</p>
        </template>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import ProjectForm from './ProjectForm.vue';
import AiImportModal from './AiImportModal.vue';

const props = defineProps({
    projects: { type: Array, default: () => [] },
    cvId:     Number,
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['project-saved', 'project-deleted']);

// undefined = list view, null = new project form, object = edit project form
const editingProject = ref(undefined);
const showAiModal    = ref(false);
const importing      = ref(false);

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('it-IT', { month: '2-digit', year: 'numeric' });
}

function onSaved(project) {
    emit('project-saved', project);
    editingProject.value = undefined;
}

async function onAiImported(projects) {
    showAiModal.value = false;
    importing.value = true;
    for (const project of projects) {
        try {
            const { data } = await axios.post(route('cv.projects.store'), {
                ...project,
                start_date: project.start_date ?? new Date().toISOString().slice(0, 10),
            });
            emit('project-saved', data);
        } catch {
            // continue importing remaining projects even if one fails
        }
    }
    importing.value = false;
}

async function deleteProject(project) {
    if (!confirm(`Eliminare il progetto "${project.project_name || '(senza titolo)'}"?`)) return;
    try {
        await axios.delete(route('cv.projects.destroy', project.id));
        emit('project-deleted', project.id);
    } catch {
        alert('Errore durante l\'eliminazione. Riprova.');
    }
}
</script>
