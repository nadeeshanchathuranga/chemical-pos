<style>
.dataTables_wrapper .dataTables_paginate {
  display: flex; justify-content: center; align-items: center; margin-top: 20px;
}
#expenseTbl_filter {
  display: flex; justify-content: flex-end; align-items: center; margin-bottom: 16px; float: left;
}
#expenseTbl_filter label { font-size: 17px; color: #000; display: flex; align-items: center; }
#expenseTbl_filter input[type="search"] {
  font-weight: 400; padding: 9px 15px; font-size: 14px; color: #000000cc;
  border: 1px solid rgb(209 213 219); border-radius: 5px; background: #fff;
  outline: none; transition: all 0.5s ease;
}
#expenseTbl_filter input[type="search"]:focus { border: 1px solid #4b5563; box-shadow: none; }
.dataTables_wrapper { margin-bottom: 10px; }
</style>

<template>
  <Head title="Expenses" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-6">
    <Header />

    <div class="w-full md:w-5/6 py-8 space-y-8">

      <!-- ── Title + Date Filters ── -->
      <div class="flex md:flex-row flex-col items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
          <Link href="/accounting"><img src="/images/back-arrow.png" class="w-12 h-12" /></Link>
          <p class="text-3xl font-bold tracking-wide text-black uppercase">Expenses</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <input v-model="startDate" type="date"
            class="px-3 py-2 border border-blue-300 rounded-lg text-blue-700 bg-white focus:outline-none" />
          <span class="text-blue-600 font-bold">To</span>
          <input v-model="endDate" type="date"
            class="px-3 py-2 border border-blue-300 rounded-lg text-blue-700 bg-white focus:outline-none" />
          <select v-model="selectedCat"
            class="px-3 py-2 border border-blue-300 rounded-lg text-blue-700 bg-white focus:outline-none">
            <option value="">All Categories</option>
            <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
          </select>
          <button @click="applyFilter"
            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">Filter</button>
          <Link href="/expenses"
            class="px-5 py-2 bg-gray-400 hover:bg-gray-500 text-white font-semibold rounded-lg">Reset</Link>
        </div>
      </div>

      <!-- ── P&L Quick Strip ── -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
          <p class="text-2xl font-extrabold text-blue-700">{{ fmt(totalRevenue) }}</p>
          <p class="text-xs font-semibold text-gray-400 uppercase mt-1">Revenue</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
          <p class="text-2xl font-extrabold text-indigo-600">{{ fmt(totalCOGS) }}</p>
          <p class="text-xs font-semibold text-gray-400 uppercase mt-1">Cost of Goods Sold</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
          <p class="text-2xl font-extrabold text-red-600">{{ fmt(totalExpenses) }}</p>
          <p class="text-xs font-semibold text-gray-400 uppercase mt-1">Total Expenses</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
          <p :class="netProfit >= 0 ? 'text-green-600' : 'text-red-600'" class="text-2xl font-extrabold">{{ fmt(netProfit) }}</p>
          <p class="text-xs font-semibold text-gray-400 uppercase mt-1">Net Profit</p>
        </div>
      </div>

      <!-- ── Add Expense Form + Category Chart ── -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Form -->
        <div class="bg-white rounded-2xl shadow p-6 md:col-span-1">
          <h2 class="text-xl font-bold text-gray-800 mb-5">{{ editingExpense ? 'Edit Expense' : 'Record Expense' }}</h2>
          <form @submit.prevent="submitForm" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Category <span class="text-red-500">*</span></label>
              <select v-model="form.category"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Select category...</option>
                <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
              </select>
              <p v-if="form.errors.category" class="text-red-500 text-xs mt-1">{{ form.errors.category }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Description <span class="text-red-500">*</span></label>
              <input v-model="form.description" type="text" placeholder="e.g. Monthly rent"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
              <p v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Amount (LKR) <span class="text-red-500">*</span></label>
              <input v-model="form.amount" type="number" step="0.01" min="0.01" placeholder="0.00"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
              <p v-if="form.errors.amount" class="text-red-500 text-xs mt-1">{{ form.errors.amount }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Date <span class="text-red-500">*</span></label>
              <input v-model="form.date" type="date"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
              <p v-if="form.errors.date" class="text-red-500 text-xs mt-1">{{ form.errors.date }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Note</label>
              <textarea v-model="form.note" rows="2" placeholder="Optional..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"></textarea>
            </div>

            <div class="flex gap-3">
              <button type="submit" :disabled="form.processing"
                class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition disabled:opacity-50">
                {{ form.processing ? 'Saving...' : (editingExpense ? 'Update' : 'Save') }}
              </button>
              <button v-if="editingExpense" type="button" @click="cancelEdit"
                class="flex-1 py-2 bg-gray-400 hover:bg-gray-500 text-white font-bold rounded-lg transition">
                Cancel
              </button>
            </div>
          </form>
        </div>

        <!-- Category Breakdown -->
        <div class="bg-white rounded-2xl shadow p-6 md:col-span-2">
          <h2 class="text-xl font-bold text-gray-800 mb-4">Expense by Category</h2>
          <div v-if="Object.keys(expenseByCategory).length > 0" class="space-y-3">
            <div v-for="(amount, cat) in sortedByCategory" :key="cat">
              <div class="flex justify-between text-sm font-medium text-gray-700 mb-1">
                <span>{{ cat }}</span>
                <span>{{ fmt(amount) }} ({{ ((amount / totalExpenses) * 100).toFixed(1) }}%)</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="h-3 rounded-full bg-red-500 transition-all duration-500"
                  :style="{ width: ((amount / totalExpenses) * 100) + '%' }"></div>
              </div>
            </div>
          </div>
          <div v-else class="flex items-center justify-center h-32 text-gray-400">
            No expenses in the selected period.
          </div>
        </div>

      </div>

      <!-- ── Expenses Table ── -->
      <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-bold text-gray-800">Expense Records</h2>
          <div class="flex items-center gap-3">
            <span class="bg-red-100 text-red-700 text-sm font-semibold px-3 py-1 rounded-full">
              {{ expenses.length }} records
            </span>
            <button @click="downloadPDF"
              class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-lg transition">
              Download PDF
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table id="expenseTbl"
            class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
            <thead>
              <tr class="bg-gradient-to-r from-red-600 via-red-500 to-red-600 text-white text-[14px]">
                <th class="p-3 text-left font-semibold">#</th>
                <th class="p-3 text-left font-semibold uppercase">Date</th>
                <th class="p-3 text-left font-semibold uppercase">Category</th>
                <th class="p-3 text-left font-semibold uppercase">Description</th>
                <th class="p-3 text-right font-semibold uppercase">Amount (LKR)</th>
                <th class="p-3 text-left font-semibold uppercase">Note</th>
                <th class="p-3 text-left font-semibold uppercase">Added By</th>
                <th class="p-3 text-center font-semibold uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="text-[13px]">
              <tr v-for="(exp, i) in expenses" :key="exp.id"
                class="hover:bg-gray-50 transition duration-150">
                <td class="p-3 border-t border-gray-200 text-center">{{ i + 1 }}</td>
                <td class="p-3 border-t border-gray-200">{{ formatDate(exp.date) }}</td>
                <td class="p-3 border-t border-gray-200">
                  <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">{{ exp.category }}</span>
                </td>
                <td class="p-3 border-t border-gray-200 font-medium">{{ exp.description }}</td>
                <td class="p-3 border-t border-gray-200 text-right font-bold text-red-700">{{ fmt(exp.amount) }}</td>
                <td class="p-3 border-t border-gray-200 text-gray-500 text-xs">{{ exp.note || '—' }}</td>
                <td class="p-3 border-t border-gray-200 text-gray-500">{{ exp.user?.name || '—' }}</td>
                <td class="p-3 border-t border-gray-200 text-center">
                  <div class="inline-flex gap-2">
                    <button @click="openEdit(exp)"
                      class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded-lg transition">
                      Edit
                    </button>
                    <button @click="confirmDelete(exp)"
                      class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded-lg transition">
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-gray-50 text-[13px] font-bold">
              <tr>
                <td colspan="4" class="p-3 text-right">Total:</td>
                <td class="p-3 text-right text-red-700">{{ fmt(totalExpenses) }}</td>
                <td colspan="3"></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

    </div>
  </div>

  <!-- Delete Modal -->
  <div v-if="deletingExpense" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-sm text-center space-y-4">
      <p class="text-lg font-bold text-gray-800">Delete Expense?</p>
      <p class="text-gray-500 text-sm">
        "<span class="font-bold">{{ deletingExpense.description }}</span>" — {{ fmt(deletingExpense.amount) }}
      </p>
      <div class="flex justify-center space-x-4 pt-2">
        <button @click="deletingExpense = null"
          class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg">Cancel</button>
        <button @click="doDelete"
          class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg">Delete</button>
      </div>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useForm, Link, Head, router } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import jsPDF from "jspdf";
import "jspdf-autotable";

const props = defineProps({
  expenses:          Array,
  totalExpenses:     Number,
  expenseByCategory: Object,
  categories:        Array,
  startDate:         String,
  endDate:           String,
  selectedCategory:  String,
  totalRevenue:      Number,
  totalCOGS:         Number,
  grossProfit:       Number,
  netProfit:         Number,
});

const startDate   = ref(props.startDate  || "");
const endDate     = ref(props.endDate    || "");
const selectedCat = ref(props.selectedCategory || "");
const today       = new Date().toISOString().slice(0, 10);

const fmt        = (n) => "Rs. " + Number(n ?? 0).toLocaleString("en-LK", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatDate = (d) => d ? new Date(d).toLocaleDateString("en-LK") : "—";

// ── Filter ──────────────────────────────────────────────────────────────
const applyFilter = () => {
  router.get(route("expenses.index"), {
    start_date: startDate.value,
    end_date:   endDate.value,
    category:   selectedCat.value,
  }, { preserveScroll: true, preserveState: false });
};

// ── Form ─────────────────────────────────────────────────────────────────
const editingExpense = ref(null);
const form = useForm({ category: "", description: "", amount: "", date: today, note: "" });

const submitForm = () => {
  if (editingExpense.value) {
    form.put(route("expenses.update", editingExpense.value.id), {
      preserveScroll: true,
      onSuccess: () => { editingExpense.value = null; form.reset(); form.date = today; },
    });
  } else {
    form.post(route("expenses.store"), {
      preserveScroll: true,
      onSuccess: () => { form.reset(); form.date = today; },
    });
  }
};

const openEdit = (exp) => {
  editingExpense.value = exp;
  form.category    = exp.category;
  form.description = exp.description;
  form.amount      = exp.amount;
  form.date        = exp.date ? exp.date.slice(0, 10) : today;
  form.note        = exp.note || "";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const cancelEdit = () => {
  editingExpense.value = null;
  form.reset();
  form.date = today;
};

// ── Delete ───────────────────────────────────────────────────────────────
const deletingExpense = ref(null);
const confirmDelete = (exp) => { deletingExpense.value = exp; };
const doDelete = () => {
  router.delete(route("expenses.destroy", deletingExpense.value.id), {
    preserveScroll: true,
    onSuccess: () => { deletingExpense.value = null; },
  });
};

// ── Category breakdown sorted ────────────────────────────────────────────
const sortedByCategory = computed(() =>
  Object.fromEntries(
    Object.entries(props.expenseByCategory).sort(([, a], [, b]) => b - a)
  )
);

// ── PDF ───────────────────────────────────────────────────────────────────
const downloadPDF = () => {
  const doc = new jsPDF("l", "mm", "a4");
  doc.setFontSize(16);
  doc.text("Expense Report", 14, 12);
  doc.setFontSize(10);
  doc.text(`Generated: ${new Date().toLocaleString()}`, 14, 18);

  const rows = props.expenses.map((e, i) => [
    i + 1,
    formatDate(e.date),
    e.category,
    e.description,
    fmt(e.amount),
    e.note || "—",
    e.user?.name || "—",
  ]);

  doc.autoTable({
    head: [["#", "Date", "Category", "Description", "Amount (LKR)", "Note", "Added By"]],
    body: rows,
    foot: [[{ content: "Total:", colSpan: 4, styles: { halign: "right", fontStyle: "bold" } },
            fmt(props.totalExpenses), "", ""]],
    startY: 22,
    theme: "striped",
    styles: { fontSize: 9, cellPadding: 2 },
    headStyles: { fillColor: [185, 28, 28] },
  });

  doc.save(`Expenses_${new Date().toISOString().slice(0, 10)}.pdf`);
};

// ── DataTables ────────────────────────────────────────────────────────────
onMounted(() => {
  if (props.expenses.length > 0) {
    $("#expenseTbl").DataTable({
      dom: "Bfrtip",
      pageLength: 15,
      buttons: [],
      columnDefs: [{ targets: [7], orderable: false, searchable: false }],
      language: { search: "" },
      initComplete() { $("div.dataTables_filter input").attr("placeholder", "Search..."); },
    });
  }
});
</script>
