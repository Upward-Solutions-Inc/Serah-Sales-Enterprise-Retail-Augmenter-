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
                
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Desgination</label>
                        <select class="form-control" v-model="form.designation">
                            <option>Manager</option>
                            <option>Cashier</option>
                            <option>Waiter</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Branch</label>
                        <select class="form-control" v-model="form.branch">
                            <option>Main</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Monthly</label>
                        <input type="number" class="form-control" v-model.number="form.monthly" />
                    </div>
                    <div class="form-group">
                        <label>Semi-Monthly</label>
                        <input type="number" class="form-control" :value="semiMonthly"/>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Daily</label>
                        <input type="number" class="form-control" :value="daily"/>
                    </div>
                    <div class="form-group">
                        <label>Hourly</label>
                        <input type="number" class="form-control" :value="hourly"/>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-3">
            <div class="row">
                <!-- Earnings -->
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5><i>Earnings</i></h5>
                        <div class="dropdown">
                            <i class="fas fa-ellipsis-v" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                style="cursor: pointer;"></i>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu">
                                <a class="dropdown-item" href="#" @click="editMode = !editMode">
                                <i :class="[editMode ? 'fas fa-eye-slash' : 'fas fa-eye']" class="mr-2"></i>
                                {{ editMode ? 'Hide' : 'Edit' }}
                                </a>
                                <a class="dropdown-item" href="#" @click="openModal('earning')" data-toggle="modal" data-target="#payrollModal">
                                <i class="fas fa-plus mr-2"></i> Add
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="form-group"  v-for="(item, index) in earnings" :key="index">
                        <label>{{ item.label }}</label>
                        <div class="d-flex align-items-center">
                            <input type="number" class="form-control" v-model.number="item.amount" />
                            <button v-if="editMode" class="btn btn-sm btn-danger ml-3" @click="removeEarning(index)">✕</button>
                        </div>
                    </div>
                </div>

                <!-- Deduction Section -->
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5><i>Deductions</i></h5>
                        <div class="dropdown">
                            <i class="fas fa-ellipsis-v" id="deductionDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                style="cursor: pointer;"></i>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="deductionDropdown">
                                <a class="dropdown-item" href="#" @click="deductionEditMode = !deductionEditMode">
                                <i :class="[deductionEditMode ? 'fas fa-eye-slash' : 'fas fa-eye']" class="mr-2"></i>
                                {{ deductionEditMode ? 'Hide' : 'Edit' }}
                                </a>
                                <a class="dropdown-item" href="#" @click="openModal('deduction')" data-toggle="modal" data-target="#payrollModal">
                                <i class="fas fa-plus mr-2"></i> Add
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" v-for="(item, index) in deductions" :key="index">
                        <label>{{ item.label }}</label>
                        <div class="d-flex align-items-center">
                            <input type="number" class="form-control" v-model.number="item.amount" />
                            <button v-if="deductionEditMode" class="btn btn-sm btn-danger ml-3" @click="removeDeduction(index)">✕</button>
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
                        <input type="number" class="form-control" :value="grossPay" readonly>
                    </div>
                    <div class="form-group">
                        <label>Total Deduction</label>
                        <input type="number" class="form-control" :value="totalDeduction" readonly>
                    </div>
                    <div class="form-group">
                        <label>Net Pay</label>
                        <input type="number" class="form-control font-weight-bold" :value="netPay" readonly>
                    </div>
                </div>


            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="payrollModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border rounded shadow-sm">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Add {{ modalType === 'earning' ? 'Earning' : 'Deduction' }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ modalType === 'earning' ? 'Earning' : 'Deduction' }} Name</label>
                            <input type="text" class="form-control" v-model="modalData.label" />
                        </div>
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" class="form-control" v-model.number="modalData.amount" />
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary mr-3" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" @click="savePayroll">Save</button>
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
                monthly: 0
            },

            modalType: '', // 'earning' or 'deduction'
            modalData: {
                label: '',
                amount: 0
            },
            earnings: [],
            deductions: [],
            editMode: false,
            deductionEditMode: false
        };
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
            return this.form.monthly ? (this.form.monthly / 22).toFixed(2) : 0; // Assuming 22 work days/month
        },
        hourly() {
            return this.form.monthly ? (this.form.monthly / 22 / 8).toFixed(2) : 0; // 8 hrs/day
        }
    },

    methods: {
        openModal(type) {
            this.modalType = type;
            this.modalData = {
                label: '',
                amount: 0
            };
        },

        savePayroll() {
            if (this.modalType === 'earning') {
                this.earnings.push({ ...this.modalData });
            } else {
                this.deductions.push({ ...this.modalData });
            }
            $('#payrollModal').modal('hide');
        },

        removeEarning(index) {
            this.earnings.splice(index, 1);
        },

        removeDeduction(index) {
            this.deductions.splice(index, 1);
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