<template>
  <div class="content-wrapper">
    <div class="row">
      <div class="col-sm-3 mb-3">
        <h4>Computation</h4>
      </div>
    </div>

    <!-- Basic Pay -->
    <div class="card p-3">
      <div class="row">
        <div class="col-md-10 mb-2">
          <h5>
            <i>Basic Pay</i>
          </h5>
        </div>
        <div class="col-md-2 mb-2 text-right">
          <button v-if="editModePay" class="btn btn-success btn-sm mr-2" 
            @click="saveDataPay">
            Save</button>
          <button class="btn btn-primary btn-sm" 
            @click="toggleEditPay">
            {{ editModePay ? "Cancel" : "Edit" }}
          </button>
        </div>

        <div class="col-lg-4">
          <div class="form-group">
            <label>Designation</label>
            <select class="form-control" v-model="form.role_id">
                <option v-for="role in roles" :key="role.id" :value="role.id" class="form-control">
                    {{ role.name }}
                </option>
            </select>
          </div>
          <div class="form-group">
            <label>Branch</label>
            <select class="form-control" v-model="form.branch_id">
                <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                    {{ branch.name }}
                </option>
            </select>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="form-group">
            <label>Monthly</label>
            <div v-if="!editModePay">
              <input type="text" class="form-control" :value="formatCurrency(form.monthly)" readonly/>
            </div>
            <div v-else>
              <input type="number" step="any" class="form-control" v-model.number="form.monthly"/>
            </div>
          </div>
          <div class="form-group">
            <label>Semi-Monthly</label>
            <input type="text" class="form-control" :value="formatCurrency(semiMonthly)" readonly/>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="form-group">
            <label>Daily</label>
            <input type="text" class="form-control" :value="formatCurrency(daily)" readonly/>
          </div>
          <div class="form-group">
            <label>Hourly</label>
            <input type="text" class="form-control" :value="formatCurrency(hourly)" readonly/>
          </div>
        </div>
      </div>
    </div>

    <!-- Payroll Variables -->
    <div class="card p-3">
      <div class="row align-items-center justify-content-between">
        <div class="col-md-10 mb-2">
          <h5><i>Payroll Variables Rates</i></h5>
          <p v-if="editModeRate" class="text-muted small mt-2">
            Note: All inputs should be in decimal format (e.g. 2% → 0.02)
          </p>
        </div>
        <div class="col-md-2 mb-2 text-right">
          <button v-if="editModeRate" class="btn btn-success btn-sm mr-2" 
            @click="saveDataRate">
            Save</button>
          <button class="btn btn-primary btn-sm" 
            @click="toggleEditRate">
            {{ editModeRate ? "Cancel" : "Edit" }}
          </button>
        </div>

        <!-- WORK PAY -->
        <div class="col-lg-4">
          <label class="mb-4"><b>Work Pay</b></label>

          <div class="form-group">
            <label> Night Differential Pay </label>
            <div v-if="!editModeRate">
              <input type="text" class="form-control" :value="formatRate(form.nightpay)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.nightpay" />
            </div>
          </div>

          <div class="form-group">
            <label> Rest Day Pay</label>
            <div v-if="!editModeRate">
              <input type="text" class="form-control" :value="formatRate(form.restpay)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.restpay" />
            </div>
          </div>

          <div class="form-group">
            <label> Holiday Pay </label>
            <div v-if="!editModeRate">
              <input type="text" class="form-control" :value="formatRate(form.holiday)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.holiday" />
            </div>
          </div>
        </div>

        <!-- OVERTIME -->
        <div class="col-lg-4">
          <label class="mb-4"><b>Overtime Pay</b></label>

          <div class="form-group">
            <label> Regular Overtime </label>
            <div v-if="!editModeRate">
              <input type="text" class="form-control" :value="formatRate(form.ot_regular)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.ot_regular" />
            </div>
          </div>

          <div class="form-group">
            <label> Rest Day Overtime </label>
            <div v-if="!editModeRate">
              <input type="text" class="form-control" :value="formatRate(form.ot_restday)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.ot_restday" />
            </div>
          </div>

          <div class="form-group">
            <label> Holiday Overtime </label>
            <div v-if="!editModeRate">
              <input type="text" class="form-control" :value="formatRate(form.ot_holiday)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.ot_holiday" />
            </div>
          </div>
        </div>

        <!-- DEDUCTIONS -->
        <div class="col-lg-4">
          <label class="mb-4"><b>Deductions</b></label>

          <div class="form-group">
            <label> Social Security System </label>
            <div v-if="!editModeRate">
              <input type="text" class="form-control" :value="formatPercent(form.sss)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.sss" />
            </div>
          </div>

          <div class="form-group">
            <label> PhilHealth </label>
            <div v-if="!editModeRate">
              <input type="text" class="form-control" :value="formatPercent(form.philhealth)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.philhealth" />
            </div>
          </div>

          <div class="form-group">
            <label> Pagibig </label>
            <div v-if="!editModeRate">
              <input type="text" class="form-control" :value="formatPercent(form.pagibig)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.pagibig" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Addional Compensation, Deductions & Total -->
    <div class="card p-3">
      <div class="row">
        <!-- Addtional Compensation -->
        <div class="col-md-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5><i>Additional Compensation</i></h5>
            <div class="dropdown">
              <i class="fas fa-ellipsis-v" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor: pointer;"></i>
              <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu">
                <a
                  class="dropdown-item"
                  v-if="earnings.length"
                  href="#"
                  @click="earningsEditMode = !earningsEditMode"
                >
                  <i :class="earningsEditMode ? 'fas fa-eye-slash mr-2' : 'fas fa-eye mr-2'"></i>
                  {{ earningsEditMode ? "Hide" : "View" }}
                </a>
                <a class="dropdown-item" href="#" @click="openModal('earning')" data-toggle="modal" data-target="#payrollModal">
                  <i class="fas fa-plus mr-2"></i> Add
                </a>
              </div>
            </div>
          </div>
          <!-- fixed -->
          <div class="form-group">
            <label>13th Month Pay</label>
            <input type="text" class="form-control" :value="formatCurrency(endYearPay)" readonly/>
          </div>
          <!-- dynamic -->
          <div class="form-group" v-for="(item, index) in earnings" :key="index">
            <label>{{ item.label }}</label>
            <div class="d-flex align-items-center">
              <input type="text" class="form-control" :value="formatCurrency(item.amount)" readonly/>
              <button 
                v-if="earningsEditMode" 
                class="btn btn-sm btn-danger ml-3" 
                :disabled="deletingItems.includes(computeCode(item.label))"
                @click="deleteItem(index, 'earnings')"
                title="Delete item"
                data-toggle="tooltip">
                ✕
              </button>
            </div>
          </div>
        </div>

        <!-- Deduction Section -->
        <div class="col-md-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5><i>Deductions</i></h5>
            <div class="dropdown">
              <i class="fas fa-ellipsis-v" id="deductionDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor: pointer;"></i>
              <div class="dropdown-menu dropdown-menu-right" aria-labelledby="deductionDropdown">
                <a
                  class="dropdown-item"
                  v-if="deductions.length"
                  href="#"
                  @click="deductionEditMode = !deductionEditMode"
                >
                  <i :class="deductionEditMode ? 'fas fa-eye-slash mr-2' : 'fas fa-eye mr-2'"></i>
                  {{ deductionEditMode ? "Hide" : "View" }}
                </a>
                <a class="dropdown-item" href="#" @click="openModal('deduction')" data-toggle="modal" data-target="#payrollModal">
                  <i class="fas fa-plus mr-2"></i> Add
                </a>
              </div>
            </div>
          </div>
          <!-- fixed -->
          <div class="form-group">
            <label>Income Tax</label>
            <input type="text" class="form-control" :value="formatCurrency(incomeTax)" readonly/>
          </div>
          <div class="form-group">
            <label>Social Security System</label>
            <input type="text" class="form-control" :value="formatCurrency(sss)" readonly/>
          </div>
          <div class="form-group">
            <label>PhilHealth</label>
            <input type="text" class="form-control" :value="formatCurrency(philhealth)" readonly/>
          </div>
          <div class="form-group">
            <label>Pagibig</label>
            <input type="text" class="form-control" :value="formatCurrency(pagibig)" readonly/>
          </div>
          <!-- dynamic -->
          <div class="form-group" v-for="(item, index) in deductions" :key="index">
            <label>{{ item.label }}</label>
            <div class="d-flex align-items-center">
              <input type="text" class="form-control"  :value="formatCurrency(item.amount)" readonly/>
              <button 
                v-if="deductionEditMode" 
                class="btn btn-sm btn-danger ml-3" 
                :disabled="deletingItems.includes(computeCode(item.label))"
                @click="deleteItem(index, 'deductions')"
                title="Delete item"
                data-toggle="tooltip">
                ✕
              </button>
            </div>
          </div>
        </div>

        <!-- Total Computation -->
        <div class="col-md-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5><i>Total Computation</i></h5>
          </div>
          <div class="form-group">
            <label>Gross Pay</label>
            <input type="text" class="form-control" :value="formatCurrency(grossPay)" readonly />
          </div>
          <div class="form-group">
            <label>Total Deduction</label>
            <input type="text" class="form-control" :value="formatCurrency(totalDeduction)" readonly />
          </div>
          <div class="form-group">
            <label>Net Pay</label>
            <input type="text" class="form-control font-weight-bold" :value="formatCurrency(netPay)" readonly />
          </div>
        </div>

      </div>
    </div>

    <!-- Modal -->
    <div
      class="modal fade"
      id="payrollModal"
      tabindex="-1"
      role="dialog"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border rounded shadow-sm">
          <div class="modal-header">
            <h5 class="modal-title">
              Add {{ modalType === "earning" ? "Earning" : "Deduction" }}
            </h5>
            <button type="button" class="close" data-dismiss="modal">
              <span>&times;</span>
            </button>
          </div>

          <div class="modal-body">
            <div class="form-group">
              <label
                >{{
                  modalType === "earning" ? "Earning" : "Deduction"
                }}
                Name</label
              >
              <input
                type="text"
                class="form-control"
                v-model="modalData.label"
              />
              <small class="text-danger" v-if="modalError">{{ modalError }}</small>
            </div>
            <div class="form-group">
              <label>Amount</label>
              <input
                type="number"
                class="form-control"
                v-model.number="modalData.amount"
              />
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary mr-3" data-dismiss="modal">
              Cancel
            </button>
            <button class="btn btn-primary" :disabled="isClick" @click="saveCompenOrDeduc">Save</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from "sweetalert2";
