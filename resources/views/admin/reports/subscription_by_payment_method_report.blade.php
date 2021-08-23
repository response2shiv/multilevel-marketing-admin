@extends('admin.layouts.main')

@section('main_content')
<div class="m-content">
    <div class="m-portlet m-portlet--mobile">
        <div class="m-portlet__head">
            <div class="m-portlet__head-caption">
                <div class="m-portlet__head-title">
                    <h3 class="m-portlet__head-text">
                        Subscription By Payment Methods
                    </h3>
                </div>
            </div>
            <form id="frmSubscriptionByPaymentMethodFilter" style="display: flex;">
                <div class="m-portlet__head-tools">
                    <input class="form-control m-input form-control-sm date_picker2" id="d_from" name="d_from" placeholder="From Date" value="{{ $from != null ? $from : "" }}" />&nbsp;
                    <input class="form-control m-input form-control-sm date_picker2" id="d_to" name="d_to" placeholder="To Date" value="{{ $to != null ? $to : "" }}" />&nbsp;
                    <button class="btn btn-info btn-sm m-btn--air" id="subscriptionByPMFiterBtn">View</button>&nbsp;
                    @if (\App\AdminPermission::export_reports())
                        <button class="btn btn-info btn-sm m-btn--air" id="exp_subscription_by_payment_method">Export</button>
                    @endif
                </div>
            </form>
        </div>
        <div class="m-portlet__body">
            <table class="table table-striped- table-bordered table-hover table-checkable" id="dt_subscripttion_by_payment_method">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Credit Card</th>
                        <th>E-Wallet</th>
                        <th>Admin</th>
                        <th>Bitpay</th>
                        <th>Skrill</th>
                        <th>Secondary CC</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection
