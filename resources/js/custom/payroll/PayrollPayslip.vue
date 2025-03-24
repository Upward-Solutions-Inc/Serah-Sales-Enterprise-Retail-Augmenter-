<template>
    <div class="content-wrapper">
      <div class="row">
        <div class="col-sm-3">
          <h4>Payslip</h4>
        </div>
      </div>
  
      <div class="row no-gutter">
        <!-- Profile Card -->
        <div class="col-12 col-md-4 col-lg-2 mt-5">
          <div class="card text-center shadow pt-4">
            <h6 class="card-title">Employee Profile</h6>
            <div class="d-flex justify-content-center">
              <img :src="profilePicture" alt="Profile Picture"
                class="rounded-circle"
                style="width: 80px; height: 80px; object-fit: cover;">
            </div>
            <div class="card-body">
              <h6 class="mb-1">{{ name }}</h6>
              <p class="text-muted small">({{ role }})</p>
            </div>
            <div class="card-footer bg-transparent small text-muted">
              Today:&nbsp;{{ currentDateTime }}
            </div>
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
            <div class="table-responsive custom-scrollbar table-view-responsive shadow pt-primary">
              <table class="table table-striped table-borderless">
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
                                <a href="#" class="text-center" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
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
    </div>
  </template>
  
  <script>
  export default {
    name: 'PayrollPayslip',
    props: {
      name: String,
      role: String,
      profilePicture: String
    },
    data() {
      return {
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
      this.updateTime()
      setInterval(this.updateTime, 1000)
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