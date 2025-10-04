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
import { Plus, Edit, Trash2, CheckCircle, Circle } from 'lucide-vue-next';

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

        if (keywordNames.length > 0) {
            await syncKeywordsForTask(editingTaskId.value, keywordNames);
            await fetchKeywords(); // Refresh global keywords list
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
        <div class="min-h-screen bg-gradient-to-br from-background to-muted/20 p-6">
            <div class="max-w-7xl mx-auto space-y-8">
                <!-- Header -->
                <div class="text-center animate-fade-in">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-primary to-primary/70 bg-clip-text text-transparent">
                        Dashboard
                    </h1>
                    <p class="text-muted-foreground mt-2">
                        Manage your tasks efficiently • {{ tasks.length }} task{{ tasks.length !== 1 ? 's' : '' }}
                    </p>
                </div>

                <div v-if="error" class="bg-destructive/10 border border-destructive/20 rounded-lg p-4 text-destructive animate-fade-in">
                    {{ error }}
                </div>

                <!-- Create Task Form -->
                <div class="bg-card border shadow-lg rounded-xl p-6 animate-fade-in-up">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-primary/10 rounded-lg">
                            <Plus class="w-6 h-6 text-primary" />
                        </div>
                        <h2 class="text-2xl font-semibold">Create New Task</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="new-title" class="text-sm font-medium">Title</Label>
                            <Input
                                id="new-title"
                                v-model="newTaskTitle"
                                placeholder="Enter task title..."
                                class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                @keyup.enter="handleCreateTask"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="new-description" class="text-sm font-medium">Description</Label>
                            <Input
                                id="new-description"
                                v-model="newTaskDescription"
                                placeholder="Enter task description..."
                                class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                            />
                        </div>
                    </div>
                    <div class="mt-6">
                        <Button
                            @click="handleCreateTask"
                            :disabled="loading"
                            class="w-full md:w-auto transition-all duration-200 hover:scale-105"
                        >
                            <Plus class="w-4 h-4 mr-2" />
                            Create Task
                        </Button>
                    </div>
                </div>

                <!-- Task List -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-semibold flex items-center gap-3">
                            <CheckCircle class="w-6 h-6 text-primary" />
                            Your Tasks
                        </h2>
                    </div>

                    <div v-if="loading" class="text-center py-12">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
                        <p class="text-muted-foreground mt-4">Loading tasks...</p>
                    </div>
                    <div v-else-if="tasks.length === 0" class="text-center py-12 animate-fade-in">
                        <Circle class="w-16 h-16 text-muted-foreground/50 mx-auto mb-4" />
                        <p class="text-muted-foreground text-lg">No tasks found</p>
                        <p class="text-muted-foreground/70">Create your first task above!</p>
                    </div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            v-for="task in tasks"
                            :key="task.id"
                            class="bg-card border shadow-md rounded-xl p-6 transition-all duration-300 hover:shadow-xl hover:scale-[1.02] animate-fade-in-up"
                            :class="{
                                'bg-green-50/50 dark:bg-green-900/10 border-green-200 dark:border-green-800': task.is_done,
                                'ring-2 ring-primary/20': editingTaskId === task.id
                            }"
                        >
                            <div v-if="editingTaskId === task.id" class="space-y-4">
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium">Title</Label>
                                    <Input
                                        v-model="editingTitle"
                                        class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium">Description</Label>
                                    <Input
                                        v-model="editingDescription"
                                        class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium">Keywords (comma separated)</Label>
                                    <Input
                                        v-model="editingKeywords"
                                        placeholder="keyword1, keyword2"
                                        class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                    />
                                </div>
                                <div class="flex gap-2 pt-2">
                                    <Button @click="saveEditing" size="sm" class="transition-all duration-200 hover:scale-105">
                                        <CheckCircle class="w-4 h-4 mr-1" />
                                        Save
                                    </Button>
                                    <Button @click="cancelEditing" variant="outline" size="sm" class="transition-all duration-200">
                                        Cancel
                                    </Button>
                                </div>
                            </div>
                            <div v-else class="space-y-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <button
                                            @click="handleToggleTask(task.id)"
                                            class="transition-all duration-200 hover:scale-110"
                                        >
                                            <CheckCircle
                                                v-if="task.is_done"
                                                class="w-6 h-6 text-green-600 dark:text-green-400"
                                            />
                                            <Circle
                                                v-else
                                                class="w-6 h-6 text-muted-foreground hover:text-primary"
                                            />
                                        </button>
                                        <h3
                                            class="text-lg font-semibold truncate transition-all duration-200"
                                            :class="{ 'line-through text-muted-foreground': task.is_done }"
                                        >
                                            {{ task.title }}
                                        </h3>
                                    </div>
                                    <div class="flex gap-1 ml-2">
                                        <Button
                                            @click="startEditing(task)"
                                            size="sm"
                                            variant="ghost"
                                            class="transition-all duration-200 hover:bg-accent hover:scale-105"
                                        >
                                            <Edit class="w-4 h-4" />
                                        </Button>
                                        <Button
                                            @click="handleDeleteTask(task.id)"
                                            size="sm"
                                            variant="ghost"
                                            class="transition-all duration-200 hover:bg-destructive/10 hover:text-destructive hover:scale-105"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </div>

                                <p v-if="task.description" class="text-sm text-muted-foreground leading-relaxed">
                                    {{ task.description }}
                                </p>

                                <div v-if="task.keywords && task.keywords.length > 0" class="flex flex-wrap gap-2">
                                    <span
                                        v-for="keyword in task.keywords"
                                        :key="keyword.id"
                                        class="px-3 py-1 bg-primary/10 text-primary text-xs font-medium rounded-full transition-all duration-200 hover:bg-primary/20"
                                    >
                                        {{ keyword.name }}
                                    </span>
                                </div>

                                <div class="text-xs text-muted-foreground pt-2 border-t border-border/50">
                                    Created by {{ task.creator?.name }} • {{ new Date(task.created_at).toLocaleDateString() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
