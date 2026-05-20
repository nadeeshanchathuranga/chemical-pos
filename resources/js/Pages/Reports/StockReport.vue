<template>
  <Head title="Product Stock Report" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
    <Header />
    <div class="w-full md:w-5/6 py-12 space-y-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4">
          <Link href="/products">
            <img src="/images/back-arrow.png" class="w-12 h-12" />
          </Link>
          <div>
            <p class="text-5xl font-bold tracking-wide text-black uppercase">Product Stock Report</p>
            <p class="text-xl italic font-semibold text-gray-700 mt-2">
              <span class="px-3 py-1 text-white bg-black rounded-xl">{{ totalProducts }}</span>
              / Products tracked
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <input
            v-model="startDate"
            type="date"
            class="px-4 py-3 text-lg border border-gray-300 rounded-md focus:ring focus:ring-blue-500"
          />
          <span class="font-semibold text-lg">To</span>
          <input
            v-model="endDate"
            type="date"
            class="px-4 py-3 text-lg border border-gray-300 rounded-md focus:ring focus:ring-blue-500"
          />
          <button @click="filterByDate" class="px-6 py-3 text-lg bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Filter
          </button>
          <Link href="/reports/stock-report" class="px-6 py-3 text-lg bg-gray-500 text-white rounded-lg hover:bg-gray-600">
            Reset
          </Link>
        </div>
      </div>

      <div class="grid gap-4 md:grid-cols-4">
        <div class="p-6 bg-white rounded-3xl shadow-md border border-gray-200">
          <p class="text-base uppercase text-gray-500">Total Products</p>
          <p class="mt-4 text-4xl font-bold text-black">{{ totalProducts }}</p>
        </div>
        <div class="p-6 bg-white rounded-3xl shadow-md border border-gray-200">
          <p class="text-base uppercase text-gray-500">Total Transactions</p>
          <p class="mt-4 text-4xl font-bold text-black">{{ totalTransactions }}</p>
        </div>
        <div class="p-6 bg-white rounded-3xl shadow-md border border-gray-200">
          <p class="text-base uppercase text-gray-500">Total Added Qty</p>
          <p class="mt-4 text-4xl font-bold text-black">{{ totalAdded }}</p>
        </div>
        <div class="p-6 bg-white rounded-3xl shadow-md border border-gray-200">
          <p class="text-base uppercase text-gray-500">Total Deducted Qty</p>
          <p class="mt-4 text-4xl font-bold text-black">{{ totalDeducted }}</p>
        </div>
      </div>

      <div class="overflow-x-auto bg-white rounded-3xl shadow-md border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <p class="text-2xl font-semibold text-black">Product Summary</p>
          <!-- <button @click="downloadProductSummaryPDF" class="px-6 py-3 text-white bg-orange-600 rounded-lg hover:bg-orange-700">
            Download Summary
          </button> -->
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse table-auto">
            <thead>
              <tr class="text-lg font-semibold text-gray-700 uppercase border-b border-gray-200">
                <th class="p-4">#</th>
                <th class="p-4">Product</th>
                <th class="p-4">Code</th>
                <th class="p-4">Supplier</th>
                <th class="p-4">Current Stock</th>
                <th class="p-4">Added Qty</th>
                <th class="p-4">Deducted Qty</th>
              </tr>
            </thead>
            <tbody class="text-lg text-gray-700">
              <tr
                v-for="(product, index) in productSummaries"
                :key="product.id"
                class="border-b border-gray-200 hover:bg-gray-50"
              >
                <td class="p-4">{{ index + 1 }}</td>
                <td class="p-4 font-semibold text-black">{{ product.name || 'N/A' }}</td>
                <td class="p-4">{{ product.code || 'N/A' }}</td>
                <td class="p-4">{{ product.supplier_name || 'N/A' }}</td>
                <td class="p-4">{{ product.current_stock }}</td>
                <td class="p-4 text-green-600">{{ product.added_qty }}</td>
                <td class="p-4 text-red-600">{{ product.deducted_qty }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="overflow-x-auto bg-white rounded-3xl shadow-md border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <p class="text-2xl font-semibold text-black">Stock Transaction Details</p>
          <!-- <button @click="downloadTransactionPDF" class="px-6 py-3 text-white bg-orange-600 rounded-lg hover:bg-orange-700">
            Download Details
          </button> -->
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse table-auto">
            <thead>
              <tr class="text-lg font-semibold text-gray-700 uppercase border-b border-gray-200">
                <th class="p-4">#</th>
                <th class="p-4">Product</th>
                <th class="p-4">Type</th>
                <th class="p-4">Date</th>
                <th class="p-4">Quantity</th>
                <th class="p-4">Supplier</th>
                <th class="p-4">Reason</th>
              </tr>
            </thead>
            <tbody class="text-lg text-gray-700">
              <tr
                v-for="(stock, index) in stockTransactions"
                :key="stock.id"
                class="border-b border-gray-200 hover:bg-gray-50"
              >
                <td class="p-4">{{ index + 1 }}</td>
                <td class="p-4 font-semibold text-black">{{ stock.product?.name || 'N/A' }}</td>
                <td class="p-4">
                  <span
                    :class="{
                      'bg-green-100 text-green-800 px-2 py-1 rounded-full': stock.transaction_type === 'Added',
                      'bg-red-100 text-red-800 px-2 py-1 rounded-full': stock.transaction_type === 'Deducted',
                      'bg-gray-100 text-gray-800 px-2 py-1 rounded-full': !stock.transaction_type,
                    }"
                  >
                    {{ stock.transaction_type || 'N/A' }}
                  </span>
                </td>
                <td class="p-4">{{ stock.transaction_date || 'N/A' }}</td>
                <td class="p-4">{{ stock.quantity }}</td>
                <td class="p-4">{{ stock.product?.supplier?.name || 'N/A' }}</td>
                <td class="p-4">{{ stock.reason || 'N/A' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <Footer />
</template>

<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import jsPDF from "jspdf";
import "jspdf-autotable";

const props = defineProps({
  productSummaries: Array,
  stockTransactions: Array,
  totalProducts: Number,
  totalTransactions: Number,
  totalAdded: Number,
  totalDeducted: Number,
  currentStock: Number,
  startDate: String,
  endDate: String,
});

const startDate = ref(props.startDate || "");
const endDate = ref(props.endDate || "");

const filterByDate = () => {
  if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
    alert("Start date cannot be after end date.");
    return;
  }

  router.get(route("reports.stockReport"), {
    start_date: startDate.value,
    end_date: endDate.value,
  });
};

const downloadProductSummaryPDF = () => {
  const doc = new jsPDF();
  doc.text("Product Stock Summary", 14, 12);

  const rows = props.productSummaries.map((product, index) => [
    index + 1,
    product.name || "N/A",
    product.code || "N/A",
    product.supplier_name || "N/A",
    product.current_stock,
    product.added_qty,
    product.deducted_qty,
  ]);

  doc.autoTable({
    head: [["#", "Product", "Code", "Supplier", "Current Stock", "Added", "Deducted"]],
    body: rows,
    startY: 20,
  });

  doc.save("ProductStockSummary.pdf");
};

const downloadTransactionPDF = () => {
  const doc = new jsPDF();
  doc.text("Stock Transaction Details", 14, 12);

  const rows = props.stockTransactions.map((stock, index) => [
    index + 1,
    stock.product?.name || "N/A",
    stock.transaction_type || "N/A",
    stock.transaction_date || "N/A",
    stock.quantity,
    stock.product?.supplier?.name || "N/A",
    stock.reason || "N/A",
  ]);

  doc.autoTable({
    head: [["#", "Product", "Type", "Date", "Quantity", "Supplier", "Reason"]],
    body: rows,
    startY: 20,
  });

  doc.save("StockTransactionDetails.pdf");
};
</script>
