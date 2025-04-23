 <template>
  <div class="payslip-wrapper">

    <!-- payslip-info -->
    <div class="payslip-header text-center">
      <img :src="company.logo" alt="Company Logo" class="logo" v-if="company.logo" />
      <h4>{{ company.name }}</h4>
    </div>

    <!-- employee-info -->
    <div class="payslip-info">
      <div>
        <div><strong>Employee Name</strong> : {{ employee.name }}</div>
        <div><strong>Designation</strong> : {{ employee.classification }}</div>
        <div><strong>Branch</strong> : {{ employee.branch }}</div>
      </div>
      <div>
        <div><strong>Payroll Type</strong> : {{ employee.payroll_type }}</div>
        <div><strong>Pay Period</strong> : {{ payslip.date_range }}</div>
        <div><strong>Generated Date</strong> : {{ payslip.pay_date }}</div>
      </div>
    </div>

    <!-- Earnings -->
    <div class="section-title">Earnings</div>
    <div class="line-row" v-for="(e, i) in payslip.entitlements" :key="'e'+i">
      <div class="left">{{ e.description }}</div>
      <div class="right">{{ formatCurrency(e.total) }}</div>
    </div>
    <div class="line-row font-weight-bold">
      <div class="left">Total Earnings</div>
      <div class="right">{{ formatCurrency(totalEntitlements) }}</div>
    </div>

    <!-- Deductions -->
    <div class="section-title">Deductions</div>
    <div class="line-row" v-for="(d, i) in payslip.deductions" :key="'d'+i">
      <div class="left">{{ d.description }}</div>
      <div class="right">{{ formatCurrency(d.total) }}</div>
    </div>
    <div class="line-row font-weight-bold">
      <div class="left">Total Deductions</div>
      <div class="right">{{ formatCurrency(totalDeductions) }}</div>
    </div>

    <!-- Net Pay -->
    <div class="section-title">Totals</div>
    <div class="line-row font-weight-bold">
      <div class="left">Net Pay</div>
      <div class="right">{{ formatCurrency(netPay) }}</div>
    </div>

    <!-- Signature -->
    <div class="signature" style="margin-top: 40px;">
      <div>
        _________________________<br />Employer Signature
      </div>
      <div>
        _________________________<br />Employee Signature
      </div>
    </div>

    <div class="text-center font-small" style="margin-top: 20px;">
      This is a system generated payslip
    </div>
  </div>
</template>

<script>
export default {
  name: 'PayslipTemplate',
  props: {
    company: Object,
    employee: Object,
    payslip: Object
  },
  computed: {
    totalEntitlements() {
      return this.payslip.entitlements.reduce((sum, e) => sum + (parseFloat(e.total) || 0), 0);
    },
    totalDeductions() {
      return this.payslip.deductions.reduce((sum, d) => sum + (parseFloat(d.total) || 0), 0);
    },
    netPay() {
      return this.totalEntitlements - this.totalDeductions;
    }
  },
  methods: {
    formatCurrency(val) {
      return `₱ ${Number(val).toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
    }
  }
};
</script>