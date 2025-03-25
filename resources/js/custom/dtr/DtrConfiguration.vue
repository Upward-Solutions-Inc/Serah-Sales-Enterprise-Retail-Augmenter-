<template>
    <div class="content-wrapper position-relative">
        <loader 
            class="position-absolute w-100 h-100 d-flex justify-content-center align-items-center"
            style="top: 0; left: 0; z-index: 1050;" 
            :visible="isLoading" 
            v-if="isLoading" 
        />

        <div class="row">
            <div class="col-sm-3">
                <h4>Schedule</h4>
            </div>
        </div>

        <div class="container mt-4">
            <h5>DTR Schedule</h5>
            <form @submit.prevent="submitForm">
                <!-- Morning Shift -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Morning Shift Start</label>
                            <input v-model="form.morning_shift_start" name="morning_shift_start" type="text" class="form-control timepicker" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Morning Shift End</label>
                            <input v-model="form.morning_shift_end" name="morning_shift_end" type="text" class="form-control timepicker" />
                        </div>
                    </div>
                </div>

                <!-- Afternoon Shift -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Afternoon Shift Start</label>
                            <input v-model="form.afternoon_shift_start" name="afternoon_shift_start" type="text" class="form-control timepicker" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Afternoon Shift End</label>
                            <input v-model="form.afternoon_shift_end" name="afternoon_shift_end" type="text" class="form-control timepicker" />
                        </div>
                    </div>
                </div>

                <!-- Night Shift -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Night Shift Start</label>
                            <input v-model="form.night_shift_start" name="night_shift_start" type="text" class="form-control timepicker" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Night Shift End</label>
                            <input v-model="form.night_shift_end" name="night_shift_end" type="text" class="form-control timepicker" />
                        </div>
                    </div>
                </div>

                <!-- Grace Period & Overtime -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Grace Period (Minutes)</label>
                            <input v-model.number="form.grace_period" name="grace_period" type="text" class="form-control minutepicker" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Overtime Threshold (Minutes)</label>
                            <input v-model.number="form.overtime" name="overtime" type="text" class="form-control minutepicker" />
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary mr-2" @click="cancel">Cancel</button>
                    <button type="submit" class="btn btn-success d-flex align-items-center justify-content-center" :disabled="isSaving" style="min-width: 120px;">
                        <loader class="d-flex align-items-center justify-content-center h-100" v-if="isSaving"/>
                        <span v-else>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
  
  <script>
    import api, { DtrConfig } from '../api'
    import Swal from 'sweetalert2'
    import flatpickr from 'flatpickr'
    import Loader from '../components/Loader.vue'

    export default {
    name: 'DtrConfiguration',
    components: {
        Loader
    },
    data() {
        return {
        form: {},
        isSaving: false,
        isLoading: false,
        timeFields: [
            'morning_shift_start',
            'morning_shift_end',
            'afternoon_shift_start',
            'afternoon_shift_end',
            'night_shift_start',
            'night_shift_end'
        ],
        minuteFields: [
            'grace_period',
            'overtime'
        ]
        }
    },
    mounted() {
        this.loadForm()
    },
    methods: {
        loadForm() {
        this.isLoading = true
        return api.get(DtrConfig.fetch).then(res => {
            this.form = res.data || {}

            this.timeFields.forEach(field => {
            flatpickr(`input[name='${field}']`, {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'h:i K',
                time_24hr: false,
                defaultDate: this.form[field]
            })
            })

            this.minuteFields.forEach(field => {
            flatpickr(`input[name='${field}']`, {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'i',
                time_24hr: true,
                defaultDate: this.form[field],
                defaultHour: 0,
                defaultMinute: this.form[field] ?? 5,
                minuteIncrement: 1
            })
            })
        }).finally(() => {
            this.isLoading = false
        })
        },
        submitForm() {
        this.isSaving = true
        api.post(DtrConfig.store, this.form)
            .then(res => {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: res.data.message,
                showConfirmButton: false,
                timer: 2000
            })
            })
            .finally(() => {
            this.isSaving = false
            })
        },
        cancel() {
            this.form = {}
            this.loadForm()
        }
    }
    }
</script>

<style scoped>
    @import 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css';
</style>  