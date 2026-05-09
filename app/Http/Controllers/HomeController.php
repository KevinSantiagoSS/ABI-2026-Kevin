<?php

namespace App\Http\Controllers;

use App\Helpers\AuthUserHelper;
use App\Helpers\UserRoleHelper;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = AuthUserHelper::fullUser();
        $userRole = $user?->role ?? '';
        $nameFromAccount = trim((string) ($user?->name ?? ''));

        if ($userRole === 'student') {
            $name = $user?->student?->name ?? $nameFromAccount;
            $surname = $user?->student?->last_name ?? '';
            $nameFromAccount = trim($name . ' ' . $surname);
        } elseif ($userRole === 'professor' || $userRole === 'committee_leader') {
            $name = $user?->professor?->name ?? $nameFromAccount;
            $surname = $user?->professor?->last_name ?? '';
            $nameFromAccount = trim($name . ' ' . $surname);
        } else {
            $name = $user?->researchStaff?->name ?? $nameFromAccount;
            $surname = $user?->researchStaff?->last_name ?? '';
            $nameFromAccount = trim($name . ' ' . $surname);
        }

        $displayName = $nameFromAccount !== '' ? $nameFromAccount : __('Usuario');
        $userTypeLabel = UserRoleHelper::displayName($user);

        return view('home', compact(
            'displayName',
            'userTypeLabel',
            'userRole'
        ));
    }
}
