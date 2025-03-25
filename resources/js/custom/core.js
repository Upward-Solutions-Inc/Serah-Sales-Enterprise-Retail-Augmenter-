import Vue from 'vue'
import PayrollPayslip from './payroll/PayrollPayslip.vue'
import PayrollComputation from './payroll/PayrollComputation.vue'
import DtrConfiguration from './dtr/DtrConfiguration.vue'

Vue.component('payroll-payslip', PayrollPayslip)
Vue.component('payroll-computation', PayrollComputation)
Vue.component('dtr-configuration', DtrConfiguration)