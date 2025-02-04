<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Record;
use App\Models\M_Qty;
use App\Models\M_Model_Part;
use App\Models\Logs;
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

        $RecordSmpn = RecordSmpn::where('no_transaksi', $noTransaksi)->first()->lot_number ?? '';
    
        return view('data.deliveryrch', compact('noTransaksi', 'tglBlnThn', 'plantDest', 'model', 'qty', 'pic','totalQtyValue','RecordSmpn'));
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

        $totalQty = M_Qty::where('no_transaksi', $noTransaksi)->first();
        $totalQtyValue = $totalQty ? $totalQty->total_qty : 0;

        $totalQtyRcrd = RecordRch::where('no_transaksi', $noTransaksi)->first();
        $totalQtyValueRcrd = $totalQtyRcrd ? $totalQtyRcrd->qty_receh : 0;
        
        $RecordSmpn = RecordRch::where('no_transaksi', $noTransaksi)->first()->lot_number ?? '';
    
        return view('data.deliveryreceh', compact('noTransaksi', 'tglBlnThn', 'plantDest', 'model', 'qty', 'pic','totalQtyValue','totalQtyValueRcrd','RecordSmpn'));
    }
    
    public function store(Request $request)
    {
        try {
            $lotNumber = $request->input('lot_number');
            $qty = (int)$request->input('qty');
            $sessionModel = session('model');
            $qtyReceh = session('qty_receh');
            $pic = session('pic');

            if (Delivery::where('lot_number', $lotNumber)->exists()) {
                return response()->json(['success' => false, 'message' => 'Lot number sudah ada dalam database.'], 400);
            }

            $qrData = $request->input('qrcode');
            $dataArray = explode('|', $qrData);
                if (count($dataArray) < 4) {
                    return response()->json(['success' => false, 'message' => 'Format data salah!'], 400);
                }

            $partNumbersInSession = session('part_numbers', []);
            $scannedPartNumbers = $dataArray[0];

                if (!in_array($scannedPartNumbers, $partNumbersInSession)) {
                    return response()->json(['success' => false, 'message' => 'Part Number tidak sesuai'], 400);
                }

            $recordSmpn = RecordSmpn::where('lot_number', $lotNumber)
                ->where('model', $sessionModel) 
                ->first();

            if ($recordSmpn) {
                if ($recordSmpn->qty < $qtyReceh) {
                    return response()->json([
                        'success' => false,
                        'message' => "Quantity dalam database lebih kecil dibanding quantity receh"
                    ], 400);
                }

                if ($qty >= $qtyReceh) {
                    $recordSmpn->update([
                        'tgl_bln_thn' => now(),
                        'pic' => $pic,
                    ]);
                    return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui.']);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Quantity tidak boleh lebih kecil dari quantity receh'
                    ], 400);
                }
            } else {
                if ($qty < $qtyReceh) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Quantity tidak boleh lebih kecil dari quantity receh'
                    ], 400);
                }

                $validatedData = [
                    'tgl_bln_thn' => now(),
                    'model' => $sessionModel,
                    'qty' => $dataArray[2],
                    'lot_number' => $dataArray[3],
                    'flag' => 1,
                    'pic' => $pic,
                ];

                $newRecord = new RecordSmpn($validatedData);
                if (!$newRecord->save()) {
                    return response()->json(['success' => false, 'message' => 'Gagal menyimpan data'], 500);
                }
            }

            $noTransaksi = session('no_transaksi');
            $transaksi = RecordRch::where('no_transaksi', $noTransaksi)->first();

            if ($transaksi) {
                $existingLotNumbers = $transaksi->lot_number ?? '';
                $newLotNumbers = $existingLotNumbers
                    ? $existingLotNumbers . ',' . $lotNumber
                    : $lotNumber;

                $transaksi->update(['lot_number' => $newLotNumbers]);
            }

            return response()->json(['success' => true, 'message' => 'Data berhasil disimpan.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storereceh(Request $request)
    {
        $noTransaksi = $request->input('no_transaksi');
        if (!$noTransaksi) {
            return response()->json(['success' => false, 'message' => 'No transaksi tidak ditemukan atau tidak valid!'], 400);
        }

        $totalQtyRcrd = RecordRch::where('no_transaksi', $noTransaksi)->first();
        $totalQtyValueRcrd = $totalQtyRcrd ? $totalQtyRcrd->qty_receh : 0;

        $totalQty = M_Qty::where('no_transaksi', $noTransaksi)->first();
        $totalQtyValue = $totalQty ? $totalQty->total_qty : 0;

        if ($totalQtyValue == $totalQtyValueRcrd) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menginput data melebihi quantity.'], 400);
        }

        $model = session('model');
        $qrcode = $request->input('qrcode');
        $modelParts = M_Model_Part::where('model', $model)->get();
        $modelPart = $modelParts->first(function ($part) use ($qrcode) {
            return strpos($qrcode, $part->serial_number) === 0;
        });

        $noTransaksi = session('no_transaksi');

        $existingDelivery = DeliveryRch::where('serial_number', $qrcode)->first();
        if ($existingDelivery) {
            return response()->json(['success' => false, 'message' => 'Serial number sudah ada dalam database'], 400);
        }

        $tglBlnThn = now()->toDateString();

        $recordData = RecordRch::whereDate('tgl_bln_thn', $tglBlnThn)
        ->where('no_transaksi', $noTransaksi)
        ->get(['no_transaksi','lot_number']);

        $lotNumbers = $recordData->pluck('lot_number')->unique()->first();

        if ($modelPart) {
            $delivery = new DeliveryRch();
            $delivery->no_transaksi = $noTransaksi;
            $delivery->tgl_bln_thn = now()->format('Y-m-d H:i:s');
            $delivery->part_number = $modelPart->part_number;
            $delivery->serial_number = $qrcode;
            $delivery->lot_number = $lotNumbers;
            $delivery->qty = 1;
            $delivery->flag = 1;
            $delivery->save();

            $totalQty = DB::table('delivery_receh')
                ->where('no_transaksi', $noTransaksi)
                ->sum('qty');

            $masterQty = M_Qty::where('no_transaksi', $noTransaksi)->first();

            if ($masterQty) {
                $masterQty->total_qty = $totalQty;
                $masterQty->flag = 1;
                $masterQty->save();
            } else {
                M_Qty::create([
                    'no_transaksi' => $noTransaksi,
                    'total_qty' => $totalQty,
                    'flag' => 1,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan!'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Serial number tidak sesuai'
            ]);
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

            $recordSmpn = RecordSmpn::whereDate('tgl_bln_thn', $tglBlnThn)
            ->get(['model', 'qty', 'lot_number']);

            $recordData = RecordRch::whereDate('tgl_bln_thn', $tglBlnThn)
                ->where('no_transaksi', $noTransaksi)
                ->get(['no_transaksi', 'model', 'qty_receh', 'lot_number']);

            $deliveryData = DeliveryRch::whereDate('tgl_bln_thn', $tglBlnThn)
                ->where('no_transaksi', $noTransaksi)
                ->selectRaw('no_transaksi, part_number, lot_number ,SUM(qty) as qty')
                ->groupBy('no_transaksi', 'part_number', 'lot_number')
                ->get();

            if ($recordData->isEmpty() && $deliveryData->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data dalam Tabel Record dan Delivery.',
                    'recordData' => [],
                    'deliveryData' => []
                ]);
            }

            $recordQty = $recordData->sum('qty_receh');
            $deliveryQty = $deliveryData->sum('qty');

            $message = '';
            $status = false;

            if ($recordQty === $deliveryQty) {
                RecordRch::whereDate('tgl_bln_thn', $tglBlnThn)->where('flag', 1)->update(['flag' => 0]);
                DeliveryRch::whereDate('tgl_bln_thn', $tglBlnThn)->where('flag', 1)->update(['flag' => 0]);

                M_Qty::where('no_transaksi', $noTransaksi)->update(['flag' => 0]);

                $lotNumbers = $recordData->pluck('lot_number')->unique();

                RecordSmpn::whereIn('lot_number', $lotNumbers)->update(['flag' => 0]);
                
                $this->reduceQtyBasedOnLotNumber($recordSmpn, $deliveryData, $noTransaksi);

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

    private function reduceQtyBasedOnLotNumber($recordSmpn, $deliveryData, $noTransaksi)
    {
        foreach ($recordSmpn as $record) {
            $matchingDelivery = $deliveryData->where('lot_number', $record->lot_number)->first();

            if ($matchingDelivery) {
                $newQty = $record->qty - $matchingDelivery->qty;
                if ($newQty >= 0) {
                    RecordSmpn::where('lot_number', $record->lot_number)
                        ->update(['qty' => $newQty]);
                }
            }
        }
    }

    public function getDeliveryDataRch(Request $request)
    {
        $noTransaksi = session('no_transaksi'); 
    
        $deliveries = DeliveryRch::where('no_transaksi', $noTransaksi)
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

    public function verifyPasswordRch(Request $request)
    {
        $password = $request->input('password');
        if ($password === 'Ce9vM3Ln4IYR') {
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
    
    public function saveLogRch(Request $request)
    {
        $validatedData = $request->validate([
            'no_transaksi' => 'required|string',
            'tgl_bln_thn' => 'required|string',
            'note' => 'required|string',
        ]);

        Logs::create([
            'no_transaksi' => $validatedData['no_transaksi'],
            'tgl_bln_thn' => $validatedData['tgl_bln_thn'],
            'note' => $validatedData['note'],
        ]);

        return response()->json(['success' => true]);
    }
    


}
