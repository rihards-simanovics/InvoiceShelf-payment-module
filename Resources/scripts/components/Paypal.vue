<template>
  <div v-if="isLoading" class="w-full flex items-center justify-center p-5">
    <BaseSpinner class="text-primary-500 h-10 w-10" />
  </div>

  <div ref="paypal" class="relative z-10"></div>
</template>
<script setup>
import { onMounted, ref, reactive, inject, computed } from 'vue'
import { usePaymentProviderStore } from '../stores/payment-provider'
import PaymentSuccessBlock from './PaymentSuccessBlock.vue'
const { useRoute } = window.VueRouter

const route = useRoute()

const props = defineProps({
  invoiceViewLink: {
    type: Object,
    required: true,
  },
})

const showSuccessMessage = ref(false)
const paymentReceiptUrl = ref(null)
const emit = defineEmits(['disable', 'reload'])

let paypal = ref()
const isLoading = ref(true)
const paymentProviderStore = usePaymentProviderStore()

setupPaypal()

async function setupPaypal() {
  const script = document.createElement('script')

  script.setAttribute(
    'src',
    `https://www.paypal.com/sdk/js?client-id=${paymentProviderStore.selectedProvider.public_key}&currency=${paymentProviderStore.currentInvoice.currency.code}`
  )

  document.body.appendChild(script)

  script.addEventListener('load', setLoaded)
}

function setLoaded() {
  isLoading.value = false

  window.paypal
    .Buttons({
      createOrder: (data, actions) => {
        return actions.order.create({
          purchase_units: [
            {
              amount: {
                currency_code:
                  paymentProviderStore.currentInvoice.currency.code,
                value: paymentProviderStore.currentInvoice.total / 100,
              },
            },
          ],
        })
      },
      onApprove: async (data, actions) => {
        emit('disable', true)
        const order = await actions.order.capture()

        paymentProviderStore
          .confirmTransaction(order.id, {
            invoice_id: paymentProviderStore.currentInvoice.id,
            company_id: route.params.company,
          })
          .then((res) => {
            paymentReceiptUrl.value = `/m/payments/pdf/${res.data.transaction.unique_hash}`

            emit('reload', paymentReceiptUrl.value)
          })
      },
      onError: (err) => {
        console.log(err)
      },
    })
    .render(paypal.value)
}
</script>
