<script setup lang="ts">
import InputError from '@/components/InputError.vue';
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
import { ExternalLink, Loader2, Pencil, Plus, Trash } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'List',
        href: '/lists',
    },
];

const props = defineProps<{
    lists: Array<{
        id: number;
        name: string;
        color?: string;
        tasks_count?: number;
        created_at: string;
    }>;
}>();

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const editingList = ref<{ id: number; name: string; color: string } | null>(
    null,
);
const deletingListId = ref<number | null>(null);

const createForm = useForm({
    name: '',
    color: '#000000',
});

const editForm = useForm({
    name: '',
    color: '#000000',
});

const openEditingDialog = (list: any) => {
    editingList.value = {
        id: list.id,
        name: list.name,
        color: list.color,
    };
    editForm.name = list.name;
    editForm.color = list.color;
    isEditDialogOpen.value = true;
};

const createList = () => {
    createForm.post('/lists', {
        preserveScroll: true,
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
            toast.success('List created successfully!');
        },
        onError: (errors) => {
            console.error('Creation failed:', errors);
            toast.error('List creation failed!');
        },
    });
};

const updateList = () => {
    if (!editingList.value) return;
    editForm.put(`/lists/${editingList.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditDialogOpen.value = false;
            editForm.reset();
            toast.success('List updated successfully!');
        },
        onError: (errors) => {
            console.error('Update failed:', errors);
            toast.error('List update failed!');
        },
    });
};

const deleteList = (listId: number) => {
    if (confirm('Are you sure you want to delete this list?')) {
        deletingListId.value = listId;
        router.delete(`/lists/${listId}`, {
            preserveScroll: true,
            onFinish: () => {
                deletingListId.value = null;
                toast.success('List deleted successfully!');
            },
        });
    }
};
</script>

<template>
    <Head title="Lists" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="item-center flex justify-between">
                <div>
                    <h1 class="text-3xl font-bold">List</h1>
                    <p class="text-muted-foreground">Manage your task lists</p>
                </div>

                <!-- Start Create List Diaglog -->
                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Create List
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Create List</DialogTitle>
                            <DialogDescription>
                                Add a new list to manage your tasks
                            </DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="createList" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="name">List Name</Label>
                                <Input
                                    id="name"
                                    v-model="createForm.name"
                                    type="text"
                                    placeholder="List name"
                                    required
                                />
                                <InputError :message="createForm.errors.name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="color">Color</Label>
                                <Input
                                    id="color"
                                    v-model="createForm.color"
                                    type="color"
                                    placeholder="List name"
                                    required
                                />
                                <InputError
                                    :message="createForm.errors.color"
                                />
                            </div>

                            <Button
                                type="submit"
                                class="w-full"
                                :disabled="createForm.processing"
                            >
                                <Loader2
                                    v-if="createForm.processing"
                                    class="mr-2 h-4 w-4 animate-spin"
                                />
                                {{
                                    createForm.processing
                                        ? 'Creating...'
                                        : 'Create List'
                                }}
                            </Button>
                        </form>
                    </DialogContent>
                </Dialog>
                <!-- End Create List Diaglog -->

                <!-- Start Edit List Diaglog -->
                <Dialog v-model:open="isEditDialogOpen">
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Edit List</DialogTitle>
                            <DialogDescription>
                                Edit a list to manage your tasks
                            </DialogDescription>
                        </DialogHeader>
                        <form
                            v-if="editingList"
                            @submit.prevent="updateList"
                            class="space-y-4"
                        >
                            <div class="space-y-2">
                                <Label for="edit-name">List Name</Label>
                                <Input
                                    id="edit-name"
                                    v-model="editForm.name"
                                    placeholder="List name"
                                    required
                                />
                                <InputError :message="editForm.errors.name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="edit-color">Color</Label>
                                <Input
                                    id="edit-color"
                                    v-model="editForm.color"
                                    type="color"
                                    placeholder="List name"
                                    required
                                />
                                <InputError :message="editForm.errors.color" />
                            </div>

                            <Button
                                type="submit"
                                class="w-full"
                                :disabled="editForm.processing"
                            >
                                <Loader2
                                    v-if="editForm.processing"
                                    class="mr-2 h-4 w-4 animate-spin"
                                />
                                {{
                                    editForm.processing
                                        ? 'Updating...'
                                        : 'Update List'
                                }}
                            </Button>
                        </form>
                    </DialogContent>
                </Dialog>
                <!-- Start Edit List Diaglog -->
            </div>

            <div
                v-if="lists.length > 0"
                class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
            >
                <Card
                    v-for="list in lists"
                    :key="list.id"
                    class="group relative transition-shadow hover:shadow-md"
                >
                    <CardHeader>
                        <div class="item-start flex justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="mr-2 h-3 w-3 rounded-full"
                                    :style="{ backgroundColor: list.color }"
                                />
                                <CardTitle class="mr-2 text-lg">{{
                                    list.name
                                }}</CardTitle>
                            </div>
                            <span
                                class="mr-2 text-2xl font-bold text-muted-foreground"
                                >{{ list.tasks_count || 0 }}</span
                            >
                        </div>
                    </CardHeader>
                    <CardContent>
                        <p class="mb-4 text-sm text-muted-foreground">
                            {{ list.tasks_count || 0 }}
                            {{ list.tasks_count === 1 ? 'task' : 'tasks' }}
                        </p>
                        <div class="flex gap-2">
                            <Link
                                :href="`/tasks?list_id=${list.id}`"
                                class="flex"
                            >
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="w-full"
                                >
                                    <ExternalLink class="h-4 w-4" />
                                    View
                                </Button>
                            </Link>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="openEditingDialog(list)"
                            >
                                <Pencil class="h-4 w-4" />
                            </Button>

                            <Button
                                variant="destructive"
                                size="sm"
                                @click="deleteList(list.id)"
                                :disabled="deletingListId === list.id"
                            >
                                <Loader2
                                    v-if="deletingListId === list.id"
                                    class="h-4 w-4 animate-spin"
                                />
                                <Trash v-else class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
            <Card v-else>
                <CardContent
                    class="flex flex-col items-center justify-center py-12"
                >
                    <p class="mb-4 text-muted-foreground">No lists yet</p>
                    <p class="text-sm text-muted-foreground">
                        Create your first list to get started.
                    </p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
