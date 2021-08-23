@extends('admin.layouts.main')

@section('main_content')
<div class="m-content">
    <div class="m-portlet m-portlet--mobile">
        <div class="m-portlet__head">
            <div class="m-portlet__head-caption">
                <div class="m-portlet__head-title">
                    <h3 class="m-portlet__head-text">
                        API Tokens
                    </h3>
                </div>
            </div>
            <div class="m-portlet__head-tools">
                <a href="{{url('/new-api-token')}}" class="btn btn-danger btn-sm m-btn--air">Create new API token</a>
            </div>
        </div>
        <div class="m-portlet__body">
            <table class="table table-striped- table-bordered table-hover table-checkable" id="dt_api_token">
                <thead>
                    <tr>
                        <th>Token</th>
                        <th>Active</th>
                        <th>Generated On</th>
                        <th>Generated By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recs as $rec)
                    <tr>
                        <td>{{$rec->token}}</td>
                        <td>
                            @if($rec->is_active == 1)
                            <span class="m-badge m-badge--success m-badge--wide">Yes</span>
                            @else
                            <span class="m-badge m-badge--danger m-badge--wide">No</span>
                            @endif
                        </td>
                        <td>{{$rec->generated_on}}</td>
                        <td>{{$rec->firstname." ".$rec->lastname}}</td>
                        <td>
                            <a href="{{url('/api-token-toggle-active/'.$rec->id)}}" class="btn btn-danger btn-sm m-btn--air">Toggle Active</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection