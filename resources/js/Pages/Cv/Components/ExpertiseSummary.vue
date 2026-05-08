<template>
    <div class="space-y-5">
        <h2 class="text-base font-semibold text-gray-900">Specific Expertise</h2>

        <div v-if="sorted.length">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider pb-2">Technology</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider pb-2">Level</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wider pb-2">Months</th>
                        <th class="w-40 pb-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="[tech, data] in sorted" :key="tech" class="py-1">
                        <td class="py-2 text-gray-800 font-medium capitalize">{{ tech }}</td>
                        <td class="py-2 text-center">
                            <span v-if="data.competence" class="inline-flex gap-0.5">
                                <span
                                    v-for="n in 5"
                                    :key="n"
                                    :class="n <= data.competence ? 'text-blue-500' : 'text-gray-200'"
                                    class="text-base leading-none"
                                >●</span>
                            </span>
                            <span v-else class="text-xs text-gray-300">—</span>
                        </td>
                        <td class="py-2 text-right text-gray-600 font-mono">{{ data.months }}</td>
                        <td class="py-2 pl-4">
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-blue-500 rounded-full"
                                    :style="{ width: barWidth(data.months) }"
                                />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- DIGIT-TM II formatted string -->
            <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">DIGIT-TM II Format</p>
                <p class="text-sm text-gray-700 leading-relaxed">{{ formattedString }}</p>
            </div>
        </div>

        <p v-else class="text-sm text-gray-400 italic">
            No data. Add projects with technologies to display your expertise.
        </p>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useExpertiseCalc } from '@/Composables/useExpertiseCalc.js';

const props = defineProps({
    projects: { type: Array, default: () => [] },
});

const projectsRef = computed(() => props.projects);
const { expertise } = useExpertiseCalc(projectsRef);

const sorted = computed(() =>
    Object.entries(expertise.value).sort(([, a], [, b]) => b.months - a.months),
);

const maxMonths = computed(() => sorted.value[0]?.[1]?.months ?? 1);

function barWidth(months) {
    return `${Math.round((months / maxMonths.value) * 100)}%`;
}

const formattedString = computed(() =>
    sorted.value.map(([tech, data]) => {
        const label = tech.charAt(0).toUpperCase() + tech.slice(1);
        return `${label}: ${data.months} month${data.months !== 1 ? 's' : ''}`;
    }).join('; '),
);
</script>
