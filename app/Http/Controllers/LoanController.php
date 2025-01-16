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

        $loanOption = DB::table('bank_options')
            ->where('amount', $amount)
            ->where('hidden', 0)
            ->first();

        if (!$loanOption) {
            return response()->json(['message' => 'Préstamo no válido'], 400);
        }

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

        $interestRate = $loanOption->interest / 100;
        $totalToPay = $amount * (1 + $interestRate);

        $newLoan = Loan::create([
            'user_id' => $user->id,
            'bank_name' => $loanOption->bank,
            'amount' => $amount,
            'interest_rate' => $loanOption->interest,
            'total_bets' => $loanOption->bets,
            'remaining_bets' => $loanOption->bets,
            'total_to_pay' => $totalToPay,
            'is_active' => true
        ]);

        $statistics = GameStatistic::where('user_id', $user->id)->first();
        $statistics->balance += $amount;
        $statistics->save();

        DB::commit();

        return response()->json([
            'message' => 'Préstamo concedido exitosamente',
            'newBalance' => $statistics->balance,
            'loan' => $newLoan
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error processing loan: ' . $e->getMessage());
        return response()->json(['message' => 'Error al procesar el préstamo'], 500);
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

public function deductLoanAmount(Request $request)
{
    try {
        $user = auth()->user();
        $statistics = GameStatistic::where('user_id', $user->id)->first();

        if (!$statistics) {
            return response()->json(['message' => 'No se encontraron estadísticas del usuario'], 404);
        }

        // Obtener préstamos activos
        $activeLoans = Loan::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('remaining_bets', 'asc') // Priorizar préstamos con menos tiradas restantes
            ->get();

        if ($activeLoans->isEmpty()) {
            return response()->json(['message' => 'No hay préstamos activos'], 400);
        }

        // Iterar sobre los préstamos activos para realizar deducciones
        foreach ($activeLoans as $loan) {
            $costPerTurn = $loan->total_to_pay / $loan->total_bets; // Costo por tirada

            if ($statistics->balance >= $costPerTurn) {
                // Deducir balance del usuario
                $statistics->balance -= $costPerTurn;
                $loan->remaining_bets -= 1;

                // Marcar el préstamo como pagado si ya no quedan tiradas
                if ($loan->remaining_bets <= 0) {
                    $loan->is_active = false;
                }

                $loan->save();
            } else {
                // Si no puede pagar completamente la tirada, marcar el préstamo como parcialmente pagado
                $loan->remaining_bets -= 1;
                $statistics->balance = 0;

                if ($loan->remaining_bets <= 0) {
                    $loan->is_active = false;
                }

                $loan->save();
                break; // Detener el ciclo ya que el balance es insuficiente
            }
        }

        $statistics->save();

        return response()->json([
            'newBalance' => $statistics->balance,
            'remainingLoans' => $activeLoans->map(function ($loan) {
                return [
                    'id' => $loan->id,
                    'remainingBets' => $loan->remaining_bets,
                    'isActive' => $loan->is_active,
                ];
            }),
        ]);
    } catch (\Exception $e) {
        Log::error('Error al deducir monto del préstamo: ' . $e->getMessage());
        return response()->json(['message' => 'Error al deducir monto del préstamo'], 500);
    }
}

    
}