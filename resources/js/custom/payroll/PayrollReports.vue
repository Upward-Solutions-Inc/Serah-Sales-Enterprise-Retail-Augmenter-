<template>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-3">
                <h4>Reports</h4>
            </div>
        </div>
    
        <!-- Table Placeholder -->
        <div class="col-lg-12 mt-2">
            <div class="datatable">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="my-2 d-flex justify-content-between">
                        <div class="d-flex align-items-center">
                        <p class="text-muted mb-0">Showing 0 to 0 items of 0</p>
                        </div>
                    </div>

                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#generatePayrollModal">
                        Generate Payroll
                    </button>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="generatePayrollModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Payroll Details</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Select Payroll Type</label>
                                <select class="form-control" v-model="selectedPayrollType">
                                    <option>Monthly</option>
                                    <option>Semi-Monthly</option>
                                    <option>Weekly</option>
                                </select>
                                <small v-if="errors.type" class="text-danger">Payroll type is required.</small>
                            </div>
                            <div class="form-group">
                                <label>Payroll Range</label>
                                <div class="d-flex">
                                    <input type="text" ref="rangePicker" class="form-control" />
                                    <button class="btn btn-light ml-2" @click="clearDate">Clear</button>
                                </div>
                                <small v-if="errors.date" class="text-danger">Date range is required.</small>
                            </div>
                            <div class="form-group">
                                <label>Select Users</label>
                                <div class="mb-2">
                                    <input type="checkbox" id="selectAll" v-model="selectAll" @change="toggleSelectAll">
                                    <label for="selectAll">Select All</label>
                                </div>
                                <div style="max-height: 150px; overflow-y: auto;">
                                    <div v-for="user in users" :key="user.id">
                                        <input type="checkbox" :id="'user-'+user.id" v-model="selectedUsers" :value="user.id">
                                        <label :for="'user-'+user.id">{{ user.id }} - {{ user.first_name }} {{ user.last_name }}</label>
                                    </div>
                                </div>
                                <small v-if="errors.users" class="text-danger d-block">Employee is required.</small>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" @click="generatePayroll">Confirm</button>
                        </div>
                        </div>
                    </div>
                </div>
                <div v-if="isGenerating" class="mb-3">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" :style="{ width: progress + '%' }">
                        {{ progress }}%
                        </div>
                    </div>
                </div>


                <div class="table-responsive custom-scrollbar table-view-responsive shadow pt-primary position-relative">
                    <loader 
                    class="position-absolute w-100 h-100 d-flex justify-content-center align-items-center"
                    style="top: 0; left: 0; z-index: 1050;" 
                    :visible="isLoading" 
                    v-if="isLoading" 
                    />
                    <table v-else class="table table-striped table-borderless">
                        <thead>
                            <tr>
                            <th v-for="(label, index) in headers" :key="index" class="datatable-th pt-0 text-center">
                                <span class="font-size-default">{{ label }}</span>
                            </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(payslip, index) in payslips" :key="index" class="text-center">
                                <td>{{ payslip.date }}</td>
                                <td>{{ payslip.employee }}</td>
                                <td>{{ formatCurrency(payslip.basic_pay) }}</td>
                                <td>{{ formatCurrency(payslip.allowance) }}</td>
                                <td>{{ formatCurrency(payslip.deductions) }}</td>
                                <td>{{ formatCurrency(payslip.gross) }}</td>
                                <td>{{ formatCurrency(payslip.net) }}</td>
                                <td>
                                    <div class="dropdown">
                                        <i class="fas fa-ellipsis-v" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor: pointer;"></i>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#">View</a>
                                            <a class="dropdown-item" href="#">Print</a>
                                            <a class="dropdown-item text-danger" href="#">Delete</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="payslips.length === 0" class="no-data-found-wrapper text-center p-primary">
                        <img src="/images/no_data.svg" alt="" class="mb-primary">
                        <p class="mb-0 text-center">Nothing to show here</p>
                        <p class="mb-0 text-center text-secondary font-size-90">
                            Please add a new entity or manage the data table to see the content here
                        </p>
                        <p class="mb-0 text-center text-secondary font-size-90">Thank you</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import Loader from '../components/Loader.vue'
