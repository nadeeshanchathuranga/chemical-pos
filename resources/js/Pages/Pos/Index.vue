<template>

    <Head title="POS" />
    <Banner />
    <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-4 bg-gray-100 md:px-36 px-16">
        <!-- Include the Header -->
        <Header />

        <div class="w-full md:w-5/6 w-full py-12 space-y-16">
            <div class="flex items-center justify-between space-x-4">
                <div class="flex w-full space-x-4">
                    <Link href="/">
                    <img src="/images/back-arrow.png" class="w-14 h-14" />
                    </Link>
                    <p class="pt-3 text-4xl font-bold tracking-wide text-black uppercase">
                        PoS
                    </p>
                </div>
                <div class="flex items-center justify-between w-full space-x-4">
                    <p class="text-3xl font-bold tracking-wide text-black">
                        Order ID : #{{ displayOrderId }}
                    </p>

                    <!-- Sale Type Toggle -->
                    <div class="flex items-center space-x-3 bg-white rounded-full shadow-lg px-3 py-2 border-2 border-gray-300">
                        <button
                            @click="sale_type = 'retail'"
                            :class="sale_type === 'retail' ? 'bg-blue-600 text-white shadow-inner' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-8 py-3 text-xl font-black tracking-wider uppercase rounded-full transition-all duration-300"
                        >
                            Retail
                        </button>
                        <button
                            @click="sale_type = 'wholesale'"
                            :class="sale_type === 'wholesale' ? 'bg-green-600 text-white shadow-inner' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-8 py-3 text-xl font-black tracking-wider uppercase rounded-full transition-all duration-300"
                        >
                            Wholesale
                        </button>
                    </div>

                    <p class="text-3xl text-black cursor-pointer">
                        <i @click="refreshData" class="ri-restart-line"></i>
                    </p>
                </div>
            </div>
            <div class="flex md:flex-row flex-col w-full gap-4">
                <div class="flex flex-col md:w-1/2 w-full">
                    <div class="flex flex-col w-full">
                        <div class="p-16 space-y-8 bg-black shadow-lg rounded-3xl">
                            <p class="mb-4 text-5xl font-bold text-white">Customer Details</p>
                            <div class="mb-3">
                                <input v-model="customer.name" type="text" placeholder="Enter Customer Name"
                                    class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div class="flex gap-2 mb-3 text-black">
                                <!-- <select
                  v-model="customer.countryCode"
                  class="w-[60px] px-2 py-2 bg-white placeholder-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="+94">+94</option>
                  <option value="+1">+1</option>
                  <option value="+44">+44</option>
                </select> -->
                                <input v-model="customer.contactNumber" type="text"
                                    placeholder="Enter Customer Contact Number"
                                    class="flex-grow px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div class="text-black">
                                <input v-model="customer.email" type="email" placeholder="Enter Customer Email"
                                    class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>

                            <div class="text-black">
                                <select required v-model="employee_id" id="employee_id"
                                    class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="" disabled selected>Select an Employee</option>
                                    <option v-for="employee in allemployee" :key="employee.id" :value="employee.id">
                                        {{ employee.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col items-center justify-center w-full md:pt-32 py-8 md:py-0 space-y-8">
                        <img src="/images/Fading wheel.gif" class="object-cover w-32 h-32 rounded-full" />
                        <p class="text-3xl text-black">
                            Bar Code Scanner is in Progress...
                        </p>
                    </div>
                </div>
                <div class="flex md:w-1/2 w-full p-8 border-4 border-black rounded-3xl">
                    <div class="flex flex-col items-start justify-center w-full md:px-12 px-4">
                        <div class="flex items-center justify-between w-full">
                            <h2 class="md:text-5xl text-4xl font-bold text-black">Billing Details</h2>
                            <div class="flex items-center gap-4">
                                <button class="rounded bg-emerald-600 px-3 py-2 text-sm font-bold text-white"
                                    @click="isReturnModalOpen = true">
                                    Return
                                </button>
                                <span class="flex cursor-pointer" @click="isSelectModalOpen = true">
                                    <p class="text-xl text-blue-600 font-bold">User Manual</p>
                                    <img src="/images/selectpsoduct.svg" class="w-6 h-6 ml-2" />
                                </span>
                            </div>
                        </div>

                        <div class="flex items-end justify-between w-full my-5 border-2 border-black rounded-2xl">
                            <div class="flex items-center justify-center w-3/4">
                                <label for="search" class="text-xl font-medium text-gray-800"></label>
                                <input v-model="form.barcode" id="search" type="text" placeholder="Enter BarCode Here!"
                                    class="w-full h-16 px-4 rounded-l-2xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div class="flex items-end justify-end w-1/4">
                                <button @click="submitBarcode"
                                    class="px-12 py-4 text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-r-xl">
                                    Enter
                                </button>
                            </div>
                        </div>

                        <!-- <div class="max-w-xs relative space-y-3">
              <label for="search" class="text-gray-900">
                Type the product name to search
              </label>

              <input
                v-model="form.barcode"
                id="search"
                type="text"
                placeholder="Enter BarCode Here!"
                class="w-full h-16 px-4 rounded-l-2xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              />

              <ul
                v-if="searchResults.length"
                class="w-full rounded bg-white border border-gray-300 px-4 py-2 space-y-1 absolute z-10"
              >
                <li class="px-1 pt-1 pb-2 font-bold border-b border-gray-200">
                  Showing {{ searchResults.length }} results
                </li>
                <li
                  v-for="product in searchResults"
                  :key="product.id"
                  @click="selectProduct(product.name)"
                  class="cursor-pointer hover:bg-gray-100 p-1"
                >
                  {{ product.name }}
                </li>
              </ul>

              <p v-if="form.barcode" class="text-lg pt-2 absolute">
                You have selected:
                <span class="font-semibold">{{ form.barcode }}</span>
              </p>
            </div> -->

                        <div class="w-full text-center">
                            <p v-if="products.length === 0" class="text-2xl text-red-500">
                                No Products to show
                            </p>
                        </div>

                        <div class="flex items-center w-full py-4 border-b border-black" v-for="item in products"
                            :key="item.id">
                            <div class="flex w-1/6">
                                <img :src="item.image ? `/${item.image}` : '/images/placeholder.jpg'
                                    " alt="Supplier Image" class="object-cover w-16 h-16 border border-gray-500" />
                            </div>
                            <div class="flex flex-col justify-between w-5/6">
                                <p class="text-xl text-black">
                                    {{ item.name }}
                                </p>
                                
                                <select 
                                    v-if="item.batches && item.batches.length > 1" 
                                    v-model="item.selected_batch_id"
                                    class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option v-for="batch in item.batches" :key="batch.id" :value="batch.id">
                                        Batch {{ batch.batch_no || batch.id }} - {{ batch.stock_quantity }} in stock (Ret: ${{ batch.retail_price }}, Whl: ${{ batch.wholesale_price }})
                                    </option>
                                </select>

                                <div
  v-if="Number(item.is_promotion) === 1"
  class="mt-2 rounded-lg border border-gray-200 p-3 bg-gray-50"
>
  <p class="text-md font-bold text-black mb-2">
    Pack items
  </p>

  <!-- Scrollable list -->
  <ul
    class="mt-1 list-disc pl-5 space-y-1 max-h-40 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100"
  >
    <li
      v-for="pi in item.promotion_items ?? []"
      :key="pi.id"
      class="text-sm text-gray-700 bg-white rounded-md px-2 py-1 shadow-sm hover:bg-gray-50"
    >

      <span v-if="pi.product">  {{ pi.product.name }}</span>
      <span class="ml-2 text-lg text-dark">× {{ pi.quantity }}</span>
    </li>
  </ul>
</div>
                                <div class="flex items-center justify-between w-full">
                                    <div class="flex space-x-4">
                                        <p @click="incrementQuantity(item.id)"
                                            class="flex items-center justify-center w-8 h-8 text-white bg-black rounded cursor-pointer">
                                            <i class="ri-add-line"></i>
                                        </p>
                                        <!-- <p
                      class="bg-[#D9D9D9] border-2 border-black h-8 w-8 text-black flex justify-center items-center rounded"
                    >
                      {{ item.quantity }}
                    </p> -->
                                        <input type="number" v-model="item.quantity" min="0"
                                            class="bg-[#D9D9D9] border-2 border-black h-8 w-24 text-black flex justify-center items-center rounded text-center" />
                                        <p @click="decrementQuantity(item.id)"
                                            class="flex items-center justify-center w-8 h-8 text-white bg-black rounded cursor-pointer">
                                            <i class="ri-subtract-line"></i>
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-center">
                                        <div>
                                            <p @click="applyDiscount(item.id)" v-if="
                                                getItemDiscountPercent(item) > 0 &&
                                                item.apply_discount == false &&
                                                !appliedCoupon &&
                                                !isReturnCashMode
                                            "
                                                class="cursor-pointer py-1 text-center px-4 bg-green-600 rounded-xl font-bold text-white tracking-wider">
                                                Apply {{ getItemDiscountPercent(item) }}% off
                                            </p>

                                            <p v-if="
                                                getItemDiscountPercent(item) > 0 &&
                                                item.apply_discount == true &&
                                                !appliedCoupon &&
                                                !isReturnCashMode
                                            " @click="removeDiscount(item.id)"
                                                class="cursor-pointer py-1 text-center px-4 bg-red-600 rounded-xl font-bold text-white tracking-wider">
                                                Remove {{ getItemDiscountPercent(item) }}% Off
                                            </p>
                                            <p class="text-2xl font-bold text-black text-right">
                                                {{ getItemDisplayPrice(item) }}
                                                LKR
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end w-1/6">
                                <p @click="removeProduct(item.id)"
                                    class="text-3xl text-black border-2 border-black rounded-full cursor-pointer">
                                    <i class="ri-close-line"></i>
                                </p>
                            </div>
                        </div>
                        <div class="w-full pt-6 space-y-2">
                            <div class="flex items-center justify-between w-full px-8">
                                <p class="text-xl">Sub Total</p>
                                <p class="text-xl">{{ subtotal }} LKR</p>
                            </div>
                            <div class="flex items-center justify-between w-full px-8 py-2 pb-4 border-b border-black">
                                <p class="text-xl">Discount</p>
                                <p class="text-xl">( {{ totalDiscount }} LKR )</p>
                            </div>
                            <!-- <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                <p class="text-xl text-black">Custom Discount</p>
                <span>
                  <CurrencyInput
                    v-model="custom_discount"
                  />
                  <span class="ml-2">LKR</span>
                </span>
              </div> -->




                            <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                                <p class="text-xl text-black">Custom Discount</p>
                                <span class="flex items-center">
                                    <CurrencyInput v-model="custom_discount" @blur="validateCustomDiscount"
                                        placeholder="Enter value" class=" rounded-md px-2 py-1 text-black text-md"
                                        :disabled="isReturnCashMode" />
                                    <select v-model="custom_discount_type"
                                        class="ml-2 px-8 border-black rounded-md text-black   py-1 text-md"
                                        :disabled="isReturnCashMode">
                                        <option value="percent">%</option>
                                        <option value="fixed">Rs</option>
                                    </select>
                                </span>
                            </div>

                            <div v-if="isExchangeReturn" class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                                <p class="text-xl text-black">Return Credit</p>
                                <p>{{ Number(exchangeCredit || 0).toFixed(2) }} LKR</p>
                            </div>

                            <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                                <p class="text-xl text-black">Current Paid</p>
                                <span>
                                    <CurrencyInput v-model="cash" :options="{ currency: 'EUR' }" />
                                    <span class="ml-2">LKR</span>
                                </span>
                            </div>
                            <div v-if="props.loadedSale" class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                                <p class="text-xl text-black">Advance Paid</p>
                                <p>{{ previouslyPaid }} LKR</p>
                            </div>
                            <div v-if="props.loadedSale" class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                                <p class="text-xl text-black">Remaining Balance</p>
                                <p>{{ remainingBalance }} LKR</p>
                            </div>
                            <div class="flex items-center justify-between w-full px-8 pt-4">
                                <p class="text-3xl text-black">{{ totalLabel }}</p>
                                <p class="text-3xl text-black">{{ total }} LKR</p>
                            </div>


                            <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                                <p class="text-xl text-black">Balance</p>
                                <p>{{ balance }} LKR</p>
                            </div>
                        </div>

                        <div class="w-full my-5" v-if="!isReturnCashMode">
                            <div class="relative flex items-center">
                                <!-- Input Field -->
                                <label for="coupon" class="sr-only">Coupon Code</label>
                                <input id="coupon" v-model="couponForm.code" type="text" placeholder="Enter Coupon Code"
                                    class="w-full h-16 px-6 pr-40 text-lg text-gray-800 placeholder-gray-500 border-2 border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />

                                <template v-if="!appliedCoupon">
                                    <button type="button" @click="submitCoupon"
                                        class="absolute right-2 top-2 h-12 px-6 text-lg font-semibold text-white uppercase bg-blue-600 rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Apply Coupon
                                    </button>
                                </template>
                                <template v-else>
                                    <button type="button" @click="removeCoupon"
                                        class="absolute right-2 top-2 h-12 px-6 text-lg font-semibold text-white uppercase bg-red-600 rounded-full hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        Remove Coupon
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="flex flex-col w-full space-y-8">
                            <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                                <label class="flex items-center space-x-3 text-xl text-black">
                                    <input type="checkbox" v-model="isCredit" class="w-5 h-5 text-blue-600 rounded" />
                                    <span>Credit Bill</span>
                                </label>
                                <span class="text-sm text-gray-600">Credit mode keeps inventory updated immediately while payments post to finance separately.</span>
                            </div>
                            <div class="flex items-center justify-center w-full pt-8 space-x-8">
                                <p class="text-xl text-black">Payment Method :</p>
                                <div @click="selectedPaymentMethod = 'cash'" :class="[
                                    'cursor-pointer w-[100px]  border border-black rounded-xl flex flex-col justify-center items-center text-center',
                                    selectedPaymentMethod === 'cash'
                                        ? 'bg-yellow-500 font-bold'
                                        : 'text-black',
                                ]">
                                    <img src="/images/money-stack.png" alt="" class="w-24" />
                                </div>
                                <div @click="selectedPaymentMethod = 'card'" :class="[
                                    'cursor-pointer w-[100px] border border-black rounded-xl flex flex-col justify-center items-center text-center',
                                    selectedPaymentMethod === 'card'
                                        ? 'bg-yellow-500 font-bold'
                                        : 'text-black',
                                ]">
                                    <img src="/images/bank-card.png" alt="" class="w-24" />
                                </div>
                            </div>

                            <div class="flex items-center justify-center w-full">
                                <button @click="() => {
                                    submitOrder();
                                }
                                    " type="button" :disabled="products.length === 0" :class="[
                                        'w-full bg-black py-4 text-2xl font-bold tracking-wider text-center text-white uppercase rounded-xl',
                                        products.length === 0
                                            ? ' cursor-not-allowed'
                                            : ' cursor-pointer',
                                    ]">
                                    <i class="pr-4 ri-add-circle-fill"></i> {{ confirmButtonLabel }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <PosSuccessModel :open="isSuccessModalOpen" @update:open="handleModalOpenUpdate" :products="receiptData.products"
        :employee="employee" :cashier="loggedInUser" :customer="receiptData.customer" :orderid="receiptData.orderid" :cash="receiptData.cash"
        :balance="receiptData.balance" :subTotal="receiptData.subTotal" :totalDiscount="receiptData.totalDiscount" :total="receiptData.total"
        :payment-method="receiptData.paymentMethod"
        :is-credit-bill="receiptData.isCreditBill"
        :previously-paid="receiptData.previouslyPaid"
        :current-paid="receiptData.currentPaid"
        :remaining-balance="receiptData.remainingBalance"
        :is-return-exchange="receiptData.isReturnExchange"
        :return-order-id="receiptData.returnOrderId"
        :exchange-credit="receiptData.exchangeCredit"
        :custom_discount_type="receiptData.custom_discount_type"
        :custom_discount="receiptData.custom_discount"
        :sale-type="receiptData.sale_type" />
    <AlertModel v-model:open="isAlertModalOpen" :message="message" />

    <SelectProductModel v-model:open="isSelectModalOpen" :allcategories="allcategories" :colors="colors" :sizes="sizes"
        @selected-products="handleSelectedProducts" />
    <PosReturnModal v-model:open="isReturnModalOpen" @apply-return-items="handleApplyReturnItems" />
    <ReturnSuccessModal :open="isReturnSuccessModalOpen" @update:open="handleReturnSuccessOpenUpdate"
        :order-id="returnSale?.order_id || ''"
        :cashier="loggedInUser" :customer="returnSale?.customer || {}" :products="returnReceiptProducts" :total="returnProcessedTotal"
        :payment-method="returnPaymentMethod" />
    <Footer />
</template>
<script setup>
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import PosSuccessModel from "@/Components/custom/PosSuccessModel.vue";
import AlertModel from "@/Components/custom/AlertModel.vue";
import { useForm, router } from "@inertiajs/vue3";
import { ref, onMounted, computed, watch } from "vue";
import { Head } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import CurrencyInput from "@/Components/custom/CurrencyInput.vue";
import SelectProductModel from "@/Components/custom/SelectProductModel.vue";
import ProductAutoComplete from "@/Components/custom/ProductAutoComplete.vue";
import PosReturnModal from "@/Components/custom/PosReturnModal.vue";
import ReturnSuccessModal from "@/Components/custom/ReturnSuccessModal.vue";
import { generateOrderId } from "@/Utils/Other.js";

const product = ref(null);
const error = ref(null);
const products = ref([]);
const isSuccessModalOpen = ref(false);
const isAlertModalOpen = ref(false);
const message = ref("");
const appliedCoupon = ref(null);
const cash = ref(0);
const custom_discount = ref(0);
const isSelectModalOpen = ref(false);
const isReturnModalOpen = ref(false);
const isReturnSuccessModalOpen = ref(false);
const custom_discount_type = ref('percent');
const orderid = computed(() => generateOrderId());
const returnSale = ref(null);
const isReturnMode = ref(false);
const returnPaymentMethod = ref('Cash');
const returnProcessedTotal = ref(0);
const returnType = ref("cash_return");
const exchangeCredit = ref(0);
const exchangeReturnItems = ref([]);
const returnReceiptProducts = ref([]);
const sale_type = ref('retail');
const isExchangeReturn = computed(() => {
    return isReturnMode.value && returnType.value === "product_return";
});
const isReturnCashMode = computed(() => {
    return isReturnMode.value && returnType.value === "cash_return";
});
const confirmButtonLabel = computed(() => {
    if (!isReturnMode.value) {
        if (props.loadedSale) {
            return "Confirm Payment";
        }
        return "Confirm Order";
    }

    if (isExchangeReturn.value) {
        return "Confirm Exchange";
    }

    return "Confirm Return";
});
const totalLabel = computed(() => {
    if (!isReturnMode.value) {
        return "Total";
    }

    return isExchangeReturn.value ? "Payable Total" : "Refund Total";
});
const displayOrderId = computed(() => {
    if (isReturnCashMode.value && returnSale.value?.order_id) {
        return returnSale.value.order_id;
    }

    if (props.loadedSale?.order_id) {
        return props.loadedSale.order_id;
    }

    return orderid.value;
});


// const balance = ref(0);

const handleModalOpenUpdate = (newValue) => {
    isSuccessModalOpen.value = newValue;
    if (!newValue) {
        refreshData();
    }
};

const handleReturnSuccessOpenUpdate = (newValue) => {
    isReturnSuccessModalOpen.value = newValue;
    if (!newValue) {
        refreshData();
    }
};

const props = defineProps({
    loggedInUser: Object, // Using backend product name to avoid messing with selected products
    allcategories: Array,
    allemployee: Array,
    initialProducts: Array,
    loadedSale: Object,
    loadedSaleDue: Number,
    colors: Array,
    sizes: Array,
});

const getPaymentMethodFromLoaded = (method) => {
    if (!method) return 'cash';
    const normalized = String(method).toLowerCase();
    if (normalized.includes('card')) return 'card';
    if (normalized.includes('online')) return 'online';
    return 'cash';
};

const isCredit = ref(Boolean(props.loadedSale) || props.loadedSale?.is_credit || false);
products.value = props.initialProducts || [];
cash.value = props.loadedSale ? Number(props.loadedSaleDue || 0) : 0;

const previouslyPaid = computed(() => Number(props.loadedSale?.paid_amount || 0).toFixed(2));
const currentPaid = computed(() => Number(cash.value || 0).toFixed(2));

const discount = ref(0);

const customer = ref({
    name: "",
    countryCode: "",
    contactNumber: "",
    email: "",
});

if (props.loadedSale?.customer) {
    customer.value = {
        name: props.loadedSale.customer.name || "",
        countryCode: "",
        contactNumber: props.loadedSale.customer.phone || "",
        email: props.loadedSale.customer.email || "",
    };
}

const employee_id = ref("");

const selectedPaymentMethod = ref(getPaymentMethodFromLoaded(props.loadedSale?.payment_method));

const refreshData = () => {
    router.visit(route("pos.index"), {
        preserveScroll: false, // Reset scroll
        preserveState: false, // Reset component state
    });
};

const receiptData = ref({
    products: [],
    customer: { name: '', countryCode: '', contactNumber: '', email: '' },
    cash: 0,
    balance: 0,
    subTotal: '0.00',
    totalDiscount: '0.00',
    total: '0.00',
    paymentMethod: 'cash',
    isCreditBill: false,
    previouslyPaid: '0.00',
    currentPaid: '0.00',
    remainingBalance: '0.00',
    orderid: '',
    custom_discount: 0,
    custom_discount_type: 'percent',
    sale_type: 'retail',
    isReturnExchange: false,
    returnOrderId: '',
    exchangeCredit: 0,
});

const captureReceiptData = () => {
    receiptData.value = {
        products: products.value.map((item) => ({ ...item })),
        customer: { ...customer.value },
        cash: cash.value,
        balance: balance.value,
        subTotal: subtotal.value,
        totalDiscount: totalDiscount.value,
        total: total.value,
        paymentMethod: selectedPaymentMethod.value,
        isCreditBill: isCredit.value,
        previouslyPaid: previouslyPaid.value,
        currentPaid: currentPaid.value,
        remainingBalance: remainingBalance.value,
        orderid: displayOrderId.value,
        custom_discount: custom_discount.value,
        custom_discount_type: custom_discount_type.value,
        sale_type: sale_type.value,
        isReturnExchange: isExchangeReturn.value,
        returnOrderId: returnSale.value?.order_id || '',
        exchangeCredit: exchangeCredit.value,
    };
};

const clearOrderState = () => {
    products.value = [];
    cash.value = 0;
    customer.value = { name: '', countryCode: '', contactNumber: '', email: '' };
    employee_id.value = '';
    appliedCoupon.value = null;
    couponForm.code = '';
    form.barcode = '';
    error.value = null;
    custom_discount.value = 0;
    custom_discount_type.value = 'percent';
    isCredit.value = false;
    selectedPaymentMethod.value = 'cash';
    returnSale.value = null;
    isReturnMode.value = false;
    returnType.value = 'cash_return';
    exchangeCredit.value = 0;
    returnReceiptProducts.value = [];
    exchangeReturnItems.value = [];
};

const removeProduct = (id) => {
    if (props.loadedSale) {
        return;
    }
    products.value = products.value.filter((item) => item.id !== id);
};

const removeCoupon = () => {
    appliedCoupon.value = null; // Clear the applied coupon
    couponForm.code = ""; // Clear the coupon field
};

const incrementQuantity = (id) => {
    if (props.loadedSale) {
        return;
    }
    const product = products.value.find((item) => item.id === id);
    if (product) {
        product.quantity += 1;
    }
};

const decrementQuantity = (id) => {
    if (props.loadedSale) {
        return;
    }
    const product = products.value.find((item) => item.id === id);
    if (product && product.quantity > 1) {
        product.quantity -= 1;
    }
};

// const orderId = computed(() => {
//   const timestamp = Date.now().toString(36).toUpperCase(); // Convert timestamp to a base-36 string
//   const randomString = Math.random().toString(36).substr(2, 5).toUpperCase(); // Generate a shorter random string
//   return `ORD-${timestamp}-${randomString}`; // Combine to create unique order ID
// });
const orderId = computed(() => {
    const characters =
        "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    return Array.from({ length: 6 }, () =>
        characters.charAt(Math.floor(Math.random() * characters.length))
    ).join("");
});

