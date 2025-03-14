@extends('layout.tenant')

@section('title', __('Configuration'))

@section('contents')
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-3">
            <h4>Configuration</h4>
        </div>
    </div>

    <div class="container mt-4">
        <h5>DTR Configuration</h5>

        <!-- Tab Navigation -->
        <div class="row">
            <ul class="nav nav-tabs custom-tabs" id="dtrTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab">
                        General
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="specific-tab" data-toggle="tab" href="#specific" role="tab">
                        Specific
                    </a>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content mt-3">
            <!-- General Tab -->
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <form>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Grace Period (Morning Shift)</label>
                                <input type="time" class="form-control" value="08:05">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Morning Shift In</label>
                                <input type="time" class="form-control" value="08:00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Morning Shift Out</label>
                                <input type="time" class="form-control" value="12:00">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Grace Period (Afternoon Shift)</label>
                                <input type="time" class="form-control" value="13:05">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Afternoon Shift In</label>
                                <input type="time" class="form-control" value="13:00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Afternoon Shift Out</label>
                                <input type="time" class="form-control" value="17:00">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Surplus Time Considered As Overtime (Default 30 min)</label>
                                <input type="time" class="form-control" value="17:30">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Specific Tab -->
            <div class="tab-pane fade" id="specific" role="tabpanel">
                <p>Specific configuration settings go here...</p>
            </div>

            <!-- Action Buttons -->
            <div class="mt-3 text-right">
                <button type="button" class="btn btn-secondary">Cancel</button>
                <button type="button" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>


<script>

</script>
@endsection