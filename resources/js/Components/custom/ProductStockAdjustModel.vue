<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="$emit('update:open', false)">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl w-full max-w-lg p-6 text-left">
            <DialogTitle class="text-2xl font-bold text-white">
              Adjust Stock
            </DialogTitle>

            <p class="mt-2 text-sm text-gray-300">
              Product: <span class="font-semibold text-white">{{ selectedProduct?.name || 'N/A' }}</span>
            </p>
            <p class="text-sm text-gray-300">
              Current stock: <span class="font-semibold text-white">{{ selectedProduct?.stock_quantity ?? 0 }}</span>
            </p>

            <form @submit.prevent="submit" class="mt-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-300">Action</label>
                <select
                  v-model="form.action"
                  class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                >
                  <option value="add">Add Stock</option>
                  <option value="deduct">Deduct Stock</option>
                </select>
                <span v-if="form.errors.action" class="text-sm text-red-500">{{ form.errors.action }}</span>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-300">Quantity</label>
                <input
                  v-model="form.quantity"
                  type="number"
                  min="1"
                  placeholder="Enter quantity"
                  class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                />
                <span v-if="form.errors.quantity" class="text-sm text-red-500">{{ form.errors.quantity }}</span>
              </div>

              <!-- Optional Price Changes (Only when adding stock) -->
              <div v-if="form.action === 'add'" class="p-4 border border-gray-600 rounded-lg bg-gray-800 space-y-4">
                <h4 class="text-sm font-bold text-gray-300">Optional: Update Pricing for New Stock</h4>
                <p class="text-xs text-gray-400">If you leave these unchanged, the stock will be added to the current batch. If you change them, a new batch will be created with the new prices.</p>
                
                <div>
                  <label class="block text-sm font-medium text-gray-300">Cost Price</label>
                  <input
                    v-model="form.cost_price"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="Enter cost price"
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.cost_price" class="text-sm text-red-500">{{ form.errors.cost_price }}</span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-300">Retail Price</label>
                    <input
                      v-model="form.retail_price"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="Enter retail price"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.retail_price" class="text-sm text-red-500">{{ form.errors.retail_price }}</span>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-300">Wholesale Price</label>
                    <input
                      v-model="form.wholesale_price"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="Enter wholesale price"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.wholesale_price" class="text-sm text-red-500">{{ form.errors.wholesale_price }}</span>
                  </div>
                </div>
              </div>

                <div>
                <label class="block text-sm font-medium text-gray-300">Supplier</label>
                <select
                  v-model="form.supplier_id"
                  class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                >
                  <option value="">Select Supplier (optional)</option>
                  <option
                    v-for="supplier in suppliers"
                    :key="supplier.id"
                    :value="supplier.id"
                  >
                    {{ supplier.name }}
                  </option>
                </select>
                <span v-if="form.errors.supplier_id" class="text-sm text-red-500">{{ form.errors.supplier_id }}</span>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-300">Reason (optional)</label>
                <textarea
                  v-model="form.reason"
                  rows="3"
                  placeholder="Enter reason for stock adjustment"
                  class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                />
                <span v-if="form.errors.reason" class="text-sm text-red-500">{{ form.errors.reason }}</span>
              </div>

              <div class="flex flex-wrap gap-3 mt-6">
                <button
                  type="submit"
                  @click="playClickSound()"
                  class="px-6 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700"
                >
                  {{ form.action === 'deduct' ? 'Deduct' : 'Add' }} Stock
                </button>
                <button
                  type="button"
                  @click="() => { playClickSound(); emit('update:open', false); }"
                  class="px-6 py-3 text-gray-900 bg-gray-200 rounded-lg hover:bg-gray-300"
                >
                  Cancel
                </button>
              </div>
            </form>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { watch } from "vue";
import { useForm } from "@inertiajs/vue3";

const emit = defineEmits(["update:open"]);
const { open, selectedProduct, action, suppliers } = defineProps({
  open: Boolean,
  selectedProduct: Object,
  action: { type: String, default: "add" },
  suppliers: { type: Array, default: () => [] },
});

const form = useForm({
  action: action || "add",
  quantity: "",
  supplier_id: selectedProduct?.supplier_id || "",
  reason: "",
  cost_price: selectedProduct?.cost_price || "",
  retail_price: selectedProduct?.retail_price || "",
  wholesale_price: selectedProduct?.wholesale_price || "",
});

const playClickSound = () => {
  const clickSound = new Audio("/sounds/click-sound.mp3");
  clickSound.play();
};

watch(
  () => action,
  (newAction) => {
    form.action = newAction || "add";
  },
  { immediate: true }
);

watch(
  () => open,
  (value) => {
    if (value) {
      form.action = action || "add";
      form.quantity = "";
      form.supplier_id = selectedProduct?.supplier_id || "";
      form.reason = "";
      form.cost_price = selectedProduct?.cost_price || "";
      form.retail_price = selectedProduct?.retail_price || "";
      form.wholesale_price = selectedProduct?.wholesale_price || "";
      form.clearErrors();
    }
  }
);

const submit = () => {
  if (!selectedProduct?.id) {
    return;
  }

  form.post(route("products.adjustStock", selectedProduct.id), {
    preserveState: true,
    onSuccess: () => {
      form.reset("quantity", "reason", "cost_price", "retail_price", "wholesale_price");
      emit("update:open", false);
    },
  });
};
</script>
