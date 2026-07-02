@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Test Page" />

    <div class="p-6">



        {{-- ALERT --}}
        @if (session('success'))
            @php
                $successData = session('success');
                $selectedCategories = is_array($successData['category'] ?? null)
                    ? implode(', ', $successData['category'])
                    : $successData['category'] ?? '';
            @endphp

            <div class="mb-4 rounded bg-green-100 p-4">
                <p><strong>Name:</strong>
                    {{ $successData['name'] ?? '' }}
                </p>

                <p><strong>Categorys:</strong>
                    {{ $selectedCategories }}
                </p>

                <p><strong>Kota:</strong>
                    {{ $successData['kota'] ?? '' }}
                </p>

                <p><strong>Gender:</strong>
                    {{ $successData['gender'] ?? '' }}
                </p>
                <p><strong>Agreement:</strong> {{ $successData['agree'] ?? '' }}</p>
                <p><strong>Permissions:</strong>
                    {{ implode(', ', $successData['permissions'] ?? []) }}
                </p>
            </div>
        @endif

        <form method="POST" action="/test-form">

            @csrf

            <div class="mb-4">

                <x-form.text-input name="name" label="Name" placeholder="Input name" required
                    requiredMessage="Ga boleh kosong" />

            </div>

            <div class="mb-4">



                <x-form.select-input name="category[]" label="Category" required create="true" direction="desc"
                    requiredMessage="Pilih kategori dulu dong" maxItems="3">

                    <option value="">Multiple select</option>

                    <option value="Xenos">
                        Xenos
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

                </x-form.select-input>

            </div>



            <div class="mb-4">



                <x-form.select-input name="kota" label="Kota" create="true" direction="asc" required
                    requiredMessage="Pilih kota dulu dong">

                    <option value="">Single select</option>

                    <option value="Tangerang">
                        Tangerang
                    </option>

                    <option value="Jakarta">
                        Jakarta
                    </option>

                    <option value="Bandung">
                        Bandung
                    </option>

                    <option value="Bogor">
                        Bogor
                    </option>

                </x-form.select-input>

            </div>


            <div class="mb-4">
                <x-form.radio-input name="gender" label="Gender" required :options="[
                    [
                        'value' => 'L',
                        'label' => 'Laki-laki',
                    ],
                    [
                        'value' => 'P',
                        'label' => 'Perempuan',
                    ],
                ]" />
            </div>


            <div class="mb-4">
                @php
                    $user = 'Administrator';
                @endphp
                <x-form.radio-input name="user" label="Auto Checked" required :options="[
                    [
                        'value' => 'user',
                        'label' => 'User',
                        'checked' => old('user', $user) == 'user',
                    ],
                    [
                        'value' => $user,
                        'label' => $user,
                        'checked' => old('user', $user) == $user,
                    ],
                    [
                        'value' => 'security',
                        'label' => 'Security',
                        'checked' => old('user', $user) == 'security',
                    ],
                ]" />
            </div>






            <div class="mb-4">
                <x-form.checkbox-input name="permissions[]" label="Permission" :options="[
                    [
                        'value' => 'create',
                        'label' => 'Create',
                    ],
                    [
                        'value' => 'edit',
                        'label' => 'Edit',
                    ],
                    [
                        'value' => 'delete',
                        'label' => 'Delete',
                    ],
                ]" />
            </div>



            <div class="mb-4">
                <x-form.text-area-input name="description" label="Deskripsi" placeholder="Masukkan deskripsi..." required />
            </div>

            {{-- Text Area With Row --}}
            <div class="mb-4">
                <x-form.text-area-input name="description" rows="8" placeholder="Text Area Rows 8" />
            </div>

            {{--  Text Area Edit Data --}}
            @php
                $description = 'ini Deskripsi dari database ceritanya';
            @endphp
            <div class="mb-4">
                <x-form.text-area-input label="Deskripsi" placeholder="Old Data" name="description" :value="$description" />
            </div>

            {{-- Text Area Resize
            none
vertical
horizontal
both
            --}}

            <div class="mb-4">
                <x-form.text-area-input label="Deskripsi" placeholder="Resize" name="description" resize="both" />
            </div>

            <div class="mb-4">
                <x-form.checkbox-input name="agree" label="Saya menyetujui syarat dan ketentuan" required
                    requiredMessage="Harap setuju" />
            </div>


            {{--
            Checkbox Edit Data
            <div class="mb-4">
                <x-form.checkbox-input name="permissions[]" label="Permission" :options="$permissions
                    ->map(
                        fn($permission) => [
                            'value' => $permission->id,
                            'label' => $permission->name,
                            'checked' => in_array(
                                $permission->id,
                                old('permissions', $role->permissions->pluck('id')->toArray()),
                            ),
                        ],
                    )
                    ->toArray()" />
            </div> --}}



            <button type="submit" class="rounded bg-blue-500 px-4 py-2 text-white">
                Submit
            </button>

        </form>

    </div>
@endsection
