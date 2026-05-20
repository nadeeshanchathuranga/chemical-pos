<template>
    <TransitionRoot as="template" :show="open" static>
        <Dialog class="relative z-10" static>
            <!-- Modal Overlay -->
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click.stop />
            </TransitionChild>

            <!-- Modal Content -->
            <div class="fixed inset-0 z-10 flex items-center justify-center">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 scale-95"
                    enter-to="opacity-100 scale-100" leave="ease-in duration-200" leave-from="opacity-100 scale-100"
                    leave-to="opacity-0 scale-95">
                    <DialogPanel
                        class="bg-white border-4 border-blue-600 rounded-[20px] shadow-xl max-w-xl w-full p-6 text-center">
                        <!-- Modal Title -->
                        <DialogTitle class="text-5xl font-bold">Payment Successful!</DialogTitle>

                        <div class="w-full h-full flex flex-col justify-center items-center space-y-8 mt-4">
                            <p class="text-justify text-3xl text-black">
                                Order Payment is Successful!
                            </p>
                            <div>
                                <img src="/images/checked.png" class="h-24 object-cover w-full" />
                            </div>
                        </div>
                        <div class="flex justify-center items-center space-x-4 pt-4 mt-4">
                            <p
                                class="cursor-pointer bg-blue-600 text-white font-bold uppercase tracking-wider px-4 shadow-xl rounded py-4 rounded-xl">
                                Send Reciept To Email
                            </p>
                            <p @click="handlePrintReceipt"
                                class="cursor-pointer bg-blue-600 text-white font-bold uppercase tracking-wider px-4 shadow-xl rounded py-4 rounded-xl">
                                Print Receipt
                            </p>
                            <p @click="$emit('update:open', false)"
                                class="cursor-pointer bg-red-600 text-white font-bold uppercase tracking-wider px-4 shadow-xl rounded py-4 rounded-xl">
                                Close
                            </p>
                        </div>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";
