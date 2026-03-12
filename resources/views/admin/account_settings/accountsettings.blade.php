@extends('admin.layout')

@section('page_title', 'Account Settings')

@section('content')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">
            @include('admin.account_settings.partials.accountsettings_profile')
        </div>

        <div class="lg:col-span-2">
            @include('admin.account_settings.partials.accountsettings_changepassword')
        </div>
    </div>

@endsection