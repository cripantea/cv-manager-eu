<template>
    <div class="p-6 text-sm text-gray-800 space-y-6 font-sans">

        <!-- CV Front Page -->
        <section>
            <h3 class="text-xs font-bold uppercase tracking-widest text-blue-700 mb-3 border-b border-blue-100 pb-1">
                CV Front Page
            </h3>
            <table class="w-full text-xs border-collapse">
                <tbody>
                    <tr v-for="row in personalRows" :key="row.label" class="border-b border-gray-100">
                        <td class="py-1.5 pr-4 font-semibold text-gray-500 whitespace-nowrap w-40">{{ row.label }}</td>
                        <td class="py-1.5 text-gray-800">{{ row.value || '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Profile Summary -->
        <section v-if="cv.profile_summary">
            <h3 class="text-xs font-bold uppercase tracking-widest text-blue-700 mb-2 border-b border-blue-100 pb-1">
                Profile Summary
            </h3>
            <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-line">{{ cv.profile_summary }}</p>
        </section>

        <!-- Languages -->
        <section v-if="cv.languages?.length">
            <h3 class="text-xs font-bold uppercase tracking-widest text-blue-700 mb-2 border-b border-blue-100 pb-1">
                Languages
            </h3>
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="text-left py-1 pr-3 font-semibold text-gray-500">Language</th>
                        <th class="text-left py-1 pr-3 font-semibold text-gray-500">Spoken</th>
                        <th class="text-left py-1 pr-3 font-semibold text-gray-500">Written</th>
                        <th class="text-left py-1 font-semibold text-gray-500">Certificate</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="lang in cv.languages" :key="lang.name" class="border-b border-gray-100">
                        <td class="py-1 pr-3 font-medium">{{ lang.name }}</td>
                        <td class="py-1 pr-3">{{ lang.spoken }}</td>
                        <td class="py-1 pr-3">{{ lang.written }}</td>
                        <td class="py-1">{{ lang.certificate || '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Specific Expertises -->
        <section v-if="sorted.length">
            <h3 class="text-xs font-bold uppercase tracking-widest text-blue-700 mb-2 border-b border-blue-100 pb-1">
                Specific Expertises
            </h3>
            <p class="text-xs text-gray-700 leading-relaxed">{{ formattedExpertise }}</p>
        </section>

        <!-- Education -->
        <section v-if="cv.educations?.length">
            <h3 class="text-xs font-bold uppercase tracking-widest text-blue-700 mb-2 border-b border-blue-100 pb-1">
                Education
            </h3>
            <table class="w-full text-xs border-collapse">
                <tbody>
                    <tr v-for="edu in cv.educations" :key="edu.id ?? edu.certificate_diploma" class="border-b border-gray-100">
                        <td class="py-1.5 pr-4 font-medium text-gray-800 w-1/2">{{ edu.certificate_diploma }}</td>
                        <td class="py-1.5 pr-4 text-gray-600">{{ edu.institute }}</td>
                        <td class="py-1.5 text-gray-500 whitespace-nowrap">{{ edu.end_date || '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Training -->
        <section v-if="cv.trainings?.length">
            <h3 class="text-xs font-bold uppercase tracking-widest text-blue-700 mb-2 border-b border-blue-100 pb-1">
                Training
            </h3>
            <table class="w-full text-xs border-collapse">
                <tbody>
                    <tr v-for="tr in cv.trainings" :key="tr.id ?? tr.training_name" class="border-b border-gray-100">
                        <td class="py-1.5 pr-4 font-medium text-gray-800">{{ tr.training_name }}</td>
                        <td class="py-1.5 pr-4 text-gray-600">{{ tr.company_institute }}</td>
                        <td class="py-1.5 pr-4 text-gray-500">{{ tr.date_followed || '—' }}</td>
                        <td class="py-1.5 text-gray-500">{{ tr.certificate_obtained || '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Projects -->
        <section v-if="cv.projects?.length">
            <h3 class="text-xs font-bold uppercase tracking-widest text-blue-700 mb-3 border-b border-blue-100 pb-1">
                Project Experience
            </h3>
            <div class="space-y-4">
                <div
                    v-for="project in cv.projects"
                    :key="project.id ?? project.project_name"
                    class="border border-gray-200 rounded-lg p-3 space-y-2"
                >
                    <div class="flex items-start justify-between gap-2">
                        <p class="font-semibold text-gray-900 text-xs">{{ project.project_name || '(senza titolo)' }}</p>
                        <span v-if="project.project_size" class="text-xs px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded font-mono flex-shrink-0">
                            {{ project.project_size }}
                        </span>
                    </div>

                    <table class="w-full text-xs border-collapse">
                        <tbody>
                            <tr v-if="project.employer">
                                <td class="pr-3 text-gray-500 w-24">Employer</td>
                                <td class="text-gray-700">{{ project.employer }}</td>
                            </tr>
                            <tr v-if="project.client">
                                <td class="pr-3 text-gray-500">Client</td>
                                <td class="text-gray-700">{{ project.client }}</td>
                            </tr>
                            <tr>
                                <td class="pr-3 text-gray-500">Period</td>
                                <td class="text-gray-700">
                                    {{ formatDate(project.start_date) }} —
                                    {{ project.end_date ? formatDate(project.end_date) : 'Ongoing' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p v-if="project.description" class="text-xs text-gray-600 leading-relaxed">
                        {{ project.description }}
                    </p>

                    <div v-if="project.roles?.length" class="text-xs">
                        <span class="font-semibold text-gray-500">Roles: </span>
                        <span class="text-gray-700">{{ project.roles.join(', ') }}</span>
                    </div>

                    <div v-if="project.technologies?.length" class="flex flex-wrap gap-1">
                        <span
                            v-for="tech in project.technologies"
                            :key="tech.technology_name"
                            class="text-xs px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded border border-blue-100"
                        >
                            {{ tech.technology_name }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <p v-if="isEmpty" class="text-xs text-gray-400 italic text-center py-8">
            Compila il form a sinistra per visualizzare la preview del documento.
        </p>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useExpertiseCalc } from '@/Composables/useExpertiseCalc.js';

const props = defineProps({
    cv: { type: Object, required: true },
});

const educationLevelLabel = { master: 'Master / Laurea Magistrale', bachelor: 'Laurea Triennale', secondary: 'Diploma' };
const contractTypeLabel   = { permanent: 'Permanent', 'non-permanent': 'Non-permanent', freelancer: 'Freelancer' };

const personalRows = computed(() => [
    { label: 'Nome',             value: [props.cv.first_name, props.cv.last_name].filter(Boolean).join(' ') },
    { label: 'Data di nascita',  value: formatDate(props.cv.birth_date) },
    { label: 'Nazionalità',      value: props.cv.nationality },
    { label: 'Funzione attuale', value: props.cv.current_function },
    { label: 'Profilo proposto', value: props.cv.profile_for },
    { label: 'Livello proposto', value: props.cv.proposed_level },
    { label: 'Disponibile dal',  value: formatDate(props.cv.date_available) },
    { label: 'Titolo di studio', value: educationLevelLabel[props.cv.education_level] },
    { label: 'Anni post-diploma',value: props.cv.years_after_secondary },
    { label: 'Inizio carriera IT',value: formatDate(props.cv.it_career_start) },
    { label: 'Tipo contratto',   value: contractTypeLabel[props.cv.contract_type] },
]);

const projectsRef = computed(() => props.cv.projects ?? []);
const { expertise } = useExpertiseCalc(projectsRef);

const sorted = computed(() =>
    Object.entries(expertise.value).sort(([, a], [, b]) => b - a),
);

const formattedExpertise = computed(() =>
    sorted.value.map(([tech, months]) => {
        const label = tech.charAt(0).toUpperCase() + tech.slice(1);
        return `${label}: ${months} month${months !== 1 ? 's' : ''}`;
    }).join('; '),
);

const isEmpty = computed(() =>
    !props.cv.first_name &&
    !props.cv.last_name &&
    !props.cv.projects?.length &&
    !props.cv.educations?.length,
);

function formatDate(dateStr) {
    if (!dateStr) return null;
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('it-IT', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
</script>