import { computed } from "vue";
import { ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";

const page = usePage();

// Access the companyInfo from the page props
const companyInfo = computed(() => page.props.companyInfo);

if (companyInfo.value) {
    console.log(companyInfo.value);
} else {
    console.log('companyInfo is undefined or null');
}

const handleClose = () => {
    console.log("Modal close prevented");
};

const emit = defineEmits(["update:open"]);

// The `open` prop controls the visibility of the modal
const props = defineProps({
    open: {
        type: Boolean,
        required: true,
    },
    products: {
        type: Array,
        required: true,
    },
    cashier: Object,
    customer: Object,
    orderid: String,
    balance: Number,
    cash: Number,
    subTotal: String,
    totalDiscount: String,
    total: String,
    custom_discount: Number,
    custom_discount_type: String,
    paymentMethod: {
        type: String,
        default: "cash",
    },
    isCreditBill: {
        type: Boolean,
        default: false,
    },
    previouslyPaid: {
        type: [Number, String],
        default: 0,
    },
    currentPaid: {
        type: [Number, String],
        default: 0,
    },
    remainingBalance: {
        type: [Number, String],
        default: 0,
    },
    saleType: {
        type: String,
        default: "retail",
    },
    isReturnExchange: {
        type: Boolean,
        default: false,
    },
    returnOrderId: {
        type: String,
        default: "",
    },
    exchangeCredit: {
        type: Number,
        default: 0,
    },
});

const paymentMethodLabel = computed(() => {
    const method = String(props.paymentMethod || "cash").toLowerCase();
    if (method === "card") {
        return "Card";
    }
    if (method === "online") {
        return "Online";
    }
    return "Cash";
});

const receiptStatus = computed(() => {
    const remaining = Number(props.remainingBalance || 0);
    return remaining <= 0 ? 'Closed' : 'Open';
});

const showCreditBillReceipt = computed(() => {
    const advancePaid = Number(props.previouslyPaid || 0);
    const remaining = Number(props.remainingBalance || 0);
    return Boolean(props.isCreditBill) || (advancePaid > 0 && remaining <= 0);
});

const isCashPayment = computed(() => paymentMethodLabel.value === "Cash");

const handlePrintReceipt = () => {
    // Helper to get selected batch logic dynamically
    const getSelectedBatch = (item) => {
        if (item.selected_batch_id && item.batches) {
            return item.batches.find(b => b.id === item.selected_batch_id) || item;
        }
        return item;
    };
    
    const getItemBasePrice = (item) => {
        const target = getSelectedBatch(item);
        if (props.saleType === 'wholesale') {
            return target.wholesale_price || target.retail_price || 0;
        }
        return target.retail_price || target.selling_price || 0;
    };

    const getItemDiscountPercent = (item) => {
        const target = getSelectedBatch(item);
        if (props.saleType === 'wholesale') {
            return parseFloat(target.wholesale_discount || 0);
        }
        return parseFloat(target.retail_discount || target.discount || 0);
    };

    const getItemDisplayPrice = (item) => {
        const base = getItemBasePrice(item);
        const target = getSelectedBatch(item);
        if (props.saleType === 'wholesale') {
            if (item.apply_discount && target.wholesale_discount > 0 && target.discounted_wholesale_price > 0) {
                return target.discounted_wholesale_price;
            }
            return base;
        }
        if (item.apply_discount && target.retail_discount > 0 && target.discounted_retail_price > 0) {
            return target.discounted_retail_price;
        }
        return base;
    };

    // Calculate totals from props.products
    const subTotal = Number(props.subTotal || 0);
    const customDiscount = Number(props.custom_discount || 0);
    const totalDiscount = Number(props.totalDiscount || 0);
    const total = subTotal - totalDiscount - customDiscount;

const productRows = props.products
    .map((product) => {
      const isPack = Number(product.is_promotion) === 1;
      const basePrice = getItemBasePrice(product);
      const discountPercent = getItemDiscountPercent(product);

      const parentRow = `
        <tr>
          <td>
            ${product.name}
            ${isPack ? `` : ``}
          </td>
          <td style="text-align:center;">${Number(product.quantity || 0)}</td>
          <td style="text-align:right;">
            <div>${Number(basePrice || 0).toFixed(2)}</div>
          </td>
        </tr>
      `;

      // Support both snake_case and camelCase from API
      const items = Array.isArray(product.promotion_items)
        ? product.promotion_items
        : (Array.isArray(product.promotionItems) ? product.promotionItems : []);

      let childRows = ``;
      if (isPack && items.length) {
        // Heading (NOT full width): first column only; keep other columns empty
        const headingRow = `
          <tr class="pack-heading">
           <td class="pack-heading-cell" colspan="3">Pack Include</td>

          </tr>
        `;

        const children = items.map((pi) => {
          const compName = (pi.product && pi.product.name) ? pi.product.name : `#${pi.product_id}`;
          const compQty = (Number(pi.quantity) || 1) * (Number(product.quantity) || 1);
          return `
            <tr class="pack-child">
              <td style="padding-left:14px; background:#f1f5f9; border:1px solid #ccc; border-radius:4px; font-size:11px;">
               * ${compName}
              </td>

              <td style="text-align:center; background:#f1f5f9; border:1px solid #ccc; font-size:11px;">
                ${compQty}
              </td>

            </tr>
          `;
        }).join('');

        childRows = headingRow + children;
      }

      return parentRow + childRows;
    })
    .join("");


    // Generate the receipt HTML
    const receiptHTML = `
  <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Receipt</title>
      <style>
          @media print {
              body {
                  margin: 0;
                  padding: 0 5mm 0 0;
                  -webkit-print-color-adjust: exact;
              }
          }
          body {
              background-color: #ffffff;
              font-size: 12px;
              font-family: 'Arial', sans-serif;
              margin: 0;
              padding: 10px 5mm 10mm 7mm;
              color: #000;
          }
          .header {
              text-align: center;
              margin-bottom: 16px;
          }
          .header h1 {
              font-size: 20px;
              font-weight: bold;
              margin: 0;
          }
          .header p {
              font-size: 12px;
              margin: 4px 0;
          }
          .exchange-badge {
              display: inline-block;
              margin-top: 6px;
              padding: 2px 8px;
              font-size: 11px;
              font-weight: bold;
              border: 1px solid #000;
          }
          .section {
              margin-bottom: 16px;
              padding-top: 8px;
              border-top: 1px solid #000;
          }
          .info-row {
              display: flex;
              justify-content: space-between;
              font-size: 14px;
              margin-top: 8px;
          }
          .info-row p {
              margin: 0;
              font-weight: bold;
          }
          .info-row small {
              font-weight: normal;
          }
          table {
              width: 100%;
              font-size: 12px;
              border-collapse: collapse;
              margin-top: 8px;
          }
          table th, table td {
              padding: 6px 8px;

          }
          table th {
              text-align: left;
          }
          table td {
              text-align: right;
          }
          table td:first-child {
              text-align: left;
          }
          .totals {
              border-top: 1px solid #000;
              padding-top: 8px;
              font-size: 12px;
          }
          .totals div {
              display: flex;
              justify-content: space-between;
              margin-bottom: 8px;
          }
          .totals div:nth-child(4) {
              font-size: 14px;
              font-weight: bold;
          }
          .footer {
              text-align: center;
              font-size: 10px;
              margin-top: 16px;
          }
          .footer p {
              margin: 6px 0;
          }
          .footer .italic {
              font-style: italic;
          }


      </style>
  </head>
  <body>
      <div class="receipt-container">
                <div class="header">
   <img src="/images/billlogo.png" style="width: 230px; height: 100px;" />

           ${companyInfo?.value?.name ? `<h1>${companyInfo.value.name}</h1>` : ''}
  ${companyInfo?.value?.address ? `<p>${companyInfo.value.address}</p>` : ''}
  ${(companyInfo?.value?.phone || companyInfo?.value?.phone2 || companyInfo?.value?.email)
            ? `<p>${companyInfo.value.phone || ''} ${companyInfo.value.phone2 || ''}  ${companyInfo.value.email || ''}</p>`
            : ''}
    ${props.isReturnExchange ? `<div class="exchange-badge">RETURN EXCHANGE BILL</div>` : ''}
    ${props.saleType === 'wholesale' ? `<div class="exchange-badge">WHOLESALE BILL</div>` : ''}

          </div>





          <div class="section">
              <div class="info-row">
                  <div>
                      <p>Date:</p>
                      <small>${new Date().toLocaleDateString()} </small>
                  </div>
                  <div>
                      <p>Order No:</p>
                      <small>${props.orderid}</small>
                  </div>
              </div>
              <div class="info-row">
                  <div>
                      <p>Customer:</p>
                      <small>${props.customer.name}</small>
                  </div>
                  <div>
                      <p>Cashier:</p>
                      <small>${props.cashier.name}</small>
                  </div>
              </div>
            ${showCreditBillReceipt.value ? `
            <div class="info-row">
                <div>
                    <p>Invoice Type:</p>
                    <small>CREDIT BILL</small>
                </div>
                <div>
                    <p>Status:</p>
                    <small>${receiptStatus.value}</small>
                </div>
            </div>
            ` : ``}
              ${props.isReturnExchange ? `
              <div class="info-row">
                  <div>
                      <p>Return Ref:</p>
                      <small>${props.returnOrderId || '-'}</small>
                  </div>
                  <div>
                      <p>Return Credit:</p>
                      <small>${(Number(props.exchangeCredit) || 0).toFixed(2)} LKR</small>
                  </div>
              </div>
              ` : ''}
          </div>



 <div class="section">
      <table>
        <!-- fixed column widths -->
        <colgroup>
          <col style="width:60%;">
          <col style="width:15%;">
          <col style="width:25%;">
        </colgroup>
        <thead>
          <tr>
            <th>Items</th>
            <th style="text-align:center;">Qty</th>
            <th style="text-align:right;">Price</th>
          </tr>
        </thead>
        <tbody style="font-size:11px;">
          ${productRows}
        </tbody>
      </table>
    </div>










          <div class="totals">
              <div>
                  <span>Sub Total</span>
                  <span>${(Number(props.subTotal) || 0).toFixed(2)} LKR</span>
              </div>
              <div>
                  <span>Discount</span>
                  <span>${(Number(props.totalDiscount) || 0).toFixed(2)} LKR</span>
              </div>
              <div>
                  <span>Custom Discount</span>
                  <span>
                    ${(Number(props.custom_discount) || 0).toFixed(2)}
                    ${props.custom_discount_type === 'percent' ? '%' :
                    props.custom_discount_type === 'fixed' ? 'LKR' : ''}
                  </span>
              </div>
              <div>
                  <span>Total</span>
                  <span>${(Number(props.total) || 0).toFixed(2)} LKR</span>
              </div>
              <div>
                  <span>Payment Method</span>
                  <span>${paymentMethodLabel.value}</span>
              </div>
              ${showCreditBillReceipt.value ? `
              <div>
                  <span>Total Bill Amount</span>
                  <span>${(Number(props.total) || 0).toFixed(2)} LKR</span>
              </div>
              ${(Number(props.previouslyPaid) || 0) > 0 ? `
              <div>
                  <span>Advance Payment</span>
                  <span>${(Number(props.previouslyPaid) || 0).toFixed(2)} LKR</span>
              </div>
              ` : ''}
              <div>
                  <span>Current Paid</span>
                  <span>${(Number(props.currentPaid) || 0).toFixed(2)} LKR</span>
              </div>
              <div style="font-weight:bold;">
                  <span>Remaining Balance</span>
                  <span>${(Number(props.remainingBalance) || 0).toFixed(2)} LKR</span>
              </div>
              ` : `
              <div>
                  <span>Cash</span>
                  <span>${(Number(props.cash) || 0).toFixed(2)} LKR</span>
              </div>
              <div style="font-weight: bold;">
                  <span>Balance</span>
                  <span>${Math.max(0, Number(props.balance) || 0).toFixed(2)} LKR</span>
              </div>
              `}
          </div>
      </div>
    </div>
    </body>
    </html>
  `;

    // Open a new window
    const printWindow = window.open("", "_blank");
    if (!printWindow) {
        alert("Failed to open print window. Please check your browser settings.");
        return;
    }

    // Write the content to the new window
    printWindow.document.open();
    printWindow.document.write(receiptHTML);
    printWindow.document.close();

    // Wait for the content to load before triggering print
    printWindow.onload = () => {
        printWindow.focus();
        printWindow.print();
        printWindow.close();
        emit('update:open', false);
    };
};
</script>
