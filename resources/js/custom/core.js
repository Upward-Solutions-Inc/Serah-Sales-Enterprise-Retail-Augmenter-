import Vue from 'vue'

import TimeClock from './dtr/TimeClock.vue'
import Attendance from './dtr/Attendance.vue'
import DtrConfiguration from './dtr/DtrConfiguration.vue'
import EmployeeId from './dtr/EmployeeId.vue'

import PayrollPayslip from './payroll/PayrollPayslip.vue'
import PayrollReports from './payroll/PayrollReports.vue'
import PayrollComputation from './payroll/PayrollComputation.vue'

import ProductIngredients from './retail/inventory/ProductIngredients.vue'
import ProductRecipe from './retail/inventory/ProductRecipe.vue'
import ProductMeasurements from './retail/inventory/ProductMeasurements.vue'

Vue.component('time-clock', TimeClock)
Vue.component('attendance', Attendance)
Vue.component('dtr-configuration', DtrConfiguration)
Vue.component('employee-id', EmployeeId);

Vue.component('payroll-payslip', PayrollPayslip)
Vue.component('payroll-reports', PayrollReports)
Vue.component('payroll-computation', PayrollComputation)

Vue.component('product-ingredients', ProductIngredients)
Vue.component('product-recipe', ProductRecipe)
Vue.component('product-measurements', ProductMeasurements)