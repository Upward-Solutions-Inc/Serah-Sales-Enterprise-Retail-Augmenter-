import Vue from 'vue'
import DtrConfiguration from './dtr/DtrConfiguration.vue'
import EmployeeId from './dtr/EmployeeId.vue'

import PayrollPayslip from './payroll/PayrollPayslip.vue'
import PayrollReports from './payroll/PayrollReports.vue'
import PayrollComputation from './payroll/PayrollComputation.vue'


Vue.component('dtr-configuration', DtrConfiguration)
Vue.component('employee-id', EmployeeId);

Vue.component('payroll-payslip', PayrollPayslip)
Vue.component('payroll-reports', PayrollReports)
Vue.component('payroll-computation', PayrollComputation)