const submitOrder = async () => {
    if (isReturnMode.value) {
        await submitReturnOrder();
        return;
    }

    // if (window.confirm("Are you sure you want to confirm the order?")) {
    console.log(products.value);
    const requiredCash = props.loadedSale ? Number(props.loadedSaleDue || 0) : Number(total.value);
    if (
        selectedPaymentMethod.value === "cash" &&
        !isCredit.value &&
        requiredCash > 0 &&
        Number(cash.value) < requiredCash
    ) {
        isAlertModalOpen.value = true;
        message.value = "Cash is not enough";
        return;
    }

    if (isCredit.value && (!customer.value.name.trim() || !customer.value.contactNumber.trim())) {
        isAlertModalOpen.value = true;
        message.value = "Customer name and contact number are required for credit bills.";
        return;
    }
    try {
        const response = await axios.post("/pos/submit", {
            customer: customer.value,
            products: products.value,
            employee_id: employee_id.value,
            paymentMethod: selectedPaymentMethod.value,
            userId: props.loggedInUser.id,
            orderid: orderid.value,
            cash: cash.value,
            is_credit: isCredit.value,
            sale_id: props.loadedSale?.id || null,
            custom_discount: custom_discount.value,
            custom_discount_type: custom_discount_type.value,
            sale_type: sale_type.value,
        });
        captureReceiptData();
        clearOrderState();
        isSuccessModalOpen.value = true;
        console.log(response.data); // Handle success
    } catch (error) {
        if (error.response.status === 423) {
            isAlertModalOpen.value = true;
            message.value = error.response.data.message;
        }
        console.error(
            "Error submitting customer details:",
            error.response?.data || error.message
        );
        // alert("Failed to submit customer details. Please try again.");
    }
};
// };

