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
                <p class="text-muted mb-0">
                  Showing {{ startItem }} to {{ endItem }} of {{ payslips.length }} items
                </p>
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
                    <tr v-for="(payslip, index) in paginatedPayslips" :key="index" class="text-center">
                        <td>{{ payslip.date }}</td>
                        <td>{{ formatCurrency(payslip.basic_pay) }}</td>
                        <td>{{ formatCurrency(payslip.overtime_pay) }}</td>  
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

            <nav v-if="totalPages > 1" class="mt-3">
              <ul class="pagination justify-content-end">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Prev</a>
                </li>
                <li class="page-item" v-for="page in totalPages" :key="page" :class="{ active: currentPage === page }">
                  <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                  <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
                </li>
              </ul>
            </nav>

          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import Loader from '../components/Loader.vue'
  import api, { PayrollPayslip } from '../api.js'
  export default {
    name: 'PayrollPayslip',
    components: {
        Loader
    },
    props: {
      name: String,
      role: String,
      profilePicture: String
    },
    data() {
      return {
        isLoading: false,
        currentDateTime: '',
        currentPage: 1,
        perPage: 10,
        payslips: [],

        headers: [
            'Date Range',
            'Basic Pay',
            'Total Overtime Pay',
            'Total Allowance',
            'Total Deductions',
            'Gross Pay',
            'Net Pay',
            'Action'
        ],
      }
    },
    mounted() {
      this.isLoading = true
      this.fetchPayslips()
      this.updateTime();
    },
    computed: {
      paginatedPayslips() {
        const start = (this.currentPage - 1) * this.perPage;
        return this.payslips.slice(start, start + this.perPage);
      },
      totalPages() {
        return Math.ceil(this.payslips.length / this.perPage);
      },
      startItem() {
        return this.payslips.length === 0 ? 0 : (this.perPage * (this.currentPage - 1)) + 1;
      },
      endItem() {
        return Math.min(this.startItem + this.paginatedPayslips.length - 1, this.payslips.length);
      }
    },
    methods: {
      formatCurrency(value) {
        return `₱ ${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2 })}`
      },

      changePage(page) {
        if (page >= 1 && page <= this.totalPages) this.currentPage = page;
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
      },

      fetchPayslips() {
        this.isLoading = true
        api.get(PayrollPayslip.fetchUserPayslips).then(res => {
          this.payslips = res.data.map(p => ({
            date: p.date_range,
            basic_pay: p.basic_pay,
            overtime_pay: p.overtime_pay,
            allowance: p.allowance,
            deductions: parseFloat(p.sss) + parseFloat(p.pagibig) + parseFloat(p.philhealth) + parseFloat(p.income_tax) + (p.deductions ? p.deductions.reduce((sum, d) => sum + parseFloat(d.amount), 0) : 0),
            gross: p.gross,
            net: p.net,
          }))
        }).finally(() => {
          this.isLoading = false
        })
      }
    }
  }
  </script>  