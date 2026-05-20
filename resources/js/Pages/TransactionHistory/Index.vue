<style>
/* General DataTables Pagination Container Style */
.dataTables_wrapper .dataTables_paginate {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}

/* Style the filter container */
#TransitionTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px; /* Add spacing below the filter */
}

/* Style the label and input field inside the filter */
#TransitionTable_filter label {
  font-size: 17px;
  color: #000000; /* Match text color of the table header */
  display: flex;
  align-items: center;
}

/* Style the input field */
#TransitionTable_filter input[type="search"] {
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
#TransitionTable_filter input[type="search"]:focus {
  outline: none; /* Removes the default outline */
  border: 1px solid #4b5563;
  box-shadow: none; /* Removes any focus box-shadow */
}

#TransitionTable_filter {
  float: left;
}

.dataTables_wrapper {
  margin-bottom: 10px;
}
</style>

<template>
    <Head title="Order History" />
     <Banner />
     <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
        <Header />
        <div class="w-full md:w-5/6 py-12 space-y-24">
            <div class="flex items-center justify-between float-end">
                <p class="text-3xl italic font-bold text-black">
                <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">{{
                    totalhistoryTransactions

                }}</span>
                <span class="text-xl">/ Total History Transition</span>
                </p>
            </div>

            <div class="flex w-full">
                <div class="flex items-center w-full h-16 space-x-4 rounded-2xl">
                <Link href="/">
                    <img src="/images/back-arrow.png" class="w-14 h-14" />
                </Link>
                <p class="text-4xl font-bold tracking-wide text-black uppercase">
                    Order History
                </p>
                </div>
                <div class="flex justify-end md:inline hidden w-full"></div>
            </div>


            <template v-if="allhistoryTransactions && allhistoryTransactions.length > 0">
                <div class="overflow-x-auto">
                <table
                    id="TransitionTable" class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
                    <thead>
                    <tr class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-[12px] text-white border-b border-blue-700">
                        <th class="p-4 font-semibold tracking-wide text-left uppercase">#</th>
                        <th class="p-4 font-semibold tracking-wide text-left uppercase">Oredr ID</th>
                        <th class="p-4 font-semibold tracking-wide text-left uppercase">Total Amount</th>
                        <th class="p-4 font-semibold tracking-wide text-left uppercase"> Discount</th>
                        <th class="p-4 font-semibold tracking-wide text-left uppercase">Payment Method</th>
                        <th class="p-4 font-semibold tracking-wide text-left uppercase">Sale Date</th>
                        <th class="p-4 font-semibold tracking-wide text-left uppercase"> Action</th>
                    </tr>
                    </thead>
                    <tbody class="text-[13px] font-normal">
                        <tr
                            v-for="(history, index) in allhistoryTransactions"
                            :key="history.id"
                            class="transition duration-200 ease-in-out hover:bg-gray-200 hover:shadow-lg"
                        >
                            <td class="px-6 py-3 text- first-letter:">{{ index + 1 }}</td>
                            <td class="p-4 font-bold border-gray-200">{{ history.order_id || "N/A" }}</td>
                            <td class="p-4 font-bold border-gray-200">{{ history.total_amount - (history.discount || 0) - (history.custom_discount || 0) || "N/A" }}</td>
                             <td class="p-4 font-bold border-gray-200">{{((parseFloat(history.discount) || 0) + (parseFloat(history.custom_discount) || 0)).toLocaleString()}}</td>
                            <td class="p-4 font-bold border-gray-200">{{ history.payment_method || "N/A" }}</td>
                            <td class="p-4 font-bold border-gray-200">{{ history.sale_date || "N/A" }}</td>
                            <td class="p-4 font-bold border-gray-200">
                                <!-- <button
                                    @click="printReceipt(history)"
                                    class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 mr-4"
                                >
                                    Print
                                </button> -->
                                <button class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600" @click="deleteReceipt(history.order_id)">
                                    Delete
                                </button>
                            </td>

                        </tr>
                    </tbody>
                </table>
                </div>
            </template>

            <template v-else>
                <div class="col-span-4 text-center text-blue-500">
                <p class="text-center text-red-500 text-[17px]">
                    No Stock Transition Available
                </p>
                </div>
            </template>
        </div>
     </div>
<Footer />
</template>