import api, { PayrollComputation } from "../api";
export default {
  name: "PayrollComputation",
  data() {
    return {
      roles: [],
      branches: [],
      components: [],
      salaries: [],
      earnings: [],
      deductions: [],
      deletingItems: [],

      isClick: false,

      editModePay: false,
      editModeRate: false,

      earningsEditMode: false,
      deductionEditMode: false,
      
      modalError: '',
      originalForm: {},
      modalType: "",
      modalData: {
        label: "",
        amount: 0,
      },
      
      form: {
        // variables
        nightpay: 0,
        restpay: 0,
        holiday: 0,
        ot_regular: 0,
        ot_restday: 0,
        ot_holiday: 0,
        sss: 0,
        philhealth: 0,
        pagibig: 0,

        // basic pay
        role_id: null,
        branch_id: null,
        monthly: 0,
      },
    };
  },

  created() {
    api.get(PayrollComputation.fetch)
      .then(({ data }) => {
        const savedRoleId   = localStorage.getItem('role_id');
        const savedBranchId = localStorage.getItem('branch_id');

        this.components = data.components;
        this.salaries = data.salaries;
        this.roles = data.roles;
        this.branches = data.branches;
        this.currencySymbol = data.currency_symbol;

        // Map component values to form
        this.form.nightpay = this.getValue("nightpay");
        this.form.restpay = this.getValue("restpay");
        this.form.holiday = this.getValue("holiday");
        this.form.ot_regular = this.getValue("ot_regular");
        this.form.ot_restday = this.getValue("ot_restday");
        this.form.ot_holiday = this.getValue("ot_holiday");
        this.form.sss = this.getValue("sss");
        this.form.philhealth = this.getValue("philhealth");
        this.form.pagibig = this.getValue("pagibig");

        this.form.role_id = savedRoleId ? parseInt(savedRoleId) : (this.roles[0]?.id || null);
        this.form.branch_id = savedBranchId ? parseInt(savedBranchId) : (this.branches[0]?.id || null);
        this.updateMonthly();
        this.originalForm = JSON.parse(JSON.stringify(this.form));
      })
      .catch((error) => console.error(error));
      this.fetchDynamicData();

  },

  watch: {
    'form.role_id'(newVal) {
      localStorage.setItem("role_id", newVal || 0);
      this.updateMonthly();
    },
    'form.branch_id'(newVal) {
      localStorage.setItem("branch_id", newVal || 0);
      this.updateMonthly();
    },
    'modalData.label'(val) {
      if (val) this.modalError = '';
    }
  },

  computed: {
    // Basic Pay
    semiMonthly() {
      const monthly = this.getMonthly() || 0;
      return (monthly / 2).toFixed(2);
    },
    daily() {
      const monthly = this.getMonthly() || 0;
      return (monthly / 22).toFixed(2);
    },
    hourly() {
      const monthly = this.getMonthly() || 0;
      return (monthly / 22 / 8).toFixed(2);
    },


    // Deductions
    incomeTax() {
      const monthlySalary = this.getMonthly() || 0;
      const annualSalary  = monthlySalary * 12;
      let annualTax = 0;

      if (annualSalary <= 250000) {
        annualTax = 0;
      } else if (annualSalary <= 400000) {
        annualTax = (annualSalary - 250000) * 0.15;
      } else if (annualSalary <= 800000) {
        annualTax = 22500 + (annualSalary - 400000) * 0.20;
      } else if (annualSalary <= 2000000) {
        annualTax = 102500 + (annualSalary - 800000) * 0.25;
      } else if (annualSalary <= 8000000) {
        annualTax = 402500 + (annualSalary - 2000000) * 0.30;
      } else {
        annualTax = 2202500 + (annualSalary - 8000000) * 0.35;
      }
      // Convert annual to monthly
      const monthlyTax = annualTax / 12;
      return monthlyTax.toFixed(2);
    },
    sss() {
      const monthly = this.getMonthly() || 0;
      const rate = Number(this.form.sss) || 0;
      return (monthly * rate).toFixed(2);
    },
    philhealth() {
      const monthly = this.getMonthly() || 0;
      const rate = Number(this.form.philhealth) || 0;
      return (monthly * rate).toFixed(2);
    },
    pagibig() {
      const monthly = this.getMonthly() || 0;
      const rate = Number(this.form.pagibig) || 0;
      if (monthly < 1500) {
        return (100).toFixed(2);
      } else {
        return (monthly * rate).toFixed(2);
      }
    },


    // Additional Compensation
    endYearPay() {
      const monthly = this.getMonthly() || 0;
      return monthly.toFixed(2);
    },


    // Total Computation
    grossPay() {
      const monthly = Number(this.getMonthly()) || 0;
      let earningsTotal = 0;
      for (let item of this.earnings) {
        earningsTotal += Number(item.amount) || 0;
      }
      return monthly + earningsTotal;
    },
    totalDeduction() {
      let deductionsTotal = 0;
      for (let item of this.deductions) {
        deductionsTotal += Number(item.amount) || 0;
      }
      const incomeTax = Number(this.incomeTax) || 0;
      const sss = Number(this.sss) || 0;
      const philhealth = Number(this.philhealth) || 0;
      const pagibig = Number(this.pagibig) || 0;
      return deductionsTotal + incomeTax + sss + philhealth + pagibig;
    },
    netPay() {
      return this.grossPay - this.totalDeduction;
    },
  },

  mounted() {
    this.fetchDynamicData();
    this.enableTooltips();
  },

  methods: {
    enableTooltips() {
      this.$nextTick(() => {
        $('[data-toggle="tooltip"]').tooltip();
      });
    },
    toggleEditPay() {
      if (this.editModePay) {
        this.form = JSON.parse(JSON.stringify(this.originalForm));
      }
      this.editModePay = !this.editModePay;
    },
    toggleEditRate() {
      if (this.editModeRate) {
        this.form = JSON.parse(JSON.stringify(this.originalForm));
      }
      this.editModeRate = !this.editModeRate;
    },

    // for Basic Pay
    saveDataPay() {
      api.post(PayrollComputation.updatePay, this.form)
      .then((response) => {
        this.editModePay = false;
        const payroll = response.data[0];
        const designation = payroll.role ? payroll.role.name : 'N/A';
        const branch = payroll.branch ? payroll.branch.name : 'N/A';
        const salary = this.formatCurrency(payroll.monthly_salary);
        // console.log('Payroll response:', response.data)
        
        Swal.fire({
          icon: 'success',
          title: 'Successfully Updated',
          html: `<strong>Designation:</strong> ${designation}<br/>
                 <strong>Branch:</strong> ${branch}<br/>
                 <strong>Salary:</strong> ${salary}`,
        });
        this.originalForm = JSON.parse(JSON.stringify(this.form));
      })
      .catch((err) => console.error(err));
    },

    // for Payroll Variables Rates
    saveDataRate() {
      api.post(PayrollComputation.updateRate, this.form)
        .then((response) => {
          this.editModeRate = false;
          const updatedRates = response.data;
          let message = "<strong>Changes applied successfully.</strong><br/><br/>";
          
          updatedRates.forEach(item => {
            message += `${item.label}: ${item.value}<br/>`;
          });

          // console.log('Rates response:', response.data)
          
          Swal.fire({
            icon: 'success',
            title: 'Successfully Updated',
            html: message,
          });
          this.originalForm = JSON.parse(JSON.stringify(this.form));
        })
        .catch((err) => console.error(err));
    },

    // for Additional Compensation & Deductions
    saveCompenOrDeduc() {
      if (!this.modalData.label) {
        this.modalError = 'Field is empty or invalid!';
        return;
      }
      
      this.modalError = '';
      this.isClick = true;

      if (this.modalType === "earning") {
        this.earnings.push({ ...this.modalData });
      } else {
        this.deductions.push({ ...this.modalData });
      }
      api.post(PayrollComputation.updateCompenOrDeduc, {
        earnings: this.earnings,
        deductions: this.deductions,
      })
      .then((response) => {
        $("#payrollModal").modal("hide");
        const data = response.data;
        let message = "<strong>Changes applied successfully.</strong><br/><br/>";

        this.earnings = data.earnings || [];
        this.deductions = data.deductions || [];
    
        if (data.earnings && data.earnings.length) {
          message += "<u>Earnings:</u><br/>";
          data.earnings.forEach(item => {
            message += `${item.label}: ${item.value}<br/>`;
          });
        }
        if (data.deductions && data.deductions.length) {
          message += "<u>Deductions:</u><br/>";
          data.deductions.forEach(item => {
            message += `${item.label}: ${item.value}<br/>`;
          });
        }
        this.fetchDynamicData();
        Swal.fire({
          icon: 'success',
          title: 'Successfully Updated',
          html: message,
        })
      })
      
      .catch((err) => console.error(err))
      .finally(() => { this.isClick = false; });
    },

    deleteItem(index, group) {
      const list = group === 'earnings' ? this.earnings : this.deductions;
      const component = list[index];
      const code = this.computeCode(component.label);

      if (this.deletingItems.includes(code)) return;

      Swal.fire({
        title: 'Are you sure?',
        text: `Delete "${component.label}" from ${group}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          this.deletingItems.push(code);

          api.delete(PayrollComputation.deleteComponent, {
            data: { code, group }
          })
            .then(response => {
              list.splice(index, 1);
              if (group === 'earnings') this.earningsEditMode = false;
              else this.deductionEditMode = false;

              this.fetchDynamicData();

              Swal.fire({
                icon: 'success',
                title: 'Deleted',
                text: `${response.data.label}: ${this.formatCurrency(response.data.value)}`
              });
            })
            .catch(err => console.error(err))
            .finally(() => {
              const i = this.deletingItems.indexOf(code);
              if (i > -1) this.deletingItems.splice(i, 1);
            });
        }
      });
    },
    
    fetchDynamicData() {
      api.get(PayrollComputation.fetchDynamicData)
        .then(response => {
          const amount = response.data;
          this.earnings = (amount.earnings || []).map(({ label, value }) => ({
            label,
            amount: parseFloat(value) || 0
          }));
          this.deductions = (amount.deductions || []).map(({ label, value }) => ({
            label,
            amount: parseFloat(value) || 0
          }));
        })
        .catch(err => console.error(err));
    },


    // Helper function
    openModal(type) {
      this.modalType = type;
      this.modalData = {
        label: "",
        amount: 0,
      };
    },

    getValue(code) {
      const item = this.components.find(c => c.code === code);
      return item ? item.value : 0;
    },

    computeCode(label) {
      return label.trim().toLowerCase().replace(/\s+/g, '_');
    },

    getMonthly() {
      return Number(this.form.monthly) || 0;
    },
    updateMonthly() {
      const match = this.salaries.find(
        (s) =>
          s.role_id === this.form.role_id &&
          s.branch_id === this.form.branch_id
      );
      this.form.monthly = match ? match.monthly_salary : 0;
    },

    formatRate(value) {
      return parseFloat(value).toFixed(2) + "x";
    },
    formatPercent(value) {
      return (parseFloat(value) * 100).toFixed(2) + "%";
    },
    formatCurrency(value) {
      const symbol = this.currencySymbol || '₱';
      const numeric = parseFloat(value) || 0;
      return symbol + ' ' + numeric.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    },
  },
};
</script>
<style>
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
    }

    input[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
    }
</style>
