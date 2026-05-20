<template>
  <Head title="Promotions" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 px-36">
    <!-- Include the Header -->
    <Header />

    <div class="w-5/6 py-12">
      <div class="flex items-center justify-between">
        <div class="flex items-center justify-center space-x-4"></div>
      </div>

      <div class="flex items-center justify-between">
        <div class="flex items-center w-full h-16 space-x-4 rounded-2xl">
          <Link href="/products">
            <img src="/images/back-arrow.png" class="w-14 h-14" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">
            Products / Add Promotions
          </p>
        </div>
        <div class="flex justify-end w-full">
          <span class="flex cursor-pointer" @click="isSelectModalOpen = true">
            <p class="text-xl text-blue-600 font-bold">Add Products</p>
            <img src="/images/selectpsoduct.svg" class="w-6 h-6 ml-2" />
          </span>
        </div>
      </div>

      <div class="overflow-x-auto mt-5">
        <div class="w-full">
          <!-- Selected Products -->
          <div class="flex flex-col items-start justify-center w-full p-5 border-2 border-black rounded-3xl py-8">
            <div class="flex items-center justify-between w-full">
              <h2 class="text-4xl font-bold text-black">Products</h2>
            </div>

            <div class="w-full text-center">
              <p v-if="form.products.length === 0" class="text-2xl text-red-500">
                No Products to show
              </p>
            </div>

            <div
              class="flex items-center w-full py-4 border-b border-black"
              v-for="item in form.products"
              :key="item.id"
            >
              <div class="flex w-1/6">
                <img
                  :src="item.image ? `/${item.image}` : '/images/placeholder.jpg'"
                  alt="Supplier Image"
                  class="object-cover w-16 h-16 border border-gray-500"
                />
              </div>

              <div class="flex flex-col justify-between w-5/6">
                <p class="text-xl text-black">
                  {{ item.name }}
                </p>
                <div class="flex items-center justify-between w-full">
                  <div class="flex space-x-4">



                    <p
                      @click="incrementQuantity(item.id)"
                      class="flex items-center justify-center w-8 h-8 text-white bg-black rounded cursor-pointer"
                    >
                      <i class="ri-add-line"></i>
                    </p>

                    <input
                      type="number"
                      v-model.number="item.quantity"
                      min="1"
                      class="bg-[#D9D9D9] border-2 border-black h-8 w-24 text-black flex justify-center items-center rounded text-center"
                      @change="onQtyChanged(item)"
                    />

                    <p
                      @click="decrementQuantity(item.id)"
                      class="flex items-center justify-center w-8 h-8 text-white bg-black rounded cursor-pointer"
                    >
                      <i class="ri-subtract-line"></i>
                    </p>
                  </div>

                  <div class="flex items-center justify-center">
                    <div>

                        <p>                     <span class="text-lg font-bold text-purple-600"> Cost price : {{ item.cost_price }}</span>