const submitReturnOrder = async () => {
    if (!returnSale.value?.id) {
        isAlertModalOpen.value = true;
        message.value = "Please select a return order first.";
        return;
    }

    const returnItems = isExchangeReturn.value ? exchangeReturnItems.value : products.value;

    if (returnItems.length === 0) {
        isAlertModalOpen.value = true;
        message.value = "Please select at least one item for return.";
        return;
    }

    if (isExchangeReturn.value && products.value.length === 0) {
        isAlertModalOpen.value = true;
        message.value = "Please select replacement products for exchange.";
        return;
    }

    if (selectedPaymentMethod.value === "cash" && Number(total.value) > 0 && Number(balance.value) < 0) {
        isAlertModalOpen.value = true;
        message.value = "Cash is not enough.";
        return;
    }

    try {
        const methodMap = {
            cash: "Cash",
            card: "Card",
            online: "Online",
        };

        const method = returnType.value === "product_return"
            ? "Exchange"
            : (methodMap[selectedPaymentMethod.value] || "Cash");
        returnPaymentMethod.value = method;

        const response = await axios.post(route("pos.return.submit"), {
            sale_id: returnSale.value.id,
            refund_method: method,
            items: returnItems.map((item) => ({
                product_id: item.id,
                quantity: Number(item.quantity || 0),
                reason: item.return_reason || "Customer return",
            })),
        });

        returnProcessedTotal.value = Number(response.data?.refund_total || 0);

        if (isExchangeReturn.value) {
            await axios.post("/pos/submit", {
                customer: customer.value,
                products: products.value,
                employee_id: employee_id.value,
                paymentMethod: selectedPaymentMethod.value,
                userId: props.loggedInUser.id,
                orderid: orderid.value,
                cash: cash.value,
                custom_discount: custom_discount.value,
                custom_discount_type: custom_discount_type.value,
                sale_type: sale_type.value,
            });
            captureReceiptData();
            clearOrderState();
            isSuccessModalOpen.value = true;
            return;
        }

        isReturnSuccessModalOpen.value = true;
    } catch (error) {
        const data = error.response?.data;
        isAlertModalOpen.value = true;
        message.value = data?.message || "Failed to submit return.";
    }
};

