import { defineAsyncComponent } from 'vue'

import PaymentProviderSettings from './views/PaymentProviderSettings.vue'
import InvoicePublicPayment from './views/InvoicePublicPayment.vue'

import moduleLocales from '../locales/locales'

import '../sass/module.scss'

window.InvoiceShelf.booting((app, router) => {
  window.InvoiceShelf.addMessages(moduleLocales)

  router.addRoute('settings', {
    path: 'payment-providers',
    name: 'payment.provider',
    component: PaymentProviderSettings,
  })

  // When paying from email
  router.addRoute({
    path: '/:company/customer/invoices/pay/:hash',
    name: 'invoice.pay',
    component: InvoicePublicPayment,
  })

  // When paying from customer portal
  router.addRoute({
    path: '/:company/customer/invoices/payment/:id',
    name: 'invoice.portal.payment',
    component: InvoicePublicPayment,
  })
})
