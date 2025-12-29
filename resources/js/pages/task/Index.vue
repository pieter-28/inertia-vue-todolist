<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { Loader2, Plus } from 'lucide-vue-next';
import { ref } from 'vue';

interface Task {
    id: number;
    title: string;
    description?: string;
    priority: 'low' | 'normal' | 'high';
    completed: boolean;
    created_at: string;
    list: {
        id: number;
        name: string;
        color?: string;
    };
}

interface TodoList {
    id: number;
    name: string;
    color?: string;
}

interface paginationLinks {
    url: string | null;
    label: string;
    active: boolean;
}

interface paginatedTasks {
    data: Task[];
    links: paginationLinks[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    tasks: paginatedTasks;
    lists: TodoList[];
    filters: {
        search?: string;
        priority?: string;
        list_id?: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'All Task',
        href: '/tasks',
    },
];

// Filter
const search = ref(props.filters.search || '');
const priority = ref(props.filters.priority || '');
const listId = ref(props.filters.list_id || '');

// Dialoh state
const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const editingTask = ref<Task | null>(null);
const deleteTaskId = ref<number | null>(null);

const createForm = useForm({
    title: '',
    description: '',
    priority: 'normal',
    list_id: props.filters.list_id || '',
});

const editForm = useForm({
    list_id: props.filters.list_id || '',
    title: '',
    description: '',
    priority: 'normal',
});

// Watchers for filters changes and update the URL query parameters
watchDebounced(
    [search, priority, listId],
    () => {
        router.get(
            '/tasks',
            {
                search: search.value || undefined,
                priority: priority.value || undefined,
                list_id: listId.value || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    },
    { debounce: 300 },
);

const clearFilters = () => {
    search.value = '';
    priority.value = '';
    listId.value = '';
};

const toggleTaskCompletion = (task: Task) => {
    router.put(
        `/tasks/${task.id}`,
        {
            title: task.title,
            description: task.description,
            priority: task.priority,
            completed: !task.completed,
        },
        {
            preserveScroll: true,
        },
    );
};

const createTask = () => {
    createForm.post('/tasks', {
        preserveScroll: true,
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
        },
    });
};

const updateTask = () => {
    if (!editingTask.value) return;

    editForm.put(`/tasks/${editingTask.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditDialogOpen.value = false;
            editForm.reset();
        },
    });
};

const deleteTask = (taskId: number) => {
    if (confirm('Are you sure you want to delete this task?')) {
        deleteTaskId.value = taskId;
        router.delete(`/tasks/${taskId}`, {
            preserveScroll: true,
            onFinish: () => {
                deleteTaskId.value = null;
            },
        });
    }
};

const openEditDialog = (task: Task) => {
    editingTask.value = { ...task };
    editForm.title = task.title;
    editForm.description = task.description || '';
    editForm.priority = task.priority;
    isEditDialogOpen.value = true;
};

const getPriorityVariant = (
    priority: string,
): 'default' | 'secondary' | 'destructive' => {
    switch (priority) {
        case 'low':
            return 'default';
        case 'high':
            return 'destructive';
        default:
            return 'default';
    }
};
</script>

<template>
    <Head title="All Task" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">All Task</h1>
                    <p class="text-muted-foreground">
                        Manage your tasks ({{ tasks.total }} total)
                    </p>
                </div>

                <!-- Start Create Task Dialog -->
                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            New Task
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Create New Task</DialogTitle>
                            <DialogDescription>
                                Fill in the details below to create a new task.
                            </DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="createTask" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="title">Task Title</Label>
                                <Input
                                    id="title"
                                    v-model="createForm.title"
                                    type="text"
                                    class="mt-1 w-full"
                                    placeholder="Task title"
                                    required
                                />
                                <InputError
                                    :message="createForm.errors?.title"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="list_id">Todo List</Label>
                                <select
                                    id="list_id"
                                    v-model="createForm.list_id"
                                    class="mt-1 w-full rounded-md border p-2"
                                    required
                                >
                                    <option value="" disabled>
                                        Select a list
                                    </option>
                                    <option
                                        v-for="list in lists"
                                        :key="list.id"
                                        :value="list.id"
                                    >
                                        {{ list.name }}
                                    </option>
                                </select>
                                <InputError
                                    :message="createForm.errors?.list_id"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="description">Description</Label>
                                <Input
                                    id="description"
                                    v-model="createForm.description"
                                    type="text"
                                    class="mt-1 w-full"
                                    placeholder="Task description"
                                />
                                <InputError
                                    :message="createForm.errors?.description"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="priority">Priority</Label>
                                <select
                                    id="priority"
                                    v-model="createForm.priority"
                                    class="mt-1 w-full rounded-md border p-2"
                                    required
                                >
                                    <option value="low">Low</option>
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                </select>
                                <InputError
                                    :message="createForm.errors?.priority"
                                />
                            </div>

                            <div class="flex justify-end gap-2">
                                <DialogClose asChild>
                                    <Button type="button">Cancel</Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    :disabled="createForm.processing"
                                >
                                    <Loader2
                                        v-if="createForm.processing"
                                        class="mr-2 h-4 w-4 animate-spin"
                                    />
                                    {{
                                        createForm.processing
                                            ? 'Creating...'
                                            : 'Create Task'
                                    }}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
                <!-- End Create Task Dialog -->

                <!-- Start Edit Task Dialog -->
                <!-- <Dialog v-model:open="isEditDialogOpen">
                    <DialogHeader>
                        <DialogTitle>Edit Task</DialogTitle>
                        <DialogDescription>
                            Fill in the details below to edit the task.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="updateTask" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="title">Task Title</Label>
                            <Input
                                id="title"
                                v-model="editForm.title"
                                type="text"
                                class="mt-1 w-full"
                                placeholder="Task title"
                                required
                            />
                            <InputError :message="editForm.errors?.title" />
                        </div>
                        <div class="space-y-2">
                            <Label for="list_id">Todo List</Label>
                            <select
                                id="list_id"
                                v-model="editForm.list_id"
                                class="mt-1 w-full border rounded-md p-2"
                                required
                            >
                                <option value="" disabled>Select a list</option>
                                <option
                                    v-for="list in lists"
                                    :key="list.id"
                                    :value="list.id"
                                >
                                    {{ list.name }}
                                </option>
                            </select>
                            <InputError :message="editForm.errors?.list_id" />
                        </div>
                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Input
                                id="description"
                                v-model="editForm.description"
                                type="text"
                                class="mt-1 w-full"
                                placeholder="Task description"
                            />
                            <InputError :message="editForm.errors?.description" />
                        </div>
                        <div class="space-y-2">
                            <Label for="priority">Priority</Label>
                            <select
                                id="priority"
                                v-model="editForm.priority"
                                class="mt-1 w-full border rounded-md p-2"
                                required
                            >
                                <option value="low">Low</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                            </select>
                            <InputError :message="editForm.errors?.priority" />
                        </div>

                        <div class="flex justify-end gap-2">
                            <DialogClose asChild>
                                <Button type="button">Cancel</Button>
                            </DialogClose>
                            <Button type="submit" :disabled="editForm.processing">
                                <Loader2
                                    v-if="editForm.processing"
                                    class="w-4 h-4 mr-2 animate-spin"
                                />
                                {{ editForm.processing ? 'Updating...' : 'Update Task' }}
                            </Button>
                        </div>
                    </form>
                </Dialog> -->
                <!-- Start Edit Task Dialog -->
            </div>
        </div>
    </AppLayout>
</template>
