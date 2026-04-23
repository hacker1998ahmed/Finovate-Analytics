<template>
    <AuthenticatedLayout>
        <Head title="Companies" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Companies Management</h2>
                    <Link :href="route('companies.create')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Add New Company
                    </Link>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tax ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="company in companies" :key="company.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ company.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ company.tax_registration_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="company.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs rounded-full">
                                                {{ company.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('companies.show', company.id)" class="text-blue-600 hover:text-blue-900 mr-3">View</Link>
                                            <Link :href="route('companies.edit', company.id)" class="text-yellow-600 hover:text-yellow-900 mr-3">Edit</Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    companies: Array
});
</script>
