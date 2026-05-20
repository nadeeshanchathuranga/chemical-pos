<template>
  <AdminLayout>
    <div class="min-h-screen p-6 bg-gray-100">
      <div class="max-w-4xl min-h-screen p-6 mx-auto bg-gray-100">
        <h1 class="mb-6 text-2xl font-bold text-gray-800">Edit Product</h1>
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Category Select -->
          <div>
            <label
              for="category_id"
              class="block text-sm font-medium text-gray-700"
              >Category</label
            >
            <select
              v-model="form.category_id"
              id="category_id"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            >
              <option :value="null" disabled>Select Category</option>
              <option
                v-for="category in categories"
                :key="category.id"
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
            <span v-if="form.errors.category_id" class="text-sm text-red-500">{{
              form.errors.category_id
            }}</span>
          </div>

          <!-- Other Fields -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700"
              >Name</label
            >
            <input
              v-model="form.name"
              type="text"
              id="name"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.name" class="text-sm text-red-500">{{
              form.errors.name
            }}</span>
          </div>

          <!-- Size Select -->
          <div>
            <label for="size_id" class="block text-sm font-medium text-gray-700"
              >Size</label
            >
            <select
              v-model="form.size_id"
              id="size_id"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            >
              <option :value="null" disabled>Select Size</option>
              <option v-for="size in sizes" :key="size.id" :value="size.id">
                {{ size.name }}
              </option>
            </select>
            <span v-if="form.errors.size_id" class="text-sm text-red-500">{{
              form.errors.size_id
            }}</span>
          </div>

          <!-- Color Select -->
          <div>
            <label
              for="color_id"
              class="block text-sm font-medium text-gray-700"
              >Color</label
            >
            <select
              v-model="form.color_id"
              id="color_id"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            >
              <option :value="null" disabled>Select Color</option>
              <option v-for="color in colors" :key="color.id" :value="color.id">
                {{ color.name }}
              </option>
            </select>
            <span v-if="form.errors.color_id" class="text-sm text-red-500">{{
              form.errors.color_id
            }}</span>
          </div>

          <!-- Cost Price Input -->
          <div>
            <label
              for="cost_price"
              class="block text-sm font-medium text-gray-700"
              >Cost Price</label
            >
            <input
              v-model="form.cost_price"
              type="number"
              step="0.01"
              id="cost_price"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.cost_price" class="text-sm text-red-500">{{
              form.errors.cost_price
            }}</span>
          </div>

          <!-- ============ RETAIL PRICING SECTION ============ -->
          <div class="p-4 border border-blue-300 rounded-lg bg-blue-50">
            <h2 class="mb-4 text-lg font-semibold text-blue-800">Retail Pricing</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
              <div>
                <label for="retail_price" class="block text-sm font-medium text-gray-700">Retail Price</label>
                <input
                  v-model="form.retail_price"
                  type="number"
                  step="0.01"
                  id="retail_price"
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                />
                <span v-if="form.errors.retail_price" class="text-sm text-red-500">{{ form.errors.retail_price }}</span>
              </div>
              <div>
                <label for="retail_discount" class="block text-sm font-medium text-gray-700">Retail Discount (%)</label>
                <input
                  v-model="form.retail_discount"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  id="retail_discount"
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                />
                <span v-if="form.errors.retail_discount" class="text-sm text-red-500">{{ form.errors.retail_discount }}</span>
              </div>
              <div>
                <label for="discounted_retail_price" class="block text-sm font-medium text-gray-700">Discounted Retail Price</label>
                <input
                  v-model="form.discounted_retail_price"
                  type="number"
                  step="0.01"
                  id="discounted_retail_price"
                  class="block w-full mt-1 bg-gray-100 border-gray-300 rounded-md shadow-sm cursor-not-allowed"
                  readonly
                />
                <span v-if="form.errors.discounted_retail_price" class="text-sm text-red-500">{{ form.errors.discounted_retail_price }}</span>
              </div>
            </div>
          </div>

          <!-- ============ WHOLESALE PRICING SECTION ============ -->
          <div class="p-4 border border-green-300 rounded-lg bg-green-50">
            <h2 class="mb-4 text-lg font-semibold text-green-800">Wholesale Pricing</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
              <div>
                <label for="wholesale_price" class="block text-sm font-medium text-gray-700">Wholesale Price</label>
                <input
                  v-model="form.wholesale_price"
                  type="number"
                  step="0.01"
                  id="wholesale_price"
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring focus:ring-green-200"
                />
                <span v-if="form.errors.wholesale_price" class="text-sm text-red-500">{{ form.errors.wholesale_price }}</span>
              </div>
              <div>
                <label for="wholesale_discount" class="block text-sm font-medium text-gray-700">Wholesale Discount (%)</label>
                <input
                  v-model="form.wholesale_discount"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  id="wholesale_discount"
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring focus:ring-green-200"
                />
                <span v-if="form.errors.wholesale_discount" class="text-sm text-red-500">{{ form.errors.wholesale_discount }}</span>
              </div>
              <div>
                <label for="discounted_wholesale_price" class="block text-sm font-medium text-gray-700">Discounted Wholesale Price</label>
                <input
                  v-model="form.discounted_wholesale_price"
                  type="number"
                  step="0.01"
                  id="discounted_wholesale_price"
                  class="block w-full mt-1 bg-gray-100 border-gray-300 rounded-md shadow-sm cursor-not-allowed"
                  readonly
                />
                <span v-if="form.errors.discounted_wholesale_price" class="text-sm text-red-500">{{ form.errors.discounted_wholesale_price }}</span>
              </div>
            </div>
          </div>

          <!-- Stock Quantity Input -->
          <div>
            <label
              for="stock_quantity"
              class="block text-sm font-medium text-gray-700"
              >Stock Quantity</label
            >
            <input
              v-model="form.stock_quantity"
              type="number"
              id="stock_quantity"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span
              v-if="form.errors.stock_quantity"
              class="text-sm text-red-500"
              >{{ form.errors.stock_quantity }}</span
            >
          </div>

          <!-- Barcode Input -->
          <div>
            <label for="barcode" class="block text-sm font-medium text-gray-700"
              >Barcode</label
            >
            <input
              v-model="form.barcode"
              type="text"
              id="barcode"
              readonly
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-700 cursor-not-allowed"
            />
            <span v-if="form.errors.barcode" class="text-sm text-red-500">{{
              form.errors.barcode
            }}</span>
          </div>

          <div class="flex flex-wrap gap-4">
            <!-- Old Image -->
            <div class="w-full md:w-6/12">
              <label class="block text-sm font-medium text-gray-700"
                >Current Image</label
              >
              <div class="mt-2">
                <img
                  v-if="product.image"
                  :src="`/${product.image}`"
                  alt="Product Image"
                  class="rounded-lg h-50 w-50"
                />

                <p v-else class="text-sm text-gray-500">No image available</p>
              </div>
            </div>

            <!-- New Image Upload -->
            <div class="w-full md:w-6/12">
              <label for="image" class="block text-sm font-medium text-gray-700"
                >Upload New Image</label
              >
              <input
                type="file"
                id="image"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                @change="handleImageUpload"
              />
              <span v-if="form.errors.image" class="text-sm text-red-500">{{
                form.errors.image
              }}</span>
            </div>
          </div>

          <!-- Submit Button -->
          <div>
            <button
              type="submit"
              class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400"
            >
              Update
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import { watch } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";

