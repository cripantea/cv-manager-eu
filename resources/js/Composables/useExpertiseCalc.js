import { computed } from 'vue';

export function useExpertiseCalc(projects) {
    const expertise = computed(() => {
        const result = {};

        for (const project of projects.value ?? []) {
            const start = new Date(project.start_date);
            const end = project.end_date ? new Date(project.end_date) : new Date();

            const months =
                (end.getFullYear() - start.getFullYear()) * 12 +
                (end.getMonth() - start.getMonth());

            const effectiveMonths = Math.max(0, Math.floor(months));

            for (const tech of project.technologies ?? []) {
                const key = tech.technology_name.trim().toLowerCase();
                result[key] = (result[key] ?? 0) + effectiveMonths;
            }
        }

        return result;
    });

    return { expertise };
}
