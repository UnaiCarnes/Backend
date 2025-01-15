<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\GameStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoanController extends Controller
{
    private $loanOptions = [
        ['bank' => 'Cucha', 'amount' => 500, 'interest' => 17, 'bets' => 10],
        ['bank' => 'Caja Urbana', 'amount' => 1000, 'interest' => 15, 'bets' => 12],
        ['bank' => 'VVBA', 'amount' => 1250, 'interest' => 19, 'bets' => 15],
        ['bank' => 'Pecander', 'amount' => 1750, 'interest' => 18, 'bets' => 20],
    ];

    public function getLoanOptions()
    {
        try {
            $bankOptions = DB::table('bank_options')->get();
            return response()->json(['bankOptions' => $bankOptions]);
        } catch (\Exception $e) {
            Log::error('Error al obtener opciones de préstamos: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener opciones'], 500);
        }
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
            $loanOption = collect($this->loanOptions)->firstWhere('amount', $amount);
            
            if (!$loanOption) {
                return response()->json([
                    'message' => 'Préstamo no válido'
                ], 400);
            }

            // Verificar si ya tiene un préstamo activo por esta cantidad
            $hasActiveLoan = Loan::where('user_id', $user->id)
                                ->where('amount', $amount)
                                ->where('is_active', true)
                                ->exists();

            if ($hasActiveLoan) {
                return response()->json([
                    'message' => 'Ya tienes un préstamo activo por esta cantidad'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Calcular el total a pagar
                $interestRate = $loanOption['interest'] / 100;
                $totalToPay = $amount * (1 + $interestRate);

                // Crear el préstamo
                Loan::create([
                    'user_id' => $user->id,
                    'bank_name' => $loanOption['bank'],
                    'amount' => $amount,
                    'interest_rate' => $loanOption['interest'],
                    'total_bets' => $loanOption['bets'],
                    'remaining_bets' => $loanOption['bets'],
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
                    'newBalance' => $statistics->balance
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
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
                              ->get();

            return response()->json([
                'activeLoans' => $activeLoans
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting active loans: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener préstamos activos'
            ], 500);
        }
    }

    public function editLoan(Request $request)
{
    $request->validate([
        'bank' => 'required|string|exists:loans,bank_name',
        'newAmount' => 'required|numeric|min:0',
    ]);

    $bank = $request->input('bank');
    $newAmount = $request->input('newAmount');

    // Buscar el préstamo relacionado
    $loan = Loan::where('bank_name', $bank)->first();

    if (!$loan) {
        return response()->json(['error' => 'Préstamo no encontrado'], 404);
    }

    DB::transaction(function () use ($loan, $newAmount, $bank) {
        // Actualizar la cantidad en el préstamo
        $loan->update(['amount' => $newAmount]);

        // Actualizar la cantidad en la tabla 'bank_options'
        DB::table('bank_options')
            ->where('bank', $bank)
            ->update(['amount' => $newAmount]);
    });

    return response()->json([
        'message' => 'Préstamo actualizado con éxito',
        'loan' => $loan, // Devuelve el préstamo actualizado
    ]);
}

public function hideLoan(Request $request)
{
    $request->validate([
        'bank' => 'required|string|exists:loans,bank_name',
    ]);

    $bank = $request->input('bank');

    $loan = Loan::where('bank_name', $bank)->first();
    if (!$loan) {
        return response()->json(['error' => 'Préstamo no encontrado'], 404);
    }

    // Alterna el estado 'hidden'
    $loan->hidden = !$loan->hidden;
    $loan->save();

    return response()->json(['message' => 'Estado del préstamo actualizado']);
}



    
}