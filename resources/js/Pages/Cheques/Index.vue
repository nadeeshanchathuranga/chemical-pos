<style>
.dataTables_wrapper .dataTables_paginate {
  display: flex; justify-content: center; align-items: center; margin-top: 20px;
}
#ChequesTable_filter { display: flex; justify-content: flex-end; align-items: center; margin-bottom: 16px; }
#ChequesTable_filter label { font-size: 17px; color: #000; display: flex; align-items: center; }
#ChequesTable_filter input[type="search"] {
  font-weight: 400; padding: 9px 15px; font-size: 14px; color: #000000cc;
  border: 1px solid rgb(209 213 219); border-radius: 5px; background: #fff;
  outline: none; transition: all .5s ease;
}
#ChequesTable_filter input[type="search"]:focus { border: 1px solid #4b5563; box-shadow: none; }
#ChequesTable_filter { float: left; }
.dataTables_wrapper { margin-bottom: 10px; }
</style>

<template>
  <Head title="Cheque Management" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-6">
    <Header />

    <div class="w-full md:w-5/6 py-8 space-y-8">

      <!-- ── Title Row ── -->
      <div class="flex items-center justify-between">
        <p class="text-3xl font-bold tracking-wide text-black uppercase">Cheque Management</p>
        <div class="flex gap-3">
          <Link href="/accounting" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-lg">
            ← Accounting
          </Link>
          <button @click="showAddForm = !showAddForm"
            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg">
            + Add Cheque
          </button>
          <button @click="exportPDF"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">
            Export PDF
          </button>
        </div>
      </div>

      <!-- ── KPI Cards ── -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-5">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Pending Received</p>
          <p class="text-2xl font-extrabold text-blue-600">{{ fmt(kpis.pending_received) }}</p>
          <p class="text-xs text-gray-400 mt-1">In-hand from customers</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Pending Issued</p>
          <p class="text-2xl font-extrabold text-purple-600">{{ fmt(kpis.pending_issued) }}</p>
          <p class="text-xs text-gray-400 mt-1">Given to suppliers</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Cleared</p>
          <p class="text-2xl font-extrabold text-green-600">
            {{ fmt(kpis.cleared_received + kpis.cleared_issued) }}
          </p>
          <p class="text-xs text-gray-400 mt-1">Received {{ fmt(kpis.cleared_received) }} / Issued {{ fmt(kpis.cleared_issued) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 relative">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Bounced</p>
          <p class="text-2xl font-extrabold text-red-600">{{ fmt(kpis.bounced) }}</p>
          <p class="text-xs text-gray-400 mt-1">
            {{ kpis.count_overdue }} overdue pending
          </p>
          <span v-if="kpis.count_overdue > 0"
            class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full animate-pulse">
            {{ kpis.count_overdue }} overdue
          </span>
        </div>
      </div>

      <!-- ── Add Cheque Form ── -->
      <div v-if="showAddForm" class="bg-white rounded-2xl shadow p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Add New Cheque</h2>
        <form @submit.prevent="submitCheque" class="grid grid-cols-1 md:grid-cols-3 gap-4">

          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Type <span class="text-red-500">*</span></label>
            <select v-model="form.type"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400">
              <option value="">Select...</option>
              <option value="Received">Received (from customer)</option>
              <option value="Issued">Issued (to supplier)</option>
            </select>
            <p v-if="form.errors.type" class="text-red-500 text-xs mt-1">{{ form.errors.type }}</p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Cheque No. <span class="text-red-500">*</span></label>
            <input v-model="form.cheque_number" type="text" placeholder="e.g. 001234"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400" />
            <p v-if="form.errors.cheque_number" class="text-red-500 text-xs mt-1">{{ form.errors.cheque_number }}</p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Bank Name <span class="text-red-500">*</span></label>
            <input v-model="form.bank_name" type="text" placeholder="e.g. Commercial Bank"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400" />
            <p v-if="form.errors.bank_name" class="text-red-500 text-xs mt-1">{{ form.errors.bank_name }}</p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Branch</label>
            <input v-model="form.branch" type="text" placeholder="Branch"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400" />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Amount <span class="text-red-500">*</span></label>
            <input v-model="form.amount" type="number" step="0.01" min="0.01" placeholder="0.00"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400" />
            <p v-if="form.errors.amount" class="text-red-500 text-xs mt-1">{{ form.errors.amount }}</p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Cheque Date <span class="text-red-500">*</span></label>
            <input v-model="form.cheque_date" type="date"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400" />
            <p v-if="form.errors.cheque_date" class="text-red-500 text-xs mt-1">{{ form.errors.cheque_date }}</p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Due / PDC Date</label>
            <input v-model="form.due_date" type="date"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400" />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Payee / Payer <span class="text-red-500">*</span></label>
            <input v-model="form.payee_payer" type="text" placeholder="Customer or Supplier name"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400" />
            <p v-if="form.errors.payee_payer" class="text-red-500 text-xs mt-1">{{ form.errors.payee_payer }}</p>
          </div>

          <div v-if="form.type === 'Issued'">
            <label class="block text-xs font-medium text-gray-600 mb-1">Supplier</label>
            <select v-model="form.supplier_id"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400">
              <option value="">None</option>
              <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>

          <div class="md:col-span-3">
            <label class="block text-xs font-medium text-gray-600 mb-1">Note</label>
            <textarea v-model="form.note" rows="2" placeholder="Optional note..."
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 resize-none"></textarea>
          </div>

          <div class="md:col-span-3 flex gap-3">
            <button type="submit" :disabled="form.processing"
              class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition disabled:opacity-50">
              {{ form.processing ? 'Saving...' : 'Save Cheque' }}
            </button>
            <button type="button" @click="showAddForm = false"
              class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
              Cancel
            </button>
          </div>
        </form>
      </div>

      <!-- ── Filters ── -->
      <div class="bg-white rounded-2xl shadow p-5 flex flex-wrap gap-4 items-end">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
          <select v-model="filterType" @change="applyFilter"
            class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none">
            <option value="">All Types</option>
            <option value="Received">Received</option>
            <option value="Issued">Issued</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
          <select v-model="filterStatus" @change="applyFilter"
            class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none">
            <option value="">All Statuses</option>
            <option value="Pending">Pending</option>
            <option value="Cleared">Cleared</option>
            <option value="Bounced">Bounced</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
          <input v-model="filterFrom" type="date" @change="applyFilter"
            class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
          <input v-model="filterTo" type="date" @change="applyFilter"
            class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none" />
        </div>
        <button @click="clearFilters"
          class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium rounded-lg transition">
          Clear
        </button>
      </div>

      <!-- ── Cheques Table ── -->
      <div class="bg-white rounded-2xl shadow p-6">
        <div class="overflow-x-auto">
          <table id="ChequesTable"
            class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto text-[13px]">
            <thead>
              <tr class="bg-gradient-to-r from-purple-700 via-purple-600 to-purple-700 text-white text-[13px]">
                <th class="p-3 text-left uppercase font-semibold">Type</th>
                <th class="p-3 text-left uppercase font-semibold">Cheque No.</th>
                <th class="p-3 text-left uppercase font-semibold">Bank</th>
                <th class="p-3 text-left uppercase font-semibold">Payee / Payer</th>
                <th class="p-3 text-right uppercase font-semibold">Amount</th>
                <th class="p-3 text-left uppercase font-semibold">Cheque Date</th>
                <th class="p-3 text-left uppercase font-semibold">Due Date</th>
                <th class="p-3 text-left uppercase font-semibold">Status</th>
                <th class="p-3 text-center uppercase font-semibold">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in cheques" :key="c.id"
                :class="isOverdue(c) ? 'bg-red-50' : 'hover:bg-gray-50'"
                class="transition duration-150">
                <td class="p-3 border-t border-gray-200">
                  <span :class="c.type === 'Received' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'"
                    class="px-2 py-0.5 rounded-full text-xs font-semibold">
                    {{ c.type }}
                  </span>
                </td>
                <td class="p-3 border-t border-gray-200 font-mono font-semibold">{{ c.cheque_number }}</td>
                <td class="p-3 border-t border-gray-200">
                  {{ c.bank_name }}<span v-if="c.branch" class="text-gray-400"> / {{ c.branch }}</span>
                </td>
                <td class="p-3 border-t border-gray-200">{{ c.payee_payer }}</td>
                <td class="p-3 border-t border-gray-200 text-right font-bold">{{ fmt(c.amount) }}</td>
                <td class="p-3 border-t border-gray-200">{{ c.cheque_date }}</td>
                <td class="p-3 border-t border-gray-200">
                  <span v-if="c.due_date" :class="isOverdue(c) ? 'text-red-600 font-bold' : ''">
                    {{ c.due_date }}
                    <span v-if="isOverdue(c)" class="ml-1 text-xs bg-red-100 text-red-600 px-1 rounded">OVERDUE</span>
                  </span>
                  <span v-else class="text-gray-400">—</span>
                </td>
                <td class="p-3 border-t border-gray-200">
                  <span :class="statusBadge(c.status)" class="px-2 py-0.5 rounded-full text-xs font-semibold">
                    {{ c.status }}
                  </span>
                </td>
                <td class="p-3 border-t border-gray-200 text-center">
                  <div class="flex justify-center gap-1 flex-wrap">
                    <!-- Clear -->
                    <button v-if="c.status === 'Pending'"
                      @click="openClearModal(c)"
                      class="px-2 py-1 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded transition">
                      Clear
                    </button>
                    <!-- Bounce -->
                    <button v-if="c.status === 'Pending'"
                      @click="updateStatus(c, 'Bounced')"
                      class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded transition">
                      Bounce
                    </button>
                    <!-- Cancel -->
                    <button v-if="c.status === 'Pending'"
                      @click="updateStatus(c, 'Cancelled')"
                      class="px-2 py-1 bg-gray-400 hover:bg-gray-500 text-white text-xs font-semibold rounded transition">
                      Cancel
                    </button>
                    <!-- Delete -->
                    <button @click="openDeleteModal(c)"
                      class="px-2 py-1 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-semibold rounded transition">
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="cheques.length === 0">
                <td colspan="9" class="p-8 text-center text-gray-400">No cheques found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- /content -->
  </div><!-- /page -->

  <!-- ── Clear Modal ── -->
  <div v-if="clearingCheque"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md space-y-5">
      <p class="text-lg font-bold text-gray-800">Clear Cheque #{{ clearingCheque.cheque_number }}</p>
      <p class="text-sm text-gray-500">
        Select the bank account to deposit/debit
        <span class="font-bold text-green-700">{{ fmt(clearingCheque.amount) }}</span>
      </p>
      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Bank Account <span class="text-red-500">*</span></label>
        <select v-model="clearForm.bank_account_id"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400">
          <option value="">Select account...</option>
          <option v-for="ba in bankAccounts" :key="ba.id" :value="ba.id">
            {{ ba.name }} ({{ ba.bank_name }}) — Rs. {{ Number(ba.current_balance).toLocaleString() }}
          </option>
        </select>
        <p v-if="clearForm.errors.bank_account_id" class="text-red-500 text-xs mt-1">{{ clearForm.errors.bank_account_id }}</p>
      </div>
      <div class="flex gap-3">
        <button @click="confirmClear" :disabled="clearForm.processing"
          class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition disabled:opacity-50">
          {{ clearForm.processing ? 'Processing...' : 'Confirm Clear' }}
        </button>
        <button @click="clearingCheque = null"
          class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
          Cancel
        </button>
      </div>
    </div>
  </div>

  <!-- ── Delete Confirmation Modal ── -->
  <div v-if="deletingCheque"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-sm text-center space-y-4">
      <p class="text-lg font-bold text-gray-800">Delete Cheque?</p>
      <p class="text-sm text-gray-500">
        This will permanently delete cheque
        <span class="font-bold text-red-600">#{{ deletingCheque.cheque_number }}</span>
        ({{ fmt(deletingCheque.amount) }}).
        Any linked bank transaction will also be reversed.
      </p>
      <div class="flex justify-center gap-4">
        <button @click="deletingCheque = null"
          class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
          Cancel
        </button>
        <button @click="confirmDelete"
          class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition">
          Delete
        </button>
      </div>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm, Link, Head, router } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';
import Banner from '@/Components/Banner.vue';

const props = defineProps({
  cheques:      Array,
  kpis:         Object,
  bankAccounts: Array,
  suppliers:    Array,
  filters:      Object,
});

// ── Filters ─────────────────────────────────────────────────
const filterType   = ref(props.filters?.type    ?? '');
const filterStatus = ref(props.filters?.status  ?? '');
const filterFrom   = ref(props.filters?.date_from ?? '');
const filterTo     = ref(props.filters?.date_to   ?? '');

function applyFilter() {
  router.get('/cheques', {
    type:      filterType.value   || undefined,
    status:    filterStatus.value || undefined,
    date_from: filterFrom.value   || undefined,
    date_to:   filterTo.value     || undefined,
  }, { preserveState: true, replace: true });
}

function clearFilters() {
  filterType.value = ''; filterStatus.value = '';
  filterFrom.value = ''; filterTo.value = '';
  router.get('/cheques', {}, { replace: true });
}

// ── Add cheque form ─────────────────────────────────────────
const showAddForm = ref(false);
const today = new Date().toISOString().slice(0, 10);

const form = useForm({
  type:          '',
  cheque_number: '',
  bank_name:     '',
  branch:        '',
  amount:        '',
  cheque_date:   today,
  due_date:      '',
  payee_payer:   '',
  supplier_id:   '',
  note:          '',
});

function submitCheque() {
  form.post('/cheques', {
    preserveScroll: true,
    onSuccess: () => { form.reset(); showAddForm.value = false; },
  });
}

// ── Status update ───────────────────────────────────────────
function updateStatus(cheque, status) {
  router.patch(`/cheques/${cheque.id}/status`, { status }, { preserveScroll: true });
}

// ── Clear modal ─────────────────────────────────────────────
const clearingCheque = ref(null);
const clearForm = useForm({ status: 'Cleared', bank_account_id: '' });

function openClearModal(cheque) {
  clearingCheque.value = cheque;
  clearForm.bank_account_id = '';
}

function confirmClear() {
  clearForm.patch(`/cheques/${clearingCheque.value.id}/status`, {
    preserveScroll: true,
    onSuccess: () => { clearingCheque.value = null; },
  });
}

// ── Delete modal ────────────────────────────────────────────
const deletingCheque = ref(null);

function openDeleteModal(cheque) { deletingCheque.value = cheque; }

function confirmDelete() {
  router.delete(`/cheques/${deletingCheque.value.id}`, {
    preserveScroll: true,
    onSuccess: () => { deletingCheque.value = null; },
  });
}

// ── Helpers ─────────────────────────────────────────────────
function fmt(value) {
  return 'Rs. ' + Number(value ?? 0).toLocaleString('en-LK', {
    minimumFractionDigits: 2, maximumFractionDigits: 2,
  });
}

function isOverdue(c) {
  return c.status === 'Pending' && c.due_date && new Date(c.due_date) < new Date();
}

const statusBadge = (s) => ({
  Pending:   'bg-yellow-100 text-yellow-700',
  Cleared:   'bg-green-100 text-green-700',
  Bounced:   'bg-red-100 text-red-700',
  Cancelled: 'bg-gray-100 text-gray-500',
}[s] ?? 'bg-gray-100 text-gray-600');

// ── DataTable ────────────────────────────────────────────────
onMounted(() => {
  if (props.cheques.length > 0) {
    $('#ChequesTable').DataTable({
      dom: 'Bfrtip', pageLength: 15, buttons: [],
      columnDefs: [{ targets: [8], orderable: false, searchable: false }],
      language: { search: '' },
      initComplete() { $('div.dataTables_filter input').attr('placeholder', 'Search...'); },
    });
  }
});

// ── PDF Export ───────────────────────────────────────────────
function exportPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'landscape' });
  doc.setFontSize(16);
  doc.text('Cheque Management Report', 14, 15);
  doc.setFontSize(10);
  doc.text(`Generated: ${new Date().toLocaleString()}`, 14, 22);

  const rows = props.cheques.map(c => [
    c.type, c.cheque_number, `${c.bank_name}${c.branch ? ' / ' + c.branch : ''}`,
    c.payee_payer, fmt(c.amount), c.cheque_date, c.due_date ?? '—', c.status,
  ]);

  doc.autoTable({
    head: [['Type','Cheque No.','Bank','Payee/Payer','Amount','Cheque Date','Due Date','Status']],
    body: rows, startY: 28, styles: { fontSize: 9 },
    headStyles: { fillColor: [109, 40, 217] },
  });

  doc.save('cheques.pdf');
}
</script>
