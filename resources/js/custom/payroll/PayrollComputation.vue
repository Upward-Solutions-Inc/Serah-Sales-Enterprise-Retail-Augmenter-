<template>
    <div class="content-wrapper">

        <div class="row">
            <div class="col-sm-3 mb-3">
                <h4>Computation</h4>
            </div>
        </div>

        <!-- Payroll Variables -->
        <div class="card p-3">
            <div class="row">
                <div class="col-md-12 mb-2">
                    <h5><i>Payroll Variables</i></h5>
                    <p class="text-muted small mt-2">
                        Note: All inputs should be in decimal format (e.g. 2% → 0.02)
                    </p>
                </div>

                <!-- BASIC PAY -->
                <div class="col-lg-4">
                    <label class="mb-4"><b>Basic Pay</b></label>
                    <div class="form-group">
                        <label> Regular Workday </label>
                        <input type="number" class="form-control" v-model.number="form.basic_workday" />
                    </div>
                    <div class="form-group">
                        <label> Regular Restday </label>
                        <input type="number" class="form-control" v-model.number="form.basic_restday" />
                    </div>
                    <div class="form-group">
                        <label> Regular Holiday </label>
                        <input type="number" class="form-control" v-model.number="form.basic_holiday" />
                    </div>
                </div>

                <!-- OVERTIME -->
                <div class="col-lg-4">
                    <label class="mb-4"><b>Overtime</b></label>
                    <div class="form-group">
                        <label> Regular Overtime </label>
                        <input type="number" class="form-control" v-model.number="form.ot_regular" />
                    </div>
                    <div class="form-group">
                        <label> Rest Day Overtime </label>
                        <input type="number" class="form-control" v-model.number="form.ot_restday" />
                    </div>
                    <div class="form-group">
                        <label> Holiday Overtime </label>
                        <input type="number" class="form-control" v-model.number="form.ot_holiday" />
                    </div>
                </div>

                <!-- DEDUCTIONS -->
                <div class="col-lg-4">
                    <label class="mb-4"><b>Deductions</b></label>
                    <div class="form-group">
                        <label> Social Security System </label>
                        <input type="number" class="form-control" v-model.number="form.sss" />
                    </div>
                    <div class="form-group">
                        <label> PhilHealth </label>
                        <input type="number" class="form-control" v-model.number="form.philhealth" />
                    </div>
                    <div class="form-group">
                        <label> Pagibig </label>
                        <input type="number" class="form-control" v-model.number="form.pagibig" />
                    </div>
                </div>
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
                <div class="col-md-6 mb-3">
                    <label>Desgination</label>
                    <select class="form-control" v-model="form.designation">
                        <option>Manager</option>
                        <option>Cashier</option>
                        <option>Waiter</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Branch</label>
                    <select class="form-control" v-model="form.branch">
                        <option>Main</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Monthly</label>
                    <input type="number" class="form-control" v-model.number="form.monthly" />
                </div>
                <div class="col-md-6 mb-3">
                    <label>Semi-Monthly</label>
                    <input type="number" class="form-control" v-model.number="form.semi_monthly" />
                </div>
                <div class="col-md-6 mb-3">
                    <label>Daily</label>
                    <input type="number" class="form-control" v-model.number="form.daily" />
                </div>
                <div class="col-md-6 mb-3">
                    <label>Hourly</label>
                    <input type="number" class="form-control" v-model.number="form.hourly" />
                </div>
            </div>
        </div>


        <!-- G -->
        <div class="card p-3">
            <!-- Top Controls -->
            <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Supplementary Earnings</h5>
            <div>
                <button class="btn btn-sm btn-secondary mr-2" @click="editMode = !editMode">
                {{ editMode ? 'Hide Delete' : 'Edit' }}
                </button>
                <button class="btn btn-sm btn-primary" @click="showModal = true">
                + Add Earning
                </button>
            </div>
            </div>

            <!-- Dynamic Input Fields -->
            <div
            v-for="(item, index) in earnings"
            :key="index"
            class="form-group d-flex align-items-center"
            >
            <label class="mr-2 mb-0" style="min-width: 180px;">{{ item.label }}</label>
            <input
                type="number"
                class="form-control mr-2"
                v-model.number="item.amount"
                style="max-width: 200px;"
            />
            <button
                v-if="editMode"
                class="btn btn-sm btn-danger"
                @click="removeEarning(index)"
            >
                ✕
            </button>
            </div>

            <!-- Modal -->
            <div v-if="showModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content p-4">
                <h5 class="mb-3">Add Supplementary Earning</h5>
                <div class="form-group">
                    <label>Earning Name</label>
                    <input type="text" class="form-control" v-model="newEarning.label" />
                </div>
                <div class="form-group">
                    <label>Amount</label>
                    <input type="number" class="form-control" v-model.number="newEarning.amount" />
                </div>
                <div class="text-right">
                    <button class="btn btn-secondary mr-2" @click="showModal = false">Cancel</button>
                    <button class="btn btn-success" @click="saveEarning">Save</button>
                </div>
                </div>
            </div>
            </div>
        </div>

    </div>
  </template>
  
  <script>
  export default {
    name: 'PayrollComputation',
    data() {
      return {
        form: {
            // variables
            basic_workday: 0,
            basic_restday: 0,
            basic_holiday: 0,
            ot_regular: 0,
            ot_restday: 0,
            ot_holiday: 0,
            sss: 0,
            philhealth: 0,
            pagibig: 0,

            // basic pay
            designation: '',
            branch: '',
            monthly: '',
            semi_monthly: '',
            daily: '',
            hourly: '',

            // Supp Earnings
            editMode: false,
            showModal: false,
            earnings: [],
            newEarning: {
                label: '',
                amount: 0
            }
        }
      }
    },

    computed: {
        totalEarnings() {
        return this.form.earnings.reduce((sum, item) => sum + Number(item.amount || 0), 0);
        },
    },

    methods: {
        saveEarning() {
            if (this.newEarning.label && this.newEarning.amount >= 0) {
                this.earnings.push({ ...this.newEarning })
                this.newEarning.label = ''
                this.newEarning.amount = 0
                this.showModal = false
            }
        },
        removeEarning(index) {
            this.earnings.splice(index, 1)
        }
    }
  }
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