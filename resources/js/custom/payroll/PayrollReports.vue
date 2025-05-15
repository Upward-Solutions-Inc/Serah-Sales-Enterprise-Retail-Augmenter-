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
                            <p class="text-muted mb-0">
                                Showing {{ startItem }} to {{ endItem }} of {{ reports.length }} items
                            </p>
                        </div>
                    </div>

                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#generatePayrollModal">
                        Generate Payroll
                    </button>
                </div>

                <!-- Progress Modal -->
                <div class="modal fade" id="progressModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                        <div class="modal-content p-4 text-center">
                            <div class="mb-3">
                                <div class="spinner-border text-primary mb-2" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <h6 class="font-weight-bold">Generating Payroll...</h6>
                            </div>
                            <div class="progress" style="height: 20px;">
                                <div 
                                    class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                                    role="progressbar"
                                    :style="{ width: progress + '%' }"
                                    aria-valuemin="0"
                                    aria-valuemax="100">
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
                            <button type="button" class="btn btn-primary" @click="generatePayroll" :disabled="isGenerating">Confirm</button>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- table -->
                <div class="table-responsive custom-scrollbar table-view-responsive shadow pt-primary position-relative">
                    <loader 
                    class="position-absolute w-100 h-100 d-flex justify-content-center align-items-center"
                    style="top: 0; left: 0; z-index: 1050;" 
                    :visible="isLoading" 
                    v-if="isLoading" 
                    />
                    <table v-else class="table table-striped table-borderless d-none d-md-table">
                        <thead>
                            <tr>
                            <th v-for="(label, index) in headers" :key="index" class="datatable-th pt-0 text-center fixed-col">
                                <span class="font-size-default">{{ label }}</span>
                            </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(report, index) in paginatedReports" :key="index" class="text-center">
                            <td class="fixed-col">{{ report.date_range }}</td>
                            <td class="fixed-col">{{ report.payroll_type }}</td>
                            <td class="fixed-col">{{ report.total_employees }}</td>
                            <td class="fixed-col">{{ formatCurrency(report.basic_pay) }}</td>
                            <td class="fixed-col">{{ formatCurrency(report.night_diff_pay) }}</td>
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
                                    <a class="dropdown-item" href="#" @click.prevent="bulkPrint(report.id)">Print</a>
                                    <a class="dropdown-item text-danger" href="#" @click.prevent="deleteReport(report)">Delete</a>
                                </div>
                                </div>
                            </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Mobile Card View -->
                    <div class="d-md-none mt-3">
                    <div
                        v-if="paginatedReports.length"
                        v-for="(report, index) in paginatedReports"
                        :key="index"
                        class="card mb-2 p-3 shadow-sm"
                    >
                        <div><strong>Date Range:</strong> {{ report.date_range }}</div>
                        <div><strong>Payroll Type:</strong> {{ report.payroll_type }}</div>
                        <div><strong>Employees:</strong> {{ report.total_employees }}</div>
                        <div><strong>Basic Pay:</strong> {{ formatCurrency(report.basic_pay) }}</div>
                        <div><strong>Night Diff. Pay:</strong> {{ formatCurrency(report.night_diff_pay) }}</div>
                        <div><strong>Overtime:</strong> {{ formatCurrency(report.overtime) }}</div>
                        <div><strong>Other Earnings:</strong> {{ formatCurrency(report.other_earnings) }}</div>
                        <div><strong>Other Deductions:</strong> {{ formatCurrency(report.other_deductions) }}</div>
                        <div><strong>SSS:</strong> {{ formatCurrency(report.sss) }}</div>
                        <div><strong>PhilHealth:</strong> {{ formatCurrency(report.philhealth) }}</div>
                        <div><strong>Pag-IBIG:</strong> {{ formatCurrency(report.pagibig) }}</div>
                        <div><strong>Income Tax:</strong> {{ formatCurrency(report.tax) }}</div>
                        <div><strong>Gross:</strong> {{ formatCurrency(report.gross) }}</div>
                        <div><strong>Deductions:</strong> {{ formatCurrency(report.total_deductions) }}</div>
                        <div><strong>Net:</strong> {{ formatCurrency(report.net) }}</div>
                        <div><strong>Generated By:</strong> {{ report.generated_by }}</div>
                        <div class="text-right mt-2">
                        <button class="btn btn-sm btn-outline-primary">Print</button>
                        <button class="btn btn-sm btn-outline-danger ml-2" @click.prevent="deleteReport(report)">Delete</button>
                        </div>
                    </div>
                    <div v-else class="text-center py-4">
                        <img src="/images/no_data.svg" alt="no data" class="mb-2" />
                        <p class="mb-0">Nothing to show here</p>
                    </div>
                    </div>

                    <div v-if="reports.length === 0" class="no-data-found-wrapper text-center p-primary">
                        <img src="/images/no_data.svg" alt="" class="mb-primary">
                        <p class="mb-0 text-center">Nothing to show here</p>
                        <p class="mb-0 text-center text-secondary font-size-90">
                            Please add a new entity or manage the data table to see the content here
                        </p>
                        <p class="mb-0 text-center text-secondary font-size-90">Thank you</p>
                    </div>
                </div>
                
                <!-- pagination -->
                <nav v-if="totalPages > 1" class="mt-3">
                    <ul class="pagination justify-content-end">
                        <li class="page-item" :class="{ disabled: currentPage === 1 }">
                            <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Prev</a>
                        </li>
                        <li class="page-item" 
                            v-for="page in totalPages" 
                            :key="page" 
                            :class="{ active: currentPage === page }">
                            <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                        </li>
                        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                            <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
                        </li>
                    </ul>
                </nav>

                <div ref="bulkPrintArea" style="display: none;">
                    <PayslipTemplate
                        v-for="(p, i) in bulkPayslips"
                        :key="i"
                        :company="p.company"
                        :employee="p.employee"
                        :payslip="p.payslip"
                    />
                </div>

            </div>
        </div>
    </div>
