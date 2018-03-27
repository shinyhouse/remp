@extends('layouts.app')

@section('title', 'Edit campaign')

@section('content')

    <div class="c-header">
        <h2>Campaigns: {{ $campaign->name }}</h2>
    </div>

    <div class="container">
        @include('flash::message')

        {!! Form::model($campaign, ['route' => ['campaigns.update', $campaign], 'method' => 'PATCH']) !!}
        @include('campaigns._form', ['action' => 'edit'])
        {!! Form::close() !!}
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-12 col-lg-8">
                <div class="card z-depth-1-top">
                    <div class="card-header">
                        <h2>Scheduled runs<small></small></h2>
                    </div>
                    <div class="card-body">
                        {!! Widget::run('DataTable', [
                        'colSettings' => [
                            'campaign' => [
                                'header' => 'Campaign',
                            ],
                            'start_time' => [
                                'header' => 'Scheduled start date',
                                'render' => 'date',
                            ],
                            'end_time' => [
                                'header' => 'Scheduled end date',
                                'render' => 'date',
                            ],
                            'status' => [
                                'header' => 'Status',
                            ],
                        ],
                        'dataSource' => route('campaign.schedule.json', ['campaign' => $campaign]),
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection