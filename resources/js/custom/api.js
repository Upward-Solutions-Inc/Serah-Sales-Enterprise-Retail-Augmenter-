import axios from 'axios'

const api = axios.create({
  baseURL: '/',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json'
  }
})

export default api

// Dtr Configuration
export const DtrConfig = {
    fetch: '/dtr/configuration/data',
    store: '/dtr/configuration/store'
}

export const PayrollComputation = {
  fetch: '/payroll/computation/data',
  updatePay: '/payroll/computation/updatePay',
  updateRate: '/payroll/computation/updateRate',
  updateCompenOrDeduc: '/payroll/computation/updateCompenOrDeduc',
  fetchDynamicData: '/payroll/computation/dynamicData',
  deleteComponent: '/payroll/computation/deleteComponent'
}