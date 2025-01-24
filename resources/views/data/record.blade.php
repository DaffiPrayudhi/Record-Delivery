@extends('layouts.app')

@section('header')
    <h2 class="text-3xl font-semibold text-gray-800">
        {{ __('Form Input') }}
    </h2>
@endsection

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <h1>Input Record Delivery <button style="margin-left:553px;" type="button" class="btn btn-outline-primary" onclick="window.location.href='{{ route('delivery.create') }}'">Scan Sebelumnya</button></h1>  
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('record.store') }}" method="POST" id="dataForm" autocomplete="off">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tgl_bln_thn" class="form-label">Tanggal Preparation</label>
                                            <input 
                                                type="date" 
                                                class="form-control @error('tgl_bln_thn') is-invalid @enderror" 
                                                id="tgl_bln_thn" 
                                                name="tgl_bln_thn" 
                                                value="{{ old('tgl_bln_thn', $todayDate) }}" 
                                                required readonly>
                                            @error('tgl_bln_thn')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="tgl_bln_thn_dlv" class="form-label">Tanggal Delivery</label>
                                            <input type="date" class="form-control @error('tgl_bln_thn_dlv') is-invalid @enderror" id="tgl_bln_thn_dlv" name="tgl_bln_thn_dlv" value="{{ old('tgl_bln_thn_dlv') }}">
                                            @error('tgl_bln_thn_dlv')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="model" class="form-label">Model</label>
                                            <select class="form-select @error('model') is-invalid @enderror" id="model" name="model" required>
                                                <option value="" disabled selected>Select Model</option>
                                                @foreach($models as $model)
                                                    <option value="{{ $model }}" {{ old('model') == $model ? 'selected' : '' }}>
                                                        {{ $model }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('model')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="plant_dest" class="form-label">Plant Destination</label>
                                            <select class="form-select @error('plant_dest') is-invalid @enderror" id="plant_dest" name="plant_dest" required>
                                                <option value="" disabled selected>Select Plant</option>
                                            </select>
                                            @error('plant_dest')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tipe_delv" class="form-label">Tipe Delivery</label>
                                            <select class="form-select @error('tipe_delv') is-invalid @enderror" id="tipe_delv" name="tipe_delv" required>
                                                <option value="" disabled selected>Select Tipe Delivery</option>
                                                @foreach($tipe_delvs as $tipe_delv)
                                                    <option value="{{ $tipe_delv }}" {{ old('tipe_delv') == $tipe_delv ? 'selected' : '' }}>
                                                        {{ $tipe_delv }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('tipe_delv')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="qty_type" class="form-label">Pilih Tipe Quantity</label>
                                            <select class="form-select @error('qty_type') is-invalid @enderror" id="qty_type" name="qty_type" onchange="toggleQtyInput()" required>
                                                <option value="" disabled selected>Pilih Tipe</option>
                                                <option value="full" {{ old('qty_type') == 'full' ? 'selected' : '' }}>Full</option>
                                                <option value="receh" {{ old('qty_type') == 'receh' ? 'selected' : '' }}>Receh</option>
                                            </select>
                                            @error('qty_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Quantity input for Full -->
                                        <div class="mb-3" id="fullQuantityGroup" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <label for="qty" class="form-label">Quantity (Full)</label>
                                                    <input 
                                                        type="number" 
                                                        class="form-control @error('qty') is-invalid @enderror" 
                                                        id="qty" 
                                                        name="qty" 
                                                        value="{{ old('qty') }}">
                                                    @error('qty')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <button 
                                                        id="checkQtyBtn" 
                                                        style="margin-top: 28px" 
                                                        type="button" 
                                                        class="btn btn-outline-danger" 
                                                        onclick="checkQuantity()">Check</button>
                                                </div>
                                            </div>
                                            <div id="checkResult" style="margin-top: 8px;"></div>
                                        </div>

                                        <!-- Quantity input for Receh -->
                                        <div class="mb-3" id="recehQuantityGroup" style="display: none;">
                                            <label for="qty_receh" class="form-label">Quantity (Receh)</label>
                                            <input type="number" class="form-control @error('qty_receh') is-invalid @enderror" id="qty_receh" name="qty_receh" value="{{ old('qty_receh') }}">
                                            @error('qty_receh')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="pic" class="form-label">PIC</label>
                                            <select class="form-select @error('pic') is-invalid @enderror" id="pic" name="pic" required>
                                                <option value="" disabled selected>Select</option>
                                                @foreach($picx as $pic)
                                                    <option value="{{ $pic }}" {{ old('pic') == $pic ? 'selected' : '' }}>
                                                        {{ $pic }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('pic')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @csrf
                            <div class="form-actions">
                                <button type="button" onclick="resetForm()" class="btn btn-outline-danger">Reset</button>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                        <div id="toast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 10px; right: 10px; display: none;">
                            <div class="d-flex">
                                <div class="toast-body" id="toastMessage"></div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let isQuantityChecked = false;

function toggleQtyInput() {
    const qtyType = document.getElementById('qty_type').value;
    const fullGroup = document.getElementById('fullQuantityGroup');
    const recehGroup = document.getElementById('recehQuantityGroup');

    if (qtyType === 'full') {
        fullGroup.style.display = 'block';
        recehGroup.style.display = 'none';
        isQuantityChecked = false;
        document.getElementById('checkResult').innerHTML = '';
    } else if (qtyType === 'receh') {
        fullGroup.style.display = 'none';
        recehGroup.style.display = 'block';
    } else {
        fullGroup.style.display = 'none';
        recehGroup.style.display = 'none';
    }
}

function checkQuantity() {
    const qtyInput = document.getElementById('qty').value;
    const checkResult = document.getElementById('checkResult');

    if (!qtyInput) {
        checkResult.innerHTML = '<span style="color: red;">Please enter a quantity first.</span>';
        isQuantityChecked = false;
        return;
    }

    if (qtyInput % 5 === 0) {
        checkResult.innerHTML = '<span style="color: green;">Quantity is valid (multiple of 5).</span>';
        isQuantityChecked = true;
    } else {
        checkResult.innerHTML = '<span style="color: red;">Quantity is not a multiple of 5.</span>';
        isQuantityChecked = false;
    }
}

document.getElementById('dataForm').addEventListener('submit', function (e) {
    const qtyType = document.getElementById('qty_type').value;

    if (qtyType === 'full' && !isQuantityChecked) {
        e.preventDefault();
        alert('Please check the quantity before submitting.');
    }
});

document.addEventListener('DOMContentLoaded', toggleQtyInput);
</script>

<script>
    $(document).ready(function () {
        $('#model').change(function () {
            var model = $(this).val();
            var plantDestDropdown = $('#plant_dest');

            plantDestDropdown.empty();
            plantDestDropdown.append('<option value="" disabled selected>Select Plant</option>');

            if (model) {
                $.ajax({
                    url: "{{ route('record.getPlantDest') }}",
                    type: "POST",
                    data: {
                        model: model,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (data) {
                        $.each(data, function (key, value) {
                            plantDestDropdown.append('<option value="' + value + '">' + value + '</option>');
                        });
                    },
                    error: function () {
                        alert('Failed to fetch plant destinations.');
                    }
                });
            }
        });
    });
</script>

<script>
    function resetForm() {
        var form = document.getElementById('dataForm');
        form.reset();
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{{ session('success') }}',
                confirmButtonText: 'OK',
                showConfirmButton: true,
                timer: 4000 
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '{{ session('error') }}',
                confirmButtonText: 'OK',
                showConfirmButton: true,
                timer: 4000 
            });
        @endif
    });
</script>

<style>
    .form-actions {
        display: flex;
        justify-content: space-between; 
        margin-top: 20px; 
    }
    .search-point {
        position: absolute; 
        z-index: 1000; 
        background: white; 
        width: 96.5%; 
        max-height: 230px;
        overflow-y: hidden;
        overflow-x: hidden;
    }

    .search-point:hover {
        overflow-y: auto;
    }
</style>


@endsection
