<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Record;
use App\Models\M_Qty;
use App\Models\M_Serial;
use App\Models\M_Qty_pcs;
use App\Models\RecordRch;
use App\Models\DeliveryRch;
use App\Models\Delivery;
use App\Models\RecordSmpn;
use Yajra\DataTables\DataTables;

use Exception;

class DeliveryRchController extends Controller
{
    public function index()
    {
        
    }

    public function create()
    {
        $noTransaksi = session('no_transaksi');
        $tglBlnThn = session('tgl_bln_thn');
        $plantDest = session('plant_dest');
        $tipeDelv = session('tipe_delv');
        $model = session('model');
        $qty = session('qty');
        $pic = session('pic');

        $totalQty = M_Qty::where('no_transaksi', $noTransaksi)->first();
        $totalQtyValue = $totalQty ? $totalQty->total_qty : 0;

        $totalQtyRcrd = Record::where('no_transaksi', $noTransaksi)->first();
        $totalQtyValueRcrd = $totalQtyRcrd ? $totalQtyRcrd->qty : 0;
        
        $qtyPcs = M_Qty_pcs::where('no_transaksi', $noTransaksi)->first()->qty_pcs ?? 0;
    
        return view('data.deliveryrch', compact('noTransaksi', 'tglBlnThn', 'plantDest', 'model', 'qty', 'pic','totalQtyValue','totalQtyValueRcrd','qtyPcs'));
    }

    public function createreceh()
    {
        $noTransaksi = session('no_transaksi');
        $tglBlnThn = session('tgl_bln_thn');
        $plantDest = session('plant_dest');
        $tipeDelv = session('tipe_delv');
        $model = session('model');
        $qty = session('qty');
        $pic = session('pic');

        $totalQty = DeliveryRch::where('no_transaksi', $noTransaksi)->first();
        $totalQtyValue = $totalQty ? $totalQty->qty : 0;

        $totalQtyRcrd = RecordRch::where('no_transaksi', $noTransaksi)->first();
        $totalQtyValueRcrd = $totalQtyRcrd ? $totalQtyRcrd->qty_receh : 0;
        
        $qtyPcs = M_Qty_pcs::where('no_transaksi', $noTransaksi)->first()->lot_number ?? 0;
        $RecordSmpn = RecordSmpn::where('no_transaksi', $noTransaksi)->first()->lot_number;
    
        return view('data.deliveryreceh', compact('noTransaksi', 'tglBlnThn', 'plantDest', 'model', 'qty', 'pic','totalQtyValue','totalQtyValueRcrd','qtyPcs','RecordSmpn'));
    }
    
