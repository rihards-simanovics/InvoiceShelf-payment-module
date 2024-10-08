<template>
  <div v-if="isLoading" class="w-full flex items-center justify-center p-5">
    <BaseSpinner class="text-primary-500 h-10 w-10" />
  </div>

  <!-- Buttons container -->
  <table
    border="0"
    align="center"
    valign="top"
    bgcolor="#FFFFFF"
    class="w-full"
  >
    <tr>
      <td colspan="2">
        <div id="paypal-button-container"></div>
      </td>
    </tr>
    <tr>
      <td colspan="2">&nbsp;</td>
    </tr>
  </table>

  <div align="center">or</div>

  <!-- Advanced credit and debit card payments form -->
  <div class="card_container">
    <form id="card-form">
      <label for="card-number">Card Number</label>
      <div id="card-number" class="card_field"></div>
      <div>
        <label for="expiration-date">Expiration Date</label>
        <div id="expiration-date" class="card_field"></div>
      </div>
      <div>
        <label for="cvv">CVV</label>
        <div id="cvv" class="card_field"></div>
      </div>
      <label for="card-holder-name">Name on Card</label>
      <input
        id="card-holder-name"
        type="text"
        name="card-holder-name"
        autocomplete="off"
        placeholder="card holder name"
      />
      <div>
        <label for="card-billing-address-street">Billing Address</label>
        <input
          id="card-billing-address-street"
          type="text"
          name="card-billing-address-street"
          autocomplete="off"
          placeholder="street address"
        />
      </div>
      <div>
        <label for="card-billing-address-unit">&nbsp;</label>
        <input
          id="card-billing-address-unit"
          type="text"
          name="card-billing-address-unit"
          autocomplete="off"
          placeholder="unit"
        />
      </div>
      <div>
        <input
          id="card-billing-address-city"
          type="text"
          name="card-billing-address-city"
          autocomplete="off"
          placeholder="city"
        />
      </div>
      <div>
        <input
          id="card-billing-address-state"
          type="text"
          name="card-billing-address-state"
          autocomplete="off"
          placeholder="state"
        />
      </div>
      <div>
        <input
          id="card-billing-address-zip"
          type="text"
          name="card-billing-address-zip"
          autocomplete="off"
          placeholder="zip / postal code"
        />
      </div>
      <div>
        <input
          id="card-billing-address-country"
          type="text"
          name="card-billing-address-country"
          autocomplete="off"
          placeholder="country code"
        />
      </div>
      <br /><br />
      <button id="submit" value="submit" class="btn">Pay</button>
    </form>
  </div>
</template>
<script setup>
import { onMounted, ref, reactive, inject, computed } from 'vue'
import { usePaymentProviderStore } from '../stores/payment-provider'

let paypal = ref()
const isLoading = ref(true)

const paymentProviderStore = usePaymentProviderStore()

setupPaypal()

async function setupPaypal() {
  let res = await paymentProviderStore.generatePayment()

  const script = document.createElement('script')

  script.setAttribute(
    'src',
    `https://www.paypal.com/sdk/js?components=buttons,hosted-fields&client-id=${paymentProviderStore.selectedProvider.public_key}`
  )

  script.setAttribute('data-client-token', res.data.order.client_token)

  document.body.appendChild(script)

  script.addEventListener('load', setLoaded)
}

function setLoaded() {
  let orderId

  // Displays PayPal buttons
  window.paypal
    .Buttons({
      style: {
        layout: 'horizontal',
      },
      createOrder: function (data, actions) {
        return actions.order.create({
          purchase_units: [
            {
              amount: {
                value: '1.00',
              },
            },
          ],
        })
      },
      onApprove: function (data, actions) {
        return actions.order.capture().then(function (details) {
          window.location.href = '/success.html'
        })
      },
    })
    .render('#paypal-button-container')

  // If this returns false or the card fields aren't visible, see Step #1.
  if (window.paypal.HostedFields.isEligible()) {
    // Renders card fields
    window.paypal.HostedFields.render({
      // Call your server to set up the transaction
      createOrder: function () {
        return fetch('/your-server/paypal/order', {
          method: 'post',
        })
          .then(function (res) {
            return res.json()
          })
          .then(function (orderData) {
            orderId = orderData.id
            return orderId
          })
      },

      styles: {
        '.valid': {
          color: 'green',
        },
        '.invalid': {
          color: 'red',
        },
      },

      fields: {
        number: {
          selector: '#card-number',
          placeholder: '4111 1111 1111 1111',
        },
        cvv: {
          selector: '#cvv',
          placeholder: '123',
        },
        expirationDate: {
          selector: '#expiration-date',
          placeholder: 'MM/YY',
        },
      },
    }).then(function (cardFields) {
      document
        .querySelector('#card-form')
        .addEventListener('submit', (event) => {
          event.preventDefault()

          cardFields
            .submit({
              // Cardholder's first and last name
              cardholderName: document.getElementById('card-holder-name').value,
              // Billing Address
              billingAddress: {
                // Street address, line 1
                streetAddress: document.getElementById(
                  'card-billing-address-street'
                ).value,
                // Street address, line 2 (Ex: Unit, Apartment, etc.)
                extendedAddress: document.getElementById(
                  'card-billing-address-unit'
                ).value,
                // State
                region: document.getElementById('card-billing-address-state')
                  .value,
                // City
                locality: document.getElementById('card-billing-address-city')
                  .value,
                // Postal Code
                postalCode: document.getElementById('card-billing-address-zip')
                  .value,
                // Country Code
                countryCodeAlpha2: document.getElementById(
                  'card-billing-address-country'
                ).value,
              },
            })
            .then(function () {
              fetch('/your-server/api/order/' + orderId + '/capture/', {
                method: 'post',
              })
                .then(function (res) {
                  return res.json()
                })
                .then(function (orderData) {
                  // Three cases to handle:
                  //   (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart()
                  //   (2) Other non-recoverable errors -> Show a failure message
                  //   (3) Successful transaction -> Show confirmation or thank you

                  // This example reads a v2/checkout/orders capture response, propagated from the server
                  // You could use a different API or structure for your 'orderData'
                  var errorDetail =
                    Array.isArray(orderData.details) && orderData.details[0]

                  if (
                    errorDetail &&
                    errorDetail.issue === 'INSTRUMENT_DECLINED'
                  ) {
                    return actions.restart() // Recoverable state, per:
                    // https://developer.paypal.com/docs/checkout/integration-features/funding-failure/
                  }

                  if (errorDetail) {
                    var msg = 'Sorry, your transaction could not be processed.'
                    if (errorDetail.description)
                      msg += '\n\n' + errorDetail.description
                    if (orderData.debug_id)
                      msg += ' (' + orderData.debug_id + ')'
                    return alert(msg) // Show a failure message
                  }

                  // Show a success message or redirect
                  alert('Transaction completed!')
                })
            })
            .catch(function (err) {
              alert('Payment could not be captured! ' + JSON.stringify(err))
            })
        })
    })
  } else {
    // Hides card fields if the merchant isn't eligible
    document.querySelector('#card-form').style = 'display: none'
  }
}
</script>
