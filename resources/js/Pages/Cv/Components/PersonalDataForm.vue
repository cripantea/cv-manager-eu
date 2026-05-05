<template>
    <div class="space-y-6">
        <h2 class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-2">Dati Personali</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nome *</label>
                <input v-model="form.first_name" :disabled="disabled" type="text" class="field" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Cognome *</label>
                <input v-model="form.last_name" :disabled="disabled" type="text" class="field" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data di nascita</label>
                <input v-model="form.birth_date" :disabled="disabled" type="date" class="field" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nazionalità</label>
                <input v-model="form.nationality" :disabled="disabled" type="text" class="field" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Funzione attuale</label>
                <input v-model="form.current_function" :disabled="disabled" type="text" class="field" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Profilo proposto (ruolo EU)</label>
                <input v-model="form.profile_for" :disabled="disabled" type="text" class="field" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Disponibile dal</label>
                <input v-model="form.date_available" :disabled="disabled" type="date" class="field" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Livello proposto</label>
                <input v-model="form.proposed_level" :disabled="disabled" type="text" placeholder="es. Level 6" class="field" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Titolo di studio</label>
                <select v-model="form.education_level" :disabled="disabled" class="field">
                    <option value="">— seleziona —</option>
                    <option value="master">Master / Laurea Magistrale</option>
                    <option value="bachelor">Laurea Triennale</option>
                    <option value="secondary">Diploma</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Anni dopo diploma</label>
                <input v-model.number="form.years_after_secondary" :disabled="disabled" type="number" min="0" class="field" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Inizio carriera IT</label>
                <input v-model="form.it_career_start" :disabled="disabled" type="date" class="field" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipo contratto</label>
                <select v-model="form.contract_type" :disabled="disabled" class="field">
                    <option value="">— seleziona —</option>
                    <option value="permanent">Permanent</option>
                    <option value="non-permanent">Non-permanent</option>
                    <option value="freelancer">Freelancer</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Profilo professionale</label>
            <textarea
                v-model="form.profile_summary"
                :disabled="disabled"
                rows="4"
                placeholder="Breve descrizione del profilo professionale..."
                class="field resize-none"
            />
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Standard / Certificati</label>
            <textarea
                v-model="form.standards_certificates"
                :disabled="disabled"
                rows="2"
                placeholder="es. ISO 27001, PMP, ITIL..."
                class="field resize-none"
            />
        </div>

        <!-- Languages -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-800">Lingue</h3>
                <button
                    v-if="!disabled"
                    @click="addLanguage"
                    type="button"
                    class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    + Aggiungi lingua
                </button>
            </div>

            <div v-if="form.languages?.length" class="space-y-3">
                <div
                    v-for="(lang, idx) in form.languages"
                    :key="idx"
                    class="grid grid-cols-12 gap-2 items-end p-3 bg-gray-50 rounded-lg border border-gray-200"
                >
                    <div class="col-span-3">
                        <label class="block text-xs text-gray-500 mb-1">Lingua</label>
                        <input v-model="lang.name" :disabled="disabled" type="text" placeholder="Italiano" class="field text-sm" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Parlato</label>
                        <select v-model="lang.spoken" :disabled="disabled" class="field text-sm">
                            <option v-for="n in levels" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Scritto</label>
                        <select v-model="lang.written" :disabled="disabled" class="field text-sm">
                            <option v-for="n in levels" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>
                    <div class="col-span-4">
                        <label class="block text-xs text-gray-500 mb-1">Certificato</label>
                        <input v-model="lang.certificate" :disabled="disabled" type="text" placeholder="es. B2, DELF..." class="field text-sm" />
                    </div>
                    <div class="col-span-1 flex justify-end">
                        <button
                            v-if="!disabled"
                            @click="removeLanguage(idx)"
                            type="button"
                            class="text-red-400 hover:text-red-600 text-lg leading-none"
                            title="Rimuovi"
                        >×</button>
                    </div>
                </div>
            </div>
            <p v-else class="text-sm text-gray-400 italic">Nessuna lingua aggiunta.</p>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch } from 'vue';

const props = defineProps({
    modelValue: Object,
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const levels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'Mother tongue'];

const form = reactive({ ...props.modelValue, languages: [...(props.modelValue.languages ?? [])] });

watch(form, (val) => {
    emit('update:modelValue', { ...props.modelValue, ...val });
}, { deep: true });

function addLanguage() {
    form.languages.push({ name: '', spoken: 'B1', written: 'B1', certificate: '' });
}

function removeLanguage(idx) {
    form.languages.splice(idx, 1);
}
</script>

<style scoped>
.field {
    @apply w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm
           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
           disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed;
}
</style>
