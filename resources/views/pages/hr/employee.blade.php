@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Employee" />
<div class="w-full space-y-6">
        <x-common.component-card title="Employee List">
             <x-tables.basic-tables.basic-tables-one />
        </x-common.component-card>
    </div>
@endsection