    public function store(Request $request)
    {
        try {
            $noTransaksi = $request->input('no_transaksi');
            $lotNumber = $request->input('lot_number');
            $qty = $request->input('qty');

            $isDuplicateNotransaksi = RecordSmpn::where('no_transaksi', $noTransaksi)->exists();
            if ($isDuplicateNotransaksi) {
                return response()->json(['success' => false, 'message' => 'No Transaksi already exists in Delivery.'], 400);
            }

            // Check if lot_number exists in Delivery model
            $isDuplicateInDelivery = Delivery::where('lot_number', $lotNumber)->exists();
            if ($isDuplicateInDelivery) {
                return response()->json(['success' => false, 'message' => 'Lot number already exists in Delivery.'], 400);
            }

            // Check if lot_number exists in RecordSmpn model and if qty is smaller than session qty_receh
            $isExistInRecordSmpn = RecordSmpn::where('lot_number', $lotNumber)->exists();
            $qtyReceh = session('qty_receh', 0); // Get qty_receh from session
            if ($isExistInRecordSmpn && $qty < $qtyReceh) {
                return response()->json(['success' => false, 'message' => 'Quantity is smaller than session qty_receh.'], 400);
            }

            // Scan data validation
            $qrData = $request->input('qrcode');
            $dataArray = explode('|', $qrData);
            if (count($dataArray) < 4) {
                return response()->json(['success' => false, 'message' => 'Invalid QR data format!'], 400);
            }

            $noTransaksi = $request->input('no_transaksi') ?? 'RCH' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

            $partNumbersInSession = session('part_numbers',[]);
                $scannedPartNumbers = $dataArray[0];

                if (!in_array($scannedPartNumbers,$partNumbersInSession)){
                    return response()->json(['success' => false, 'message' => 'Part Number tidak sesuai'], 400);
                }

            // After validation, save the data in RecordSmpn model instead of Delivery
            $validatedData = [
                'no_transaksi' => $noTransaksi,
                'tgl_bln_thn' => now(),
                'model' => session('model'),
                'qty' => $dataArray[2],
                'lot_number' => $dataArray[3],
                'flag' => 1,
                'plant_dest' => session('plant_dest'),
                'tipe_delv' => session('tipe_delv'),
                'pic' => session('pic'),
            ];

            // Create a new record in RecordSmpn
            $record = new RecordSmpn($validatedData);
            if ($record->save()) {
                return response()->json(['success' => true, 'message' => 'Data successfully saved.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Failed to save data'], 500);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storereceh(Request $request)
    {
        try {
            $noTransaksi = $request->input('no_transaksi');
            if (!$noTransaksi) {
                return response()->json(['success' => false, 'message' => 'No transaksi tidak ditemukan atau tidak valid!'], 400);
            }

            $totalQtyRcrd = RecordRch::where('no_transaksi', $noTransaksi)->first();
            $totalQtyValueRcrd = $totalQtyRcrd ? $totalQtyRcrd->qty : 0;
    
            $totalQty = M_Qty::where('no_transaksi', $noTransaksi)->first();
            $totalQtyValue = $totalQty ? $totalQty->total_qty : 0;

            $inputQty = $request->input('qty');
    
            if ($totalQtyValue == $totalQtyValueRcrd) {
                return response()->json(['success' => false, 'message' => 'Tidak dapat menginput data melebihi quantity.'], 400);
            }

            if (($totalQtyValue + $inputQty) > $totalQtyValueRcrd) {
                return response()->json(['success' => false, 'message' => 'Quantity tidak dapat melebihi quantity record'], 400);
            }
    
            $qrData = $request->input('qrcode');
            $dataArray = explode('|', $qrData);
    
            if (count($dataArray) < 4) {
                return response()->json(['success' => false, 'message' => 'Format data salah!'], 400);
            }
    
            $validatedData = [
                'tgl_bln_thn' => now(),
                'model' => $dataArray[0],
                'qty' => $dataArray[2],
            ];
    
            $noTransaksi = $request->input('no_transaksi') ?? 'RCH' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
    
            $record = new DeliveryRch([
                'no_transaksi' => $noTransaksi,
                'tgl_bln_thn' => $validatedData['tgl_bln_thn'],
                'part_number' => $validatedData['model'],
                'lot_number' => $dataArray[3],
                'flag' => 1,
                'qty' => $validatedData['qty'],
            ]);



            $partNumber = $validatedData['model'];
            $lotNumber = $dataArray[3];
    
            $isDuplicate = DeliveryRch::where('part_number', $partNumber)
                ->where('lot_number', $lotNumber)
                ->exists();
    
            if ($isDuplicate) {
                return response()->json(['success' => false, 'message' => 'Data sudah ada dalam database'], 409);
            }
    
            if ($record->save()) {

                $totalQty = DB::table('delivery_receh')
                    ->where('no_transaksi', $noTransaksi)
                    ->sum('qty');

                $masterQty = M_Qty::where('no_transaksi', $noTransaksi)->first();

                if ($masterQty) {
                    $masterQty->total_qty = $totalQty;
                    $masterQty->flag = 1;
                    $masterQty->save();
                } else {
                    M_Qty::create(attributes: [
                        'no_transaksi' => $noTransaksi,
                        'total_qty' => $totalQty,
                        'flag' => 1,
                    ]);
                }
                
                return response()->json(['success' => true, 'message' => 'Data berhasil disimpan','total_qty' => $totalQty]);
            } else {
                return response()->json(['success' => false, 'message' => 'Data gagal disimpan'], 500);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    // old function
    // public function store(Request $request)
    // {
    //     try {
    //         $noTransaksi = $request->input('no_transaksi');
    //         if (!$noTransaksi) {
    //             return response()->json(['success' => false, 'message' => 'No transaksi tidak ditemukan atau tidak valid!'], 400);
    //         }

    //         $qrData = $request->input('qrcode');
    //         $dataArray = explode('|', $qrData);

    //         if (count($dataArray) < 4) {
    //             return response()->json(['success' => false, 'message' => 'Format data salah!'], 400);
    //         }

    //         $validatedData = [
    //             'tgl_bln_thn' => now(),
    //             'model' => $dataArray[0],
    //             'qty' => $dataArray[2],
    //         ];

    //         $noTransaksi = $request->input('no_transaksi') ?? 'AVI' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

    //         $record = new Delivery([
    //             'no_transaksi' => $noTransaksi,
    //             'tgl_bln_thn' => $validatedData['tgl_bln_thn'],
    //             'part_number' => $validatedData['model'],
    //             'lot_number' => $dataArray[3],
    //             'flag' => 1,
    //             'qty' => $validatedData['qty'],
    //         ]);

    //         $isDuplicate = Delivery::where('no_transaksi', $noTransaksi)
    //             ->where('part_number', $validatedData['model'])
    //             ->where('lot_number', $dataArray[3])
    //             ->exists();

    //         if ($isDuplicate) {
    //             return response()->json(['success' => false, 'message' => 'Data sudah ada dalam database'], 409);
    //         }

    //         if ($record->save()) {
    //             return response()->json(['success' => true, 'message' => 'Data berhasil disimpan']);
    //         } else {
    //             return response()->json(['success' => false, 'message' => 'Data gagal disimpan'], 500);
    //         }
    //     } catch (Exception $e) {
    //         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    //     }
    // }
  
    public function show(Delivery $data)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Delivery $data)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Delivery $data)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Delivery $data)
    {
        //
    }

    public function compareQty(Request $request)
    {
        try {
            $noTransaksi = session('no_transaksi');
            $tglBlnThn = now()->toDateString();

            $recordData = Record::whereDate('tgl_bln_thn', $tglBlnThn)
                ->where('no_transaksi', $noTransaksi)
                ->get(['no_transaksi', 'model', 'qty']);

            $qtyPcsData = M_Qty_pcs::where('no_transaksi', $noTransaksi)->get(['no_transaksi', 'qty_pcs']);

            foreach ($recordData as $record) {
                $qtyPcs = $qtyPcsData->where('no_transaksi', $record->no_transaksi)->first();

                if ($qtyPcs) {
                    $record->total_qty = $record->qty + $qtyPcs->qty_pcs;
                } else {
                    $record->total_qty = $record->qty; 
                }
            }

            $deliveryData = Delivery::whereDate('tgl_bln_thn', $tglBlnThn)
                ->where('no_transaksi', $noTransaksi)
                ->selectRaw('no_transaksi, part_number, SUM(qty) as qty')
                ->groupBy('no_transaksi', 'part_number')
                ->get();

            if ($recordData->isEmpty() && $deliveryData->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data dalam Tabel Record dan Delivery.',
                    'recordData' => [],
                    'deliveryData' => []
                ]);
            }

            $recordTotalQty = $recordData->sum('total_qty');
            $deliveryTotalQty = $deliveryData->sum('qty');

            $message = '';
            $status = false;

            if ($recordTotalQty === $deliveryTotalQty) {
                Record::whereDate('tgl_bln_thn', $tglBlnThn)->where('flag', 1)->update(['flag' => 0]);
                Delivery::whereDate('tgl_bln_thn', $tglBlnThn)->where('flag', 1)->update(['flag' => 0]);

                M_Qty::where('no_transaksi', $noTransaksi)->update(['flag' => 0]);

                $message = 'Data cocok, berhasil mengupdate data.';
                $status = true;
            } else {
                $message = 'Data tidak cocok, gagal mengupdate data.';
            }

            return response()->json([
                'success' => $status,
                'message' => $message,
                'recordData' => $recordData,
                'deliveryData' => $deliveryData
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDeliveryData(Request $request)
    {
        $noTransaksi = session('no_transaksi'); 
    
        $deliveries = Delivery::where('no_transaksi', $noTransaksi)
            ->orderBy('tgl_bln_thn', 'desc') 
            ->get();
    
        return DataTables::of($deliveries)
            ->addIndexColumn() 
            ->make(true);
    }
    
    public function getTotalQty($noTransaksi)
    {
        $totalQty = M_Qty::where('no_transaksi', $noTransaksi)->first();
        $totalQtyValue = $totalQty ? $totalQty->total_qty : 0;
    
        return response()->json([
            'total_qty' => $totalQtyValue,
        ]);
    }

    public function verifyPassword(Request $request)
    {
        $password = $request->input('password');
        if ($password === 'warehouse021') {
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Password salah.'], 403);
    }

    public function getTotalQtyStatus($noTransaksi)
    {
        $totalQty = M_Qty::where('no_transaksi', $noTransaksi)->first();
        $totalQtyValue = $totalQty ? $totalQty->total_qty : 0;

        $totalQtyRcrd = Record::where('no_transaksi', $noTransaksi)->first();
        $totalQtyValueRcrd = $totalQtyRcrd ? $totalQtyRcrd->qty : 0;

        return response()->json([
            'totalQtyValue' => $totalQtyValue,
            'totalQtyValueRcrd' => $totalQtyValueRcrd,
        ]);
    }



}
