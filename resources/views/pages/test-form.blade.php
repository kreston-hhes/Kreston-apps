@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Test Page" />

    <div class="p-6">

        <h1 class="text-2xl font-bold mb-6">
            Test Form
        </h1>

        {{-- ALERT --}}
        @if (session('success'))
            <div class="mb-4 rounded bg-green-100 p-4">

                <p><strong>Name:</strong>
                    {{ session('success.name') }}
                </p>

                <p><strong>Category:</strong>
                    {{ session('success.category') }}
                </p>

            </div>
        @endif

        <form method="POST" action="/test-form">

            @csrf

            <div class="mb-4">


                <x-test.text-input name="name" label="Name" placeholder="Input name" />

            </div>

            <div class="mb-4">



                <x-test.text-select name="category" label="Category">

                    <option value="">
                        Select Category
                    </option>

                    <option value="Laptop">
                        Laptop
                    </option>

                    <option value="Mouse">
                        Mouse
                    </option>

                    <option value="Printer">
                        Printer
                    </option>

                </x-test.text-select>

            </div>

            <button type="submit" class="rounded bg-blue-500 px-4 py-2 text-white">
                Submit
            </button>

        </form>

    </div>
@endsection