const subtotal = computed(() => {
    return products.value
        .reduce(
            (total, item) => total + parseFloat(getItemBasePrice(item)) * item.quantity,
            0
        )
        .toFixed(2);
});

const totalDiscount = computed(() => {
    const productDiscount = products.value.reduce((total, item) => {
        const basePrice = parseFloat(getItemBasePrice(item));
        const finalPrice = parseFloat(getItemDisplayPrice(item));
        if (item.apply_discount == true && basePrice > finalPrice) {
            const discountAmount = (basePrice - finalPrice) * item.quantity;
            return total + discountAmount;
        }
        return total;
    }, 0);

    const couponDiscount = appliedCoupon.value
        ? Number(appliedCoupon.value.discount)
        : 0;

    return (productDiscount + couponDiscount).toFixed(2);
});

const validateCustomDiscount = () => {
    if (!custom_discount.value || isNaN(custom_discount.value)) {
        custom_discount.value = 0; // Set default to 0 if the field is empty or invalid
    }
};

const total = computed(() => {
    const subtotalValue = parseFloat(subtotal.value) || 0;
    const discountValue = parseFloat(totalDiscount.value) || 0;
    const customDiscount = parseFloat(custom_discount.value) || 0;

    let customValue = 0;

    if (custom_discount_type.value === 'percent') {
        customValue = (subtotalValue * customDiscount) / 100;
    } else if (custom_discount_type.value === 'fixed') {
        customValue = customDiscount;
    }

    const baseTotal = subtotalValue - discountValue - customValue;

    if (isExchangeReturn.value) {
        return Math.max(0, baseTotal - Number(exchangeCredit.value || 0)).toFixed(2);
    }

    return baseTotal.toFixed(2);
});

