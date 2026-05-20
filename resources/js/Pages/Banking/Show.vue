<style>
.dataTables_wrapper .dataTables_paginate {
  display: flex; justify-content: center; align-items: center; margin-top: 20px;
}
#bankTxnTbl_filter {
  display: flex; justify-content: flex-end; align-items: center; margin-bottom: 16px; float: left;
}
#bankTxnTbl_filter label { font-size: 17px; color: #000; display: flex; align-items: center; }
#bankTxnTbl_filter input[type="search"] {
  font-weight: 400; padding: 9px 15px; font-size: 14px; color: #000000cc;
  border: 1px solid rgb(209 213 219); border-radius: 5px; background: #fff; outline: none;
}
#bankTxnTbl_filter input[type="search"]:focus { border: 1px solid #4b5563; box-shadow: none; }
.dataTables_wrapper { margin-bottom: 10px; }
</style>

<template>
  <Head :title="`${bankAccount.name} — Transactions`" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-6">
    <Header />

    <div class="w-full md:w-5/6 py-8 space-y-8">

      <!-- Title -->
      <div class="flex items-center space-x-4">
        <Link href="/accounting"><img src="/images/back-arrow.png" class="w-12 h-12" /></Link>
        <div>
          <p class="text-3xl font-bold text-black uppercase">{{ bankAccount.name }}</p>
          <p class="text-gray-500 text-sm">{{ bankAccount.bank_name || '' }} {{ bankAccount.account_number ? '· Acc: ' + bankAccount.account_number : '' }}</p>
        </div>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-5 flex flex-col items-center">
          <p class="text-2xl font-extrabold text-gray-700">{{ fmt(bankAccount.opening_balance) }}</p>
          <p class="text-xs font-semibold text-gray-400 uppercase mt-1">Opening Balance</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 flex flex-col items-center">
          <p class="text-2xl font-extrabold text-green-600">{{ fmt(totalCredits) }}</p>
          <p class="text-xs font-semibold text-gray-400 uppercase mt-1">Total Credits (In)</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 flex flex-col items-center">
          <p class="text-2xl font-extrabold text-red-600">{{ fmt(totalDebits) }}</p>
          <p class="text-xs font-semibold text-gray-400 uppercase mt-1">Total Debits (Out)</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 flex flex-col items-center">
          <p :class="bankAccount.current_balance >= 0 ? 'text-blue-600' : 'text-red-600'" class="text-2xl font-extrabold">
            {{ fmt(bankAccount.current_balance) }}
          </p>
          <p class="text-xs font-semibold text-gray-400 uppercase mt-1">Current Balance</p>
        </div>
      </div>

      <!-- Add Transaction + History -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Form -->
        <div class="bg-white rounded-2xl shadow p-6 md:col-span-1">
          <h2 class="text-xl font-bold text-gray-800 mb-5">Record Transaction</h2>
          <form @submit.prevent="submitTxn" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Type <span class="text-red-500">*</span></label>
              <div class="flex gap-3">
                <button type="button" @click="txnForm.type = 'Credit'"
                  :class="txnForm.type === 'Credit' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700'"
                  class="flex-1 py-2 rounded-lg font-semibold transition border border-green-300">
                  ↑ Credit (In)
                </button>
                <button type="button" @click="txnForm.type = 'Debit'"
                  :class="txnForm.type === 'Debit' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700'"
                  class="flex-1 py-2 rounded-lg font-semibold transition border border-red-300">
                  ↓ Debit (Out)
                </button>
              </div>
              <p v-if="txnForm.errors.type" class="text-red-500 text-xs mt-1">{{ txnForm.errors.type }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Amount (LKR) <span class="text-red-500">*</span></label>
              <input v-model="txnForm.amount" type="number" step="0.01" min="0.01" placeholder="0.00"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
              <p v-if="txnForm.errors.amount" class="text-red-500 text-xs mt-1">{{ txnForm.errors.amount }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Date <span class="text-red-500">*</span></label>
              <input v-model="txnForm.transaction_date" type="date"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
              <p v-if="txnForm.errors.transaction_date" class="text-red-500 text-xs mt-1">{{ txnForm.errors.transaction_date }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Reference / Description</label>
              <input v-model="txnForm.description" type="text" placeholder="e.g. Cash deposit, Salary payment"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Reference Type</label>
              <select v-model="txnForm.reference_type"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Manual</option>
                <option value="Sale">Sale</option>
                <option value="Expense">Expense</option>
                <option value="SupplierPayment">Supplier Payment</option>
                <option value="Transfer">Transfer</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <button type="submit" :disabled="txnForm.processing"
              class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition disabled:opacity-50">
              {{ txnForm.processing ? 'Saving...' : 'Record Transaction' }}
            </button>
          </form>
        </div>

        <!-- Transaction History -->
        <div class="bg-white rounded-2xl shadow p-6 md:col-span-2">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">Transaction History</h2>
            <div class="flex gap-3">
              <span class="bg-blue-100 text-blue-700 text-sm font-semibold px-3 py-1 rounded-full">
                {{ bankAccount.transactions.length }} transactions
              </span>
              <button @click="downloadPDF"
                class="px-4 py-1 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-lg">PDF</button>
            </div>
          </div>

          <div v-if="bankAccount.transactions.length > 0" class="overflow-x-auto">
            <table id="bankTxnTbl"
              class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
              <thead>
                <tr class="bg-gradient-to-r from-blue-700 via-blue-600 to-blue-700 text-white text-[13px]">
                  <th class="p-3 text-left font-semibold">Date</th>
                  <th class="p-3 text-center font-semibold">Type</th>
                  <th class="p-3 text-left font-semibold">Description</th>
                  <th class="p-3 text-left font-semibold">Ref. Type</th>
                  <th class="p-3 text-right font-semibold">Amount (LKR)</th>
                  <th class="p-3 text-center font-semibold">Action</th>
                </tr>
              </thead>
              <tbody class="text-[13px]">
                <tr v-for="txn in bankAccount.transactions" :key="txn.id"
                  class="hover:bg-gray-50 transition duration-150">
                  <td class="p-3 border-t border-gray-200">{{ formatDate(txn.transaction_date) }}</td>
                  <td class="p-3 border-t border-gray-200 text-center">
                    <span :class="txn.type === 'Credit' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                      class="px-2 py-0.5 rounded-full text-xs font-bold">
                      {{ txn.type === 'Credit' ? '↑ Credit' : '↓ Debit' }}
                    </span>
                  </td>
                  <td class="p-3 border-t border-gray-200">{{ txn.description || '—' }}</td>
                  <td class="p-3 border-t border-gray-200 text-gray-500 text-xs">{{ txn.reference_type || 'Manual' }}</td>
                  <td class="p-3 border-t border-gray-200 text-right font-bold"
                    :class="txn.type === 'Credit' ? 'text-green-700' : 'text-red-700'">
                    {{ txn.type === 'Credit' ? '+' : '-' }}{{ fmt(txn.amount) }}
                  </td>
                  <td class="p-3 border-t border-gray-200 text-center">
                    <button @click="confirmDeleteTxn(txn)"
                      class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded-lg transition">
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="text-center text-gray-400 py-8">No transactions yet.</div>
        </div>

      </div>
    </div>
  </div>

  <!-- Delete confirmation -->
  <div v-if="deletingTxn" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-sm text-center space-y-4">
      <p class="text-lg font-bold text-gray-800">Delete Transaction?</p>
      <p class="text-gray-500 text-sm">
        <span :class="deletingTxn.type === 'Credit' ? 'text-green-600' : 'text-red-600'" class="font-bold">
          {{ deletingTxn.type }}
        </span>
        of <strong>{{ fmt(deletingTxn.amount) }}</strong> on {{ formatDate(deletingTxn.transaction_date) }}
        <br/><em>This will update the account balance.</em>
      </p>
      <div class="flex justify-center space-x-4 pt-2">
        <button @click="deletingTxn = null"
          class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg">Cancel</button>
        <button @click="doDeleteTxn"
          class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg">Delete</button>
      </div>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useForm, Link, Head, router } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import jsPDF from "jspdf";
import "jspdf-autotable";

const props = defineProps({
  bankAccount:  Object,
  totalCredits: Number,
  totalDebits:  Number,
});

const today = new Date().toISOString().slice(0, 10);
const fmt        = (n) => "Rs. " + Number(n ?? 0).toLocaleString("en-LK", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatDate = (d) => d ? new Date(d).toLocaleDateString("en-LK") : "—";

// ── Form ─────────────────────────────────────────────────────────────────
const txnForm = useForm({
  type:             "Credit",
  amount:           "",
  transaction_date: today,
  description:      "",
  reference_type:   "",
});

const submitTxn = () => {
  txnForm.post(route("banking.transactions.store", props.bankAccount.id), {
    preserveScroll: true,
    onSuccess: () => { txnForm.reset(); txnForm.type = "Credit"; txnForm.transaction_date = today; },
  });
};

// ── Delete ────────────────────────────────────────────────────────────────
const deletingTxn = ref(null);
const confirmDeleteTxn = (txn) => { deletingTxn.value = txn; };
const doDeleteTxn = () => {
  router.delete(route("banking.transactions.destroy", deletingTxn.value.id), {
    preserveScroll: true,
    onSuccess: () => { deletingTxn.value = null; },
  });
};

// ── PDF ────────────────────────────────────────────────────────────────────
const downloadPDF = () => {
  const doc = new jsPDF("l", "mm", "a4");
  doc.setFontSize(16);
  doc.text(`${props.bankAccount.name} — Transaction History`, 14, 12);
  doc.setFontSize(10);
  doc.text(`Current Balance: ${fmt(props.bankAccount.current_balance)} | Generated: ${new Date().toLocaleString()}`, 14, 18);

  const rows = props.bankAccount.transactions.map((t, i) => [
    i + 1,
    formatDate(t.transaction_date),
    t.type,
    t.description || "—",
    t.reference_type || "Manual",
    fmt(t.amount),
  ]);

  doc.autoTable({
    head: [["#", "Date", "Type", "Description", "Ref. Type", "Amount (LKR)"]],
    body: rows,
    startY: 22,
    theme: "striped",
    styles: { fontSize: 9 },
    headStyles: { fillColor: [29, 78, 216] },
  });

  doc.save(`${props.bankAccount.name.replace(/\s+/g, "_")}_Transactions.pdf`);
};

// ── DataTables ─────────────────────────────────────────────────────────────
onMounted(() => {
  if (props.bankAccount.transactions.length > 0) {
    $("#bankTxnTbl").DataTable({
      dom: "Bfrtip",
      pageLength: 15,
      buttons: [],
      columnDefs: [{ targets: [5], orderable: false, searchable: false }],
      language: { search: "" },
      initComplete() { $("div.dataTables_filter input").attr("placeholder", "Search..."); },
    });
  }
});
</script>
