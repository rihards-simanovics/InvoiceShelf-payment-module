<template>
  <div class="flex flex-col items-center w-full">
    <div v-if="showErrorMessage">
      <PaymentErrorBlock :message="errorMessage" />
    </div>
    <div v-else>
      <div v-if="isLoading" class="w-full flex items-center justify-center p-5">
        <BaseSpinner class="text-primary-500 h-10 w-10" />
      </div>

      <form
        v-show="showPaymentForm"
        id="payment-form"
        class="w-full"
        @submit.prevent="handleSubmit"
      >
        <div id="payment-element">
          <!--Stripe.js injects the Payment Element-->
        </div>
        <button
          id="submit"
          type="submit"
          class="
            border-transparent
            shadow-sm
            text-white
            bg-primary-600
            hover:bg-primary-700
            focus:ring-primary-500
            payment-button
          "
        >
          <div id="spinner" class="spinner hidden"></div>
          <span id="button-text">Pay now</span>
        </button>
      </form>
    </div>
    
    <div id="payment-message" class="hidden"></div>
  </div>
</template>

<script setup>
import { loadStripe } from '@stripe/stripe-js'
import { ref, onMounted, reactive } from 'vue'
import { usePaymentProviderStore } from '~/scripts/stores/payment-provider'
import PaymentSuccessBlock from './PaymentSuccessBlock.vue'
import PaymentErrorBlock from './PaymentErrorBlock.vue'

const { useRoute } = window.VueRouter

const props = defineProps({
  invoiceViewLink: {
    type: Object,
    required: true,
  },
})

const route = useRoute()

let errorMessage = ref(null)

const paymentProviderStore = usePaymentProviderStore()
let stripe = ''
let elements = ref(null)
const showPaymentForm = ref(false)
const paymentForm = ref(null)
const paymentElement = ref(null)
const transactionServerHash = ref(null)
const isLoading = ref(true)
const showErrorMessage = ref(false)
const paymentReceiptUrl = ref(null)
const emit = defineEmits(['disable', 'reload'])

setupStripe()

async function setupStripe() {
  stripe = await loadStripe(paymentProviderStore.selectedProvider.public_key)
  checkStatus()
}

async function generatePaymentIntent() {
  showPaymentForm.value = true
  let res = await paymentProviderStore.generatePayment(route.params.company)

  if (res.data.error) {
    errorMessage.value = res.data.error.message
    showErrorMessage.value = true
    return
  }

  if (res.data) {
    const appearance = {
      theme: 'stripe',

      variables: {
        colorPrimary: '#5851D8',
        colorBackground: '#ffffff',
        colorText: '#282461',
        colorDanger: '#df1b41',
        fontFamily: 'Poppins, system-ui, sans-serif',
        spacingUnit: '4px',
        borderRadius: '4px',
        spacingGridRow: '12px',
        fontSizeBase: '16px',
        fontSizeSm: '14px',
        // See all possible variables below
      },
    }

    elements.value = stripe.elements({
      appearance,
      clientSecret: res.data.order.client_secret,
    })
    paymentElement.value = elements.value.create('payment')
    paymentElement.value.mount('#payment-element')
    transactionServerHash.value = res.data.transaction_unique_hash

    isLoading.value = false
  }
}

// Fetches the payment intent status after payment submission
async function checkStatus() {
  const clientSecret = new URLSearchParams(window.location.search).get(
    'payment_intent_client_secret'
  )

  if (!clientSecret) {
    generatePaymentIntent()
    return
  }

  emit('disable', true)

  const { paymentIntent } = await stripe.retrievePaymentIntent(clientSecret)

  switch (paymentIntent.status) {
    case 'succeeded':
      const response = await paymentProviderStore.confirmTransaction(
        paymentIntent.id,
        {
          company_id: route.params.company,
        }
      )

      paymentReceiptUrl.value = `/m/payments/pdf/${response.data.transaction.unique_hash}`

      emit('reload', paymentReceiptUrl.value)
      break
    case 'processing':
      showMessage('Your payment is processing.')
      break
    case 'requires_payment_method':
      showMessage('Your payment was not successful, please try again.')
      break
    default:
      showMessage('Something went wrong.')
      break
  }

  isLoading.value = false
}

