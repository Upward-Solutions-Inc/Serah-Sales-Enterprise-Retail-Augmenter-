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
        <div class="col-md-12 mb-3">
          <h5>
            <i>Basic Pay</i>
          </h5>
        </div>

        <div class="col-lg-4">
          <div class="form-group">
            <label>Desgination</label>
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
            <input type="number" class="form-control" v-model.number="form.monthly"/>
          </div>
          <div class="form-group">
            <label>Semi-Monthly</label>
            <input type="number" class="form-control" :value="semiMonthly" />
          </div>
        </div>

        <div class="col-lg-4">
          <div class="form-group">
            <label>Daily</label>
            <input type="number" class="form-control" :value="daily" />
          </div>
          <div class="form-group">
            <label>Hourly</label>
            <input type="number" class="form-control" :value="hourly" />
          </div>
        </div>
      </div>
    </div>

    <!-- Payroll Variables -->
    <div class="card p-3">
      <div class="row align-items-center justify-content-between">
        <div class="col-md-10 mb-2">
          <h5><i>Payroll Variables</i></h5>
          <p class="text-muted small mt-2">
            Note: All inputs should be in decimal format (e.g. 2% → 0.02)
          </p>
        </div>
        <div class="col-md-2 mb-2 text-right">
          <button
            v-if="editMode"
            class="btn btn-success btn-sm mr-2"
            @click="saveData"
          >
            Save
          </button>
          <button class="btn btn-primary btn-sm" @click="toggleEdit">
            {{ editMode ? "Cancel" : "Edit" }}
          </button>
        </div>

        <!-- WORK PAY -->
        <div class="col-lg-4">
          <label class="mb-4"><b>Work Pay</b></label>

          <div class="form-group">
            <label> Night Differential Pay </label>
            <div v-if="!editMode">
              <input type="text" class="form-control" :value="formatRate(form.nightpay)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.nightpay" />
            </div>
          </div>

          <div class="form-group">
            <label> Rest Day Pay</label>
            <div v-if="!editMode">
              <input type="text" class="form-control" :value="formatRate(form.basic_restday)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.basic_restday" />
            </div>
          </div>

          <div class="form-group">
            <label> Holiday Pay </label>
            <div v-if="!editMode">
              <input type="text" class="form-control" :value="formatRate(form.basic_holiday)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.basic_holiday" />
            </div>
          </div>
        </div>

        <!-- OVERTIME -->
        <div class="col-lg-4">
          <label class="mb-4"><b>Overtime Pay</b></label>

          <div class="form-group">
            <label> Regular Overtime </label>
            <div v-if="!editMode">
              <input type="text" class="form-control" :value="formatRate(form.ot_regular)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.ot_regular" />
            </div>
          </div>

          <div class="form-group">
            <label> Rest Day Overtime </label>
            <div v-if="!editMode">
              <input type="text" class="form-control" :value="formatRate(form.ot_restday)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.ot_restday" />
            </div>
          </div>

          <div class="form-group">
            <label> Holiday Overtime </label>
            <div v-if="!editMode">
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
            <div v-if="!editMode">
              <input type="text" class="form-control" :value="formatPercent(form.sss)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.sss" />
            </div>
          </div>

          <div class="form-group">
            <label> PhilHealth </label>
            <div v-if="!editMode">
              <input type="text" class="form-control" :value="formatPercent(form.philhealth)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.philhealth" />
            </div>
          </div>

          <div class="form-group">
            <label> Pagibig </label>
            <div v-if="!editMode">
              <input type="text" class="form-control" :value="formatPercent(form.pagibig)" readonly />
            </div>
            <div v-else>
              <input type="number" class="form-control" v-model.number="form.pagibig" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card p-3">
      <div class="row">
        <!-- Addtional Compensation -->
        <div class="col-md-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5><i>Addtional Compensation</i></h5>
            <div class="dropdown">
              <i
                class="fas fa-ellipsis-v"
                id="dropdownMenu"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
                style="cursor: pointer"
              ></i>

              <div
                class="dropdown-menu dropdown-menu-right"
                aria-labelledby="dropdownMenu"
              >
                <a class="dropdown-item" href="#" @click="editMode = !editMode">
                  <i
                    :class="[editMode ? 'fas fa-eye-slash' : 'fas fa-eye']"
                    class="mr-2"
                  ></i>
                  {{ editMode ? "Hide" : "Edit" }}
                </a>
                <a
                  class="dropdown-item"
                  href="#"
                  @click="openModal('earning')"
                  data-toggle="modal"
                  data-target="#payrollModal"
                >
                  <i class="fas fa-plus mr-2"></i> Add
                </a>
              </div>
            </div>
          </div>
          <div
            class="form-group"
            v-for="(item, index) in earnings"
            :key="index"
          >
            <label>{{ item.label }}</label>
            <div class="d-flex align-items-center">
              <input
                type="number"
                class="form-control"
                v-model.number="item.amount"
              />
              <button
                v-if="editMode"
                class="btn btn-sm btn-danger ml-3"
                @click="removeEarning(index)"
              >
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
              <i
                class="fas fa-ellipsis-v"
                id="deductionDropdown"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
                style="cursor: pointer"
              ></i>
              <div
                class="dropdown-menu dropdown-menu-right"
                aria-labelledby="deductionDropdown"
              >
                <a
                  class="dropdown-item"
                  href="#"
                  @click="deductionEditMode = !deductionEditMode"
                >
                  <i
                    :class="[
                      deductionEditMode ? 'fas fa-eye-slash' : 'fas fa-eye',
                    ]"
                    class="mr-2"
                  ></i>
                  {{ deductionEditMode ? "Hide" : "Edit" }}
                </a>
                <a
                  class="dropdown-item"
                  href="#"
                  @click="openModal('deduction')"
                  data-toggle="modal"
                  data-target="#payrollModal"
                >
                  <i class="fas fa-plus mr-2"></i> Add
                </a>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Income Tax</label>
            <input type="number" class="form-control" :value="incomeTax" readonly/>
          </div>
          <div class="form-group">
            <label>Social Security System</label>
            <input type="number" class="form-control" :value="sss" readonly/>
          </div>
          <div class="form-group">
            <label>PhilHealth</label>
            <input type="number" class="form-control" :value="philhealth" readonly/>
          </div>
          <div class="form-group">
            <label>Pagibig</label>
            <input type="number" class="form-control" :value="pagibig" readonly/>
          </div>


          <div
            class="form-group"
            v-for="(item, index) in deductions"
            :key="index"
          >
            <label>{{ item.label }}</label>
            <div class="d-flex align-items-center">
              <input
                type="number"
                class="form-control"
                v-model.number="item.amount"
              />
              <button
                v-if="deductionEditMode"
                class="btn btn-sm btn-danger ml-3"
                @click="removeDeduction(index)"
              >
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
            <input
              type="number"
              class="form-control"
              :value="grossPay"
              readonly
            />
          </div>
          <div class="form-group">
            <label>Total Deduction</label>
            <input
              type="number"
              class="form-control"
              :value="totalDeduction"
              readonly
            />
          </div>
          <div class="form-group">
            <label>Net Pay</label>
            <input
              type="number"
              class="form-control font-weight-bold"
              :value="netPay"
              readonly
            />
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
            <button class="btn btn-primary" @click="savePayroll">Save</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
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
      editMode: false,
      deductionEditMode: false,

      modalType: "",
      modalData: {
        label: "",
        amount: 0,
      },
      
      form: {
        // variables
        nightpay: 0,
        basic_restday: 0,
        basic_holiday: 0,
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

        // Map component values to form
        this.form.basic_workday = this.getValue("nightpay");
        this.form.basic_restday = this.getValue("restday");
        this.form.basic_holiday = this.getValue("holiday");
        this.form.ot_regular = this.getValue("ot_regular");
        this.form.ot_restday = this.getValue("ot_restday");
        this.form.ot_holiday = this.getValue("ot_holiday");
        this.form.sss = this.getValue("sss");
        this.form.philhealth = this.getValue("philhealth");
        this.form.pagibig = this.getValue("pagibig");

        this.form.role_id = savedRoleId ? parseInt(savedRoleId) : (this.roles[0]?.id || null);
        this.form.branch_id = savedBranchId ? parseInt(savedBranchId) : (this.branches[0]?.id || null);
        this.updateMonthly();
      })
      .catch((error) => console.error(error));
  },

  watch: {
    form: {
      handler(newVal) {
        localStorage.setItem("role_id", newVal.role_id || 0);
        localStorage.setItem("branch_id", newVal.branch_id || 0);
        this.updateMonthly();
      },
      deep: true,
    }
  },

  computed: {
    grossPay() {
      let total = 0;
      for (let item of this.earnings) {
        total += item.amount;
      }
      return total;
    },
    totalDeduction() {
      let total = 0;
      for (let item of this.deductions) {
        total += item.amount;
      }
      return total;
    },
    netPay() {
      return this.grossPay - this.totalDeduction;
    },

    semiMonthly() {
      return this.form.monthly ? (this.form.monthly / 2).toFixed(2) : 0;
    },
    daily() {
      return this.form.monthly ? (this.form.monthly / 22).toFixed(2) : 0;
    },
    hourly() {
      return this.form.monthly ? (this.form.monthly / 22 / 8).toFixed(2) : 0;
    },
  },

  methods: {
    getValue(code) {
      const item = this.components.find(c => c.code === code);
      return item ? item.value : 0;
    },
    formatRate(value) {
      return parseFloat(value).toFixed(2) + "x";
    },
    formatPercent(value) {
      return (parseFloat(value) * 100).toFixed(2) + "%";
    },
    toggleEdit() {
      this.editMode = !this.editMode;
    },


    updateMonthly() {
      const match = this.salaries.find(
        (s) =>
          s.role_id === this.form.role_id &&
          s.branch_id === this.form.branch_id
      );
      this.form.monthly = match ? match.monthly_salary : 0;
    },


    saveData() {
      // Send updated data to your controller
      api
        .post("/payroll/computation/update", this.form)
        .then(() => {
          this.editMode = false;
          // Optionally refetch data if needed
        })
        .catch((err) => console.error(err));
    },


    

    openModal(type) {
      this.modalType = type;
      this.modalData = {
        label: "",
        amount: 0,
      };
    },

    savePayroll() {
      if (this.modalType === "earning") {
        this.earnings.push({ ...this.modalData });
      } else {
        this.deductions.push({ ...this.modalData });
      }
      $("#payrollModal").modal("hide");
    },

    removeEarning(index) {
      this.earnings.splice(index, 1);
    },

    removeDeduction(index) {
      this.deductions.splice(index, 1);
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
