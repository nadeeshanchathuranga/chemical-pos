<style>
.dataTables_wrapper .dataTables_paginate {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}
#SupplierProductsTable_filter,
#SupplierPaymentsTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px;
}
#SupplierProductsTable_filter label,
#SupplierPaymentsTable_filter label {
  font-size: 17px;
  color: #000000;
  display: flex;
  align-items: center;
}
#SupplierProductsTable_filter input[type="search"],
#SupplierPaymentsTable_filter input[type="search"] {
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
#SupplierProductsTable_filter input[type="search"]:focus,
#SupplierPaymentsTable_filter input[type="search"]:focus {
  outline: none;
  border: 1px solid #4b5563;
  box-shadow: none;
}
#SupplierProductsTable_filter,
#SupplierPaymentsTable_filter {
  float: left;
}
.dataTables_wrapper {
  margin-bottom: 10px;
}
</style>

<template>
  <Head :title="`Supplier — ${supplier.name}`" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-6">
    <Header />

    <div class="w-full md:w-5/6 py-8 space-y-10">

      <!-- ── Back + Title ── -->
      <div class="flex items-center space-x-4">
        <Link href="/suppliers">
          <img src="/images/back-arrow.png" class="w-12 h-12" />
        </Link>
        <p class="text-3xl font-bold tracking-wide text-black uppercase">
          Supplier Details
        </p>
      </div>

      <!-- ── Supplier Info Card ── -->
      <div class="flex flex-col md:flex-row items-center gap-8 bg-white rounded-2xl shadow p-6">
        <img
          :src="supplier.image ? `/${supplier.image}` : '/images/placeholder.jpg'"
          alt="Supplier"
          class="w-32 h-32 rounded-full object-cover shadow border-4 border-blue-500"
        />
        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Name</p>
            <p class="text-xl font-bold">{{ supplier.name }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Contact</p>
            <p class="text-lg">{{ supplier.contact || '—' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Email</p>
            <p class="text-lg">{{ supplier.email || '—' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Address</p>
            <p class="text-lg">{{ supplier.address || '—' }}</p>
          </div>
        </div>
      </div>

      <!-- ── KPI Cards ── -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-5 flex flex-col items-center">
          <p class="text-3xl font-extrabold text-blue-600">{{ supplier.products.length }}</p>
          <p class="text-sm font-medium text-gray-500 mt-1 text-center">Total Products</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 flex flex-col items-center">
          <p class="text-3xl font-extrabold text-indigo-600">{{ formatCurrency(totalPurchaseCost) }}</p>
          <p class="text-sm font-medium text-gray-500 mt-1 text-center">Total Purchase Cost</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 flex flex-col items-center">
          <p class="text-3xl font-extrabold text-green-600">{{ formatCurrency(totalPaid) }}</p>
          <p class="text-sm font-medium text-gray-500 mt-1 text-center">Total Paid</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 flex flex-col items-center">
          <p :class="balance > 0 ? 'text-red-600' : 'text-green-600'" class="text-3xl font-extrabold">
            {{ formatCurrency(Math.abs(balance)) }}
          </p>
          <p class="text-sm font-medium text-gray-500 mt-1 text-center">
            {{ balance > 0 ? 'Outstanding Balance' : 'Overpaid' }}
          </p>
        </div>
      </div>

      <!-- ── Products Table ── -->
      <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-bold text-gray-800">Linked Products</h2>
          <span class="bg-blue-100 text-blue-700 text-sm font-semibold px-3 py-1 rounded-full">
            {{ supplier.products.length }} products
          </span>
        </div>

        <div v-if="supplier.products.length > 0" class="overflow-x-auto">
          <table
            id="SupplierProductsTable"
            class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto"
          >
            <thead>
              <tr class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-white text-[14px]">
                <th class="p-3 text-left uppercase font-semibold tracking-wide">Name</th>
                <th class="p-3 text-left uppercase font-semibold tracking-wide">Code</th>
                <th class="p-3 text-left uppercase font-semibold tracking-wide">Category</th>
                <th class="p-3 text-right uppercase font-semibold tracking-wide">Cost Price</th>
                <th class="p-3 text-right uppercase font-semibold tracking-wide">Selling Price</th>
                <th class="p-3 text-right uppercase font-semibold tracking-wide">Total Qty</th>
                <th class="p-3 text-right uppercase font-semibold tracking-wide">Stock Qty</th>
                <th class="p-3 text-right uppercase font-semibold tracking-wide">Total Cost</th>
                <th class="p-3 text-left uppercase font-semibold tracking-wide">Purchase Date</th>
              </tr>
            </thead>
            <tbody class="text-[13px]">
              <tr
                v-for="product in supplier.products"
                :key="product.id"
                class="hover:bg-gray-50 transition duration-150"
              >
                <td class="p-3 border-t border-gray-200 font-semibold">{{ product.name }}</td>
                <td class="p-3 border-t border-gray-200">{{ product.code || '—' }}</td>
                <td class="p-3 border-t border-gray-200">{{ product.category?.name || '—' }}</td>
                <td class="p-3 border-t border-gray-200 text-right">{{ formatCurrency(product.cost_price) }}</td>
                <td class="p-3 border-t border-gray-200 text-right">{{ formatCurrency(product.selling_price) }}</td>
                <td class="p-3 border-t border-gray-200 text-right">{{ product.total_quantity ?? 0 }}</td>
                <td class="p-3 border-t border-gray-200 text-right">{{ product.stock_quantity ?? 0 }}</td>
                <td class="p-3 border-t border-gray-200 text-right font-semibold">
                  {{ formatCurrency((product.total_quantity ?? 0) * (product.cost_price ?? 0)) }}
                </td>
                <td class="p-3 border-t border-gray-200">{{ product.purchase_date || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center text-gray-400 py-8">
          No products linked to this supplier yet.
        </div>
      </div>

      <!-- ── Payments Section ── -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Add Payment Form -->
        <div class="bg-white rounded-2xl shadow p-6 md:col-span-1">
          <h2 class="text-xl font-bold text-gray-800 mb-5">Record Payment</h2>
          <form @submit.prevent="submitPayment" class="space-y-4">
            <!-- Amount -->
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Amount <span class="text-red-500">*</span></label>
              <input
                v-model="paymentForm.amount"
                type="number"
                step="0.01"
                min="0.01"
                placeholder="0.00"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
              />
              <p v-if="paymentForm.errors.amount" class="text-red-500 text-xs mt-1">{{ paymentForm.errors.amount }}</p>
            </div>

            <!-- Payment Date -->
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Payment Date <span class="text-red-500">*</span></label>
              <input
                v-model="paymentForm.payment_date"
                type="date"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
              />
              <p v-if="paymentForm.errors.payment_date" class="text-red-500 text-xs mt-1">{{ paymentForm.errors.payment_date }}</p>
            </div>

            <!-- Payment Method -->
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Payment Method <span class="text-red-500">*</span></label>
              <select
                v-model="paymentForm.payment_method"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
              >
                <option value="">Select method...</option>
                <option value="Cash">Cash</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Cheque">Cheque</option>
                <option value="Online">Online</option>
              </select>
              <p v-if="paymentForm.errors.payment_method" class="text-red-500 text-xs mt-1">{{ paymentForm.errors.payment_method }}</p>
            </div>

            <!-- Bank Account (Bank Transfer / Online / Cheque) -->
            <div v-if="needsBank(paymentForm.payment_method)">
              <label class="block text-sm font-medium text-gray-600 mb-1">
                Bank Account <span class="text-red-500">*</span>
              </label>
              <select
                v-model="paymentForm.bank_account_id"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
              >
                <option value="">Select account...</option>
                <option v-for="ba in bankAccounts" :key="ba.id" :value="ba.id">
                  {{ ba.name }} ({{ ba.bank_name }}) — Rs. {{ Number(ba.current_balance).toLocaleString() }}
                </option>
              </select>
              <p v-if="paymentForm.errors.bank_account_id" class="text-red-500 text-xs mt-1">{{ paymentForm.errors.bank_account_id }}</p>
            </div>

            <!-- Cheque Fields -->
            <template v-if="isCheque(paymentForm.payment_method)">
              <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 space-y-3">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-widest">Cheque Details</p>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cheque No. <span class="text-red-500">*</span></label>
                    <input v-model="paymentForm.cheque_number" type="text" placeholder="e.g. 001234"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400" />
                    <p v-if="paymentForm.errors.cheque_number" class="text-red-500 text-xs mt-1">{{ paymentForm.errors.cheque_number }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Bank Name <span class="text-red-500">*</span></label>
                    <input v-model="paymentForm.cheque_bank_name" type="text" placeholder="e.g. BOC"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400" />
                    <p v-if="paymentForm.errors.cheque_bank_name" class="text-red-500 text-xs mt-1">{{ paymentForm.errors.cheque_bank_name }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Branch</label>
                    <input v-model="paymentForm.cheque_branch" type="text" placeholder="Branch"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400" />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cheque Date <span class="text-red-500">*</span></label>
                    <input v-model="paymentForm.cheque_date" type="date"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400" />
                    <p v-if="paymentForm.errors.cheque_date" class="text-red-500 text-xs mt-1">{{ paymentForm.errors.cheque_date }}</p>
                  </div>
                  <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Due / Clearance Date (PDC)</label>
                    <input v-model="paymentForm.due_date" type="date"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400" />
                  </div>
                </div>
              </div>
            </template>

            <!-- Note -->
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Note</label>
              <textarea
                v-model="paymentForm.note"
                rows="3"
                placeholder="Optional note..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"
              ></textarea>
            </div>

            <button
              type="submit"
              :disabled="paymentForm.processing"
              class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition duration-150 disabled:opacity-50"
            >
              {{ paymentForm.processing ? 'Saving...' : 'Record Payment' }}
            </button>
          </form>
        </div>

        <!-- Payment History -->
        <div class="bg-white rounded-2xl shadow p-6 md:col-span-2">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">Payment History</h2>
            <span class="bg-green-100 text-green-700 text-sm font-semibold px-3 py-1 rounded-full">
              {{ supplier.supplier_payments.length }} payments
            </span>
          </div>

          <div v-if="supplier.supplier_payments.length > 0" class="overflow-x-auto">
            <table
              id="SupplierPaymentsTable"
              class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto"
            >
              <thead>
                <tr class="bg-gradient-to-r from-green-600 via-green-500 to-green-600 text-white text-[14px]">
                  <th class="p-3 text-left uppercase font-semibold tracking-wide">Date</th>
                  <th class="p-3 text-left uppercase font-semibold tracking-wide">Method</th>
                  <th class="p-3 text-left uppercase font-semibold tracking-wide">Bank / Cheque</th>
                  <th class="p-3 text-right uppercase font-semibold tracking-wide">Amount</th>
                  <th class="p-3 text-left uppercase font-semibold tracking-wide">Note</th>
                  <th class="p-3 text-center uppercase font-semibold tracking-wide">Action</th>
                </tr>
              </thead>
              <tbody class="text-[13px]">
                <tr
                  v-for="payment in supplier.supplier_payments"
                  :key="payment.id"
                  class="hover:bg-gray-50 transition duration-150"
                >
                  <td class="p-3 border-t border-gray-200">{{ payment.payment_date }}</td>
                  <td class="p-3 border-t border-gray-200">
                    <span :class="methodBadge(payment.payment_method)" class="px-2 py-0.5 rounded-full text-xs font-semibold">
                      {{ payment.payment_method }}
                    </span>
                  </td>
                  <td class="p-3 border-t border-gray-200 text-xs text-gray-600">
                    {{ payment.bank_account ? payment.bank_account.name : '—' }}
                  </td>
                  <td class="p-3 border-t border-gray-200 text-right font-bold text-green-700">
                    {{ formatCurrency(payment.amount) }}
                  </td>
                  <td class="p-3 border-t border-gray-200 text-gray-500">{{ payment.note || '—' }}</td>
                  <td class="p-3 border-t border-gray-200 text-center">
                    <button
                      @click="deletePayment(payment)"
                      class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded-lg transition duration-150"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="text-center text-gray-400 py-8">
            No payments recorded yet.
          </div>
        </div>

      </div>
      <!-- ── End Payments Section ── -->

    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div
    v-if="deletingPayment"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
  >
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-sm text-center space-y-4">
      <p class="text-lg font-bold text-gray-800">Delete Payment?</p>
      <p class="text-gray-500 text-sm">
        This will permanently remove the payment of
        <span class="font-bold text-red-600">{{ formatCurrency(deletingPayment.amount) }}</span>
        on {{ deletingPayment.payment_date }}.
      </p>
      <div class="flex justify-center space-x-4 pt-2">
        <button
          @click="deletingPayment = null"
          class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition"
        >
          Cancel
        </button>
        <button
          @click="confirmDelete"
          class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition"
        >
          Delete
        </button>
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

const props = defineProps({
  supplier:          Object,
  totalPurchaseCost: Number,
  totalPaid:         Number,
  balance:           Number,
  bankAccounts:      Array,
});

// ── Payment form ──────────────────────────────────────
const today = new Date().toISOString().slice(0, 10);
const paymentForm = useForm({
  amount:           "",
  payment_date:     today,
  payment_method:   "",
  note:             "",
  // Bank fields
  bank_account_id:  "",
  // Cheque fields
  cheque_number:    "",
  cheque_bank_name: "",
  cheque_branch:    "",
  cheque_date:      today,
  due_date:         "",
});

const needsBank    = (m) => ['Bank Transfer', 'Online', 'Cheque'].includes(m);
const isCheque     = (m) => m === 'Cheque';

function submitPayment() {
  paymentForm.post(route("supplier.payments.store", props.supplier.id), {
    preserveScroll: true,
    onSuccess: () => {
      paymentForm.reset();
      paymentForm.payment_date = today;
    },
  });
}

// ── Delete payment ─────────────────────────────────────
const deletingPayment = ref(null);

function deletePayment(payment) {
  deletingPayment.value = payment;
}

function confirmDelete() {
  router.delete(route("supplier.payments.destroy", deletingPayment.value.id), {
    preserveScroll: true,
    onSuccess: () => { deletingPayment.value = null; },
  });
}

// ── Helpers ────────────────────────────────────────────
function formatCurrency(value) {
  return "Rs. " + Number(value ?? 0).toLocaleString("en-LK", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

const methodBadge = (method) => {
  const map = {
    Cash:            "bg-yellow-100 text-yellow-700",
    "Bank Transfer": "bg-blue-100 text-blue-700",
    Cheque:          "bg-purple-100 text-purple-700",
    Online:          "bg-teal-100 text-teal-700",
  };
  return map[method] ?? "bg-gray-100 text-gray-600";
};

// ── DataTables ─────────────────────────────────────────
onMounted(() => {
  if (props.supplier.products.length > 0) {
    $("#SupplierProductsTable").DataTable({
      dom: "Bfrtip",
      pageLength: 10,
      buttons: [],
      columnDefs: [{ targets: [0, 2], orderable: true }],
      language: { search: "" },
      initComplete: function () {
        $("div.dataTables_filter input").attr("placeholder", "Search ...");
      },
    });
  }

  if (props.supplier.supplier_payments.length > 0) {
    $("#SupplierPaymentsTable").DataTable({
      dom: "Bfrtip",
      pageLength: 10,
      buttons: [],
      columnDefs: [{ targets: [5], orderable: false, searchable: false }],
      language: { search: "" },
      initComplete: function () {
        $("div.dataTables_filter input").attr("placeholder", "Search ...");
      },
    });
  }
});
</script>
