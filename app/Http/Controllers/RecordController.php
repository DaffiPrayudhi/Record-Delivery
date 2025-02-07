<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Record;
use App\Models\RecordRch;
use App\Models\M_Model_Part;
use App\Models\M_Qty_pcs;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class RecordController extends Controller
{
    public function index()
    {
        
    }

    public function create()
    {
        $models = M_Model_Part::select('model')
                        ->whereNotNull('model')
                        ->distinct()
                        ->pluck('model');

        $plant_dests = M_Model_Part::select('plant_dest')
                        ->whereNotNull('plant_dest')
                        ->distinct()
                        ->pluck('plant_dest');  
                        
        $tipe_delvs = M_Model_Part::select('tipe_delv')
                        ->whereNotNull('tipe_delv')
                        ->distinct()
                        ->pluck('tipe_delv');  
        

        $picx = ['Iqbal', 'Nauval', 'Dandi', 'Bayu F', 'Eko', 'Putut'];
        
        $todayDate = date('Y-m-d');

        return view('data.record', compact('models','plant_dests','tipe_delvs','todayDate','picx'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'tgl_bln_thn'       => 'required|date',
            'tgl_bln_thn_dlv'   => 'required|date',
            'model'             => 'required|string',
            'qty_type'          => 'required|string',
            'qty'               => 'nullable|integer|required_if:qty_type,full',
            'qty_receh'         => 'nullable|integer|required_if:qty_type,receh',
        ]);

        return DB::transaction(function () use ($request, $validatedData) {

            $dateNow = now()->format('dmy');

            $lastTransaction = DB::table('record')
                ->select('no_transaksi')
                ->where('no_transaksi', 'like', 'AV' . $dateNow . '%')
                ->orderBy('no_transaksi', 'DESC')
                ->lockForUpdate() 
                ->first();

            $counter = '01'; 

            if ($lastTransaction) {
                $lastCounter = substr($lastTransaction->no_transaksi, 8); 
                $nextCounter = (int)$lastCounter + 1;
                $counter = str_pad($nextCounter, 2, '0', STR_PAD_LEFT);
            }

            $noTransaksi = 'AV' . $dateNow . $counter;

            $lastTransactionRch = DB::table('record_receh')
                ->select('no_transaksi')
                ->where('no_transaksi', 'like', 'RC' . $dateNow . '%')
                ->orderBy('no_transaksi', 'DESC')
                ->first();

            $counterRch = '01'; 

            if ($lastTransactionRch) {
                $lastCounterRch = substr($lastTransactionRch->no_transaksi, 8); 
                $nextCounterRch = (int)$lastCounterRch + 1; 
                $counterRch = str_pad($nextCounterRch, 2, '0', STR_PAD_LEFT);
            }

            $noTransaksiRch = 'RC' . $dateNow . $counterRch;
        
            if ($validatedData['qty_type'] === 'full') {
                if (!$request->has('qty') || empty($validatedData['qty'])) {
                    return redirect()->back()
                        ->withErrors(['qty' => 'Quantity Full harus diisi.'])
                        ->withInput();
                }
        
                $record = new Record([
                    'no_transaksi'      => $noTransaksi,
                    'tgl_bln_thn'       => $validatedData['tgl_bln_thn'],
                    'tgl_bln_thn_dlv'   => $validatedData['tgl_bln_thn_dlv'],
                    'model'             => $validatedData['model'],
                    'plant_dest'        => $request->input('plant_dest'),
                    'tipe_delv'         => $request->input('tipe_delv'),
                    'pic'               => $request->input('pic'),
                    'flag'              => 1,
                    'qty'               => $validatedData['qty'],
                    'qty_receh'         => 0,
                ]);

                $partNumbers = M_Model_Part::where('model', $validatedData['model'])
                                    ->distinct()
                                    ->pluck('part_number')
                                    ->toArray();
        
                if ($record->save()) {
                    $request->session()->put([
                        'no_transaksi'      => $noTransaksi,
                        'tgl_bln_thn'       => $validatedData['tgl_bln_thn'],
                        'tgl_bln_thn_dlv'   => $validatedData['tgl_bln_thn_dlv'],
                        'plant_dest'        => $request->input('plant_dest'),
                        'tipe_delv'         => $request->input('tipe_delv'),
                        'model'             => $validatedData['model'],
                        'qty'               => $validatedData['qty'],
                        'pic'               => $request->input('pic'),
                        'part_numbers'      => $partNumbers, 
                    ]);
            
                    return redirect()->route('delivery.create')
                        ->with('success', 'Data Quantity Full berhasil disimpan.');
                }
            }
            elseif ($validatedData['qty_type'] === 'receh') {
                if (!$request->has('qty_receh') || empty($validatedData['qty_receh'])) {
                    return redirect()->back()
                        ->withErrors(['qty_receh' => 'Quantity Receh harus diisi.'])
                        ->withInput();
                }
        
                $recordRch = new RecordRch([
                    'no_transaksi'      => $noTransaksiRch,
                    'tgl_bln_thn'       => $validatedData['tgl_bln_thn'],
                    'tgl_bln_thn_dlv'   => $validatedData['tgl_bln_thn_dlv'],
                    'model'             => $validatedData['model'],
                    'plant_dest'        => $request->input('plant_dest'),
                    'tipe_delv'         => $request->input('tipe_delv'),
                    'pic'               => $request->input('pic'),
                    'flag'              => 1,
                    'qty_receh'         => $validatedData['qty_receh'],
                    'qty'               => 0,
                ]);

                $partNumbers = M_Model_Part::where('model', $validatedData['model'])
                                    ->distinct()
                                    ->pluck('part_number')
                                    ->toArray();
        
                if ($recordRch->save()) {
                    $request->session()->put([
                        'no_transaksi'      => $noTransaksiRch,
                        'tgl_bln_thn'       => $validatedData['tgl_bln_thn'],
                        'tgl_bln_thn_dlv'   => $validatedData['tgl_bln_thn_dlv'],
                        'model'             => $validatedData['model'],
                        'plant_dest'        => $request->input('plant_dest'),
                        'tipe_delv'         => $request->input('tipe_delv'),
                        'pic'               => $request->input('pic'),
                        'flag'              => 1,
                        'qty_receh'         => $validatedData['qty_receh'],
                        'qty'               => 0,
                        'part_numbers'      => $partNumbers, 
                    ]);

                    return redirect()->route('deliveryrch.create')
                        ->with('success', 'Data Quantity Receh berhasil disimpan.');
                }
            }
        
            return redirect()->back()
                ->withErrors(['qty_type' => 'Terjadi kesalahan saat menyimpan data.'])
                ->withInput();
        });
    }

    public function show(Record $data)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Record $data)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Record $data)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Record $data)
    {
        //
    }

    public function getModelData($model)
    {
        $data = M_Model_Part::where('model', $model)->first(['plant_dest', 'pic']);
        return response()->json($data);
    }

    public function getPlantDest(Request $request)
    {
        $model = $request->input('model');
        $plant_dests = M_Model_Part::select('plant_dest')
            ->where('model', $model)
            ->whereNotNull('plant_dest')
            ->distinct()
            ->pluck('plant_dest');

        return response()->json($plant_dests);
    }

    


}
