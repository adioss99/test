<?php

namespace App\Http\Controllers;

use App\Models\MasterItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MasterItemsController extends Controller
{
    public function index()
    {
        return view('master_items.index.index');
    }

    public function search(Request $request)
    {
        $kode = $request->kode;
        $nama = $request->nama;
        $hargamin = $request->hargamin;
        $hargamax = $request->hargamax;

        $data_search = MasterItem::query();

        if (!empty($kode)) $data_search = $data_search->where('kode', $kode);
        if (!empty($nama)) $data_search = $data_search->where('nama', 'LIKE', '%' . $nama . '%');
        if (!empty($hargamin)) $data_search = $data_search->where('harga_beli', '>=', $hargamin);
        if (!empty($hargamax)) $data_search = $data_search->where('harga_beli', '<=', $hargamax);

        $data_search = $data_search->select('kode', 'nama', 'jenis', 'harga_beli', 'laba', 'supplier')->orderBy('id')->get();


        return json_encode([
            'status' => 200,
            'data' => $data_search
        ]);
    }

    public function formView($method, $id = 0)
    {
        if ($method == 'new') {
            $item = [];
        } else {
            $item = MasterItem::find($id);
        }
        $data['item'] = $item;
        $data['method'] = $method;
        return view('master_items.form.index', $data);
    }

    public function singleView($kode)
    {
        $data['data'] = MasterItem::where('kode', $kode)->first();
        return view('master_items.single.index', $data);
    }

    public function formSubmit(Request $request, $method, $id = 0)
    {
        if ($method == 'new') {
            $data_item = new MasterItem;
            $last = $data_item->orderBy('id', 'desc')->first();
            if ($last) {
                $lastKode = intval($last->kode);
                $kode = str_pad($lastKode + 1, 5, '0', STR_PAD_LEFT);
            } else {
                $kode = str_pad(1, 5, '0', STR_PAD_LEFT);
            }
            while ($data_item->where('kode', $kode)->exists()) {
                $lastKode++;
                $kode = str_pad($lastKode + 1, 5, '0', STR_PAD_LEFT);
            }
            $path = null;
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('items', 'public');
                $data_item->photo = $path;
            }
        } else {
            $data_item = MasterItem::find($id);
            $kode = $data_item->kode;
            if ($request->hasFile('photo')) {
                if ($data_item->photo && Storage::disk('public')->exists($data_item->photo)) {
                    Storage::disk('public')->delete($data_item->photo);
                }
                $data_item->photo = $request->file('photo')->store('items', 'public');
            }
        }

        $data_item->nama = $request->nama;
        $data_item->harga_beli = $request->harga_beli;
        $data_item->laba = $request->laba;
        $data_item->kode = $kode;
        $data_item->supplier = $request->supplier;
        $data_item->jenis = $request->jenis;

        $data_item->save();

        return redirect('master-items');
    }

    public function delete($id)
    {
        $item = MasterItem::find($id);
        if ($item->photo && Storage::disk('public')->exists($item->photo)) {
            Storage::disk('public')->delete($item->photo);
            # code...
        }
        $item->delete();
        return redirect('master-items');
    }

    public function updateRandomData()
    {
        $data = MasterItem::get();
        foreach ($data as $item) {
            $kode = $item->id;
            $kode = str_pad($kode, 5, '0', STR_PAD_LEFT);

            $item->harga_beli = rand(100, 1000000);
            $item->laba = rand(10, 99);
            $item->kode = $kode;
            $item->supplier = $this->getRandomSupplier();
            $item->jenis = $this->getRandomJenis();
            $item->save();
        }
    }

    private function getRandomSupplier()
    {
        $array = ['Tokopaedi', 'Bukulapuk', 'TokoBagas', 'E Commurz', 'Blublu'];
        $random = rand(0, 4);
        return $array[$random];
    }

    private function getRandomJenis()
    {
        $array = ['Obat', 'Alkes', 'Matkes', 'Umum', 'ATK'];
        $random = rand(0, 4);
        return $array[$random];
    }
}
