<style>
/* General DataTables Pagination Container Style */
.dataTables_wrapper .dataTables_paginate {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
}

/* Style the filter container */
#TransitionTable_filter {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-bottom: 16px;
    /* Add spacing below the filter */
}

/* Style the label and input field inside the filter */
#TransitionTable_filter label {
    font-size: 17px;
    color: #000000;
    /* Match text color of the table header */
    display: flex;
    align-items: center;
}

/* Style the input field */
#TransitionTable_filter input[type="search"] {
    font-weight: 400;
    padding: 9px 15px;
    font-size: 14px;
    color: #000000cc;
    border: 1px solid rgb(209 213 219);
    border-radius: 5px;
    background: #fff;
    outline: none;
    transition: all 0.5s ease;
}

#TransitionTable_filter input[type="search"]:focus {
    outline: none;
    /* Removes the default outline */
    border: 1px solid #4b5563;
    box-shadow: none;
    /* Removes any focus box-shadow */
}

#TransitionTable_filter {
    float: left;
}

.dataTables_wrapper {
    margin-bottom: 10px;
}
</style>

<template>

    <Head title="Stock Transition" />
    <Banner />
    <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
        <Header />
        <div class="w-full md:w-5/6 py-12 space-y-24">






<!-- Header Section -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between w-full space-y-4 md:space-y-0 mb-6">
  <!-- Left: Back Button and Title -->
  <div class="flex items-center space-x-4">
    <Link href="/">
      <img src="/images/back-arrow.png" class="w-14 h-14" />
    </Link>
    <div>
      <p class="text-4xl font-bold tracking-wide text-black uppercase">Stock Transitions</p>
      <p class="text-lg italic font-semibold text-gray-700">
        <span class="px-3 py-1 text-white bg-black rounded-xl">{{ totalStockTransactions }}</span>
        / Total Stock Transition
      </p>
    </div>
  </div>

  <!-- Right: Date Filter + Buttons -->
  <div class="flex flex-wrap items-center justify-end gap-4">
    <!-- Date range -->
    <input
      v-model="startDate"
      type="date"
      class="px-4 py-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-500"
    />
    <span class="font-semibold">To</span>
    <input
      v-model="endDate"
      type="date"
      class="px-4 py-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-500"
    />

    <!-- Product search -->
    <input
      v-model="productSearch"
      type="text"
      placeholder="Search product..."
      class="px-4 py-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-500 min-w-[160px]"
    />

    <!-- Category filter -->
    <select
      v-model="categoryId"
      class="px-4 py-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-500 bg-white"
    >
      <option :value="null">All Categories</option>
      <option v-for="cat in categories" :key="cat.id" :value="cat.id">
        {{ cat.name }}
      </option>
    </select>

    <!-- Transaction type filter -->
    <select
      v-model="transactionType"
      class="px-4 py-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-500 bg-white"
    >
      <option value="">All Types</option>
      <option value="Added">Added</option>
      <option value="Sold">Sold</option>
      <option value="Deducted">Deducted</option>
      <option value="Deleted">Deleted</option>
    </select>

    <button
      @click="applyFilters"
      class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
    >
      Filter
    </button>

    <Link
      href="/stock-transition"
      class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600"
    >
      Reset
    </Link>

    <button
      @click="downloadFilteredPDF"
      class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700"
    >
      Download PDF
    </button>
  </div>
