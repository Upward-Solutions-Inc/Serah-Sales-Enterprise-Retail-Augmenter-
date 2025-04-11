import axios from 'axios'

const api = axios.create({
  baseURL: '/',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json'
  }
})

export default api

// Dtr Time Clock
export const TimeClock = {
  status: '/dtr/status',
  clockIn: '/dtr/clock-in',
  clockOut: '/dtr/clock-out',
  logs: '/dtr/logs',
  uploadFace: '/dtr/time_clock/uploadFace'
}

// Dtr Configuration
export const DtrConfig = {
    fetch: '/dtr/configuration/data',
    store: '/dtr/configuration/store'
}

// Employee Id
export const EmployeeId = {
  generateQr: '/dtr/employee_id/generateUserQrFromData'
}

// Payroll Payslip
export const PayrollPayslip = {
  fetchUserPayslips: '/payroll/payslip/employeePayslips',
}

// Payroll Reports
export const PayrollReports = {
  fetchData: '/payroll/reports/data',
  fetchUsers: '/payroll/reports/users',
  generate: '/payroll/reports/generate'
}

export const PayrollComputation = {
  fetch: '/payroll/computation/data',
  updatePay: '/payroll/computation/updatePay',
  updateRate: '/payroll/computation/updateRate',
  updateCompenOrDeduc: '/payroll/computation/updateCompenOrDeduc',
  fetchDynamicData: '/payroll/computation/dynamicData',
  deleteComponent: '/payroll/computation/deleteComponent'
}