import flatpickr from 'flatpickr'
import 'flatpickr/dist/flatpickr.css'
import api, { PayrollReports } from '../api.js'
    export default {
    name: 'PayrollReports',
    components: {
        Loader
    },
    data() {
        return {
            isGenerating: false,
            progress: 0,
            progressInterval: null,
            isLoading: false,
            picker: null,
            currentDateTime: '',
            selectedPayrollType: 'Monthly',
            users: [],
            selectedUsers: [],
            selectAll: false,
            errors: {
                type: false,
                date: false,
                users: false
            },

            headers: [
                'Date',
                'Employee',
                'Basic Pay',
                'Allowance',
                'Deductions',
                'Gross Pay',
                'Net Pay',
                'Action'
            ],

            payslips: [
                {
                    date: '2025-03-24',
                    employee: 'John Doe',
                    basic_pay: 500,
                    allowance: 100,
                    deductions: 50,
                    gross: 600,
                    net: 550,
                },
            ]
        }
    },
    mounted() {
        this.isLoading = true
        this.fetchUsers()

        this.picker = flatpickr(this.$refs.rangePicker, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            maxDate: 'today',
            onChange: () => {
                if (this.$refs.rangePicker.value) this.errors.date = false;
            }
        });

        $('#generatePayrollModal').on('hidden.bs.modal', () => {
            this.selectedUsers = [];
            this.selectAll = false;
            this.clearDate();
            this.selectedPayrollType = '';
            this.errors = { type: false, date: false, users: false };
        });

        this.isLoading = false
    },
    methods: {
        clearDate() {
            this.picker.clear();
        },

        formatCurrency(value) {
            return `₱ ${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2 })}`
        },

        fetchUsers() {
            api.get(PayrollReports.fetchUsers).then(res => { this.users = res.data })
        },
        toggleSelectAll() {
            this.selectedUsers = this.selectAll ? this.users.map(u => u.id) : []
        },

        generatePayroll() {
            this.errors.type = !this.selectedPayrollType;
            this.errors.date = !this.$refs.rangePicker.value;
            this.errors.users = this.selectedUsers.length === 0;
            if (this.errors.type || this.errors.date || this.errors.users) return;

            console.log('Payroll Type:', this.selectedPayrollType);
            console.log('Selected Date Range:', this.$refs.rangePicker.value);
            console.log('Selected Users:', this.selectedUsers);

            this.isGenerating = true;
            this.progress = 0;

            const total = this.selectedUsers.length;
            let count = 0;

            this.progressInterval = setInterval(() => {
                if (this.progress < 95) {
                this.progress += Math.floor((100 / total) * 0.5); // slow ramp
                }
            }, 500);

            api.post(PayrollReports.generate, {
                payroll_type: this.selectedPayrollType,
                start_date: this.$refs.rangePicker.value.split(' to ')[0],
                end_date: this.$refs.rangePicker.value.split(' to ')[1],
                user_ids: this.selectedUsers
            }).then(() => {
                clearInterval(this.progressInterval);
                this.progress = 100;
                setTimeout(() => {
                    this.isGenerating = false;
                    this.$toast.success('Payroll generated successfully.');
                }, 600);
            }).catch(error => {
                clearInterval(this.progressInterval);
                this.isGenerating = false;
                this.progress = 0;
                this.$toast.error(error.response?.data?.message || 'Failed to generate payroll.');
            });
        },
    },
    watch: {
        selectedUsers(val) {
            this.selectAll = val.length === this.users.length
        },
        selectedPayrollType(val) {
            if (val) this.errors.type = false;
        },
        'selectedUsers.length'(val) {
            if (val > 0) this.errors.users = false;
        }
    }
}
</script>  