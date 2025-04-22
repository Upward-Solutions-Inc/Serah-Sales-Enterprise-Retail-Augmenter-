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
              <table v-else class="table table-striped table-borderless d-none d-md-table">
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
                        <td>{{ payslip.payroll_type }}</td>
                        <td>{{ formatCurrency(payslip.basic_pay) }}</td>
                        <td>{{ formatCurrency(payslip.overtime_pay) }}</td>
                        <td>{{ formatCurrency(payslip.night_diff) }}</td> 
                        <td>{{ formatCurrency(totalAllowance(payslip)) }}</td>
                        <td>{{ formatCurrency(totalDeductions(payslip)) }}</td>
                        <td>{{ formatCurrency(payslip.gross) }}</td>
                        <td>{{ formatCurrency(payslip.net) }}</td>
                        <td>
                            <div class="dropdown">
                                <i class="fas fa-ellipsis-v" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor: pointer;"></i>
                                <div class="dropdown-menu">
                                  <a class="dropdown-item" href="#" @click.prevent="printPayslip(payslip)">View</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
              </table>

              <!-- Mobile Card View -->
              <div class="d-md-none mt-3">
                <div v-if="paginatedPayslips.length" v-for="(p, index) in paginatedPayslips" :key="index" class="card mb-2 p-3 shadow-sm">
                  <div><strong>Date Range:</strong> {{ p.date }}</div>
                  <div><strong>Payroll Type:</strong> {{ p.payroll_type }}</div>
                  <div><strong>Basic Pay:</strong> {{ formatCurrency(p.basic_pay) }}</div>
                  <div><strong>Overtime Pay:</strong> {{ formatCurrency(p.overtime_pay) }}</div>
                  <div><strong>Night Differential:</strong> {{ formatCurrency(p.night_diff) }}</div>
                  <div><strong>Total Allowance:</strong> {{ formatCurrency(totalAllowance(p)) }}</div>
                  <div><strong>Total Deductions:</strong> {{ formatCurrency(p.deductions) }}</div>
                  <div><strong>Gross Pay:</strong> {{ formatCurrency(p.gross) }}</div>
                  <div><strong>Net Pay:</strong> {{ formatCurrency(p.net) }}</div>
                  <div class="text-right mt-2">
                    <button class="btn btn-sm btn-outline-secondary ml-2" @click.prevent="printPayslip(p)">View</button>
                  </div>
                </div>
                <div v-else class="text-center py-4">
                  <img src="/images/no_data.svg" alt="no data" class="mb-2" />
                  <p class="mb-0">Nothing to show here</p>
                </div>
              </div>


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

            <div ref="printArea" style="display: none;">
              <PayslipTemplate :company="printData.company" :employee="printData.employee" :payslip="printData.payslip" />
            </div>

          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import Loader from '../components/Loader.vue'
  import PayslipTemplate from '../components/PayslipTemplate.vue'
  import api, { PayrollPayslip } from '../api.js'
  export default {
    name: 'PayrollPayslip',
    components: {
        Loader,
        PayslipTemplate
    },
    props: {
      name: String,
      role: String,
      profilePicture: String,
      userId: Number
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
            'Payroll Type',
            'Basic Pay',
            'Overtime Pay',
            'Night Differential',
            'Total Allowance',
            'Total Deductions',
            'Gross Pay',
            'Net Pay',
            'Action'
        ],

        printData: {
          company: { name: 'NA Corp', number: 'NA', address: 'NA' },
          employee: { name: 'NA', status: 'NA', award: 'NA', classification: 'NA' },
          payslip: {
            hourly_rate: 0, annual_salary: 0, start_date: 'NA', end_date: 'NA', pay_date: 'NA',
            annual_leave: 'NA', sick_leave: 'NA',
            entitlements: [], deductions: [], bank: 'NA', account: 'NA'
          }
        }
      }
    },
    mounted() {
      this.isLoading = true
      this.fetchPayslips();
      this.updateTime();
      this.listenToPayslipBroadcast();
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
      listenToPayslipBroadcast() {
          Echo.channel(`payslip-channel.${this.userId}`)
              .listen('.payslip.generated', () => {
                  this.fetchPayslips();
              });
      },
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

      totalDeductions(payslip) {
        const base = [
          payslip.income_tax || 0,
          payslip.sss || 0,
          payslip.pagibig || 0,
          payslip.philhealth || 0
        ];
        const extras = (payslip.deductions || []).reduce((sum, d) => sum + (parseFloat(d.total) || 0), 0);
        return base.reduce((a, b) => a + b, 0) + extras;
      },

      totalAllowance(payslip) {
        const base = payslip.allowance || 0;
        const extra = (payslip.earnings || []).reduce((sum, e) => sum + (parseFloat(e.total) || 0), 0);
        return base + extra;
      },
      
      fetchPayslips() {
        this.isLoading = true
        api.get(PayrollPayslip.fetchUserPayslips).then(res => {
          this.companyName = res.data.company.name;
          this.companyLogo = res.data.company.logo;
          this.payslips = res.data.payslips.map(p => ({
            date: p.date_range,
            date_range: p.date_range,
            date_start: p.date_start,
            date_end: p.date_end,
            pay_date: p.pay_date,
            branch: p.branch,

            basic_pay: p.basic_pay,
            payroll_type: p.payroll_type,
            overtime_pay: p.overtime_pay,
            allowance: p.total_allowance,

            night_diff: p.night_diff,
            sss: p.sss,
            pagibig: p.pagibig,
            philhealth: p.philhealth,
            income_tax: p.income_tax,

            earnings: p.earnings,
            deductions: p.deductions,
            gross: p.gross,
            net: p.net,
          }))
        }).finally(() => {
          this.isLoading = false
        })
      },

      printPayslip(payslip) {
        this.printData.company = {
          name: this.companyName || 'NA',
          logo: this.companyLogo ? `${window.location.origin}${this.companyLogo}` : null
        };
        this.printData.employee = {
          name: this.name,
          classification: this.role,
          branch: payslip.branch || 'NA',
          payroll_type: payslip.payroll_type || 'NA'
        };

        const earnings = Array.isArray(payslip.earnings) ? payslip.earnings : [];
        const deductions = Array.isArray(payslip.deductions) ? payslip.deductions : [];
        const formattedRange = payslip.date_range || 'NA';
        this.printData.payslip = {
          ...this.printData.payslip,
          date_range: formattedRange,
          start_date: payslip.date_start || 'NA',
          end_date: payslip.date_end || 'NA',
          pay_date: payslip.pay_date || 'NA',
          hourly_rate: payslip.hourly_rate || 0,
          annual_salary: payslip.annual_salary || 0,
          night_diff: payslip.night_diff || 0,
          sss: payslip.sss || 0,
          pagibig: payslip.pagibig || 0,
          philhealth: payslip.philhealth || 0,
          income_tax: payslip.income_tax || 0,
          annual_leave: payslip.annual_leave || 'NA',
          sick_leave: payslip.sick_leave || 'NA',
          entitlements: [
            { description: 'Basic Pay', total: payslip.basic_pay },
            { description: 'Overtime Pay', total: payslip.overtime_pay },
            { description: 'Night Differential', total: payslip.night_diff },
            ...earnings
          ],
          deductions: [
          { description: 'Income Tax', total: payslip.income_tax },
            { description: 'SSS', total: payslip.sss },
            { description: 'Pagibig', total: payslip.pagibig },
            { description: 'Philhealth', total: payslip.philhealth },
            ...deductions
          ]
        };

        this.$nextTick(() => {
          const printContents = this.$refs.printArea.innerHTML;
          const style = `
            <style>
              body { font-family: Arial, sans-serif; padding: 30px; }
              .text-center { text-align: center; }
              .text-right { text-align: right; }
              .font-small { font-size: 13px; }
              .font-weight-bold { font-weight: bold; }

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

          const win = window.open('', '', 'width=1000,height=600');
          win.document.write(`<html><head><title>Payslip</title>${style}</head><body>${printContents}</body></html>`);
          win.document.close();
          win.focus();
          win.print();
          win.close();
        });
      }
    }
  }
  </script> 
  <style>
    .card div {
      font-size: 14px;
      margin-bottom: 3px;
    }
  </style> 