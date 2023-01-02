<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public const PAGE_SIZE = 10;
    public static function report(Request $request)
    {
        $from = $request->input("from") ?? "";
        $to = $request->input("to") ?? "";
        $name = $request->input("name") ?? "";

        if (empty($name)) {
            $orders = Order::orderBy('id')
                ->where(function ($q) use ($from, $to) {
                    if (!empty($from)) {
                        $q->where("order_date", ">=", $from);
                    }
                    if (!empty($to)) {
                        $q->where("order_date", "<=", $to);
                    }
                })
                ->with(
                    [
                        'purchaser' => [
                            'referredBy' => [
                                'referrals'
                            ]
                        ],
                        'items'
                    ]
                );
            $orders = $orders
                ->paginate(UserController::PAGE_SIZE)
                ->withQueryString();
        } else {
            $orders = UserController::reportSearch($request);
        }
        $total_commission = 0;
        foreach ($orders as $order) {
            $total_commission += $order->commission;
        }
        return ['orders' => $orders, 'total_commission' => $total_commission];
    }

    public static function reportSearch(Request $request)
    {
        $from = $request->input("from") ?? "";
        $to = $request->input("to") ?? "";
        $name = $request->input("name") ?? "";
        $pageNumber = $request->input('page') ?? 1;
        $users = User::whereHas('categories', function ($qu) {
            $qu->where('id', 1);
        })
            ->with([
                'referrals',
                'allOrders' => function ($q) use ($to, $from) {
                    if (!empty($from)) {
                        $q->where("order_date", ">=", $from);
                    }
                    if (!empty($to)) {
                        $q->where("order_date", "<=", $to);
                    }
                }
            ])
            ->where(function ($q2) use ($name) {
                $q2->where('id', $name);
                $q2->orWhere('username', 'like', '%' . $name . '%');
                $q2->orWhere('first_name', 'like', '%' . $name . '%');
                $q2->orWhere('last_name', 'like', '%' . $name . '%');
            })->get();
        $order_array = array();
        foreach ($users as $user) {
            $allOrders = $user->allOrders;
            foreach ($allOrders as $order) {
                $order->distributor = $user;
                array_push($order_array, $order);
            }
            $user->allOrders = null;
        }
        // dd($users);

        $perPage = UserController::PAGE_SIZE;
        $offset = ($pageNumber * $perPage) - $perPage;
        $sliced = array_slice($order_array, $offset, $perPage, true);
        return new LengthAwarePaginator(
            $sliced,
            count($order_array),
            $perPage,
            $pageNumber,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    public function getReport(Request $request)
    {
        $arr = UserController::report($request);

        return view('report', $arr);
    }

    public function postReport(Request $request)
    {
        $arr = UserController::report($request);

        return view('report', $arr);
    }

    public function top100(Request $request)
    {
        $from = $request->input("page") ?? 1;
        DB::statement("SET SQL_MODE=''");
        $basicQuery = DB::select(
            "
             with items_total as 
            (SELECT (oi.quantity * p.price) as total, oi.order_id 
            FROM order_items oi
            INNER JOIN products p on p.id = oi.product_id),

            orders_total as (SELECT o.id, o.purchaser_id, SUM(it.total) as total
            FROM orders o
            inner JOIN items_total it on it.order_id = o.id GROUP BY o.id), 

            distributors as (SELECT u.id, u.first_name, u.last_name, c.name
            FROM users u 
            INNER JOIN user_category uc on uc.user_id=u.id
            INNER JOIN categories c on c.id = uc.category_id
            WHERE c.id=1 GROUP BY u.id)

            select d.first_name, d.last_name, sum(ot.total) as total_sales, d.id, dense_rank() over (order by total_sales desc) as 'rank'
            from distributors d
            INNER JOIN users uu on uu.referred_by=d.id
            inner join orders_total ot on ot.purchaser_id = uu.id
            GROUP by d.id order BY total_sales DESC LIMIT 100
           "
        );

        $distributors = UserController::arrayPaginator($basicQuery, $request);
        return view('top', ['distributors' => $distributors]);
    }

    public static function arrayPaginator($array, $request)
    {
        $page = $request->input('page') ?? 1;
        $perPage = 10;
        $offset = ($page * $perPage) - $perPage;

        return new LengthAwarePaginator(
            array_slice($array, $offset, $perPage, true),
            count($array),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    public static function autocomplete(Request $request)
    {
        $name = $request->name;
        $data = User::with('categories')
            ->where(function ($q2) use ($name) {
                $q2->whereHas('categories', function ($q3) use ($name) {
                    $q3->where('name', "Distributor");
                });
                $q2->where(function ($q3)  use ($name) {
                    $q3->where('id', $name);
                    $q3->orWhere('username', 'like', '%' . $name . '%');
                    $q3->orWhere('first_name', 'like', '%' . $name . '%');
                    $q3->orWhere('last_name', 'like', '%' . $name . '%');
                });
            })->get();
        return response()->json($data);
    }
}
