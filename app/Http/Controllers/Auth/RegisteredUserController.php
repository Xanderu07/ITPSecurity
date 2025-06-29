<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Toon het registratieformulier.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Verwerk het registratieformulier.
     *
     * Valideer de input, inclusief sterk wachtwoord volgens eisen.
     * Hash het wachtwoord met bcrypt via Hash::make() — hiermee voorkom je
     * rainbow table attacks dankzij de ingebouwde salt.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => [
                'required',
                'confirmed',
                Password::min(12)       // minimaal 12 tekens
                ->mixedCase()      // hoofd- en kleine letters
                ->numbers()        // cijfers
                ->symbols()        // speciale tekens
                ->uncompromised(), // niet gelekt in datalekkenz
            ],
        ]);

        // Wachtwoord wordt gehashed met bcrypt en een unieke salt toegevoegd
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard'));
    }
}
