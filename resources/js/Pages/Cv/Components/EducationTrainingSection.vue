<template>
    <div class="space-y-8">
        <!-- Educations -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-semibold text-gray-900">Education</h2>
                <button v-if="!disabled" @click="addEducation" type="button" class="btn-add">
                    + Add
                </button>
            </div>

            <div v-if="form.educations?.length" class="space-y-3">
                <div
                    v-for="(edu, idx) in form.educations"
                    :key="idx"
                    class="grid grid-cols-12 gap-2 items-end p-3 bg-gray-50 rounded-lg border border-gray-200"
                >
                    <div class="col-span-5">
                        <label class="block text-xs text-gray-500 mb-1">Diploma / Certificate</label>
                        <input v-model="edu.certificate_diploma" :disabled="disabled" type="text" class="field" />
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs text-gray-500 mb-1">Institution</label>
                        <input v-model="edu.institute" :disabled="disabled" type="text" class="field" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Start (mm/yy)</label>
                        <input v-model="edu.start_date" :disabled="disabled" type="text" placeholder="09/19" class="field" />
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs text-gray-500 mb-1">End (mm/yy)</label>
                        <input v-model="edu.end_date" :disabled="disabled" type="text" placeholder="06/23" class="field" />
                    </div>
                    <div class="col-span-1 flex justify-end items-end">
                        <button v-if="!disabled" @click="removeEducation(idx)" type="button" class="remove-btn">×</button>
                    </div>
                </div>
            </div>
            <p v-else class="text-sm text-gray-400 italic">No education entries added.</p>
        </div>

        <!-- Trainings -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-semibold text-gray-900">Courses & Training</h2>
                <button v-if="!disabled" @click="addTraining" type="button" class="btn-add">
                    + Add
                </button>
            </div>

            <div v-if="form.trainings?.length" class="space-y-3">
                <div
                    v-for="(tr, idx) in form.trainings"
                    :key="idx"
                    class="grid grid-cols-12 gap-2 items-end p-3 bg-gray-50 rounded-lg border border-gray-200"
                >
                    <div class="col-span-4">
                        <label class="block text-xs text-gray-500 mb-1">Course Name</label>
                        <input v-model="tr.training_name" :disabled="disabled" type="text" class="field" />
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs text-gray-500 mb-1">Organisation / Company</label>
                        <input v-model="tr.company_institute" :disabled="disabled" type="text" class="field" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Date</label>
                        <input v-model="tr.date_followed" :disabled="disabled" type="text" placeholder="e.g. 2022" class="field" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Certificate</label>
                        <input v-model="tr.certificate_obtained" :disabled="disabled" type="text" class="field" />
                    </div>
                    <div class="col-span-1 flex justify-end">
                        <button v-if="!disabled" @click="removeTraining(idx)" type="button" class="remove-btn">×</button>
                    </div>
                </div>
            </div>
            <p v-else class="text-sm text-gray-400 italic">No courses added.</p>
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

const form = reactive({
    educations: [...(props.modelValue.educations ?? [])],
    trainings:  [...(props.modelValue.trainings ?? [])],
});

watch(form, () => {
    emit('update:modelValue', { ...props.modelValue, ...form });
}, { deep: true });

function addEducation() {
    form.educations.push({ certificate_diploma: '', institute: '', start_date: '', end_date: '', order: form.educations.length });
}
function removeEducation(idx) { form.educations.splice(idx, 1); }

function addTraining() {
    form.trainings.push({ training_name: '', company_institute: '', date_followed: '', certificate_obtained: '', order: form.trainings.length });
}
function removeTraining(idx) { form.trainings.splice(idx, 1); }
</script>

<style scoped>
.field {
    @apply w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm
           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
           disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed;
}
.btn-add {
    @apply text-xs px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors;
}
.remove-btn {
    @apply text-red-400 hover:text-red-600 text-lg leading-none;
}
</style>
