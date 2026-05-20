<template>
    <TransitionRoot as="template" :show="open">
        <Dialog as="div" class="relative z-30" @close="$emit('update:open', false)">
            <TransitionChild as="template" enter="ease-out duration-200" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-150" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-black/40" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <TransitionChild as="template" enter="ease-out duration-200" enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100" leave="ease-in duration-150" leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95">
                        <DialogPanel class="w-full max-w-5xl rounded-2xl bg-white p-6 shadow-xl">
                            <div class="flex items-center justify-between border-b pb-4">
                                <DialogTitle class="text-2xl font-bold text-black">Process Return</DialogTitle>
                                <button class="rounded bg-red-600 px-4 py-2 text-white" @click="$emit('update:open', false)">
                                    Close
                                </button>
                            </div>

                            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-gray-700">Find Order</label>
                                    <input v-model.trim="searchText" type="text" placeholder="Search by order id or customer"
                                        class="w-full rounded border px-3 py-2 focus:border-blue-500 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700">Order</label>
                                    <select v-model="selectedSaleId"
                                        class="w-full rounded border px-3 py-2 focus:border-blue-500 focus:outline-none">
                                        <option value="">Select order</option>
                                        <option v-for="sale in filteredSales" :key="sale.id" :value="sale.id">
                                            {{ sale.order_id }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div v-if="selectedSale" class="mt-4 rounded-lg border bg-gray-50 p-4 text-sm text-gray-800">
                                <p><span class="font-semibold">Order:</span> {{ selectedSale.order_id }}</p>
                                <p><span class="font-semibold">Customer:</span> {{ selectedSale.customer?.name || 'N/A' }}</p>
                                <p><span class="font-semibold">Date:</span> {{ selectedSale.sale_date }}</p>
                            </div>

                            <div class="mt-5 overflow-x-auto">
                                <table class="min-w-full border text-sm">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="border px-3 py-2 text-left">Select</th>
                                            <th class="border px-3 py-2 text-left">Product</th>
                                            <th class="border px-3 py-2 text-right">Available</th>
                                            <th class="border px-3 py-2 text-right">Return Qty</th>
                                            <th class="border px-3 py-2 text-right">Unit Price</th>
                                            <th class="border px-3 py-2 text-left">Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="loadingItems">
                                            <td colspan="6" class="border px-3 py-4 text-center">Loading items...</td>
                                        </tr>
                                        <tr v-else-if="orderItems.length === 0">
                                            <td colspan="6" class="border px-3 py-4 text-center">No returnable items</td>
                                        </tr>
                                        <tr v-for="item in orderItems" :key="item.product_id">
                                            <td class="border px-3 py-2 text-center">
                                                <input type="checkbox" v-model="item.selected" />
                                            </td>
                                            <td class="border px-3 py-2">
                                                <div class="flex items-center gap-3">
                                                    <img :src="item.product?.image ? `/${item.product.image}` : '/images/placeholder.jpg'"
                                                        class="h-10 w-10 rounded object-cover" />
                                                    <span>{{ item.product?.name || 'Unknown Product' }}</span>
                                                </div>
                                            </td>
                                            <td class="border px-3 py-2 text-right">{{ item.available_quantity }}</td>
                                            <td class="border px-3 py-2 text-right">
                                                <input v-model.number="item.return_quantity" type="number" min="1"
                                                    :max="item.available_quantity"
                                                    class="w-24 rounded border px-2 py-1 text-right" />
                                            </td>
                                            <td class="border px-3 py-2 text-right">{{ Number(item.unit_price || 0).toFixed(2) }}</td>
                                            <td class="border px-3 py-2">
                                                <input v-model="item.reason" type="text" placeholder="Reason"
                                                    class="w-full rounded border px-2 py-1" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 flex items-center justify-between">
                                <p class="text-sm text-gray-700">Selected refund total: {{ selectedRefundTotal.toFixed(2) }} LKR</p>
                                <div class="flex items-center gap-3">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-gray-700">Return Type</label>
                                        <select v-model="returnType"
                                            class="rounded border px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                                            <option value="cash_return">Cash Return</option>
                                            <option value="product_return">Product Exchange</option>
                                        </select>
                                    </div>
                                    <button class="rounded bg-blue-600 px-4 py-2 font-semibold text-white"
                                        :disabled="selectedProducts.length === 0" @click="applyToBilling">
                                        Load Selected To Billing
                                    </button>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import axios from "axios";
import { computed, ref, watch } from "vue";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";

const props = defineProps({
    open: { type: Boolean, required: true },
});

const emit = defineEmits(["update:open", "apply-return-items"]);

const loadingOrders = ref(false);
const loadingItems = ref(false);
const sales = ref([]);
const orderItems = ref([]);
const selectedSaleId = ref("");
const selectedSale = ref(null);
const searchText = ref("");
const returnType = ref("cash_return");

const filteredSales = computed(() => {
    const q = searchText.value.toLowerCase();
    if (!q) {
        return sales.value;
    }

    return sales.value.filter((sale) => {
        const orderId = String(sale.order_id || "").toLowerCase();
        const customer = String(sale.customer?.name || "").toLowerCase();
        return orderId.includes(q) || customer.includes(q);
    });
});

const selectedProducts = computed(() => {
    return orderItems.value.filter((item) => item.selected && item.return_quantity > 0);
});

const selectedRefundTotal = computed(() => {
    return selectedProducts.value.reduce((sum, item) => {
        return sum + Number(item.unit_price || 0) * Number(item.return_quantity || 0);
    }, 0);
});

const loadOrders = async () => {
    loadingOrders.value = true;
    try {
        const response = await axios.get(route("pos.return.orders"));
        sales.value = response.data.sales || [];
    } finally {
        loadingOrders.value = false;
    }
};

const loadOrderItems = async (saleId) => {
    if (!saleId) {
        orderItems.value = [];
        selectedSale.value = null;
        return;
    }

    loadingItems.value = true;
    try {
        const response = await axios.get(route("pos.return.items", saleId));
        selectedSale.value = response.data.sale;
        orderItems.value = (response.data.items || []).map((item) => ({
            ...item,
            selected: false,
            return_quantity: 1,
            reason: "",
        }));
    } finally {
        loadingItems.value = false;
    }
};

const applyToBilling = () => {
    const products = selectedProducts.value.map((item) => ({
        id: item.product_id,
        name: item.product?.name,
        image: item.product?.image,
        selling_price: Number(item.unit_price || 0),
        discounted_price: Number(item.unit_price || 0),
        discount: 0,
        quantity: Number(item.return_quantity || 1),
        is_return: true,
        return_reason: item.reason || "Customer return",
        available_quantity: Number(item.available_quantity || 0),
    }));

    emit("apply-return-items", {
        sale: selectedSale.value,
        products,
        refundTotal: selectedRefundTotal.value,
        returnType: returnType.value,
    });

    emit("update:open", false);
};

watch(
    () => props.open,
    async (isOpen) => {
        if (isOpen) {
            selectedSaleId.value = "";
            selectedSale.value = null;
            orderItems.value = [];
            returnType.value = "cash_return";
            await loadOrders();
        }
    }
);

watch(selectedSaleId, (saleId) => {
    loadOrderItems(saleId);
});
</script>
