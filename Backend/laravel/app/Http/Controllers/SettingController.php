<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Models\User;
//use App\Models\Groups;
//use App\Models\Grouped;
//use App\Models\Positions;
//use App\Models\Currencies;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        //$currency = Currencies::get('symbol');
        //$users_in_business = User::get();
        //$groups_in_business = Groups::get();
        //$positions_in_business = Positions::get();
        //$roles = Roles::get();
        //$groupeds = Grouped::join('users','users.id', 'groupeds.user_id')->join('groups','groups.user_id','users.id')->select('users.name as users')->get(); // Groupeds should'select alls users groups grouped in this fields
        return view('settings');
    }

    public function store(Request $request, Settings $settings )
    {
       
       if ($image = $request->file('image')) {
            $destinationPath = 'storage/settings/';
            $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move($destinationPath, $profileImage);
        }else {
             $profileImage = config('settings.image');
        }
        
        $data = $request->except('_token');
        $data['image'] = $profileImage;
        
        // Manejar checkbox show_notifications (si no está marcado, no viene en el request)
        $data['show_notifications'] = $request->has('show_notifications') ? '1' : '0';
        
        foreach ($data as $key => $value) {
            $setting = Settings::firstOrCreate(['key' => $key]);
            $setting->value = $value;
            $setting->save();
        }
        return redirect()->route('settings.index')->with('status', 'Configuración actualizada correctamente.');
    }
}