// Props
const props = defineProps({
  product: Object,
  categories: Array,
  colors: Array,
  sizes: Array,
});

// Initialize form with old values
const form = useForm({
  category_id: props.product.category_id || null,
  name: props.product.name || "",
  size_id: props.product.size_id || "",
  color_id: props.product.color_id || "",
  cost_price: props.product.cost_price || null,
  retail_price: props.product.retail_price || null,
  retail_discount: props.product.retail_discount || 0,
  discounted_retail_price: props.product.discounted_retail_price || null,
  wholesale_price: props.product.wholesale_price || null,
  wholesale_discount: props.product.wholesale_discount || 0,
  discounted_wholesale_price: props.product.discounted_wholesale_price || null,
  stock_quantity: props.product.stock_quantity || null,
  barcode: props.product.barcode || "",
  image: null,
});

// Auto-calculate discounted retail price
watch(
  () => [form.retail_price, form.retail_discount],
  ([price, discount]) => {
    const p = parseFloat(price) || 0;
    const d = parseFloat(discount) || 0;
    if (p > 0 && d > 0) {
      form.discounted_retail_price = (p - (p * d) / 100).toFixed(2);
    } else {
      form.discounted_retail_price = null;
    }
  }
);

// Auto-calculate discounted wholesale price
watch(
  () => [form.wholesale_price, form.wholesale_discount],
  ([price, discount]) => {
    const p = parseFloat(price) || 0;
    const d = parseFloat(discount) || 0;
    if (p > 0 && d > 0) {
      form.discounted_wholesale_price = (p - (p * d) / 100).toFixed(2);
    } else {
      form.discounted_wholesale_price = null;
    }
  }
);

const handleImageUpload = (event) => {
  form.image = event.target.files[0];
};

const submit = () => {
  form.post(`/products/${props.product.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      console.log("Product updated successfully!");
    },
    onError: (errors) => {
      console.error("Form submission failed:", errors);
    },
  });
};
</script>
