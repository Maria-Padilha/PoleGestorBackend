<?php

namespace App\Console\Commands;

use App\Models\estoque\ReservaEquipamentosModel;
use Illuminate\Console\Command;

class AtualizarEquipamentos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:atualizar-equipamentos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $agora = now();

        $reservas = ReservaEquipamentosModel::where('status', 'reservado')
            ->where('data_inicio', '<=', $agora)
            ->get();

        foreach ($reservas as $reserva) {

            $item = $reserva->item;

            $item->quantidade_em_uso += $reserva->quantidade;
            $item->quantidade_disponivel -= $reserva->quantidade;
            $item->status = 'em_uso';
            $item->save();

            $reserva->status = 'em_uso';
            $reserva->save();
        }
    }
}
