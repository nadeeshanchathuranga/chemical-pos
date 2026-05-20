<template>
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
    <Header />
    <Banner />

    <div class="w-full md:w-5/6 py-12 space-y-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-center space-x-4">
          <Link href="/pos">
            <img src="/images/back-arrow.png" class="w-14 h-14" alt="Back" />
          </Link>
          <div>
            <p class="text-4xl font-bold tracking-wide text-black uppercase">Credit Bills</p>
            <p class="mt-2 text-base text-slate-600">Review outstanding credit sales and load them into the POS for payment or closure.</p>
          </div>
        </div>
        <Link
          :href="route('pos.index')"
          class="rounded-xl bg-slate-900 px-5 py-3 text-white font-semibold shadow-lg hover:bg-slate-800"
        >
          Back to POS
        </Link>
      </div>

      <div class="bg-white border border-slate-200 shadow-lg rounded-3xl p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div class="grid w-full lg:grid-cols-3 grid-cols-1 gap-3">
            <input
              v-model="search"
              type="text"
              placeholder="Search order ID, customer, phone or email"
              class="w-full border border-slate-300 rounded-xl px-4 py-3 text-base text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <input
              v-model="customerName"
              type="text"
              placeholder="Filter by customer name"
              class="w-full border border-slate-300 rounded-xl px-4 py-3 text-base text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <div class="grid grid-cols-2 gap-2">
              <input
                v-model="startDate"
                type="date"
                class="w-full border border-slate-300 rounded-xl px-3 py-3 text-base text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <input
                v-model="endDate"
                type="date"
                class="w-full border border-slate-300 rounded-xl px-3 py-3 text-base text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
          <div class="flex flex-wrap gap-3">
            <button
              type="button"
              @click="searchCreditBills"
              class="rounded-xl bg-blue-600 px-5 py-3 text-white font-semibold hover:bg-blue-700"
            >
              Search
            </button>
            <button
              v-if="search"
              type="button"
              @click="clearSearch"
              class="rounded-xl border border-slate-300 px-5 py-3 text-slate-700 hover:bg-slate-100"
            >
              Clear
            </button>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-3xl bg-white shadow-lg border border-slate-200">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Order ID</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Customer</th>
            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Total</th>
            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Advance Paid</th>
            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Remaining Balance</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
          <tr v-if="creditSales.data.length === 0">
            <td colspan="7" class="px-6 py-8 text-center text-slate-600">No credit bills found.</td>
          </tr>
          <tr v-for="sale in creditSales.data" :key="sale.id" class="hover:bg-slate-50">
            <td class="px-6 py-4 text-slate-900 font-medium">{{ sale.order_id }}</td>
            <td class="px-6 py-4 text-slate-700">{{ sale.customer?.name || 'Walk-in' }}</td>
            <td class="px-6 py-4 text-right text-slate-800">{{ formatCurrency(sale.total_amount) }} LKR</td>
            <td class="px-6 py-4 text-right text-slate-800">{{ formatCurrency(sale.paid_amount) }} LKR</td>
            <td class="px-6 py-4 text-right text-slate-800">{{ formatCurrency(remaining(sale)) }} LKR</td>
            <td class="px-6 py-4 text-left">
              <span
                :class="[
                  'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold',
                  status(sale) === 'Closed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800',
                ]"
              >
                {{ status(sale) }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <template v-if="status(sale) !== 'Closed'">
                <Link
                  :href="route('pos.index', { credit_sale_id: sale.id })"
                  class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                >
                  Load Bill
                </Link>
              </template>
              <template v-else>
                <span class="inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-500">
                  Closed
                </span>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="creditSales.links" class="mt-6 flex flex-wrap items-center justify-center gap-2">
      <Link
        v-for="link in creditSales.links"
        :key="link.label"
        :href="link.url"
        class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
        v-html="link.label"
      />
    </div>
      </div>
  </div>
</template>

<script setup>
import Header from '@/Components/custom/Header.vue';
import Banner from '@/Components/Banner.vue';
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
  creditSales: Object,
  filters: Object,
});

const search = ref(props.filters.search || '');
const customerName = ref(props.filters.customer_name || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const searchCreditBills = () => {
  router.get(route('creditBills.index', {
    search: search.value,
    customer_name: customerName.value,
    start_date: startDate.value,
    end_date: endDate.value,
  }), {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearSearch = () => {
  search.value = '';
  customerName.value = '';
  startDate.value = '';
  endDate.value = '';
  router.get(route('creditBills.index'), {
    preserveState: true,
    preserveScroll: true,
  });
};

const formatCurrency = (value) => Number(value || 0).toFixed(2);
const due = (sale) => Number(sale.total_amount || 0) - Number(sale.paid_amount || 0);
const remaining = (sale) => Number(sale.balance_due ?? due(sale));
const status = (sale) => {
  if (sale.status) {
    return sale.status;
  }
  const dueAmount = remaining(sale);
  return dueAmount <= 0 ? 'Closed' : 'Open';
};
</script>
