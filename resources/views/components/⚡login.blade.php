<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $email = '';
    public $password = '';
    public $error = '';

    public function login()
    {
        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            return redirect()->intended('/dashboard');
        }

        $this->error = 'Usuario o contraseña incorrectos.';
    }
};
?>

<div id="contenedor">
    <div id="arriba">
        <img src="{{ asset('imagenes/barras.jpeg') }}" alt="Encabezado Institucional" style="width: 100%; height: 100%; object-fit: fill; display: block;">
    </div>

    <div id="centro_login">
        <h2 style="font-size: 22px; font-weight: bold; margin-top: 30px; margin-bottom: 30px;">
            Inicie Sesión en el Software para la Gestión Académica
        </h2>

        <form wire:submit="login">
            <table align="center" style="margin-bottom: 20px;">
                <tr>
                    <td align="right" style="font-weight: bold; padding: 10px 10px 10px 0; font-size: 15px;">Usuario:</td>
                    <td align="left" style="padding: 5px 0;">
                        <input wire:model="email" type="text" placeholder="NOMBRE DE USUARIO" required> 
                        <span style="color: red; font-weight: bold; font-size: 16px; margin-left: 5px;">*</span>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="font-weight: bold; padding: 10px 10px 10px 0; font-size: 15px;">Contraseña:</td>
                    <td align="left" style="padding: 5px 0;">
                        <input wire:model="password" type="password" placeholder="CONTRASEÑA" required> 
                        <span style="color: red; font-weight: bold; font-size: 16px; margin-left: 5px;">*</span>
                    </td>
                </tr>
            </table>

            @if($error)
                <div style="color: red; font-weight: bold; margin-bottom: 20px;">
                    {{ $error }}
                </div>
            @endif

            <div style="margin-bottom: 30px;">
                <button type="submit" class="boton" style="margin-bottom: 30px;">Iniciar sesión</button>
            </div>
        </form>

        <div style="text-align: left; padding: 0 10px; margin-top: 80px;">
            <p style="margin-bottom: 15px; font-size: 14px;">Los campos con <span style="color: red; font-weight: bold;">*</span> son obligatorios</p>
            <p style="margin: 0; font-size: 12px; font-weight: normal; line-height: 1.4;">
                Nota:<br>
                -Si es la primera vez que ingresa, su usuario y contraseña es la cédula.<br>
                -Debe cambiar la contraseña cuando inicie sesión por primera vez.
            </p>
        </div>
    </div>

    <div id="abajo" style="margin-top: 0;">
        Todos los Derechos Reservados 2014 UPTP - Créditos Unidad de Sistemas / Desarrollo de Software.
    </div>
</div>
