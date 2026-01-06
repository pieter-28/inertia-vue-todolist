<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, ListTodo, TrendingUp } from 'lucide-vue-next';
import { computed } from 'vue';

interface Task {
    id: number;
    title: string;
    completed: boolean;
    created_at: string;
    list?: {
        id: number;
        name: string;
        color?: string;
    };
}

interface TodoList {
    id: number;
    name: string;
    color?: string;
    tasks_count: number;
    completed_tasks_count: number;
    created_at: string;
}

const props = withDefaults(
    defineProps<{
        lists?: TodoList[];
        recentTasks?: Task[];
        totalLists: number;
        totalTasks: number;
        completedTasks: number;
        pendingTasks: number;
    }>(),
    {
        lists: () => [],
        recentTasks: () => [],
        totalLists: 0,
        totalTasks: 0,
        completedTasks: 0,
        pendingTasks: 0,
    },
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
];

const completionRate = computed(() =>
    props.totalTasks > 0
        ? Math.round((props.completedTasks / props.totalTasks) * 100)
        : 0,
);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <p class="text-muted-foreground">
                Overview of your tasks and lists
            </p>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3 lg:grid-cols-4">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <ListTodo class="h-5 w-5" />
                            <span>Total Lists</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <span class="text-2xl font-bold">
                            {{ totalLists ?? 0 }}
                        </span>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <ListTodo class="h-5 w-5" />
                            <span>Total Tasks</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <span class="text-3xl font-semibold">
                            {{ totalTasks ?? 0 }}
                        </span>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <CheckCircle2 class="h-5 w-5" />
                            <span>Completed Tasks</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <span class="text-3xl font-semibold">
                            {{ completedTasks ?? 0 }}
                        </span>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <TrendingUp class="h-5 w-5" />
                            <span>Completion Rate</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <span class="text-3xl font-semibold">
                            {{ completionRate }} %
                        </span>
                    </CardContent>
                </Card>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between"
                    >
                        <CardTitle>Your Lists</CardTitle>
                        <Link href="/lists">
                            <Button variant="outline" size="sm"
                                >View All Lists</Button
                            >
                        </Link>
                    </CardHeader>

                    <CardContent>
                        <div v-if="lists.length" class="space-y-3">
                            <Link
                                v-for="list in lists"
                                :key="list.id"
                                :href="`/lists/${list.id}`"
                                class="block rounded-lg p-3 transition hover:bg-accent"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="h-3 w-3 rounded-full"
                                            :style="{
                                                backgroundColor:
                                                    list.color || '#6366f1',
                                            }"
                                        />
                                        <div>
                                            <p class="font-medium">
                                                {{ list.name }}
                                            </p>
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{ list.completed_tasks_count }}
                                                /
                                                {{ list.tasks_count }} completed
                                            </p>
                                        </div>
                                    </div>

                                    <span class="text-sm font-medium">
                                        {{ list.tasks_count }}
                                    </span>
                                </div>
                            </Link>
                        </div>

                        <div
                            v-else
                            class="py-8 text-center text-muted-foreground"
                        >
                            No lists found
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
