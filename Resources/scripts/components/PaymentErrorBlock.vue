<template>
  <div class="rounded-md bg-red-50 p-4 w-full">
    <div class="flex">
      <div class="flex-shrink-0">
        <BaseIcon
          name="XCircleIcon"
          class="h-5 w-5 text-red-700"
          aria-hidden="true"
        />
      </div>
      <div class="ml-3">
        <h3 class="text-sm font-medium text-red-800">
          {{ $t('payment_providers.payment_error') }}
        </h3>
        <div class="mt-2 text-sm text-red-700">
          <p>{{ message }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
    message: {
        type: String,
        default: null,
        required: true
    }
})
</script>