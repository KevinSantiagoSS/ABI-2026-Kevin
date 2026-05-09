<?php

namespace App\Http\Controllers;

use App\Helpers\AuthUserHelper;
use App\Helpers\UserRoleHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function show()
    {
        $user = AuthUserHelper::fullUser();
        $userMail = $user?->email ?? '';
        $userRole = $user?->role ?? '';
        $nameFromAccount = trim((string) ($user?->name ?? ''));
        $nameUserRole = UserRoleHelper::displayName($user);

        if ($userRole === 'student') {
            $userCard = $user?->student?->card_id;
            $userPhone = $user?->student?->phone;
            $userCity = $user?->student?->cityProgram?->city?->name;
            $userProgram = $user?->student?->cityProgram?->program?->name;
            $name = $user?->student?->name ?? $nameFromAccount;
            $surname = $user?->student?->last_name ?? '';
            $nameFromAccount = trim($name . ' ' . $surname);
        } elseif ($userRole === 'professor' || $userRole === 'committee_leader') {
            $userCard = $user?->professor?->card_id;
            $userPhone = $user?->professor?->phone;
            $userCity = $user?->professor?->cityProgram?->city?->name;
            $userProgram = $user?->professor?->cityProgram?->program?->name;
            $name = $user?->professor?->name ?? $nameFromAccount;
            $surname = $user?->professor?->last_name ?? '';
            $nameFromAccount = trim($name . ' ' . $surname);
        } else {
            $name = $user?->researchStaff?->name ?? $nameFromAccount;
            $userCard = $user?->researchStaff?->card_id;
            $userPhone = $user?->researchStaff?->phone;
            $userCity = 'N/A';
            $userProgram = 'N/A';
            $surname = $user?->researchStaff?->last_name ?? '';
            $nameFromAccount = trim($name . ' ' . $surname);
        }

        $displayName = $nameFromAccount !== '' ? $nameFromAccount : __('Usuario');

        return view('perfil_show', compact(
            'displayName',
            'userCity',
            'userProgram',
            'nameUserRole',
            'userMail',
            'userCard',
            'userPhone'
        ));
    }

    public function edit()
    {
        return view('perfil');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->input('name');
        $user->email = $request->input('email');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        return redirect()->route('perfil.edit')->with('status', 'Perfil actualizado correctamente');
    }
}