const remainingBalance = computed(() => {
    const totalValue = Number(total.value || 0);
    const previous = Number(props.loadedSale?.paid_amount || 0);
    const current = Number(cash.value || 0);
    return Math.max(0, totalValue - previous - current).toFixed(2);
});

const balance = computed(() => {
    const cashValue = Number(cash.value || 0);
    if (props.loadedSale) {
        const dueValue = Number(props.loadedSaleDue || 0);
        return Math.max(0, cashValue - dueValue).toFixed(2);
    }
    if (!cash.value) {
        return 0;
    }
    return (cashValue - Number(total.value)).toFixed(2);
});
// Check for product or handle errors
const form = useForm({
    employee_id: "",
    barcode: "", // Form field for barcode
});

const couponForm = useForm({
    code: "",
});

// Temporary barcode storage during scanning
let barcode = "";
let timeout; // Timeout to detect the end of the scan

const submitCoupon = async () => {
    try {
        const response = await axios.post(route("pos.getCoupon"), {
            code: couponForm.code, // Send the coupon field
        });

        const { coupon: fetchedCoupon, error: fetchedError } = response.data;

        if (fetchedCoupon) {
            appliedCoupon.value = fetchedCoupon;
            products.value.forEach((product) => {
                product.apply_discount = false;
            });
        } else {
            isAlertModalOpen.value = true;
            message.value = fetchedError;
            error.value = fetchedError;
        }
    } catch (err) {
        // console.error(error);
        if (err.response.status === 422) {
            isAlertModalOpen.value = true;
            message.value = err.response.data.message;
        }
    }
};

