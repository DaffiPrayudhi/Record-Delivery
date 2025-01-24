@extends('layouts.app')

@section('header')
    <h2 class="text-3xl font-semibold text-gray-800 dark:text-gray-200">
        {{ __('Dashboard') }}
    </h2>
@endsection

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Update Data</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item active">Update Data</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->
        
        <div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <table id="spareparts-table" class="table table-hover table-bordered table-responsive">
                    <thead class="table-header">
                        <tr>
                            <th>Nomor Transaksi</th>
                            <th>Tanggal</th>
                            <th>Model</th>
                            <th>Lot Number</th>
                            <th>Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label for="editNoTransaksi" class="form-label">Nomor Transaksi</label>
                        <input type="text" class="form-control" name="no_transaksi" id="editNoTransaksi" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTanggal" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="tgl_bln_thn" id="editTanggal" required>
                    </div>
                    <div class="mb-3">
                        <label for="editModel" class="form-label">Model</label>
                        <input type="text" class="form-control" name="part_number" id="editModel" required>
                    </div>
                    <div class="mb-3">
                        <label for="editLotNumber" class="form-label">Lot Number</label>
                        <input type="text" class="form-control" name="lot_number" id="editLotNumber" required>
                    </div>
                    <div class="mb-3">
                        <label for="editQty" class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="qty" id="editQty" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        let table = $('#spareparts-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('editdata.index') }}",
            columns: [
                { data: 'no_transaksi', name: 'no_transaksi' },
                { data: 'tgl_bln_thn', name: 'tgl_bln_thn' },
                { data: 'part_number', name: 'part_number' },
                { data: 'lot_number', name: 'lot_number' },
                { data: 'qty', name: 'qty' },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `
                            <button class="btn btn-sm btn-primary edit-btn" data-id="${row.id}"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}"><i class="fas fa-trash"></i></button>
                        `;
                    }
                }
            ],
            paging: true,
            searching: false,
            ordering: true,
            info: false,
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50],
            lengthChange: false
        });

        $('#spareparts-table').on('click', '.edit-btn', function () {
            let id = $(this).data('id');
            $.get("{{ route('editdata.index') }}/" + id, function (data) {
                $('#editId').val(data.id);
                $('#editNoTransaksi').val(data.no_transaksi);
                $('#editTanggal').val(data.tgl_bln_thn);
                $('#editModel').val(data.part_number);
                $('#editLotNumber').val(data.lot_number);
                $('#editQty').val(data.qty);
                $('#editModal').modal('show');
            });
        });

        $('#spareparts-table').on('click', '.delete-btn', function () {
            let id = $(this).data('id');
            if (confirm('Are you sure you want to delete this record?')) {
                $.ajax({
                    url: "{{ route('editdata.index') }}/" + id,
                    type: 'DELETE',
                    success: function (result) {
                        table.ajax.reload();
                    },
                    error: function () {
                        alert('Failed to delete the record.');
                    }
                });
            }
        });

        $('#editForm').submit(function (e) {
            e.preventDefault();
            let id = $('#editId').val();
            let formData = $(this).serialize();
            $.ajax({
                url: "{{ route('editdata.index') }}/" + id,
                type: 'PUT',
                data: formData,
                success: function () {
                    $('#editModal').modal('hide');
                    table.ajax.reload();
                },
                error: function () {
                    alert('Failed to update the record.');
                }
            });
        });
    });
</script>
@endsection

@section('style')
<style>
    .table-rs thead {
        background-color: #b3d9ff; 
        color: white;
    }

    .table-rs thead th {
        text-align: center;
    }
    .table-header th {
        color: #fff; 
        background-color: #2088ef;
    }

</style>
@endsection