</div>


            <template v-if="allStockTransactions && allStockTransactions.length > 0">
                <div class="overflow-x-auto">
                    <table id="TransitionTable"
                        class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
                        <thead>
                            <tr
                                class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-[12px] text-white border-b border-blue-700">
                                <th class="p-4 font-semibold tracking-wide text-left uppercase">
                                    #
                                </th>
                                <th class="p-4 font-semibold tracking-wide text-left uppercase">
                                    Product Name
                                </th>
                                <th class="p-4 font-semibold tracking-wide text-left uppercase">
                                    Category
                                </th>
                                <th class="p-4 font-semibold tracking-wide text-left uppercase">
                                    Transaction Type & Date
                                </th>

                                <th class="p-4 font-semibold tracking-wide text-left uppercase">
                                    Quantity
                                </th>

                                <!-- <th class="p-4 font-semibold tracking-wide text-left uppercase">
                    Transaction Date
                 </th> -->
                                <th class="p-4 font-semibold tracking-wide text-left uppercase">
                                    Supplier
                                </th>
                                <th class="p-4 font-semibold tracking-wide text-left uppercase">
                                    Reason
                                </th>
                                <th class="p-4 font-semibold tracking-wide text-left uppercase">
                                    Reason Add
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-[13px] font-normal">
                            <tr v-for="(stock, index) in allStockTransactions" :key="stock.id"
                                class="transition duration-200 ease-in-out hover:bg-gray-200 hover:shadow-lg">
                                <td class="px-6 py-3 text- first-letter:">{{ index + 1 }}</td>
                                <td class="p-4 font-bold border-gray-200">
                                    {{ stock.product?.name || "N/A" }}
                                </td>
                                <td class="p-4 border-gray-200">
                                    {{ stock.product?.category?.name || "N/A" }}
                                </td>
                                <td class="p-4 border-gray-200">
                                    <span :class="{
                                        'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-300':
                                            stock.transaction_type === 'Added',
                                        'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-300':
                                            stock.transaction_type === 'Deducted',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-300':
                                            stock.transaction_type === 'Sold',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300':
                                            stock.transaction_type === 'Deleted' ||
                                            !stock.transaction_type,
                                    }" class="font-medium me-2 px-2.5 py-0.5 rounded">
                                        {{ stock.transaction_type || "N/A" }}
                                    </span>

                                    <br />
                                    {{ stock.transaction_date || "N/A" }}
                                </td>
                                <td class="p-4 font-bold border-gray-200">
                                    {{ stock.quantity || "N/A" }}
                                </td>

                                <td class="p-4 border-gray-200">
                                    {{ stock?.product?.supplier?.name || "N/A" }}
                                </td>
                                <td class="p-4 font-bold border-gray-200">
                                    {{ stock.reason || "N/A" }}
                                </td>

                                <td class="p-4 border-gray-200">
                                    <button class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600"
                                        @click="openEditModal(stock)">
                                        Add
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
            <template v-else>
                <div class="col-span-4 text-center text-blue-500">
                    <p class="text-center text-red-500 text-[17px]">
                        No Stock Transition Available
                    </p>
                </div>
            </template>
        </div>
    </div>

    <StockUpdateModel :stocks="allStockTransactions" :selected-stock="selectedStock" v-model:open="isEditModalOpen" />
    <Footer />
</template>

<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { router } from '@inertiajs/vue3';
import { Head, Link } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import StockUpdateModel from "@/Components/custom/StockTransResonModel.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";
import jsPDF from 'jspdf';
import 'jspdf-autotable';

const props = defineProps({
    allStockTransactions: Array,
    totalStockTransactions: Number,
    categories: Array,
    startDate: String,
    endDate: String,
    productSearch: String,
    categoryId: Number,
    transactionType: String,
});

const allStockTransactions = ref(props.allStockTransactions);

const form = useForm({});

const isEditModalOpen = ref(false);
const selectedStock = ref(null);

const openEditModal = (stock) => {
    selectedStock.value = stock;
    isEditModalOpen.value = true;
};

// Bind to reactive refs
const startDate       = ref(props.startDate || '');
const endDate         = ref(props.endDate || '');
const productSearch   = ref(props.productSearch || '');
const categoryId      = ref(props.categoryId || null);
const transactionType = ref(props.transactionType || '');