</template>
<script>
import Loader from '../components/Loader.vue'
import PayslipTemplate from '../components/PayslipTemplate.vue'
import Swal from "sweetalert2";
import flatpickr from 'flatpickr'
import 'flatpickr/dist/flatpickr.css'
import api, { PayrollReports } from '../api.js'
    export default {
    name: 'PayrollReports',
    components: {
        Loader,
        PayslipTemplate
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
            reports: [],
            selectAll: false,
            currentPage: 1,
            perPage: 10,
            bulkPayslips: [],
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
                'Total Night Diff. Pay',
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
            ]
        }
    },
    mounted() {
        this.isLoading = true
        this.fetchUsers()
        this.fetchReports()
        this.initEchoListener()

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
    },
    computed: {
        paginatedReports() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.reports.slice(start, start + this.perPage);
        },
        totalPages() {
            return Math.ceil(this.reports.length / this.perPage);
        },
        startItem() {
        return this.reports.length === 0 ? 0 : (this.perPage * (this.currentPage - 1)) + 1;
        },
        endItem() {
            return Math.min(this.startItem + this.paginatedReports.length - 1, this.reports.length);
        }
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

        initEchoListener() {
            Echo.channel('payroll-channel')
                .listen('.payroll.generated', (e) => {
                    console.log('Payroll broadcast received:', e.message);
                    this.fetchReports();
                    $('#progressModal').modal('hide');
                    clearInterval(this.progressInterval);
                    this.isGenerating = false;
                    this.progress = 100;
                });
        },

        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },

        fetchReports() {
            this.isLoading = true;
            api.get(PayrollReports.fetchData)
                .then(res => {
                    this.reports = res.data.map(r => ({
                        id: r.id,
                        date_range: r.date_range,
                        payroll_type: r.payroll_type,
                        total_employees: r.total_employees,
                        basic_pay: r.total_basic_pay,
                        night_diff_pay: r.total_night_differential,
                        overtime: r.total_overtime,
                        other_earnings: r.total_other_earnings,
                        other_deductions: r.total_other_deductions,
                        sss: r.total_sss,
                        philhealth: r.total_philhealth,
                        pagibig: r.total_pagibig,
                        tax: r.total_income_tax,
                        gross: r.total_gross,
                        total_deductions: r.total_deductions,
                        net: r.total_net,
                        generated_by: r.generated_by,
                    }));
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },


        generatePayroll() {
            this.errors.type = !this.selectedPayrollType;
            this.errors.date = !this.$refs.rangePicker.value;
            this.errors.users = this.selectedUsers.length === 0;
            if (this.errors.type || this.errors.date || this.errors.users) return;

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

            const [start, end] = this.$refs.rangePicker.value.split(' to ');

            api.post(PayrollReports.generate, {
                payroll_type: this.selectedPayrollType,
                start_date: start,
                end_date: end,
                user_ids: this.selectedUsers
            }).then((res) => {
                this.progress = 100;
                clearInterval(this.progressInterval);
                this.isGenerating = false;

                setTimeout(() => {
                    $('#progressModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Payroll generated successfully.'
                    });
                }, 500);
            }).catch(error => {
                clearInterval(this.progressInterval);
                this.isGenerating = false;
                this.progress = 0;
                $('#progressModal').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.message || 'Failed to generate payroll.'
                });
            });
        },

        deleteReport(report) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will delete the payroll report and all linked payslips.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    api.post(PayrollReports.delete, { id: report.id })
                        .then(() => {
                            this.fetchReports();
                            Swal.fire('Deleted!', 'The payroll report has been removed.', 'success');
                        })
                        .catch(() => {
                            Swal.fire('Error', 'Failed to delete payroll report.', 'error');
                        });
                }
            });
        },

        bulkPrint(reportId) {
            api.get(`/payroll/reports/view-json/${reportId}`).then(res => {
                const payslips = res.data.payslips;
                const company = res.data.company;

                this.bulkPayslips = payslips.map(p => ({
                    company: {
                        name: company.name,
                        logo: company.logo ? `${window.location.origin}${company.logo}` : null
                    },
                    employee: {
                        name: p.employee_name,
                        classification: p.role,
                        branch: p.branch,
                        payroll_type: p.payroll_type
                    },
                    payslip: {
                        date_range: p.date_range,
                        start_date: p.date_start,
                        end_date: p.date_end,
                        pay_date: p.pay_date,
                        hourly_rate: p.hourly_rate,
                        annual_salary: p.annual_salary,
                        night_diff: p.night_diff,
                        sss: p.sss,
                        pagibig: p.pagibig,
                        philhealth: p.philhealth,
                        income_tax: p.income_tax,
                        annual_leave: p.annual_leave,
                        sick_leave: p.sick_leave,
                        entitlements: [
                            { description: 'Basic Pay', total: p.basic_pay },
                            { description: 'Overtime Pay', total: p.overtime_pay },
                            { description: 'Night Differential', total: p.night_diff },
                            ...(p.earnings || [])
                        ],
                        deductions: [
                            { description: 'Income Tax', total: p.income_tax },
                            { description: 'SSS', total: p.sss },
                            { description: 'Pagibig', total: p.pagibig },
                            { description: 'Philhealth', total: p.philhealth },
                            ...(p.deductions || [])
                        ]
                    }
                }));

                this.$nextTick(() => {
                const printContents = this.$refs.bulkPrintArea.innerHTML;
                const style = `
                    <style>
                    body { font-family: Arial, sans-serif; padding: 30px; }
                    .text-center { text-align: center; }
                    .text-right { text-align: right; }
                    .font-small { font-size: 13px; }
                    .font-weight-bold { font-weight: bold; }

                    .payslip-wrapper {
                        page-break-after: always;
                    }

                    .payslip-header {
                        text-align: center;
                        margin-bottom: 35px;
                    }
                    h3 {
                        margin: 5px 0 !important;
                    }
                    .payslip-header img {
                        width: 100px;
                        height: auto;
                        margin-bottom: 10px;
                    }

                    .section-title {
                        font-weight: bold;
                        border-bottom: 1px solid #000;
                        margin-top: 20px;
                        margin-bottom: 8px;
                        padding-bottom: 4px;
                    }

                    .payslip-info {
                        display: flex;
                        justify-content: space-between;
                        gap: 200px;
                        margin-bottom: 16px;
                        font-size: 14px;
                        line-height: 1.4;
                    }
                    .payslip-info > div {
                        width: 48%;
                        line-height: 1.4;
                    }

                    .line-row {
                        display: flex;
                        justify-content: space-between;
                        font-size: 14px;
                        padding: 3px 0;
                    }

                    .line-row.font-weight-bold {
                        background-color: #f2f2f2 !important;
                        border-radius: 4px;
                    }

                    .signature {
                        margin-top: 50px;
                        display: flex;
                        justify-content: space-between;
                        font-size: 14px;
                    }

                    .signature div {
                        width: 48%;
                        text-align: center;
                    }
                    </style>
                `;
                const win = window.open('', '', 'width=1000,height=800');
                win.document.write(`<html><head><title>Payslips</title>${style}</head><body>${printContents}</body></html>`);
                win.document.close();
                win.focus();
                win.print();
                win.close();
                });
            });
        }
    }
}
</script>  
<style>
    .fixed-col {
        min-width: 150px;
        white-space: nowrap;
    }
    .card div {
        font-size: 14px;
        margin-bottom: 3px;
    }
</style>