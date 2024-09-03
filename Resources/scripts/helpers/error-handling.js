import { useNotificationStore } from '@/scripts/stores/notification'

export const handleError = (err) => {
  const notificationStore = useNotificationStore(true)
  if (!err.response) {
    notificationStore.showNotification({
      type: 'error',
      message:
        'Please check your internet connection or wait until servers are back online.',
    })
  } else {
    if (err.response.data.errors) {
      const errors = JSON.parse(JSON.stringify(err.response.data.errors))
      for (const i in errors) {
        showError(errors[i][0])
      }
    } else if (err.response.data.error) {
      showError(err.response.data.error)
    } else {
      showError(err.response.data.message)
    }
  }
}