const applyFilters = () => {
    if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
        alert("Start date cannot be after end date.");
        return;
    }
    router.get(route('stock-transition.index'), {
        start_date:       startDate.value,
        end_date:         endDate.value,
        product_search:   productSearch.value,
        category_id:      categoryId.value,
        transaction_type: transactionType.value,
    });
};


const downloadFilteredPDF = () => {
    const doc = new jsPDF('l', 'mm', 'a4');
    doc.setFontSize(14);
    doc.text("Stock Transitions", 14, 12);

    // Build active filter label
    const parts = [];
    if (startDate.value)       parts.push(`From: ${startDate.value}`);
    if (endDate.value)         parts.push(`To: ${endDate.value}`);
    if (productSearch.value)   parts.push(`Product: ${productSearch.value}`);
    if (transactionType.value) parts.push(`Type: ${transactionType.value}`);
    const catName = props.categories?.find(c => c.id === categoryId.value)?.name;
    if (catName)               parts.push(`Category: ${catName}`);

    doc.setFontSize(9);
    doc.text(parts.length ? parts.join('  |  ') : 'All records', 14, 18);
    doc.text(`Generated: ${new Date().toLocaleString()}`, 14, 22);

    // Read from DataTable visible rows (respects client-side search/sort)
    const rows = [];
    const $ = window.$;
    if ($ && $.fn.dataTable && $.fn.dataTable.isDataTable('#TransitionTable')) {
        const dt = $('#TransitionTable').DataTable();
        dt.rows({ search: 'applied' }).every(function () {
            const cells = Array.from(this.node().querySelectorAll('td'))
                .map(td => (td.textContent || '').trim());
            // cells: [#, product, category, type+date, qty, supplier, reason, add-btn]
            // We rebuild cleanly from data instead
            const d = this.data();
            rows.push(cells.slice(0, 7)); // exclude the last "Add" button column
        });
    }

    // Fallback: use server-side data directly
    if (rows.length === 0) {
        const data = Array.isArray(props.allStockTransactions) ? props.allStockTransactions : [];
        data.forEach((stock, index) => {
            rows.push([
                index + 1,
                stock.product?.name || 'N/A',
                stock.product?.category?.name || 'N/A',
                stock.transaction_type || 'N/A',
                stock.transaction_date || 'N/A',
                stock.quantity ?? 'N/A',
                stock.product?.supplier?.name || 'N/A',
                stock.reason || 'N/A',
            ]);
        });
    }

    doc.autoTable({
        head: [['#', 'Product Name', 'Category', 'Type', 'Date', 'Qty', 'Supplier', 'Reason']],
        body: rows,
        startY: 26,
        theme: 'striped',
        styles: { fontSize: 9, cellPadding: 2 },
        headStyles: { fillColor: [37, 99, 235], textColor: 255 },
        columnStyles: {
            0: { cellWidth: 10 },
            1: { cellWidth: 50 },
            2: { cellWidth: 35 },
            3: { cellWidth: 25 },
            4: { cellWidth: 28 },
            5: { cellWidth: 15, halign: 'center' },
            6: { cellWidth: 40 },
            7: { cellWidth: 60 },
        },
        margin: { top: 26, left: 8, right: 8 },
    });

    const label = parts.length ? parts.join('_').replace(/[^\w-]/g, '_') : 'All';
    doc.save(`StockTransitions_${label}.pdf`);
};


$(document).ready(function () {
    let table = $("#TransitionTable").DataTable({
        dom: "Bfrtip",
        pageLength: 10,
        buttons: [],
        columnDefs: [
            {
                targets: 2,
                searchable: false,
                orderable: false,
            },
        ],
        initComplete: function () {
            let searchInput = $("div.dataTables_filter input");
            searchInput.attr("placeholder", "Search ...");
            searchInput.on("keypress", function (e) {
                if (e.which == 13) {
                    table.search(this.value).draw();
                }
            });
        },
        language: {
            search: "",
        },
    });
});
</script>
