<template>
    <div class="space-y-5">
        <h3 class="text-base font-semibold text-gray-900">
            {{ form.id ? 'Modifica Progetto' : 'Nuovo Progetto' }}
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="field-label">Nome progetto</label>
                <input v-model="form.project_name" type="text" class="field" />
            </div>
            <div>
                <label class="field-label">Employer</label>
                <input v-model="form.employer" type="text" class="field" />
            </div>
            <div>
                <label class="field-label">Cliente</label>
                <input v-model="form.client" type="text" class="field" />
            </div>
            <div>
                <label class="field-label">Data inizio *</label>
                <input v-model="form.start_date" type="date" class="field" required />
            </div>
            <div>
                <label class="field-label">Data fine</label>
                <div class="flex items-center gap-2">
                    <input
                        v-model="form.end_date"
                        type="date"
                        class="field"
                        :disabled="ongoing"
                    />
                    <label class="flex items-center gap-1 text-xs text-gray-600 whitespace-nowrap cursor-pointer">
                        <input v-model="ongoing" type="checkbox" class="rounded" />
                        In corso
                    </label>
                </div>
            </div>
            <div>
                <label class="field-label">Dimensione progetto</label>
                <select v-model="form.project_size" class="field">
                    <option value="">— seleziona —</option>
                    <option value="S">S — Small</option>
                    <option value="M">M — Medium</option>
                    <option value="L">L — Large</option>
                    <option value="XL">XL — Extra Large</option>
                </select>
            </div>
        </div>

        <div>
            <label class="field-label">Descrizione</label>
            <textarea v-model="form.description" rows="3" class="field resize-none" />
        </div>

        <!-- Roles -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="field-label mb-0">Ruoli ricoperti</label>
                <button @click="addRole" type="button" class="btn-add">+ Aggiungi ruolo</button>
            </div>
            <div class="space-y-2">
                <div v-for="(role, idx) in form.roles" :key="idx" class="flex gap-2">
                    <input v-model="form.roles[idx]" type="text" placeholder="es. Senior Java Developer" class="field" />
                    <button @click="form.roles.splice(idx, 1)" type="button" class="remove-btn">×</button>
                </div>
            </div>
            <p v-if="!form.roles?.length" class="text-sm text-gray-400 italic">Nessun ruolo aggiunto.</p>
        </div>

        <!-- Responsibilities -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="field-label mb-0">Responsabilità</label>
                <button @click="addResponsibility" type="button" class="btn-add">+ Aggiungi</button>
            </div>
            <div class="space-y-2">
                <div v-for="(item, idx) in form.responsibilities" :key="idx" class="flex gap-2">
                    <input v-model="form.responsibilities[idx]" type="text" placeholder="es. Coordinamento team di 5 sviluppatori" class="field" />
                    <button @click="form.responsibilities.splice(idx, 1)" type="button" class="remove-btn">×</button>
                </div>
            </div>
            <p v-if="!form.responsibilities?.length" class="text-sm text-gray-400 italic">Nessuna responsabilità aggiunta.</p>
        </div>

        <!-- Technologies -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="field-label mb-0">Tecnologie</label>
                <button @click="addTechnology" type="button" class="btn-add">+ Aggiungi tecnologia</button>
            </div>
            <div class="space-y-2">
                <div
                    v-for="(tech, idx) in form.technologies"
                    :key="idx"
                    class="flex gap-2 items-center"
                >
                    <input v-model="tech.technology_name" type="text" placeholder="es. Vue.js" class="field" />
                    <select v-model.number="tech.competence" class="field w-28">
                        <option :value="null">—</option>
                        <option v-for="n in 5" :key="n" :value="n">{{ n }} / 5</option>
                    </select>
                    <button @click="form.technologies.splice(idx, 1)" type="button" class="remove-btn">×</button>
                </div>
            </div>
            <p v-if="!form.technologies?.length" class="text-sm text-gray-400 italic">Nessuna tecnologia aggiunta.</p>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <button @click="$emit('cancel')" type="button" class="btn-secondary">Annulla</button>
            <button @click="submit" type="button" class="btn-primary">Salva progetto</button>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    project: { type: Object, default: null },
    cvId: Number,
});

const emit = defineEmits(['saved', 'cancel']);

const ongoing = ref(!props.project?.end_date);

const form = reactive({
    id:               props.project?.id ?? null,
    project_name:     props.project?.project_name ?? '',
    employer:         props.project?.employer ?? '',
    client:           props.project?.client ?? '',
    start_date:       props.project?.start_date ?? '',
    end_date:         props.project?.end_date ?? '',
    project_size:     props.project?.project_size ?? '',
    description:      props.project?.description ?? '',
    roles:            [...(props.project?.roles ?? [])],
    responsibilities: [...(props.project?.responsibilities ?? [])],
    technologies:     (props.project?.technologies ?? []).map(t => ({ ...t })),
});

watch(ongoing, (val) => {
    if (val) form.end_date = '';
});

function addRole()           { form.roles.push(''); }
function addResponsibility() { form.responsibilities.push(''); }
function addTechnology()     { form.technologies.push({ technology_name: '', competence: null }); }

async function submit() {
    const payload = { ...form, end_date: ongoing.value ? null : form.end_date };

    try {
        let response;
        if (form.id) {
            response = await axios.put(route('cv.projects.update', form.id), payload);
        } else {
            response = await axios.post(route('cv.projects.store'), payload);
        }
        emit('saved', response.data);
    } catch (e) {
        alert('Errore nel salvataggio del progetto. Riprova.');
    }
}
</script>

<style scoped>
.field       { @apply w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500; }
.field-label { @apply block text-xs font-medium text-gray-600 mb-1; }
.btn-add     { @apply text-xs px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors; }
.btn-primary { @apply px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors; }
.btn-secondary { @apply px-5 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors; }
.remove-btn  { @apply text-red-400 hover:text-red-600 text-xl leading-none flex-shrink-0; }
</style>
