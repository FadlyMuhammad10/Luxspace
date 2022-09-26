<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(request()->ajax()){
            $query= User::query();
            return DataTables::of($query)
            ->addColumn('action',function($item){
                return '
                        
                        <a  href="'.route('dashboard.user.edit',$item->id).'" class="bg-gray-500 text-white rounded-md px-2 py-1 m-2">
                            Edit
                        </a>
                        <form class="inline-block" method="POST" action="'.route('dashboard.user.destroy',$item->id).'">
                            <button class="bg-red-500 text-white rounded-md px-2 py-1 m-2">
                                Hapus
                            </button>
                        '.method_field('delete').csrf_field().'
                        </form>
                ';
            })
            
            ->rawColumns(['action'])
            ->make();
        }
        return view('pages.dashboard.user.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return view('pages.dashboard.user.edit',[
            'item'=>$user
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {
        $data= $request->all();
        $user->update($data);

        return redirect()->route('dashboard.user.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('dashboard.user.index');
    }

    public function google()
    {
        return Socialite::driver('google')->redirect();
    }
    public function handleprovidercallback()
    {
        $callback= Socialite::driver('google')->stateless()->user(); // dari documentation laravel socialite
        $data=[
            'name'=>$callback->getName(),
            'email'=>$callback->getEmail(),
            'email_verified_at'=>date('Y-m-d H:i:s',time())
        ];

        $user= User::firstOrCreate(['email'=>$data['email']], $data);//jika ketemu email yang sama tidak perlu nambah email jika ketemu sama maka nambah email
        Auth::login($user,true);

        return redirect(route('index'));
    }
}
