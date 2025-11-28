<?php

namespace App\Console\Commands;

use App\Models\estoque\ReservaEquipamentosModel;
use Illuminate\Console\Command;

class AtualizarStatusEquipamentos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:atualizar-status-equipamentos';

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

        $reservas = ReservaEquipamentosModel::where('data_inicio', '<=', $agora)
            ->where('data_fim', '>=', $agora)
            ->get();

        foreach ($reservas as $reserva) {
            $item = $reserva->item;

            if ($item->status !== 'em_uso') {
                $item->status = 'em_uso';
                $item->quantidade_em_uso += 1;
                $item->save();
            }
        }
    }
}
