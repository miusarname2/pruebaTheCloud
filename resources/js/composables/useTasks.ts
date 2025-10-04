import { ref } from 'vue';
import api from '@/lib/api';

interface Task {
    id: number;
    title: string;
    description?: string;
    is_done: boolean;
    due_date?: string;
    priority?: string;
    creator_id: number;
    assigned_to_id?: number;
    created_at: string;
    updated_at: string;
    creator?: User;
    assignees?: User[];
    keywords?: Keyword[];
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface Keyword {
    id: number;
    name: string;
}

interface CreateTaskData {
    title: string;
    description?: string;
    due_date?: string;
    priority?: string;
    keywords?: string[];
}

interface UpdateTaskData {
    title?: string;
    description?: string;
    is_done?: boolean;
    due_date?: string;
    priority?: string;
    keywords?: number[];
}

const tasks = ref<Task[]>([]);
const loading = ref(false);
const error = ref<string>('');

export function useTasks() {
    const fetchTasks = async () => {
        loading.value = true;
        error.value = '';
        try {
            const response = await api.get('/tasks');
            tasks.value = response.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to fetch tasks';
        } finally {
            loading.value = false;
        }
    };

    const createTask = async (data: CreateTaskData) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await api.post('/tasks', data);
            const newTask = response.data.data;
            tasks.value.push(newTask);
            return newTask;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to create task';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const updateTask = async (id: number, data: UpdateTaskData) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await api.patch(`/tasks/${id}`, data);
            const updatedTask = response.data.data;
            const index = tasks.value.findIndex(t => t.id === id);
            if (index !== -1) {
                tasks.value[index] = updatedTask;
            }
            return updatedTask;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to update task';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deleteTask = async (id: number) => {
        loading.value = true;
        error.value = '';
        try {
            await api.delete(`/tasks/${id}`);
            tasks.value = tasks.value.filter(t => t.id !== id);
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to delete task';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const toggleTask = async (id: number) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await api.patch(`/tasks/${id}/toggle`);
            const updatedTask = response.data.data;
            const index = tasks.value.findIndex(t => t.id === id);
            if (index !== -1) {
                tasks.value[index] = updatedTask;
            }
            return updatedTask;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to toggle task';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        tasks,
        loading,
        error,
        fetchTasks,
        createTask,
        updateTask,
        deleteTask,
        toggleTask,
    };
}
