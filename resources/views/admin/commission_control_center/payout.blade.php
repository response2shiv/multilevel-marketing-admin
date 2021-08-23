@extends('admin.layouts.main')

@section('main_content')
    <script>
        var baseUrl = '{{url('/')}}';
        var csrfToken = '{{csrf_token()}}';
    </script>
    <div class="m-content commission-control-center-page">
        <payout-page></payout-page>
    </div>
@endsection
@section('scripts')
    <script src="{{asset('/js/app.js')}}" type="text/javascript"></script>
@endsection
