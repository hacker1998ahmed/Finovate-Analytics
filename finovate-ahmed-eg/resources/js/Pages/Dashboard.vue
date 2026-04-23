<template>
    <AuthenticatedLayout>
        <Head title="Dashboard" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Welcome Section -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-lg shadow-lg p-8 mb-8 text-white">
                    <h1 class="text-3xl font-bold mb-2">Welcome to Finovate – AHMED EG</h1>
                    <p class="text-blue-100">Professional Legal Accounting & Electronic Invoice Management System</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Companies -->
                    <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Companies</p>
                                <p class="text-3xl font-bold text-gray-800">{{ stats.companies || 0 }}</p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-full">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Invoices -->
                    <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 uppercase">E-Invoices</p>
                                <p class="text-3xl font-bold text-gray-800">{{ stats.invoices || 0 }}</p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-full">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Tax Audits -->
                    <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Tax Audits</p>
                                <p class="text-3xl font-bold text-gray-800">{{ stats.tax_audits || 0 }}</p>
                            </div>
                            <div class="bg-yellow-100 p-3 rounded-full">
                                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Employees -->
                    <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Employees</p>
                                <p class="text-3xl font-bold text-gray-800">{{ stats.employees || 0 }}</p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-full">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <Link :href="route('invoices.create')" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition text-center">
                        <div class="text-green-600 mb-3">
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Create Invoice</h3>
                        <p class="text-sm text-gray-500 mt-1">Submit to ETA</p>
                    </Link>

                    <Link :href="route('tax-audits.index')" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition text-center">
                        <div class="text-yellow-600 mb-3">
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Tax Audits</h3>
                        <p class="text-sm text-gray-500 mt-1">Manage audits</p>
                    </Link>

                    <Link :href="route('companies.index')" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition text-center">
                        <div class="text-blue-600 mb-3">
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Companies</h3>
                        <p class="text-sm text-gray-500 mt-1">Manage tenants</p>
                    </Link>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold text-gray-800">Recent Invoices</h2>
                    </div>
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="invoice in recentInvoices" :key="invoice.id">
                                    <td class="py-3">{{ invoice.invoice_number }}</td>
                                    <td class="py-3">{{ invoice.customer_name }}</td>
                                    <td class="py-3">{{ formatCurrency(invoice.total_amount) }}</td>
                                    <td class="py-3">
                                        <span :class="getStatusClass(invoice.status)" class="px-2 py-1 text-xs rounded-full">
                                            {{ invoice.status }}
                                        </span>
                                    </td>
                                    <td class="py-3">{{ invoice.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recentInvoices: Array
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-EG', { style: 'currency', currency: 'EGP' }).format(amount);
};

const getStatusClass = (status) => {
    const classes = {
        'valid': 'bg-green-100 text-green-800',
        'invalid': 'bg-red-100 text-red-800',
        'submitted': 'bg-blue-100 text-blue-800',
        'rejected': 'bg-orange-100 text-orange-800',
        'draft': 'bg-gray-100 text-gray-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>
