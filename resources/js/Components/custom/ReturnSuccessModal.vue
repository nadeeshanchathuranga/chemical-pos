<template>
    <TransitionRoot as="template" :show="open" static>
        <Dialog class="relative z-20" static>
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click.stop />
            </TransitionChild>

            <div class="fixed inset-0 z-10 flex items-center justify-center">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 scale-95"
                    enter-to="opacity-100 scale-100" leave="ease-in duration-200" leave-from="opacity-100 scale-100"
                    leave-to="opacity-0 scale-95">
                    <DialogPanel
                        class="bg-white border-4 border-blue-600 rounded-[20px] shadow-xl max-w-xl w-full p-6 text-center">
                        <DialogTitle class="text-4xl font-bold">Return Successful</DialogTitle>

                        <div class="w-full h-full flex flex-col justify-center items-center space-y-4 mt-4">
                            <p class="text-2xl text-black">Refund amount: {{ Number(total || 0).toFixed(2) }} LKR</p>
                            <img src="/images/checked.png" alt="Return success" class="h-20 object-cover" />
                        </div>

                        <div class="flex justify-center items-center space-x-3 pt-6 mt-4">
                            <p @click="handlePrint"
                                class="cursor-pointer bg-blue-600 text-white font-bold uppercase tracking-wider px-4 py-3 rounded-xl">
                                Print Return Receipt
                            </p>
                            <p @click="$emit('update:open', false)"
                                class="cursor-pointer bg-red-600 text-white font-bold uppercase tracking-wider px-4 py-3 rounded-xl">
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
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const companyInfo = computed(() => page.props.companyInfo);

const props = defineProps({
    open: { type: Boolean, required: true },
    orderId: { type: String, default: "" },
    cashier: { type: Object, default: () => ({}) },
    customer: { type: Object, default: () => ({}) },
    products: { type: Array, default: () => [] },
    total: { type: Number, default: 0 },
    paymentMethod: { type: String, default: "Cash" },
});

defineEmits(["update:open"]);

const handlePrint = () => {
    const productRows = props.products
        .map((item) => {
            return `
                <tr>
                    <td>${item.name || "-"}</td>
                    <td style="text-align:center;">${Number(item.quantity || 0)}</td>
                    <td style="text-align:right;">${Number(item.selling_price || 0).toFixed(2)}</td>
                </tr>
            `;
        })
        .join("");

    const html = `
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Return Receipt</title>
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
                    .totals .final {
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
                </style>
            </head>
            <body>
                <div class="receipt-container">
                    <div class="header">
                        <img src="/images/billlogo.png" style="width: 230px; height: 100px;" />
                        ${companyInfo?.value?.name ? `<h1>${companyInfo.value.name}</h1>` : ""}
                        ${companyInfo?.value?.address ? `<p>${companyInfo.value.address}</p>` : ""}
                        ${(companyInfo?.value?.phone || companyInfo?.value?.phone2 || companyInfo?.value?.email)
                            ? `<p>${companyInfo.value.phone || ""} ${companyInfo.value.phone2 || ""} ${companyInfo.value.email || ""}</p>`
                            : ""}
                    </div>

                    <div class="section">
                        <div class="info-row">
                            <div>
                                <p>Date:</p>
                                <small>${new Date().toLocaleDateString()}</small>
                            </div>
                            <div>
                                <p>Order No:</p>
                                <small>${props.orderId || "-"}</small>
                            </div>
                        </div>
                        <div class="info-row">
                            <div>
                                <p>Customer:</p>
                                <small>${props.customer?.name || "N/A"}</small>
                            </div>
                            <div>
                                <p>Cashier:</p>
                                <small>${props.cashier?.name || "N/A"}</small>
                            </div>
                        </div>
                        <div class="info-row">
                            <div>
                                <p>Refund Method:</p>
                                <small>${props.paymentMethod || "Cash"}</small>
                            </div>
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
                        <div class="final">
                            <span>Total Refund</span>
                            <span>${Number(props.total || 0).toFixed(2)} LKR</span>
                        </div>
                    </div>

                    <div class="footer">
                        <p>THANK YOU COME AGAIN</p>
                        <p style="font-weight: bold;">Powered by JAAN Network Ltd.</p>
                        <p>${new Date().toLocaleTimeString()}</p>
                    </div>
                </div>
            </body>
        </html>
    `;

    const printWindow = window.open("", "_blank");
    if (!printWindow) {
        return;
    }

    printWindow.document.open();
    printWindow.document.write(html);
    printWindow.document.close();
    printWindow.onload = () => {
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    };
};
</script>