async function handleSubmit() {
  setLoading(true)  
  
  const { error } = await stripe.confirmPayment({
    elements: elements.value,
    confirmParams: {
      return_url: window.location.href,
    },
  })

  if (error.type === 'card_error' || error.type === 'validation_error') {
    showMessage(error.message)
  } else {
    showMessage('An unexpected error occured.')
  }

  setLoading(false)
}

function setPaymentForm() {
  paymentForm.value = elements.value.create('payment')
  paymentForm.value.mount('#payment-element')
  paymentForm.value.on('change', (event) => {
    displayError(event)
  })
}

// ------- UI helpers -------

function showMessage(messageText) {
  const messageContainer = document.querySelector('#payment-message')

  messageContainer.classList.remove('hidden')
  messageContainer.textContent = messageText

  setTimeout(() => {
    messageContainer.classList.add('hidden')
    messageContainer.textContent = ''
  }, 4000)
}

function setLoading(isLoading) {
  if (isLoading) {
    // Disable the button and show a spinner
    document.querySelector('#submit').disabled = true
    document.querySelector('#spinner').classList.remove('hidden')
    document.querySelector('#button-text').classList.add('hidden')
  } else {
    document.querySelector('#submit').disabled = false
    document.querySelector('#spinner').classList.add('hidden')
    document.querySelector('#button-text').classList.remove('hidden')
  }
}
</script>

<style lang="scss" scoped>
form {
  align-self: center;
  border-radius: 7px;
}

.hidden {
  display: none;
}

#payment-message {
  color: rgb(105, 115, 134);
  font-size: 16px;
  line-height: 20px;
  padding-top: 12px;
  text-align: center;
}

#payment-element {
  margin-bottom: 24px;
}

/* Buttons and links */
.payment-button {
  border-radius: 4px;
  border: 0;
  padding: 12px 16px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  display: block;
  transition: all 0.2s ease;
  width: 100%;
}

.payment-button:hover {
  filter: contrast(115%);
}

.payment-button:disabled {
  opacity: 0.5;
  cursor: default;
}

/* spinner/processing state, errors */
.spinner,
.spinner:before,
.spinner:after {
  border-radius: 50%;
}
.spinner {
  color: #ffffff;
  font-size: 22px;
  text-indent: -99999px;
  margin: 0px auto;
  position: relative;
  width: 20px;
  height: 20px;
  box-shadow: inset 0 0 0 2px;
  -webkit-transform: translateZ(0);
  -ms-transform: translateZ(0);
  transform: translateZ(0);
}
.spinner:before,
.spinner:after {
  position: absolute;
  content: '';
}
.spinner:before {
  width: 10.4px;
  height: 20.4px;
  background: #5469d4;
  border-radius: 20.4px 0 0 20.4px;
  top: -0.2px;
  left: -0.2px;
  -webkit-transform-origin: 10.4px 10.2px;
  transform-origin: 10.4px 10.2px;
  -webkit-animation: loading 2s infinite ease 1.5s;
  animation: loading 2s infinite ease 1.5s;
}
.spinner:after {
  width: 10.4px;
  height: 10.2px;
  background: #5469d4;
  border-radius: 0 10.2px 10.2px 0;
  top: -0.1px;
  left: 10.2px;
  -webkit-transform-origin: 0px 10.2px;
  transform-origin: 0px 10.2px;
  -webkit-animation: loading 2s infinite ease;
  animation: loading 2s infinite ease;
}

@-webkit-keyframes loading {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
@keyframes loading {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
</style>
