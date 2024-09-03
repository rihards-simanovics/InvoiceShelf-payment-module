<template>
  <BaseModal
    :show="modalActive"
    @close="closePaymentModal"
    @open="setInitialModalData"
  >
    <template #header>
      <div class="flex justify-between w-full">
        {{ modalStore.title }}

        <BaseIcon
          name="XIcon"
          class="h-6 w-6 text-gray-500 cursor-pointer"
          @click="closePaymentModal"
        />
      </div>
    </template>

    <form @submit.prevent="submitPaymentProvider">
      <div class="px-4 md:px-8 py-8 overflow-y-auto sm:p-6">
        <BaseInputGrid layout="one-column">
          <BaseInputGroup
            :label="$tc('payment_providers.name')"
            :error="v$.name.$error && v$.name.$errors[0].$message"
            required
            :tooltip="$t('payment_providers.provider_name_tooltip')"
            :help-text="$t('payment_providers.provider_name_help_text')"
          >
            <BaseInput
              v-model="paymentProviderStore.currentPaymentProvider.name"
              :content-loading="isFetchingInitialData"
              type="text"
              :invalid="v$.name.$error"
              :placeholder="$t('payment_providers.provider_name_placeholder')"
              @input="v$.name.$touch()"
            />
          </BaseInputGroup>
          <BaseInputGroup
            :label="$tc('payment_providers.driver')"
            :error="v$.driver.$error && v$.driver.$errors[0].$message"
            required
          >
            <BaseMultiselect
              v-model="paymentProviderStore.currentPaymentProvider.driver"
              :options="drivers"
              :can-deselect="false"
              :searchable="true"
              label="key"
              value-prop="value"
              :invalid="v$.driver.$error"
              @input="v$.driver.$touch()"
            />
          </BaseInputGroup>
          <BaseInputGroup
            :label="$t('payment_providers.key')"
            :error="
              v$.settings.key.$error && v$.settings.key.$errors[0].$message
            "
            required
          >
            <BaseInput
              v-model="paymentProviderStore.currentPaymentProvider.settings.key"
              :content-loading="isFetchingInitialData"
              type="text"
              :invalid="v$.settings.key.$error"
              @input="v$.settings.key.$touch()"
            />
          </BaseInputGroup>
          <BaseInputGroup
            :label="$t('payment_providers.secret_key')"
            :error="
              v$.settings.secret.$error &&
              v$.settings.secret.$errors[0].$message
            "
            required
          >
            <BaseInput
              v-model="
                paymentProviderStore.currentPaymentProvider.settings.secret
              "
              :content-loading="isFetchingInitialData"
              type="text"
              :invalid="v$.settings.secret.$error"
              @input="v$.settings.secret.$touch()"
            />
          </BaseInputGroup>

          <BaseSwitch
            v-if="
              paymentProviderStore.currentPaymentProvider.driver === 'paypal'
            "
            v-model="paymentProviderStore.currentPaymentProvider.use_test_env"
            class="flex"
            :label-right="$t('payment_providers.use_test_env')"
          />

          <BaseSwitch
            v-model="paymentProviderStore.currentPaymentProvider.active"
            class="flex"
            :label-right="$t('payment_providers.active')"
          />
        </BaseInputGrid>
      </div>
      <div
        class="z-0 flex justify-end p-4 border-t border-gray-200 border-solid"
      >
        <BaseButton
          class="mr-3"
          variant="primary-outline"
          type="button"
          @click="closePaymentModal"
        >
          {{ $t('general.cancel') }}
        </BaseButton>

        <BaseButton
          :loading="isSaving"
          :disabled="isSaving"
          variant="primary"
          type="submit"
        >
          <template #left="slotProps">
            <BaseIcon
              v-if="!isSaving"
              name="SaveIcon"
              :class="slotProps.class"
            />
          </template>
          {{
            paymentProviderStore.isEdit
              ? $t('general.update')
              : $t('general.save')
          }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>

<script setup>
let { computed, reactive, ref } = window.Vue
const { useVuelidate } = window.Vuelidate
import { useModalStore } from '@/scripts/stores/modal'
import { usePaymentProviderStore } from '~/scripts/stores/payment-provider'
import { required, helpers } from '@vuelidate/validators'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

let isFetchingInitialData = ref(false)
let isSaving = ref(false)

const modalStore = useModalStore(true)
const paymentProviderStore = usePaymentProviderStore()

const modalActive = computed(
  () => modalStore.active && modalStore.componentName === 'PaymentMethodModal'
)

const drivers = computed(() => {
  return paymentProviderStore.paymentDrivers.map((item) => {
    return Object.assign({}, item, {
      key: t(item.key),
    })
  })
})

const rules = computed(() => {
  return {
    name: {
      required: helpers.withMessage(t('validation.required'), required),
    },
    driver: {
      required: helpers.withMessage(t('validation.required'), required),
    },

    settings: {
      key: {
        required: helpers.withMessage(t('validation.required'), required),
      },
      secret: {
        required: helpers.withMessage(t('validation.required'), required),
      },
    },
  }
})

const v$ = useVuelidate(
  rules,
  computed(() => paymentProviderStore.currentPaymentProvider)
)

fetchData()

function setInitialModalData() {
  if (!paymentProviderStore.isEdit && drivers.value.length) {
    paymentProviderStore.currentPaymentProvider.driver = drivers.value[0].value
  }
}

async function fetchData() {
  isFetchingInitialData.value = true
  await paymentProviderStore.fetchPaymentDrivers()
  isFetchingInitialData.value = false
}

async function submitPaymentProvider() {
  v$.value.$touch()

  if (v$.value.$invalid) {
    return true
  }

  isSaving.value = true
  try {
    const action = paymentProviderStore.isEdit
      ? paymentProviderStore.updatePaymentProvider
      : paymentProviderStore.addPaymentProvider
    isSaving.value = true
    await action(paymentProviderStore.currentPaymentProvider)
    isSaving.value = false
    modalStore.refreshData ? modalStore.refreshData() : ''
    closePaymentModal()
  } catch (error) {
    isSaving.value = false
    return true
  }
}

function resetModalData() {
  paymentProviderStore.currentPaymentProvider = {
    name: '',
    driver: '',
    use_test_env: false,
    active: false,
    settings: {
      key: '',
      secret: '',
    },
  }
}

function closePaymentModal() {
  modalStore.closeModal()
  setTimeout(() => {
    resetModalData()
    v$.value.$reset()
  }, 300)
}
</script>