// Automatically submit the barcode to the backend
const submitBarcode = async () => {
    if (isReturnCashMode.value) {
        isAlertModalOpen.value = true;
        message.value = "Return mode is active. Refresh page to switch back to sales mode.";
        return;
    }

    try {
        // Send POST request to the backend
        const response = await axios.post(route("pos.getProduct"), {
            barcode: form.barcode, // Send the barcode field
        });

        // Extract the response data
        const { product: fetchedProduct, error: fetchedError } = response.data;

        if (fetchedProduct) {
            if (fetchedProduct.stock_quantity < 1) {
                isAlertModalOpen.value = true;
                message.value = "Product is out of stock";
                return;
            }
            // Check if the product already exists in the products array
            const existingProduct = products.value.find(
                (item) => item.id === fetchedProduct.id
            );

            if (existingProduct) {
                // If it exists, increment the quantity
                existingProduct.quantity += 1;
            } else {
                // If it doesn't exist, add it to the products array with quantity 1
                let defaultBatchId = null;
                if (fetchedProduct.batches && fetchedProduct.batches.length > 0) {
                    defaultBatchId = fetchedProduct.batches[0].id;
                }
                products.value.push({
                    ...fetchedProduct,
                    quantity: 1,
                    apply_discount: false, // Add the new attribute
                    selected_batch_id: defaultBatchId,
                });
            }

            product.value = fetchedProduct; // Update product state for individual display
            error.value = null; // Clear any previous errors
            console.log(
                "Product fetched successfully and added to cart:",
                fetchedProduct
            );
        } else {
            isAlertModalOpen.value = true;
            message.value = fetchedError;
            error.value = fetchedError; // Set the error message
            console.error("Error:", fetchedError);
        }
    } catch (err) {
        if (err.response.status === 422) {
            isAlertModalOpen.value = true;
            message.value = err.response.data.message;
        }

        console.error("An error occurred:", err.response?.data || err.message);
        error.value = "An unexpected error occurred. Please try again.";
    }
};

