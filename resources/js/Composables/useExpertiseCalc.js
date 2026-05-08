import { computed } from 'vue';

export function useExpertiseCalc(projects) {
    const expertise = computed(() => {
        const intervals   = {};
        const competences = {};

        for (const project of projects.value ?? []) {
            if (!project.start_date) continue;

            const startDate = new Date(project.start_date);
            startDate.setDate(1);
            startDate.setHours(0, 0, 0, 0);

            const endDate = project.end_date ? new Date(project.end_date) : new Date();
            endDate.setDate(1);
            endDate.setHours(0, 0, 0, 0);

            if (startDate >= endDate) continue;

            const start = startDate.getTime();
            const end   = endDate.getTime();

            for (const tech of project.technologies ?? []) {
                const key = tech.technology_name.trim().toLowerCase();

                if (!intervals[key]) intervals[key] = [];
                intervals[key].push([start, end]);

                // Keep the highest competence rating seen for this technology
                if (tech.competence != null) {
                    competences[key] = Math.max(competences[key] ?? 0, tech.competence);
                }
            }
        }

        const result = {};

        for (const [tech, periods] of Object.entries(intervals)) {
            periods.sort((a, b) => a[0] - b[0]);

            const merged = [[...periods[0]]];
            for (let i = 1; i < periods.length; i++) {
                const [s, e] = periods[i];
                const last = merged[merged.length - 1];
                if (s <= last[1]) {
                    last[1] = Math.max(last[1], e);
                } else {
                    merged.push([s, e]);
                }
            }

            let totalMonths = 0;
            for (const [s, e] of merged) {
                const sd = new Date(s);
                const ed = new Date(e);
                totalMonths += (ed.getFullYear() - sd.getFullYear()) * 12 +
                               (ed.getMonth()    - sd.getMonth());
            }

            result[tech] = {
                months:     totalMonths,
                competence: competences[tech] ?? null,
            };
        }

        return result;
    });

    return { expertise };
}
