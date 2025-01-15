<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\GameStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoanController extends Controller
{

    public function getLoanOptions(Request $request)
{
    $user = $request->user();
    $loanOptionsQuery = DB::table('bank_options');

    if ($user->role !== 'admin') {
        $loanOptionsQuery->where('hidden', 0);
    }

    $loanOptions = $loanOptionsQuery->get();

    return response()->json(['bankOptions' => $loanOptions]);
}



public function takeLoan(Request $request)
{
    try {
        $request->validate([
            'amount' => 'required|numeric'
        ]);

        $user = auth()->user();
        $amount = $request->amount;

        // Verificar si el préstamo existe en las opciones disponibles
        $loanOption = DB::table('bank_options')
            ->where('amount', $amount)
            ->where('hidden', 0)
            ->first();

        if (!$loanOption) {
            return response()->json(['message' => 'Préstamo no válido'], 400);
        }

        // Verificar si ya tiene un préstamo activo para el mismo banco
        $hasActiveLoan = Loan::where('user_id', $user->id)
            ->where('bank_name', $loanOption->bank)
            ->where('is_active', true)
            ->exists();

        if ($hasActiveLoan) {
            return response()->json([
                'message' => 'Ya tienes un préstamo activo con este banco'
            ], 400);
        }

        DB::beginTransaction();

        // Calcular el total a pagar
        $interestRate = $loanOption->interest / 100;
        $totalToPay = $amount * (1 + $interestRate);

        Loan::create([
            'user_id' => $user->id,
            'bank_name' => $loanOption->bank,
            'amount' => $amount,
            'interest_rate' => $loanOption->interest,
            'total_bets' => $loanOption->bets,
            'remaining_bets' => $loanOption->bets,
            'total_to_pay' => $totalToPay,
            'is_active' => true
        ]);

        // Actualizar el balance en GameStatistic
        $statistics = GameStatistic::where('user_id', $user->id)->first();
        $statistics->balance += $amount;
        $statistics->save();

        DB::commit();

        return response()->json([
            'message' => 'Préstamo concedido exitosamente',
            'newBalance' => $statistics->balance,
            'loanId' => $newLoan->id
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error processing loan: ' . $e->getMessage());
        return response()->json([
            'message' => 'Error al procesar el préstamo'
        ], 500);
    }
}


public function getActiveLoans()
{
    try {
        $user = auth()->user();
        $activeLoans = Loan::where('user_id', $user->id)
                          ->where('is_active', true)
                          ->get(['id', 'bank_name', 'amount', 'interest_rate', 'remaining_bets', 'total_to_pay']);

        return response()->json([
            'activeLoans' => $activeLoans,
        ]);

    } catch (\Exception $e) {
        Log::error('Error getting active loans: ' . $e->getMessage());
        return response()->json([
            'message' => 'Error al obtener préstamos activos',
        ], 500);
    }
}


    public function editLoan(Request $request)
{
    $request->validate([
        'bank' => 'required|string|exists:bank_options,bank', // Verifica en 'bank_options'
        'newAmount' => 'required|numeric|min:0',
    ]);

    $bank = $request->input('bank');
    $newAmount = $request->input('newAmount');

    try {
        // Actualizar únicamente la columna 'amount' en la tabla 'bank_options'
        DB::table('bank_options')
            ->where('bank', $bank)
            ->update(['amount' => $newAmount]);

        return response()->json([
            'message' => 'Préstamo actualizado con éxito en bank_options',
        ]);
    } catch (\Exception $e) {
        Log::error('Error al editar el préstamo: ' . $e->getMessage());
        return response()->json(['message' => 'Error al editar el préstamo'], 500);
    }
}



public function hideLoan(Request $request)
{
    $request->validate([
        'bank' => 'required|string|exists:bank_options,bank', // Validar en 'bank_options'
    ]);

    $bank = $request->input('bank');

    $bankOption = DB::table('bank_options')->where('bank', $bank)->first();
    if (!$bankOption) {
        return response()->json(['error' => 'Préstamo no encontrado'], 404);
    }

    // Alterna el estado 'hidden'
    $newHiddenState = !$bankOption->hidden;
    DB::table('bank_options')
        ->where('bank', $bank)
        ->update(['hidden' => $newHiddenState]);

    $message = $newHiddenState
        ? 'Préstamo ocultado con éxito'
        : 'Préstamo reactivado con éxito';

    return response()->json(['message' => $message]);
}




    
}