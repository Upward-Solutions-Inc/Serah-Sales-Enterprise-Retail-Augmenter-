<template>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-3">
                <h4>Payslip</h4>
            </div>
        </div>
    
        <!-- Table Placeholder -->
        <div class="col-lg-10 mt-2">
            <div class="datatable">
            <div class="my-2 d-flex justify-content-between">
                <div class="d-flex align-items-center">
                <p class="text-muted mb-0">Showing 0 to 0 items of 0</p>
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
    export default {
    name: 'PayrollPayslip',
    components: {
        Loader
    },
    data() {
        return {
        isLoading: false,

        currentDateTime: '',

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

        setTimeout(() => {
        this.updateTime()
        this.isLoading = false
        }, 2000)
    },
    methods: {
        formatCurrency(value) {
        return `₱ ${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2 })}`
        },

        updateTime() {
        const now = new Date()
        const options = {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
        }
        this.currentDateTime = now.toLocaleString('en-US', options)
        }
    }
    }
</script>  