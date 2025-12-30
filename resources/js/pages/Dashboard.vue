<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { CheckCircle2, ListTodo, TrendingUp } from 'lucide-vue-next';
import { computed } from 'vue';

interface Task {
    id: number;
    title: string;
    description?: string | null;
    priority: 'low' | 'medium' | 'high';
    completed: boolean;
    list_id: number;
    created_at: string;
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
        totalTasks?: number;
        completedTasks?: number;
        pendingTasks?: number;
    }>(),
    {
        lists: () => [],
        recentTasks: () => [],
        totalTasks: 0,
        completedTasks: 0,
        pendingTasks: 0,
    },
);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const completionRate = computed(() =>
    props.totalTasks === 0
        ? 0
        : Math.round((props.completedTasks / props.totalTasks) * 100) || 0,
);

const totalLists = computed(() => props.lists?.length ?? 0);
const totalTasks = computed(() => props.totalTasks ?? 0);
const completedTasks = computed(() => props.completedTasks ?? 0);
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
                        <span class="text-3xl font-semibold">
                            {{ totalLists }}
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
                            {{ totalTasks }}
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
                            {{ completedTasks }}
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
        </div>
    </AppLayout>
</template>
