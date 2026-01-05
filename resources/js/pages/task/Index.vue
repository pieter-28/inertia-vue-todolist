<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import {
    CheckCircle2,
    Circle,
    Loader2,
    Pencil,
    Plus,
    Search,
    Trash,
    X,
} from 'lucide-vue-next';

import { ref } from 'vue';
import { toast } from 'vue-sonner';

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

const isDeleteDialogOpen = ref(false);
const taskIdToDelete = ref<number | null>(null);

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
            toast.success('Task created successfully!');
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
            toast.success('Task updated successfully!');
        },
    });
};

const deleteTask = (taskId: number) => {
    taskIdToDelete.value = taskId;
    isDeleteDialogOpen.value = true;
};

const confirmDeleteTask = () => {
    if (!taskIdToDelete.value) return;
    deleteTaskId.value = taskIdToDelete.value;
    router.delete(`/tasks/${taskIdToDelete.value}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteTaskId.value = null;
            taskIdToDelete.value = null;
            isDeleteDialogOpen.value = false;
            toast.success('Task deleted successfully!');
        },
    });
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
                                    class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
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
                                    class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
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
                                    class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
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
                <Dialog v-model:open="isEditDialogOpen">
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Edit Task</DialogTitle>
                            <DialogDescription>
                                Fill in the details below to edit the task.
                            </DialogDescription>
                        </DialogHeader>
                        <form
                            v-if="editingTask"
                            @submit.prevent="updateTask"
                            class="space-y-4"
                        >
                            <div class="space-y-2">
                                <Label for="edit-title">Task Title</Label>
                                <Input
                                    id="edit-title"
                                    v-model="editForm.title"
                                    type="text"
                                    class="mt-1 w-full"
                                    placeholder="Task title"
                                    required
                                />
                                <InputError :message="editForm.errors?.title" />
                            </div>
                            <div class="space-y-2">
                                <Label for="edit-description"
                                    >Description</Label
                                >
                                <Input
                                    id="edit-description"
                                    v-model="editForm.description"
                                    type="text"
                                    class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                    placeholder="Task description"
                                />
                                <InputError
                                    :message="editForm.errors?.description"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="edit-priority">Priority</Label>
                                <select
                                    id="edit-priority"
                                    v-model="editForm.priority"
                                    class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                    required
                                >
                                    <option value="low">Low</option>
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                </select>
                                <InputError
                                    :message="editForm.errors?.priority"
                                />
                            </div>
                            <div class="flex justify-end gap-2">
                                <Button
                                    type="submit"
                                    :disabled="editForm.processing"
                                >
                                    <Loader2
                                        v-if="editForm.processing"
                                        class="mr-2 h-4 w-4 animate-spin"
                                    />
                                    {{
                                        editForm.processing
                                            ? 'Updating...'
                                            : 'Update Task'
                                    }}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
                <!-- End Edit Task Dialog -->
            </div>

            <!-- Start Filter -->
            <Card class="mt-4">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Filter</CardTitle>
                        <Button variant="ghost" size="sm" @click="clearFilters">
                            <X class="mr-2 h-4 w-4" />
                            Clear Filters
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="search">Search</Label>
                            <div class="relative">
                                <Search
                                    class="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground"
                                ></Search>
                                <Input
                                    id="search"
                                    v-model="search"
                                    type="text"
                                    class="pl-8"
                                    placeholder="Search tasks..."
                                />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label for="list">List</Label>
                            <select
                                id="list"
                                v-model="listId"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            >
                                <option value="">All List</option>
                                <option
                                    v-for="list in lists"
                                    :key="list.id"
                                    :value="list.id"
                                >
                                    {{ list.name }}
                                </option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <Label for="priority">Priority</Label>
                            <select
                                id="priority"
                                v-model="priority"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            >
                                <option value="">All Priorities</option>
                                <option value="low">Low</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <!-- End Filter -->

            <!-- Task Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Task List</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="tasks.data.length > 0" class="space-y-4">
                        <div class="rounded-md border">
                            <table class="w-full caption-bottom text-sm">
                                <thead class="[&_tr]:border-b">
                                    <tr
                                        class="border-b transition-colors hover:bg-muted"
                                    >
                                        <th
                                            class="h-12 px-4 text-left align-middle font-medium text-muted-foreground"
                                        >
                                            Title
                                        </th>
                                        <th
                                            class="h-12 px-4 text-left align-middle font-medium text-muted-foreground"
                                        >
                                            Deskription
                                        </th>
                                        <th
                                            class="h-12 px-4 text-left align-middle font-medium text-muted-foreground"
                                        >
                                            List
                                        </th>
                                        <th
                                            class="h-12 px-4 text-left align-middle font-medium text-muted-foreground"
                                        >
                                            Priority
                                        </th>
                                        <th
                                            class="h-12 px-4 text-left align-middle font-medium text-muted-foreground"
                                        >
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="[&_tr:last-child]:border-0">
                                    <tr
                                        v-for="task in tasks.data"
                                        :key="task.id"
                                        class="border-b transition-colors hover:bg-muted/50"
                                    >
                                        <!-- Title -->
                                        <td class="p-4 align-middle">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <button
                                                    @click="
                                                        toggleTaskCompletion(
                                                            task,
                                                        )
                                                    "
                                                    class="flex items-center justify-center"
                                                    aria-label="Toggle task completion"
                                                >
                                                    <CheckCircle2
                                                        v-if="task.completed"
                                                        class="h-5 w-5 text-muted-foreground"
                                                    />
                                                    <Circle
                                                        v-else
                                                        class="h-5 w-5 text-muted-foreground"
                                                    />
                                                </button>

                                                <span
                                                    :class="{
                                                        'text-muted-foreground line-through':
                                                            task.completed,
                                                    }"
                                                >
                                                    {{ task.title }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Description -->
                                        <td class="p-4 align-middle">
                                            <span
                                                class="text-sm text-muted-foreground"
                                                :class="{
                                                    'line-through':
                                                        task.completed,
                                                }"
                                            >
                                                {{ task.description }}
                                            </span>
                                        </td>

                                        <!-- List -->
                                        <td class="p-4 align-middle">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    class="h-3 w-3 rounded-full"
                                                    :style="{
                                                        backgroundColor:
                                                            task.list?.color ||
                                                            '#6366f1',
                                                    }"
                                                />
                                                <span class="text-sm">
                                                    {{ task.list?.name ?? '-' }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Priority -->
                                        <td class="p-4 align-middle">
                                            <Badge
                                                :variant="
                                                    getPriorityVariant(
                                                        task.priority,
                                                    )
                                                "
                                            >
                                                {{ task.priority }}
                                            </Badge>
                                        </td>

                                        <!-- Actions -->
                                        <td class="p-4 align-middle">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    @click="
                                                        openEditDialog(task)
                                                    "
                                                >
                                                    <Pencil class="h-4 w-4" />
                                                </Button>

                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    @click="deleteTask(task.id)"
                                                    :disabled="
                                                        deleteTaskId === task.id
                                                    "
                                                >
                                                    <Loader2
                                                        v-if="
                                                            deleteTaskId ===
                                                            task.id
                                                        "
                                                        class="h-4 w-4 animate-spin"
                                                    />
                                                    <Trash
                                                        v-else
                                                        class="h-4 w-4"
                                                    />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginate -->
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-muted-foreground">
                                Showing {{ tasks.current_page }} of
                                {{ tasks.last_page }} to {{ tasks.total }} Total
                            </p>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-for="(link, i) in tasks.links"
                                    :key="i"
                                    :href="link.url || ''"
                                    preserve-state
                                    preserve-scroll
                                    v-html="link.label"
                                    class="rounded-md px-3 py-1 text-sm"
                                    :class="
                                        link.active
                                            ? 'bg-primary text-primary-foreground'
                                            : link.url
                                              ? 'hover:bg-muted'
                                              : 'cursor-not-allowed opacity-50'
                                    "
                                />
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-12 text-center text-muted-foreground">
                        No tasks found.
                    </div>
                </CardContent>
            </Card>
            <!-- END Task Table -->

            <!-- Delete Confirmation Dialog -->
            <AlertDialog v-model:open="isDeleteDialogOpen">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle> Hapus Task? </AlertDialogTitle>
                        <AlertDialogDescription>
                            Tindakan ini tidak dapat dibatalkan. Task akan
                            dihapus secara permanen.
                        </AlertDialogDescription>
                    </AlertDialogHeader>

                    <AlertDialogFooter>
                        <AlertDialogCancel @click="isDeleteDialogOpen = false">
                            Batal
                        </AlertDialogCancel>

                        <AlertDialogAction
                            variant="destructive"
                            @click="confirmDeleteTask"
                        >
                            Hapus
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
            <!-- END Delete Confirmation Dialog -->
        </div>
    </AppLayout>
</template>
