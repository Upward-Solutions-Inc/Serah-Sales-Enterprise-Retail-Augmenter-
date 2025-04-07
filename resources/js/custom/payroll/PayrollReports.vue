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

                <!-- Progress Modal -->
                <div class="modal fade" id="progressModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content text-center p-4">
                        <h5 class="mb-3">Generating Payroll...</h5>
                        <div class="progress w-100">
                            <div class="progress-bar" role="progressbar" :style="{ width: progress + '%' }">
                            {{ progress }}%
                            </div>
                        </div>
                        </div>
                    </div>
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
                            <th v-for="(label, index) in headers" :key="index" class="datatable-th pt-0 text-center fixed-col">
                                <span class="font-size-default">{{ label }}</span>
                            </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(report, index) in reports" :key="index" class="text-center">
                            <td class="fixed-col">{{ report.date_range }}</td>
                            <td class="fixed-col">{{ report.payroll_type }}</td>
                            <td class="fixed-col">{{ report.total_employees }}</td>
                            <td class="fixed-col">{{ formatCurrency(report.basic_pay) }}</td>
                            <td class="fixed-col">{{ formatCurrency(report.allowance) }}</td>
                            <td class="fixed-col">{{ formatCurrency(report.overtime) }}</td>
                            <td class="fixed-col">{{ formatCurrency(report.other_earnings) }}</td>
                            <td class="fixed-col">{{ formatCurrency(report.other_deductions) }}</td>
                            <td class="fixed-col">{{ formatCurrency(report.sss) }}</td>
                            <td class="fixed-col">{{ formatCurrency(report.philhealth) }}</td>
                            <td class="fixed-col">{{ formatCurrency(report.pagibig) }}</td>
                            <td class="fixed-col">{{ formatCurrency(report.tax) }}</td>
                            <td class="fixed-col">{{ formatCurrency(report.gross) }}</td>
                            <td class="fixed-col">{{ formatCurrency(report.total_deductions) }}</td>
                            <td class="fixed-col">{{ formatCurrency(report.net) }}</td>
                            <td class="fixed-col">{{ report.generated_by }}</td>
                            <td class="fixed-col">
                                <div class="dropdown">
                                <i class="fas fa-ellipsis-v" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor: pointer;"></i>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#" @click.prevent="showPayrollDetails(report)">View</a>
                                    <a class="dropdown-item" href="#">Print</a>
                                    <a class="dropdown-item text-danger" href="#">Delete</a>
                                </div>
                                </div>
                            </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="reports.length === 0" class="no-data-found-wrapper text-center p-primary">
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
import Swal from "sweetalert2";
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
                'Date Range',
                'Payroll Type',
                'No. of Employee',
                'Total Basic Pay',
                'Total Allowance',
                'Total Overtime',
                'Total Other Earnings',
                'Total Other Deductions',
                'Total SSS',
                'Total PhilHealth',
                'Total Pag-IBIG',
                'Total Income Tax',
                'Total Gross Pay',
                'Total Deductions',
                'Total Net Pay',
                'Generated by',
                'Action'
            ],

            reports: [
                {
                    date_range: '2025-03-01 to 2025-03-15',
                    payroll_type: 'Semi-Monthly',
                    total_employees: 5,
                    basic_pay: 25000,
                    allowance: 5000,
                    overtime: 1200,
                    other_earnings: 800,
                    other_deductions: 500,
                    sss: 1500,
                    philhealth: 750,
                    pagibig: 500,
                    tax: 2000,
                    gross: 32000,
                    total_deductions: 5250,
                    net: 26750,
                    generated_by: 'Admin',
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

            $('#generatePayrollModal').modal('hide');
            $('#progressModal').modal('show');
            this.isGenerating = true;
            this.progress = 0;

            const total = this.selectedUsers.length;

            this.progressInterval = setInterval(() => {
                if (this.progress < 95) {
                this.progress += Math.floor((100 / total) * 0.5);
                }
            }, 500);

            api.post(PayrollReports.generate, {
                payroll_type: this.selectedPayrollType,
                start_date: this.$refs.rangePicker.value.split(' to ')[0],
                end_date: this.$refs.rangePicker.value.split(' to ')[1],
                user_ids: this.selectedUsers
            }).then((res) => {
                console.log('API response:', res); 
                $('#progressModal').modal('hide');
                clearInterval(this.progressInterval);
                this.progress = 100;
                setTimeout(() => {
                    this.isGenerating = false;
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Payroll generated successfully.'
                    });
                }, 600);
            }).catch(error => {
                $('#progressModal').modal('hide');
                clearInterval(this.progressInterval);
                this.isGenerating = false;
                this.progress = 0;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.message || 'Failed to generate payroll.'
                });
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
<style>
    .fixed-col {
        min-width: 150px;
        white-space: nowrap;
    }
</style>