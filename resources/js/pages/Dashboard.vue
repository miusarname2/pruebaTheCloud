<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useTasks } from '@/composables/useTasks';
import { useKeywords } from '@/composables/useKeywords';
import { onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';

const { tasks, loading, error, fetchTasks, createTask, updateTask, deleteTask, toggleTask } = useTasks();
const { keywords, fetchKeywords, syncKeywordsForTask } = useKeywords();

const newTaskTitle = ref('');
const newTaskDescription = ref('');
const editingTaskId = ref<number | null>(null);
const editingTitle = ref('');
const editingDescription = ref('');
const editingKeywords = ref<string>('');

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

onMounted(async () => {
    await fetchTasks();
    await fetchKeywords();
});

const handleCreateTask = async () => {
    if (!newTaskTitle.value.trim()) return;
    try {
        await createTask({
            title: newTaskTitle.value,
            description: newTaskDescription.value,
        });
        newTaskTitle.value = '';
        newTaskDescription.value = '';
    } catch (err) {
        console.error('Failed to create task:', err);
    }
};

const startEditing = (task: any) => {
    editingTaskId.value = task.id;
    editingTitle.value = task.title;
    editingDescription.value = task.description || '';
    editingKeywords.value = task.keywords?.map((k: any) => k.name).join(', ') || '';
};

const cancelEditing = () => {
    editingTaskId.value = null;
    editingTitle.value = '';
    editingDescription.value = '';
    editingKeywords.value = '';
};

const saveEditing = async () => {
    if (!editingTaskId.value) return;
    try {
        await updateTask(editingTaskId.value, {
            title: editingTitle.value,
            description: editingDescription.value,
        });

        // Handle keywords
        const keywordNames = editingKeywords.value.split(',').map(s => s.trim()).filter(s => s);
        const keywordIds = keywordNames.map(name => {
            const existing = keywords.value.find(k => k.name === name);
            return existing ? existing.id : null;
        }).filter(id => id !== null) as number[];

        if (keywordIds.length > 0) {
            await syncKeywordsForTask(editingTaskId.value, keywordIds);
        }

        cancelEditing();
        await fetchTasks(); // Refresh to get updated keywords
    } catch (err) {
        console.error('Failed to update task:', err);
    }
};

const handleDeleteTask = async (id: number) => {
    if (confirm('Are you sure you want to delete this task?')) {
        try {
            await deleteTask(id);
        } catch (err) {
            console.error('Failed to delete task:', err);
        }
    }
};

const handleToggleTask = async (id: number) => {
    try {
        await toggleTask(id);
    } catch (err) {
        console.error('Failed to toggle task:', err);
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div v-if="error" class="text-red-500">{{ error }}</div>

            <!-- Create Task Form -->
            <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                <h2 class="text-lg font-semibold mb-4">Create New Task</h2>
                <div class="space-y-4">
                    <div>
                        <Label for="new-title">Title</Label>
                        <Input
                            id="new-title"
                            v-model="newTaskTitle"
                            placeholder="Task title"
                            @keyup.enter="handleCreateTask"
                        />
                    </div>
                    <div>
                        <Label for="new-description">Description</Label>
                        <Input
                            id="new-description"
                            v-model="newTaskDescription"
                            placeholder="Task description"
                        />
                    </div>
                    <Button @click="handleCreateTask" :disabled="loading">Create Task</Button>
                </div>
            </div>

            <!-- Task List -->
            <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                <h2 class="text-lg font-semibold mb-4">Tasks</h2>
                <div v-if="loading" class="text-center">Loading...</div>
                <div v-else-if="tasks.length === 0" class="text-center text-muted-foreground">No tasks found</div>
                <div v-else class="space-y-4">
                    <div
                        v-for="task in tasks"
                        :key="task.id"
                        class="border rounded-lg p-4"
                        :class="{ 'bg-green-50 dark:bg-green-900/20': task.is_done }"
                    >
                        <div v-if="editingTaskId === task.id" class="space-y-4">
                            <div>
                                <Label>Title</Label>
                                <Input v-model="editingTitle" />
                            </div>
                            <div>
                                <Label>Description</Label>
                                <Input v-model="editingDescription" />
                            </div>
                            <div>
                                <Label>Keywords (comma separated)</Label>
                                <Input v-model="editingKeywords" placeholder="keyword1, keyword2" />
                            </div>
                            <div class="flex gap-2">
                                <Button @click="saveEditing" size="sm">Save</Button>
                                <Button @click="cancelEditing" variant="outline" size="sm">Cancel</Button>
                            </div>
                        </div>
                        <div v-else>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Checkbox
                                        :checked="task.is_done"
                                        @change="handleToggleTask(task.id)"
                                    />
                                    <h3
                                        class="text-lg font-medium"
                                        :class="{ 'line-through text-muted-foreground': task.is_done }"
                                    >
                                        {{ task.title }}
                                    </h3>
                                </div>
                                <div class="flex gap-2">
                                    <Button @click="startEditing(task)" size="sm" variant="outline">Edit</Button>
                                    <Button @click="handleDeleteTask(task.id)" size="sm" variant="destructive">Delete</Button>
                                </div>
                            </div>
                            <p v-if="task.description" class="text-sm text-muted-foreground mt-2">
                                {{ task.description }}
                            </p>
                            <div v-if="task.keywords && task.keywords.length > 0" class="flex flex-wrap gap-1 mt-2">
                                <span
                                    v-for="keyword in task.keywords"
                                    :key="keyword.id"
                                    class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs rounded"
                                >
                                    {{ keyword.name }}
                                </span>
                            </div>
                            <div class="text-xs text-muted-foreground mt-2">
                                Created by {{ task.creator?.name }} • {{ new Date(task.created_at).toLocaleDateString() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