// Handle input from the barcode scanner
const handleScannerInput = (event) => {
    clearTimeout(timeout); // Clear the timeout for each keypress
    if (event.key === "Enter") {
        // Barcode scanning completed
        form.barcode = barcode; // Set the scanned barcode into the form
        submitBarcode(); // Automatically submit the barcode
        barcode = ""; // Reset the barcode for the next scan
    } else {
        // Append the pressed key to the barcode
        barcode += event.key;
    }

    // Timeout to reset the barcode if scanning is interrupted
    timeout = setTimeout(() => {
        barcode = "";
    }, 100); // Adjust timeout based on scanner speed
};

// Attach the keypress event listener when the component is mounted
onMounted(() => {
    document.addEventListener("keypress", handleScannerInput);
    console.log(props.products);
});

const applyDiscount = (id) => {
    products.value.forEach((product) => {
        if (product.id === id) {
            product.apply_discount = true;
        }
    });
};

const removeDiscount = (id) => {
    products.value.forEach((product) => {
        if (product.id === id) {
            product.apply_discount = false;
        }
    });
};

const handleSelectedProducts = (selectedProducts) => {
    if (isReturnCashMode.value) {
        isAlertModalOpen.value = true;
        message.value = "Return mode is active. Finish or refresh before adding normal products.";
        return;
    }

    selectedProducts.forEach((fetchedProduct) => {
        const existingProduct = products.value.find(
            (item) => item.id === fetchedProduct.id
        );

        if (existingProduct) {
            // If the product exists, increment its quantity
            existingProduct.quantity += 1;
        } else {
            let defaultBatchId = null;
            if (fetchedProduct.batches && fetchedProduct.batches.length > 0) {
                defaultBatchId = fetchedProduct.batches[0].id;
            }
            // If the product doesn't exist, add it with a default quantity
            products.value.push({
                ...fetchedProduct,
                quantity: 1,
                apply_discount: false, // Default additional attribute
                selected_batch_id: defaultBatchId,
            });
        }
    });
};

