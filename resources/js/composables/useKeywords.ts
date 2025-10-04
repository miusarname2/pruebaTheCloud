import { ref } from 'vue';
import api from '@/lib/api';

interface Keyword {
    id: number;
    name: string;
}

const keywords = ref<Keyword[]>([]);
const loading = ref(false);
const error = ref<string>('');

export function useKeywords() {
    const fetchKeywords = async () => {
        loading.value = true;
        error.value = '';
        try {
            const response = await api.get('/keywords');
            keywords.value = response.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to fetch keywords';
        } finally {
            loading.value = false;
        }
    };

    const createKeyword = async (name: string) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await api.post('/keywords', { name });
            const newKeyword = response.data.data;
            keywords.value.push(newKeyword);
            return newKeyword;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to create keyword';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const updateKeyword = async (id: number, name: string) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await api.patch(`/keywords/${id}`, { name });
            const updatedKeyword = response.data.data;
            const index = keywords.value.findIndex(k => k.id === id);
            if (index !== -1) {
                keywords.value[index] = updatedKeyword;
            }
            return updatedKeyword;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to update keyword';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deleteKeyword = async (id: number) => {
        loading.value = true;
        error.value = '';
        try {
            await api.delete(`/keywords/${id}`);
            keywords.value = keywords.value.filter(k => k.id !== id);
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to delete keyword';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const attachKeywordsToTask = async (taskId: number, keywordIds: number[]) => {
        loading.value = true;
        error.value = '';
        try {
            await api.post(`/tasks/${taskId}/keywords`, { keyword_ids: keywordIds });
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to attach keywords';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const detachKeywordFromTask = async (taskId: number, keywordId: number) => {
        loading.value = true;
        error.value = '';
        try {
            await api.delete(`/tasks/${taskId}/keywords/${keywordId}`);
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to detach keyword';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const syncKeywordsForTask = async (taskId: number, keywordIds: number[]) => {
        loading.value = true;
        error.value = '';
        try {
            await api.patch(`/tasks/${taskId}/keywords`, { keyword_ids: keywordIds });
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to sync keywords';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        keywords,
        loading,
        error,
        fetchKeywords,
        createKeyword,
        updateKeyword,
        deleteKeyword,
        attachKeywordsToTask,
        detachKeywordFromTask,
        syncKeywordsForTask,
    };
}
