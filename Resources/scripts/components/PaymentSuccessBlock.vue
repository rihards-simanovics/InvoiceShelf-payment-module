<template>
  <div class="rounded-md bg-green-50 p-4 w-full">
    <div class="flex">
      <div class="flex-shrink-0">
        <BaseIcon
          name="CheckCircleIcon"
          class="h-5 w-5 text-green-400"
          aria-hidden="true"
        />
      </div>
      <div class="ml-3">
        <h3 class="text-sm font-medium text-green-800">
          {{ $t('payment_providers.payment_successful') }}
        </h3>
        <div class="mt-2 text-sm text-green-700">
          <p>{{ $t('payment_providers.payment_successful_description') }}</p>
        </div>
        <div class="mt-4">
          <div class="-mx-2 -my-1.5 flex">
            <router-link :to="invoiceViewLink">
              <button
                type="button"
                class="
                  bg-green-50
                  px-2
                  py-1.5
                  rounded-md
                  text-sm
                  font-medium
                  text-green-800
                  hover:bg-green-100
                  focus:outline-none
                  focus:ring-2
                  focus:ring-offset-2
                  focus:ring-offset-green-50
                  focus:ring-green-600
                "
              >
                {{ $t('general.view_invoice') }}
              </button>
            </router-link>
            <a :href="paymentReceiptUrl" target="_blank">
              <button
                type="button"
                class="
                  ml-3
                  bg-green-50
                  px-2
                  py-1.5
                  rounded-md
                  text-sm
                  font-medium
                  text-green-800
                  hover:bg-green-100
                  focus:outline-none
                  focus:ring-2
                  focus:ring-offset-2
                  focus:ring-offset-green-50
                  focus:ring-green-600
                "
              >
                {{ $t('general.view_payment_receipt') }}
              </button>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  invoiceViewLink: {
    type: Object,
    required: true,
  },
  paymentReceiptUrl: {
    type: String,
    required: true,
  },
})
</script>

<style lang="scss" scoped>
</style>