<script setup>
import { ref } from "vue";
import { router,useForm } from "@inertiajs/vue3";
import { Head, Link } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";

const props = defineProps({
  allhistoryTransactions: Array,
  totalhistoryTransactions: Number,
  companyInfo: Array
});
const form = useForm({});


const deleteReceipt = (orderId) => {
  if (confirm("Are you sure you want to delete this record? This action cannot be undone.")) {
    router.post(route("transactions.delete"), { order_id: orderId }, {
      onError: (error) => {
        alert("Error: " + (error.message || "Something went wrong."));
      },
    });
  }
};



$(document).ready(function () {
  let table = $("#TransitionTable").DataTable({
    dom: "Bfrtip",
    pageLength: 10,
    buttons: [],
    columnDefs: [
      {
        targets: 2,
        searchable: false,
        orderable: false,
      },
    ],
    initComplete: function () {
      let searchInput = $("div.dataTables_filter input");
      searchInput.attr("placeholder", "Search ...");
      searchInput.on("keypress", function (e) {
        if (e.which == 13) {
          table.search(this.value).draw();
        }
      });
    },
    language: {
      search: "",
    },
  });
});

 const handlePrintReceipt = () => {
  // --- Safe helpers ---
  const n = (v) => Number(v ?? 0);
  const f2 = (v) => (Number(v ?? 0)).toFixed(2);

  // --- Totals (fallbacks if props didn’t provide) ---
  const subTotal = (props.products || []).reduce(
    (sum, p) => sum + n(p.selling_price) * n(p.quantity),
    0
  );

  const totalDiscountRaw = (props.products || []).reduce((total, item) => {
    if (item.discount && n(item.discount) > 0 && item.apply_discount === true) {
      const diff = n(item.selling_price) - n(item.discounted_price);
      return total + diff * n(item.quantity);
    }
    return total;
  }, 0);

  const totalDiscount = Number(totalDiscountRaw.toFixed(2));
  const customDiscount = n(props.custom_discount);
  const total = subTotal - totalDiscount - customDiscount;

  // --- Build product rows with PACK expansion (full-width heading, aligned children) ---
  const productRows = (props.products || [])
    .map((product) => {
      const isPack = Number(product.is_promotion) === 1;

      const parentRow = `
        <tr>
          <td>
            ${product.name ?? ""}
          </td>
          <td style="text-align:center;">${n(product.quantity)}</td>
          <td>
            ${
              (n(product.discount) > 0 && product.apply_discount)
                ? `<div style="font-weight:bold;font-size:7px;background-color:black;color:white;text-align:center;">${n(product.discount)}% off</div>`
                : ``
            }
            <div>${f2(product.selling_price)}</div>
          </td>
        </tr>
      `;

      // Support both snake_case and camelCase from API
      const items = Array.isArray(product.promotion_items)
        ? product.promotion_items
        : (Array.isArray(product.promotionItems) ? product.promotionItems : []);

      let childRows = ``;
      if (isPack && items.length) {
        const headingRow = `
          <tr class="pack-heading">
            <td class="pack-heading-cell" colspan="3">Pack Include</td>
          </tr>
        `;

        const children = items.map((pi) => {
          const compName = (pi.product && pi.product.name) ? pi.product.name : `#${pi.product_id}`;
          const compQty = (n(pi.quantity) || 1) * (n(product.quantity) || 1);
          return `
            <tr class="pack-child">
              <td class="pack-child-name">* ${compName}</td>
              <td class="pack-child-qty">${compQty}</td>
              <td class="pack-child-price"></td>
            </tr>
          `;
        }).join("");

        childRows = headingRow + children;
      }

      return parentRow + childRows;
    })
    .join("");

  // --- Receipt HTML ---
  const receiptHTML = `
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Receipt</title>
<style>
  @media print {
    body { margin:0; padding:0; -webkit-print-color-adjust:exact; }
  }
  body {
    background-color:#ffffff; font-size:12px; font-family:"Arial",sans-serif;
    margin:0; padding:10px; color:#000;
  }
  .header { text-align:center; margin-bottom:16px; }
  .header h1 { font-size:20px; font-weight:bold; margin:0; }
  .header p { font-size:10px; margin:4px 0; }
  .section { margin-bottom:16px; padding-top:8px; border-top:1px solid #000; }
  .info-row { display:flex; justify-content:space-between; font-size:12px; margin-top:8px; }
  .info-row p { margin:0; font-weight:bold; }
  .info-row small { font-weight:normal; }
  table { width:100%; font-size:12px; border-collapse:collapse; margin-top:8px; table-layout:fixed; }
  table th, table td { padding:6px 8px; word-wrap:break-word; }
  table th { text-align:left; }
  table td { text-align:right; }
  table td:first-child { text-align:left; }
  .totals { border-top:1px solid #000; padding-top:8px; font-size:12px; }
  .totals div { display:flex; justify-content:space-between; margin-bottom:8px; }
  .totals div:nth-child(4) { font-size:14px; font-weight:bold; }
  .footer { text-align:center; font-size:10px; margin-top:16px; }
  .footer p { margin:6px 0; }
  .footer .italic { font-style:italic; }

  /* --- PACK styling --- */
  .pack-heading .pack-heading-cell{
    background:#e2e8f0;
    border:1px solid #ccc;
    padding:6px 8px;
    font-weight:bold;
    text-align:left;
  }
  .pack-child td{ font-size:11px; }
  .pack-child-name{
    padding-left:14px;
    background:#f1f5f9;
    border:1px solid #ccc;
    border-radius:4px;
  }
  .pack-child-qty{
    text-align:center;
    background:#f1f5f9;
    border:1px solid #ccc;
  }
  .pack-child-price{
    background:#f1f5f9;
    border:1px solid #ccc;
  }
</style>
</head>
<body>
  <div class="receipt-container">
    <div class="header">
      <img src="/images/billlogo.png" style="width:220px;height:100px;" />
      ${companyInfo?.value?.name ? `<h1>${companyInfo.value.name}</h1>` : ''}
      ${companyInfo?.value?.address ? `<p>${companyInfo.value.address}</p>` : ''}
      ${
        (companyInfo?.value?.phone || companyInfo?.value?.phone2 || companyInfo?.value?.email)
          ? `<p>${companyInfo.value.phone || ''} | ${companyInfo.value.phone2 || ''} ${companyInfo.value.email || ''}</p>`
          : ''
      }
    </div>

    <div class="section">
      <div class="info-row">
        <div><p>Date:</p><small>${new Date().toLocaleDateString()}</small></div>
        <div><p>Order No:</p><small>${props.orderid ?? ''}</small></div>
      </div>
      <div class="info-row">
        <div><p>Customer:</p><small>${props.customer?.name ?? ''}</small></div>
        <div><p>Cashier:</p><small>${props.cashier?.name ?? ''}</small></div>
      </div>
    </div>

    <div class="section">
      <table>
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
      <div><span>Sub Total</span><span>${f2(props.subTotal ?? subTotal)} LKR</span></div>
      <div><span>Discount</span><span>${f2(props.totalDiscount ?? totalDiscount)} LKR</span></div>
      <div>
        <span>Custom Discount</span>
        <span>${f2(props.custom_discount ?? customDiscount)} ${
          props.custom_discount_type === 'percent'
            ? '%'
            : props.custom_discount_type === 'fixed'
            ? 'LKR'
            : ''
        }</span>
      </div>
      <div><span>Total</span><span>${f2(props.total ?? total)} LKR</span></div>
      <div><span>Cash</span><span>${f2(props.cash)} LKR</span></div>
      <div style="font-weight:bold;"><span>Balance</span><span>${f2(props.balance)} LKR</span></div>
    </div>

    <div class="footer">
      <p>No Exchange On Purchased Items</p>
      <p>THANK YOU COME AGAIN</p>
      <p class="italic">Let the quality define its own standards</p>
      <p style="font-weight:bold;">Powered by JAAN Network Ltd.</p>
      <p>${new Date().toLocaleTimeString()}</p>
    </div>
  </div>
</body>
</html>
  `;

  const printWindow = window.open("", "_blank");
  if (!printWindow) {
    alert("Failed to open print window. Please check your browser settings.");
    return;
  }
  printWindow.document.open();
  printWindow.document.write(receiptHTML);
  printWindow.document.close();

  printWindow.onload = () => {
    printWindow.focus();
    printWindow.print();
    printWindow.close();
  };
};



</script>
