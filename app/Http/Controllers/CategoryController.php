<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MasterItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        return view('category.index.index');
    }

    public function search(Request $request)
    {
        $kode = $request->kode;
        $nama = $request->nama;

        $data_search = Category::query();

        if (!empty($kode)) $data_search = $data_search->where('kode', $kode);
        if (!empty($nama)) $data_search = $data_search->where('nama', 'LIKE', '%' . $nama . '%');

        $data_search = $data_search->select('kode', 'nama')->orderBy('id')->get();


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
            $item = Category::find($id);
            if (!$item) {
                return redirect('category-items');
            }
        }
        $data['item'] = $item;
        $data['method'] = $method;
        return view('category.form.index', $data);
    }


    public function singleView($kode)
    {
        $data['data'] = Category::with('masterItems')->where('kode', $kode)->first();
        if (!$data['data']) {
            return redirect('category-items');
        }
        return view('category.single.index', $data);
    }

    public function formSubmit(Request $request, $method, $id = 0)
    {
        if ($method == 'new') {
            $data_item = new Category();
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
        } else {
            $data_item = Category::find($id);
            if (!$data_item) {
                return redirect('category-items');
            }
            $kode = $data_item->kode;
        }
        $request->validate([
            'nama' => 'required',
        ]);
        $data_item->kode = $kode;
        $data_item->nama = $request->nama;

        $data_item->save();

        return redirect('category-items');
    }

    public function delete($id)
    {
        $item = Category::find($id);
        if (!$item) {
            return redirect('category-items');
        }
        $item->delete();
        return redirect('category-items');
    }
}