</p>
                      <p class="text-2xl font-bold text-black text-right">
                        {{ item.selling_price }} LKR
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex justify-end w-1/6">
                <p
                  @click="removeProduct(item.id)"
                  class="text-3xl text-black border-2 border-black rounded-full cursor-pointer"
                  title="Remove"
                >
                  <i class="ri-close-line"></i>
                </p>
              </div>
            </div>
          </div>

          <!-- Details -->
          <div class="flex flex-col items-start justify-center w-full p-5 border-2 border-black rounded-3xl py-8 mt-4">
            <div class="flex items-center justify-between w-full">
              <h2 class="text-4xl font-bold text-black">Product Details</h2>
            </div>

            <div class="mt-6 space-y-4 text-left w-full">
              <!-- Category Name -->
              <div>
                <label class="block text-sm font-medium text-black-300">Category Name:</label>
                <select
                  required
                  v-model="form.category_id"
                  id="parent_id"
                  class="w-full px-4 py-2 mt-2 text-black bg-white rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                >
                  <option value="">Select a Category</option>
                  <option
                    v-for="category in allcategories"
                    :key="category.id"
                    :value="category.id"
                  >
                    {{
                      category.hierarchy_string
                        ? category.hierarchy_string + " ----> " + category.name
                        : category.name
                    }}
                  </option>
                </select>
                <span v-if="form.errors.category_id" class="mt-4 text-red-500">
                  {{ form.errors.category_id }}
                </span>
              </div>

              <div>
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-black-300">Product Name:</label>
                    <input
                      v-model="form.name"
                      type="text"
                      id="name"
                      required
                      placeholder="Enter Product Name"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.name" class="mt-4 text-red-500">
                      {{ form.errors.name }}
                    </span>
                  </div>
                </div>
              </div>

              <div>
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label for="size_id" class="block text-sm font-medium text-black-300">Size:</label>
                    <select
                      v-model="form.size_id"
                      id="size_id"
                      class="w-full px-4 py-2 mt-2 text-black bg-white rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    >
                      <option value="">Select a Size</option>
                      <option v-for="size in sizes" :key="size.id" :value="size.id">
                        {{ size.name }}
                      </option>
                    </select>
                    <span v-if="form.errors.size_id" class="mt-2 text-red-500">
                      {{ form.errors.size_id }}
                    </span>
                  </div>

                  <div class="w-full">
                    <label for="color_id" class="block text-sm font-medium text-black-300">Base :</label>
                    <select
                      v-model="form.color_id"
                      id="color_id"
                      class="w-full px-4 py-2 mt-2 text-black bg-white rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    >
                      <option value="">Select a Base</option>
                      <option v-for="color in colors" :key="color.id" :value="color.id">
                        {{ color.name }}
                      </option>
                    </select>
                    <span v-if="form.errors.color_id" class="mt-2 text-red-500">
                      {{ form.errors.color_id }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-8 mt-6">
                <!-- Cost Price (auto from products × qty) -->
                <div class="w-full">
                  <label for="cost_price" class="block text-sm font-medium text-black-300">Cost Price (auto):</label>
                  <input
                    type="number"
                    id="cost_price"
                    :value="form.cost_price"
                    readonly
                    class="w-full px-4 py-2 mt-2 text-black bg-gray-100 rounded-md border border-black/20"
                  />
                  <span v-if="form.errors.cost_price" class="mt-2 text-red-500">
                    {{ form.errors.cost_price }}
                  </span>
                </div>

                <div class="w-full">
                  <label for="stock_quantity" class="block text-sm font-medium text-black-300">Stock Quantity:</label>
                  <input
                    type="number"
                    id="stock_quantity"
                    v-model.number="form.stock_quantity"
                    min="0"
                    class="w-full px-4 py-2 mt-2 text-black bg-white rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    placeholder="Enter stock quantity"
                    required
                  />
                  <span v-if="form.errors.stock_quantity" class="mt-2 text-red-500">
                    {{ form.errors.stock_quantity }}
                  </span>
                </div>
              </div>

              <div class="flex items-center gap-8 mt-6">
                <!-- Selling Price -->
                <div class="w-full">
                  <label for="selling_price" class="block text-sm font-medium text-black-300">Selling Price:</label>
                  <input
                    type="number"
                    id="selling_price"
                    :value="form.selling_price"
                    @input="onSellingPriceInput"
                    @blur="updateDiscountedPrice"
                    step="0.01"
                    min="0"
                    class="w-full px-4 py-2 mt-2 text-black bg-white rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    placeholder="Enter selling price"
                    required
                  />
                  <span v-if="form.errors.selling_price" class="mt-2 text-red-500">
                    {{ form.errors.selling_price }}
                  </span>
                </div>

                <!-- Discount (%) -->
                <div class="w-full">
                  <label for="discount" class="block text-sm font-medium text-black-300">Discount (%):</label>
                  <input
                    type="number"
                    id="discount"
                    :value="form.discount"
                    @input="onDiscountInput"
                    @blur="updateDiscountedPrice"
                    step="0.01"
                    min="0"
                    class="w-full px-4 py-2 mt-2 text-black bg-white rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    placeholder="Enter discount percentage"
                  />
                </div>

                <!-- Discounted Price -->
                <div class="w-full">
                  <label for="discounted_price" class="block text-sm font-medium text-black-300">Discounted Price:</label>
                  <input
                    type="number"
                    id="discounted_price"
                    :value="form.discounted_price"
                    @input="onDiscountedPriceInput"
                    @blur="updateDiscount"
                    step="0.01"
                    min="0"
                    class="w-full px-4 py-2 mt-2 text-black bg-white rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    placeholder="Discounted price will appear here"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-black-300">Description:</label>
                <textarea
                  v-model="form.description"
                  id="description"
                  placeholder="Enter Description"
                  class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  rows="2"
                ></textarea>
                <span v-if="form.errors.description" class="mt-4 text-red-500">
                  {{ form.errors.description }}
                </span>
              </div>

              <div class="flex items-center gap-8 mt-6">
                <div class="w-full">
                  <label for="image" class="block text-sm font-medium text-black-300">Image:</label>
                  <input
                    type="file"
                    id="image"
                    @change="handleImageUpload"
                    class="w-full px-4 py-2 mt-2 text-white bg-gray-800 rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.image" class="mt-2 text-red-500">
                    {{ form.errors.image }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div v-if="form.products.length !== 0" class="mt-4">
            <button
              @click.prevent="submitForm"
              :class="
                HasRole(['Admin'])
                  ? 'px-12 py-4 text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-xl cursor-pointer'
                  : 'px-12 py-4 text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 cursor-not-allowed rounded-xl'
              "
              :title="HasRole(['Admin']) ? '' : 'You do not have permission to add more Products'"
            >
              <i class="pr-4 ri-add-circle-fill"></i> Save Promotion
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <SelectProductModel
    v-model:open="isSelectModalOpen"
    :allcategories="allcategories"
    :colors="colors"
    :sizes="sizes"
    @selected-products="handleSelectedProducts"
    :hidePromotions="true"
  />
  <Footer />
</template>

<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";
import SelectProductModel from "@/Components/custom/SelectProductModel2.vue";

const isSelectModalOpen = ref(false);

const props = defineProps({
  allcategories: Array,
  colors: Array,
  sizes: Array,
});

/* -----------------
   Numeric helpers
------------------*/
const toNum = (v) => {
  const n = Number(v);
  return Number.isFinite(n) ? n : 0;
};
const round2 = (n) => Number((toNum(n)).toFixed(2));
const clampNN = (n) => Math.max(0, toNum(n));

/* ---------------
   Inertia form
----------------*/
const form = useForm({
  category_id: "",
  supplier_id: "",
  name: "",
  code: "",
  size_id: "",
  color_id: "",
  cost_price: 0,          // auto from products × qty
  discount: 0,            // %
  discounted_price: 0,    // derived
  selling_price: 0,
  stock_quantity: 0,
  barcode: "",
  image: null,
  description: "",
  products: [],           // [{ id, name, image, cost_price, selling_price, quantity }]
});

/* ---------------------------
   PRODUCT SELECTION & QTY
---------------------------- */
const handleSelectedProducts = (selectedProducts) => {
  selectedProducts.forEach((p) => {
    const existing = form.products.find((item) => item.id === p.id);
    if (existing) {
      existing.quantity += 1;
    } else {
      form.products.push({
        ...p,
        quantity: 1,
        apply_discount: false,
      });
    }
  });
  recalcCostFromProducts();
};

const incrementQuantity = (id) => {
  const product = form.products.find((i) => i.id === id);
  if (product) {
    product.quantity = clampNN(product.quantity) + 1;
    recalcCostFromProducts();
  }
};

const decrementQuantity = (id) => {
  const product = form.products.find((i) => i.id === id);
  if (product && product.quantity > 1) {
    product.quantity = clampNN(product.quantity) - 1;
    recalcCostFromProducts();
  }
};

const onQtyChanged = (item) => {
  item.quantity = Math.max(1, clampNN(item.quantity));
  recalcCostFromProducts();
};

const removeProduct = (id) => {
  form.products = form.products.filter((i) => i.id !== id);
  recalcCostFromProducts();
};

const recalcCostFromProducts = () => {
  const total = form.products.reduce((sum, p) => {
    const unit = clampNN(p.cost_price);
    const qty = clampNN(p.quantity);
    return sum + unit * qty;
  }, 0);
  form.cost_price = round2(total);
};

/* --------------------------------
   PRICE / DISCOUNT CALCULATIONS
--------------------------------- */
const updateDiscountedPrice = () => {
  const sp = clampNN(form.selling_price);
  const d  = clampNN(form.discount);
  const dp = sp * (1 - d / 100);
  form.selling_price = round2(sp);
  form.discount = round2(d);
  form.discounted_price = round2(dp);
};

const updateDiscount = () => {
  const sp0 = clampNN(form.selling_price);
  let dp0 = clampNN(form.discounted_price);

  if (sp0 <= 0) {
    form.discount = 0;
    form.discounted_price = 0;
    return;
  }
  if (dp0 > sp0) dp0 = sp0;

  const d0 = (1 - dp0 / sp0) * 100;
  form.selling_price = round2(sp0);
  form.discounted_price = round2(dp0);
  form.discount = round2(d0);
};

// Keep numbers numeric while typing
const onSellingPriceInput = (e) => {
  form.selling_price = clampNN(e.target.value);
};
const onDiscountInput = (e) => {
  form.discount = clampNN(e.target.value);
};
const onDiscountedPriceInput = (e) => {
  form.discounted_price = clampNN(e.target.value);
};

// Recalc cost if product qty/cost changes elsewhere
watch(
  () => form.products.map(p => `${p.id}:${p.quantity}:${p.cost_price}`).join("|"),
  recalcCostFromProducts
);

// Initialize derived fields once
updateDiscountedPrice();

/* ---------------
   File input
----------------*/
const handleImageUpload = (event) => {
  form.image = event.target.files?.[0] ?? null;
};

/* ---------------
   Submit
----------------*/
const submitForm = () => {
  recalcCostFromProducts();
  updateDiscountedPrice();

  form.post("/submit_promotion", {
    onSuccess: () => {
      console.log("Promotion created successfully!");
      form.reset();
      // keep explicit resets for derived/numeric fields
      form.products = [];
      form.cost_price = 0;
      form.discount = 0;
      form.discounted_price = 0;
      form.selling_price = 0;
    },
    onError: (errors) => {
      console.error("Form submission failed:", errors);
    },
  });
};
</script>