const handleApplyReturnItems = ({ sale, products: selectedProducts, refundTotal, returnType: selectedReturnType }) => {
    const mappedReturnItems = selectedProducts.map((item) => ({
        ...item,
        apply_discount: false,
        discount: 0,
        discounted_price: item.selling_price,
    }));

    returnSale.value = sale;
    isReturnMode.value = true;
    returnType.value = selectedReturnType || "cash_return";

    appliedCoupon.value = null;
    couponForm.code = "";
    custom_discount.value = 0;
    custom_discount_type.value = "fixed";
    returnReceiptProducts.value = mappedReturnItems;

    returnProcessedTotal.value = Number(refundTotal || 0);
    if (returnType.value === "product_return") {
        exchangeReturnItems.value = mappedReturnItems;
        exchangeCredit.value = Number(refundTotal || 0);
        products.value = [];
        selectedPaymentMethod.value = "cash";
        cash.value = 0;
        isSelectModalOpen.value = true;
    } else {
        exchangeReturnItems.value = [];
        exchangeCredit.value = 0;
        products.value = mappedReturnItems;
        selectedPaymentMethod.value = "cash";
        cash.value = Number(refundTotal || 0);
    }
};

// const searchTerm = ref(form.barcode);

// // Computed property for filtered product results
// const searchResults = computed(() => {
//   if (searchTerm.value === "") {
//     return [];
//   }

//   let matches = 0;

//   return props.products.filter((product) => {
//     if (
//       product.name.toLowerCase().includes(searchTerm.value.toLowerCase()) &&
//       matches < 10
//     ) {
//       matches++;
//       return product;
//     }
//   });
// });

// // Watch for changes in the form barcode field and update the search term
// watch(
//   () => form.barcode,
//   (newValue) => {
//     searchTerm.value = newValue;
//   }
// );

// // Method to select a product (or barcode)
// const selectProduct = (productName) => {
//   form.barcode = productName; // Set the selected product name to the barcode field
//   searchTerm.value = ""; // Clear the search term after selection
// };

const getSelectedBatch = (item) => {
    if (item.selected_batch_id && item.batches) {
        return item.batches.find(b => b.id === item.selected_batch_id) || item;
    }
    return item;
};

/**
 * Helper: Get the display price for a cart item based on the current sale_type.
 * Returns the base (non-discounted) price for the selected pricing tier.
 */
const getItemBasePrice = (item) => {
    const target = getSelectedBatch(item);
    if (sale_type.value === 'wholesale') {
        return target.wholesale_price || target.retail_price || 0;
    }
    return target.retail_price || target.selling_price || 0;
};

/**
 * Helper: Get the discount percentage for a cart item based on the current sale_type.
 */
const getItemDiscountPercent = (item) => {
    const target = getSelectedBatch(item);
    if (sale_type.value === 'wholesale') {
        return parseFloat(target.wholesale_discount || 0);
    }
    return parseFloat(target.retail_discount || target.discount || 0);
};

/**
 * Helper: Get the effective selling price for a cart item.
 * If a discount is applied and a discounted price exists, use that.
 * Otherwise, use the base price for the selected pricing tier.
 */
const getItemDisplayPrice = (item) => {
    const base = getItemBasePrice(item);
    const target = getSelectedBatch(item);
    if (sale_type.value === 'wholesale') {
        if (item.apply_discount && target.wholesale_discount > 0 && target.discounted_wholesale_price > 0) {
            return target.discounted_wholesale_price;
        }
        return base;
    }
    // Retail
    if (item.apply_discount && target.retail_discount > 0 && target.discounted_retail_price > 0) {
        return target.discounted_retail_price;
    }
    return base;
};
</script>
