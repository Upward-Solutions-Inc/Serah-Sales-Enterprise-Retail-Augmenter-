 <template>
  <div class="payslip-header payslip-wrapper">
    <h3 class="text-center">Payslip</h3>
    <div class="text-center font-small">{{ company.name }}</div>
    <div class="text-center font-small mb-4">{{ company.address }}</div>

    <div class="payslip-info">
      <div>
        <div><strong>Employee Name</strong> : {{ employee.name }}</div>
        <div><strong>Payroll Type</strong> : {{ employee.payroll_type }}</div>
        <div><strong>Branch</strong> : {{ employee.branch }}</div>
      </div>
      <div>
        <div><strong>Designation</strong> : {{ employee.classification }}</div>
        <div><strong>Pay Period</strong> : {{ payslip.start_date }} - {{ payslip.end_date }}</div>
        <div><strong>Pay Date</strong> : {{ payslip.pay_date }}</div>
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
    <div class="line-row font-weight-bold" style="margin-top: 20px;">
      <div class="left">Net Pay</div>
      <div class="right">{{ formatCurrency(netPay) }}</div>
    </div>
    <div class="text-right font-small">{{ netPayText }}</div>

    <!-- Signature -->
    <div class="signature">
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
      return this.payslip.entitlements.reduce((sum, e) => sum + Number(e.total), 0);
    },
    totalDeductions() {
      return this.payslip.deductions.reduce((sum, d) => sum + Number(d.total), 0);
    },
    netPay() {
      return this.totalEntitlements - this.totalDeductions;
    },
    netPayText() {
      return `${this.netPay.toLocaleString()} pesos`;
    }
  },
  methods: {
    formatCurrency(val) {
      return `₱ ${Number(val).toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
    }
  }
};
</script>