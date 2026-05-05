import { ref, watch, onMounted } from 'vue';
import axios from 'axios';

export function useAutoSave(cvData, url, cvId) {
    const saveStatus = ref('saved');
    const lastSavedAt = ref(null);
    const saveErrorMessage = ref(null);

    let debounceTimer = null;

    const localStorageKey = `cv_draft_${cvId}`;

    function saveToLocalStorage() {
        try {
            localStorage.setItem(localStorageKey, JSON.stringify({
                data: cvData.value,
                savedAt: new Date().toISOString(),
            }));
        } catch {
            // quota exceeded or private mode — silently ignore
        }
    }

    async function persist() {
        saveStatus.value = 'saving';
        try {
            const res = await axios.put(url, cvData.value);
            lastSavedAt.value = res.data?.saved_at
                ?? new Date().toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            saveStatus.value = 'saved';
        } catch (err) {
            saveStatus.value = err?.response?.status === 422 ? 'error' : 'offline';
            saveErrorMessage.value = err?.response?.data?.message ?? null;
        }
    }

    watch(
        cvData,
        () => {
            saveStatus.value = 'pending';
            saveToLocalStorage();

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(persist, 2000);
        },
        { deep: true },
    );

    onMounted(() => {
        try {
            const stored = localStorage.getItem(localStorageKey);
            if (!stored) return;

            const { savedAt } = JSON.parse(stored);
            const serverUpdatedAt = cvData.value?.updated_at;

            if (serverUpdatedAt && new Date(savedAt) > new Date(serverUpdatedAt)) {
                window.dispatchEvent(new CustomEvent('draft-recovery-available', {
                    detail: { key: localStorageKey },
                }));
            }
        } catch {
            // corrupted localStorage entry — ignore
        }
    });

    return { saveStatus, lastSavedAt, saveErrorMessage };
}
