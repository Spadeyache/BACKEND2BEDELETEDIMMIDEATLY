<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrdersController extends Controller
{
    //
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::with(['user', 'order_item'])->latest();
            
            return DataTables::of($data)
                ->addColumn('user_name', fn($row) => $row->user->first_name . ' ' . $row->user->last_name)
                ->addColumn('order_date', fn($row) => $row->created_at ? $row->created_at->format('d M Y, h:i a') : '')
                ->addColumn('order_details', function ($row) {
                    return '
                        <div class="d-flex align-items-center gap-2">
                            <a 
                                href="' . route('orders.details', $row->id) . '"
                                class="btn btn-sm btn-light-primary view-orders-btn">
                                View Details
                            </a>
                        </div>
                    ';
                })
                ->addColumn('actions', function ($row) {
                    return '
                            <div class="d-flex justify-content-end">
                                <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm"
                                   data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    Actions
                                    <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                </a>
                            </div>
                            ';
                })
                ->rawColumns(['order_details'])
                ->make(true);
        }

        return view('backend.layout.Orders.index');
    }

    public function details($id)
    {
        $order = Order::findOrFail($id);
        return view('backend.layout.Order_details.index', compact('order'));
    }

    public function detailsData(Request $request, $id)
    {
        $data = OrderItem::where('order_id', $id)->latest();
        return DataTables::of($data)->make(true);
    }
}
