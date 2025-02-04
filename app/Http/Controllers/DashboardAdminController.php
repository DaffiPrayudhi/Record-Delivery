<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\M_Qty;
use Yajra\DataTables\Facades\DataTables;
use App\Imports\SparepartsImports;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ErrorLogExport;
use App\Exports\ErrorLogRchExport;
use App\Exports\DashboardTransaksi;
use App\Exports\DashboardTransaksiDetail;
use App\Exports\DashboardRecehTransaksi;
use App\Exports\DashboardRecehTransaksiDetail;
use DB;

class DashboardAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        return view('dashboardadmin');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboardrecehadmin');
    }

    public function errorview()
    {
        return view('dashboarderror');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    
    
    }
    
}
