<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\ContactUs;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ContactUsController extends Controller
{
    //
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ContactUs::latest();
            
            return DataTables::of($data)->make(true);
        }

        return view('backend.layout.ContactUs.index');
    }
}
