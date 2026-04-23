<template>
    <AuthenticatedLayout>
        <Head title="Electronic Invoices" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Electronic Invoices (ETA)</h2>
                    <Link :href="route('invoices.create')" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                        Create New Invoice
                    </Link>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <div class="text-sm text-gray-500">Total Invoices</div>
                        <div class="text-2xl font-bold">{{ stats.total || 0 }}</div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <div class="text-sm text-gray-500">Valid</div>
                        <div class="text-2xl font-bold text-green-600">{{ stats.valid || 0 }}</div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <div class="text-sm text-gray-500">Invalid</div>
                        <div class="text-2xl font-bold text-red-600">{{ stats.invalid || 0 }}</div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <div class="text-sm text-gray-500">Pending</div>
                        <div class="text-2xl font-bold text-yellow-600">{{ stats.pending || 0 }}</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white p-4 rounded-lg shadow mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <input v-model="filters.date_from" type="date" class="border rounded px-3 py-2" placeholder="From Date" />
                        <input v-model="filters.date_to" type="date" class="border rounded px-3 py-2" placeholder="To Date" />
                        <select v-model="filters.status" class="border rounded px-3 py-2">
                            <option value="">All Statuses</option>
                            <option value="valid">Valid</option>
                            <option value="invalid">Invalid</option>
                            <option value="submitted">Submitted</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <button @click="searchInvoices" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Search</button>
                    </div>
                </div>

                <!-- Invoices Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ETA UUID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="invoice in invoices" :key="invoice.id">
                                    <td class="px-6 py-4">{{ invoice.invoice_number }}</td>
                                    <td class="px-6 py-4">{{ invoice.customer_name }}</td>
                                    <td class="px-6 py-4">{{ invoice.issue_date }}</td>
                                    <td class="px-6 py-4">{{ formatCurrency(invoice.total_amount) }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="getStatusClass(invoice.status)" class="px-2 py-1 text-xs rounded-full">
                                            {{ invoice.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs">{{ invoice.uuid ? invoice.uuid.substring(0, 8) + '...' : '-' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <Link v-if="invoice.status === 'draft'" :href="route('invoices.submit', invoice.id)" class="text-green-600 hover:text-green-900 mr-2">Submit</Link>
                                        <Link :href="route('invoices.show', invoice.id)" class="text-blue-600 hover:text-blue-900 mr-2">View</Link>
                                        <button @click="syncInvoice(invoice.id)" class="text-yellow-600 hover:text-yellow-900">Sync</button>
                                    </td>
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
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    invoices: Array,
    stats: Object
});

const filters = ref({
    date_from: '',
    date_to: '',
    status: ''
});

const searchInvoices = () => {
    router.get(route('invoices.index'), filters.value);
};

const syncInvoice = (id) => {
    router.post(route('invoices.sync', id));
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-EG', { style: 'currency', currency: 'EGP' }).format(amount);
};

const getStatusClass = (status) => {
    const classes = {
        'valid': 'bg-green-100 text-green-800',
        'invalid': 'bg-red-100 text-red-800',
        'submitted': 'bg-blue-100 text-blue-800',
        'rejected': 'bg-orange-100 text-orange-800',
        'draft': 'bg-gray-100 text-gray-800',
        'cancelled': 'bg-purple-100 text-purple-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>
