<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{


    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function storeLogin(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $this->throwLoginValidationException($request, 'Email atau password tidak sesuai.');
        }

        if (! $request->user()->isAdmin() && ! $request->user()->isValidator()) {
            Auth::guard('web')->logout();

            $this->throwLoginValidationException(
                $request,
                'Akses login hanya untuk admin atau validator.'
            );
        }

        $request->session()->regenerate();

        return to_route(
            $request->user()->dashboardRoute()
        )->with('status', 'Login berhasil.');
    }



    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('welcome', ['modal' => 'internal'])->with('status', 'Anda telah keluar dari sistem.');
    }



    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    private function throwLoginValidationException(LoginRequest $request, string $Message): never
    {
        throw ValidationException::withMessages([
            'email' => $Message,
        ])
            ->errorBag('internalLogin')
            ->redirectTo(url()->previous());
    }
}
