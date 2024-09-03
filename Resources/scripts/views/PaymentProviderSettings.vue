<template>
  <PaymentProviderModal />

  <BaseSettingCard
    :title="$t('payment_providers.payment_providers')"
    :description="$t('payment_providers.description')"
  >
    <template #action>
      <BaseButton
        type="submit"
        variant="primary-outline"
        @click="addPaymentProvider"
      >
        <template #left="slotProps">
          <BaseIcon :class="slotProps.class" name="PlusIcon" />
        </template>
        {{ $t('payment_providers.add_payment_provider') }}
      </BaseButton>
    </template>

    <BaseTable
      ref="table"
      :data="fetchData"
      :columns="paymentColumns"
      class="mt-16"
    >
      <template #cell-active="{ row }">
        <BaseBadge
          :bg-color="
            utils.getBadgeStatusColor(row.data.active ? 'YES' : 'NO').bgColor
          "
          :color="
            utils.getBadgeStatusColor(row.data.active ? 'YES' : 'NO').color
          "
        >
          {{ row.data.active ? 'Yes' : 'No'.replace('_', ' ') }}
        </BaseBadge>
      </template>
      <template #cell-actions="{ row }">
        <BaseDropdown>
          <template #activator>
            <div class="inline-block">
              <BaseIcon name="DotsHorizontalIcon" class="w-5 text-gray-500" />
            </div>
          </template>

          <BaseDropdownItem @click="editPaymentProvider(row.data.id)">
            <BaseIcon name="PencilIcon" class="h-5 mr-3 text-gray-600" />
            {{ $t('general.edit') }}
          </BaseDropdownItem>

          <BaseDropdownItem @click="removePaymentProvider(row.data.id)">
            <BaseIcon name="TrashIcon" class="h-5 mr-3 text-gray-600" />
            {{ $t('general.delete') }}
          </BaseDropdownItem>
        </BaseDropdown>
      </template>
    </BaseTable>
  </BaseSettingCard>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { computed, ref, inject } from 'vue'
import { usePaymentProviderStore } from '~/scripts/stores/payment-provider'
import { useDialogStore } from '@/scripts/stores/dialog'
import { useModalStore } from '@/scripts/stores/modal'
import PaymentProviderModal from '~/scripts/components/PaymentProviderModal.vue'
const { t } = useI18n()

const utils = inject('utils')
const modalStore = useModalStore(true)
const dialogStore = useDialogStore(true)
const paymentProviderStore = usePaymentProviderStore()

const table = ref(null)

const paymentColumns = computed(() => {
  return [
    {
      key: 'name',
      label: t('payment_providers.name'),
      thClass: 'extra',
      tdClass: 'font-medium text-gray-900',
    },
    {
      key: 'driver',
      label: t('payment_providers.driver'),
      sortable: false,
    },
    {
      key: 'active',
      label: t('payment_providers.active'),
      sortable: false,
    },
    {
      key: 'actions',
      label: '',
      tdClass: 'text-right text-sm font-medium',
      sortable: false,
    },
  ]
})

function addPaymentProvider() {
  modalStore.openModal({
    title: t('payment_providers.add_payment_provider'),
    componentName: 'PaymentMethodModal',
    refreshData: table.value && table.value.refresh,
    size: 'sm',
  })
}

function editPaymentProvider(data) {
  paymentProviderStore.fetchPaymentProvider(data)

  modalStore.openModal({
    title: t('payment_providers.edit_payment_provider'),
    componentName: 'PaymentMethodModal',
    size: 'md',
    data: data,
    refreshData: table.value && table.value.refresh,
  })
}

function removePaymentProvider(id) {
  dialogStore
    .openDialog({
      title: t('general.are_you_sure'),
      message: t('settings.exchange_rate.exchange_rate_confirm_delete'),
      yesLabel: t('general.ok'),
      noLabel: t('general.cancel'),
      variant: 'danger',
      hideNoButton: false,
      size: 'lg',
    })
    .then(async (res) => {
      if (res) {
        await paymentProviderStore.deletePaymentProvider(id)
        table.value && table.value.refresh()
      }
    })
}

async function fetchData({ page, filter, sort }) {
  let data = {
    orderByField: sort.fieldName || 'created_at',
    orderBy: sort.order || 'desc',
    page,
  }

  let response = await paymentProviderStore.fetchPaymentProviders(data)

  return {
    data: response.data.data,
    pagination: {
      totalPages: response.data.meta.last_page,
      currentPage: page,
      totalCount: response.data.meta.total,
      limit: 5,
    },
  }
}
</script>
