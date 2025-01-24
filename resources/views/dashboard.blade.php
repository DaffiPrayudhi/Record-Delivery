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
                    <h4 class="mb-sm-0 font-size-18">Record Delivery</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item active">Record Delivery</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- <div class="row mb-3">
            <div class="col-md-6">
                <form id="filter-form" class="form-inline">
                    <div class="form-group mr-3">
                        <label for="status-filter" class="mr-2">Filter :</label>
                        <select id="status-filter" class="form-control">
                            <option value="">All Status</option>
                            <option value="OK">OK</option>
                            <option value="DANGER">DANGER</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-right">
                
            </div>
        </div> -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h1>Transaksi ID</h1>
                        <table id="record-table" class="table table-hover table-bordered table-responsive">
                            <thead class="table-header">
                                <tr>
                                    <th>No Transaksi</th>
                                    <th>Tanggal Preparation</th>
                                    <th>Tanggal Delivery</th>
                                    <th>Model</th>
                                    <th>Part Name</th>
                                    <th>Part Number</th>
                                    <th>Tipe Delivery</th>
                                    <th>Plant Destination</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h1>Transaksi Detail ID</h1>
                        <table id="spareparts-table" class="table table-hover table-bordered table-responsive">
                            <thead class="table-header">
                                <tr>
                                    <th>No Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Model</th>
                                    <th>Part Name</th>
                                    <th>Part Number</th>
                                    <th>Lot Number</th>
                                    <th>Tipe Delivery</th>
                                    <th>Plant Destination</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row-->                        
    </div>
    <!-- container-fluid -->
</div>
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

@section('scripts')

<script>
   $(document).ready(function() {
       var table = $('#record-table').DataTable({
           processing: true,
           serverSide: true,
           ajax: {
               url: '{{ route('getrecord.data') }}',
               type: 'GET'
           },
           columns: [
               { data: 'no_transaksi', name: 'no_transaksi' },
               { data: 'tgl_bln_thn', name: 'tgl_bln_thn' },
               { data: 'tgl_bln_thn_dlv', name: 'tgl_bln_thn_dlv' },
               { data: 'model', name: 'model' },
               { data: 'part_name', name: 'part_name' },
               { data: 'part_number', name: 'part_number' },
               { data: 'tipe_delv', name: 'tipe_delv' },
               { data: 'plant_dest', name: 'plant_dest' },
               { data: 'qty', name: 'qty' },
               { 
                   data: 'status', 
                   name: 'status',
                   render: function(data, type, row) {
                       if (data === 'Proses') {
                           return '<span style="color: red; font-weight: bold;">' + data + '</span>';
                       } else if (data === 'Berhasil') {
                           return '<span style="color: green; font-weight: bold;">' + data + '</span>';
                       }
                       return data;
                   }
               }
           ],
           paging: true,
           searching: true,
           ordering: true,
           info: false,
           pageLength: 5,
           lengthMenu: [5, 10, 25, 50],
           lengthChange: false
       });
   });
</script>

<script>
   $(document).ready(function() {
    var table = $('#spareparts-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('getspareparts.data') }}',
            type: 'GET'
        },
        columns: [
            { data: 'no_transaksi', name: 'no_transaksi'}, 
            { data: 'tgl_bln_thn', name: 'tgl_bln_thn'}, 
            { data: 'model', name: 'model'}, 
            { data: 'part_name', name: 'part_name'},         
            { data: 'part_number', name: 'part_number'},     
            { data: 'lot_number', name: 'lot_number'},       
            { data: 'tipe_delv', name: 'tipe_delv'},       
            { data: 'plant_dest', name: 'plant_dest'},    
            { data: 'qty', name: 'qty'}
        ],
        paging: true,
        searching: true,
        ordering: true,
        info: false,
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],
        lengthChange: false
    });
});
</script>

@endsection
