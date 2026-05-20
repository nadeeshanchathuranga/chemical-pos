<template>
  <Head title="Accounting" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-6">
    <Header />

    <div class="w-full md:w-5/6 py-8 space-y-10">

      <!-- ── Title + Date Filter ── -->
      <div class="flex md:flex-row flex-col items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
          <Link href="/"><img src="/images/back-arrow.png" class="w-12 h-12" /></Link>
          <p class="text-3xl font-bold tracking-wide text-black uppercase">Accounting</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <input v-model="startDate" type="date"
            class="px-3 py-2 border border-blue-300 rounded-lg text-blue-700 bg-white focus:outline-none" />
          <span class="text-blue-600 font-bold">To</span>
          <input v-model="endDate" type="date"
            class="px-3 py-2 border border-blue-300 rounded-lg text-blue-700 bg-white focus:outline-none" />
          <button @click="applyFilter"
            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">Filter</button>
          <Link href="/accounting"
            class="px-5 py-2 bg-gray-400 hover:bg-gray-500 text-white font-semibold rounded-lg">Reset</Link>
        </div>
      </div>

      <!-- ── P&L Statement ── -->
      <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-gray-800">Profit & Loss Statement</h2>
          <button @click="downloadPL"
            class="px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg text-sm">
            Download PDF
          </button>
        </div>

        <div class="space-y-2 max-w-lg">
          <div class="flex justify-between py-2 border-b border-gray-200">
            <span class="font-semibold text-gray-700">Revenue (Sales)</span>
            <span class="font-bold text-blue-700">{{ fmt(totalRevenue) }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-gray-200 text-gray-600">
            <span class="pl-4">Cost of Goods Sold (COGS)</span>
            <span class="text-red-600">− {{ fmt(totalCOGS) }}</span>
          </div>
          <div class="flex justify-between py-3 border-b-2 border-gray-400 font-semibold">
            <span>Gross Profit</span>
            <span :class="grossProfit >= 0 ? 'text-green-600' : 'text-red-600'">{{ fmt(grossProfit) }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-gray-200 text-gray-600">
            <span class="pl-4">Operating Expenses</span>
            <span class="text-red-600">− {{ fmt(totalExpenses) }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-gray-200 text-gray-600">
            <span class="pl-4">Supplier Payments</span>
            <span class="text-red-600">− {{ fmt(totalSupplierPaid) }}</span>
          </div>
          <div class="flex justify-between py-4 text-xl font-extrabold">
            <span>Net Profit</span>
            <span :class="netProfit >= 0 ? 'text-green-600' : 'text-red-600'">{{ fmt(netProfit) }}</span>
          </div>
        </div>
      </div>

      <!-- ── KPI Overview ── -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-blue-600 rounded-xl shadow p-5 flex flex-col items-center">
          <p class="text-2xl font-extrabold text-white">{{ fmt(totalRevenue) }}</p>
          <p class="text-xs font-semibold text-blue-100 uppercase mt-1">Total Revenue</p>
        </div>
        <div class="bg-indigo-600 rounded-xl shadow p-5 flex flex-col items-center">
          <p class="text-2xl font-extrabold text-white">{{ fmt(totalCOGS) }}</p>
          <p class="text-xs font-semibold text-indigo-100 uppercase mt-1">COGS</p>
        </div>
        <div class="bg-yellow-500 rounded-xl shadow p-5 flex flex-col items-center">
          <p class="text-2xl font-extrabold text-white">{{ fmt(grossProfit) }}</p>
          <p class="text-xs font-semibold text-yellow-100 uppercase mt-1">Gross Profit</p>
        </div>
        <div class="bg-red-600 rounded-xl shadow p-5 flex flex-col items-center">
          <p class="text-2xl font-extrabold text-white">{{ fmt(totalExpenses) }}</p>
          <p class="text-xs font-semibold text-red-100 uppercase mt-1">Expenses</p>
        </div>
        <div :class="netProfit >= 0 ? 'bg-green-600' : 'bg-red-800'" class="rounded-xl shadow p-5 flex flex-col items-center">
          <p class="text-2xl font-extrabold text-white">{{ fmt(netProfit) }}</p>
          <p class="text-xs font-semibold text-green-100 uppercase mt-1">Net Profit</p>
        </div>
      </div>

      <!-- ── Revenue by Method + Expense by Category ── -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Revenue by Payment Method -->
        <div class="bg-white rounded-2xl shadow p-6">
          <h2 class="text-xl font-bold text-gray-800 mb-4">Revenue by Payment Method</h2>
          <div class="space-y-3">
            <div v-for="(amount, method) in sortedRevenue" :key="method">
              <div class="flex justify-between text-sm font-medium text-gray-700 mb-1">
                <span class="capitalize">{{ method || 'Unknown' }}</span>
                <span>{{ fmt(amount) }} ({{ totalRevenue > 0 ? ((amount / totalRevenue) * 100).toFixed(1) : 0 }}%)</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="h-3 rounded-full bg-blue-500"
                  :style="{ width: totalRevenue > 0 ? ((amount / totalRevenue) * 100) + '%' : '0%' }"></div>
              </div>
            </div>
            <div v-if="!Object.keys(revenueByMethod).length" class="text-gray-400 text-center py-4">No sales data.</div>
          </div>
        </div>

        <!-- Expense by Category -->
        <div class="bg-white rounded-2xl shadow p-6">
          <h2 class="text-xl font-bold text-gray-800 mb-4">Expenses by Category</h2>
          <div class="space-y-3">
            <div v-for="(amount, cat) in sortedExpenses" :key="cat">
              <div class="flex justify-between text-sm font-medium text-gray-700 mb-1">
                <span>{{ cat }}</span>
                <span>{{ fmt(amount) }} ({{ totalExpenses > 0 ? ((amount / totalExpenses) * 100).toFixed(1) : 0 }}%)</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="h-3 rounded-full bg-red-500"
                  :style="{ width: totalExpenses > 0 ? ((amount / totalExpenses) * 100) + '%' : '0%' }"></div>
              </div>
            </div>
            <div v-if="!Object.keys(expenseByCategory).length" class="text-gray-400 text-center py-4">No expenses.</div>
          </div>
        </div>

      </div>

      <!-- ── Monthly Trend ── -->
      <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">6-Month Trend</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-[13px] text-gray-700">
            <thead>
              <tr class="bg-gray-100 text-gray-600 uppercase text-xs font-semibold">
                <th class="p-3 text-left">Month</th>
                <th class="p-3 text-right">Revenue</th>
                <th class="p-3 text-right">Expenses</th>
                <th class="p-3 text-right">Net Profit</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in monthlyTrend" :key="row.month" class="border-t border-gray-100 hover:bg-gray-50">
                <td class="p-3 font-semibold">{{ formatMonth(row.month) }}</td>
                <td class="p-3 text-right text-blue-700 font-medium">{{ fmt(row.revenue) }}</td>
                <td class="p-3 text-right text-red-600 font-medium">{{ fmt(row.expenses) }}</td>
                <td class="p-3 text-right font-bold" :class="(row.revenue - row.expenses) >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ fmt(row.revenue - row.expenses) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── Bank Accounts ── -->
      <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-bold text-gray-800">Bank Accounts</h2>
          <div class="flex items-center gap-4">
            <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-2 text-center">
              <p class="text-lg font-extrabold text-blue-700">{{ fmt(totalBankBalance) }}</p>
              <p class="text-xs text-blue-500 font-semibold uppercase">Total Balance</p>
            </div>
            <button @click="showAddAccount = true"
              class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm">
              + Add Account
            </button>
          </div>
        </div>

        <div v-if="bankAccounts.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div v-for="acc in bankAccounts" :key="acc.id"
            class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition group">
            <div class="flex justify-between items-start mb-3">
              <div>
                <p class="text-lg font-bold text-gray-800">{{ acc.name }}</p>
                <p class="text-xs text-gray-400">{{ acc.bank_name || '' }}</p>
                <p v-if="acc.account_number" class="text-xs text-gray-400">Acc: {{ acc.account_number }}</p>
              </div>
              <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition">
                <button @click="editAccount(acc)" class="text-green-600 hover:text-green-800 text-sm font-bold">Edit</button>
                <button @click="confirmDeleteAccount(acc)" class="text-red-500 hover:text-red-700 text-sm font-bold">Delete</button>
              </div>
            </div>
            <p :class="acc.current_balance >= 0 ? 'text-blue-700' : 'text-red-700'"
              class="text-2xl font-extrabold mb-1">{{ fmt(acc.current_balance) }}</p>
            <p class="text-xs text-gray-400">Opening: {{ fmt(acc.opening_balance) }}</p>
            <Link :href="route('banking.show', acc.id)"
              class="mt-3 block text-center px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold rounded-lg text-sm transition">
              View Transactions →
            </Link>
          </div>
        </div>
        <div v-else class="text-center text-gray-400 py-8">
          No bank accounts added yet. Click "+ Add Account" to start.
        </div>
      </div>

      <!-- ── Quick Links ── -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Link href="/expenses"
          class="flex items-center gap-4 bg-red-50 border border-red-200 rounded-2xl p-5 hover:shadow-md transition">
          <div class="w-12 h-12 bg-red-600 rounded-xl flex items-center justify-center text-white text-2xl">💸</div>
          <div>
            <p class="text-lg font-bold text-gray-800">Expense Manager</p>
            <p class="text-sm text-gray-500">Track, categorise and analyse all business expenses.</p>
          </div>
        </Link>
        <Link href="/cheques"
          class="flex items-center gap-4 bg-purple-50 border border-purple-200 rounded-2xl p-5 hover:shadow-md transition">
          <div class="w-12 h-12 bg-purple-600 rounded-xl flex items-center justify-center text-white text-2xl">🏦</div>
          <div>
            <p class="text-lg font-bold text-gray-800">Cheque Management</p>
            <p class="text-sm text-gray-500">Track received &amp; issued cheques, PDC and clearance.</p>
          </div>
        </Link>
        <Link href="/reports"
          class="flex items-center gap-4 bg-orange-50 border border-orange-200 rounded-2xl p-5 hover:shadow-md transition">
          <div class="w-12 h-12 bg-orange-600 rounded-xl flex items-center justify-center text-white text-2xl">📊</div>
          <div>
            <p class="text-lg font-bold text-gray-800">Sales Reports</p>
            <p class="text-sm text-gray-500">Detailed sales analysis, charts and PDF exports.</p>
          </div>
        </Link>
      </div>

    </div>
  </div>

  <!-- ── Add / Edit Account Modal ── -->
  <div v-if="showAddAccount || editingAccount"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md space-y-4">
      <h2 class="text-xl font-bold text-gray-800">{{ editingAccount ? 'Edit Account' : 'Add Bank Account' }}</h2>
      <form @submit.prevent="submitAccount" class="space-y-3">
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Account Name <span class="text-red-500">*</span></label>
          <input v-model="accountForm.name" type="text" placeholder="e.g. Petty Cash"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
          <p v-if="accountForm.errors.name" class="text-red-500 text-xs mt-1">{{ accountForm.errors.name }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Bank Name</label>
          <input v-model="accountForm.bank_name" type="text" placeholder="e.g. Commercial Bank"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Account Number</label>
          <input v-model="accountForm.account_number" type="text"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>
        <div v-if="!editingAccount">
          <label class="block text-sm font-medium text-gray-600 mb-1">Opening Balance (LKR) <span class="text-red-500">*</span></label>
          <input v-model="accountForm.opening_balance" type="number" step="0.01" min="0" placeholder="0.00"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
          <p v-if="accountForm.errors.opening_balance" class="text-red-500 text-xs mt-1">{{ accountForm.errors.opening_balance }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Note</label>
          <input v-model="accountForm.note" type="text"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>
        <div class="flex gap-3 pt-2">
          <button type="submit" :disabled="accountForm.processing"
            class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg disabled:opacity-50">
            {{ accountForm.processing ? 'Saving...' : (editingAccount ? 'Update' : 'Create') }}
          </button>
          <button type="button" @click="closeAccountModal"
            class="flex-1 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold rounded-lg">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ── Delete Account Confirmation ── -->
  <div v-if="deletingAccount" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-sm text-center space-y-4">
      <p class="text-lg font-bold text-gray-800">Delete Account?</p>
      <p class="text-gray-500 text-sm">
        "<strong>{{ deletingAccount.name }}</strong>" and all its transaction history will be permanently removed.
      </p>
      <div class="flex justify-center space-x-4 pt-2">
        <button @click="deletingAccount = null"
          class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg">Cancel</button>
        <button @click="doDeleteAccount"
          class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg">Delete</button>
      </div>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { ref, computed } from "vue";
import { useForm, Link, Head, router } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import jsPDF from "jspdf";
import "jspdf-autotable";

const props = defineProps({
  totalRevenue:      Number,
  totalCOGS:         Number,
  grossProfit:       Number,
  totalExpenses:     Number,
  netProfit:         Number,
  totalSupplierPaid: Number,
  revenueByMethod:   Object,
  expenseByCategory: Object,
  bankAccounts:      Array,
  totalBankBalance:  Number,
  monthlyTrend:      Array,
  startDate:         String,
  endDate:           String,
});

const startDate = ref(props.startDate || "");
const endDate   = ref(props.endDate   || "");

const fmt = (n) => "Rs. " + Number(n ?? 0).toLocaleString("en-LK", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const formatMonth = (m) => {
  const [y, mo] = m.split("-");
  return new Date(y, mo - 1).toLocaleString("en-LK", { month: "short", year: "numeric" });
};

// ── Filter ──────────────────────────────────────────────────────────────
const applyFilter = () => {
  router.get(route("accounting.index"), {
    start_date: startDate.value,
    end_date:   endDate.value,
  }, { preserveScroll: true, preserveState: false });
};

// ── Sorted breakdowns ────────────────────────────────────────────────────
const sortedRevenue = computed(() =>
  Object.fromEntries(Object.entries(props.revenueByMethod).sort(([, a], [, b]) => b - a))
);
const sortedExpenses = computed(() =>
  Object.fromEntries(Object.entries(props.expenseByCategory).sort(([, a], [, b]) => b - a))
);

// ── Bank Account Modal ───────────────────────────────────────────────────
const showAddAccount  = ref(false);
const editingAccount  = ref(null);
const deletingAccount = ref(null);

const accountForm = useForm({
  name:            "",
  bank_name:       "",
  account_number:  "",
  opening_balance: "",
  note:            "",
});

const closeAccountModal = () => {
  showAddAccount.value  = false;
  editingAccount.value  = null;
  accountForm.reset();
};

const editAccount = (acc) => {
  editingAccount.value      = acc;
  accountForm.name           = acc.name;
  accountForm.bank_name      = acc.bank_name || "";
  accountForm.account_number = acc.account_number || "";
  accountForm.note           = acc.note || "";
};

const submitAccount = () => {
  if (editingAccount.value) {
    accountForm.put(route("bank-accounts.update", editingAccount.value.id), {
      preserveScroll: true,
      onSuccess: closeAccountModal,
    });
  } else {
    accountForm.post(route("bank-accounts.store"), {
      preserveScroll: true,
      onSuccess: closeAccountModal,
    });
  }
};

const confirmDeleteAccount = (acc) => { deletingAccount.value = acc; };
const doDeleteAccount = () => {
  router.delete(route("bank-accounts.destroy", deletingAccount.value.id), {
    preserveScroll: true,
    onSuccess: () => { deletingAccount.value = null; },
  });
};

// ── P&L PDF ──────────────────────────────────────────────────────────────
const downloadPL = () => {
  const doc = new jsPDF();
  doc.setFontSize(18);
  doc.text("Profit & Loss Statement", 14, 16);
  doc.setFontSize(10);
  doc.text(`Generated: ${new Date().toLocaleString()}`, 14, 22);

  const rows = [
    ["Revenue (Sales)",        fmt(props.totalRevenue),  ""],
    ["Cost of Goods Sold",     "",                        `− ${fmt(props.totalCOGS)}`],
    ["Gross Profit",           fmt(props.grossProfit),   ""],
    ["Operating Expenses",     "",                        `− ${fmt(props.totalExpenses)}`],
    ["Supplier Payments",      "",                        `− ${fmt(props.totalSupplierPaid)}`],
    ["Net Profit",             fmt(props.netProfit),     ""],
  ];

  doc.autoTable({
    head: [["Item", "Credit", "Debit"]],
    body: rows,
    startY: 28,
    theme: "striped",
    styles: { fontSize: 11, cellPadding: 4 },
    headStyles: { fillColor: [30, 64, 175] },
  });

  doc.save(`PL_Statement_${new Date().toISOString().slice(0, 10)}.pdf`);
};
</script>
