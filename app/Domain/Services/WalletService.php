<?php

namespace App\Domain\Services;

use App\Infrastructure\Repository\TransactionRepository;
use App\Infrastructure\Models\TransactionModel;
use App\Infrastructure\Models\UserModel;

class WalletService
{
    protected $transactionRepository;
    protected $userModel;

    protected $transactionModel;


    public function __construct()
    {
        $this->transactionRepository = new TransactionRepository();
        $this->userModel = new UserModel();
        $this->transactionModel = new TransactionModel();
    }

    public function deposit(int $userId, float $valor)
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            throw new \Exception("Usuário não encontrado.");
        }

        $user['saldo'] += $valor;
        $this->userModel->update($userId, ['saldo' => $user['saldo']]);

        $res = $this->transactionRepository->inserirTransacao(
            $userId,          
            $userId,          
            $valor,           
            'deposito',      
            true            
        );

        if ($res === true) {
            return ;
        } else {
            return redirect()->back()->with('error', $res);
        }
    
    }

    public function transfer(int $fromUserId, int $toUserId, float $valor)
    {
        $fromUser = $this->userModel->find($fromUserId);
        $toUser = $this->userModel->find($toUserId);

        if (!$fromUser || !$toUser) {
            throw new \Exception("Usuário(s) não encontrado(s).");
        }

        if ($fromUser['saldo'] < $valor) {
            throw new \Exception("Saldo insuficiente.");
        }

        $this->userModel->update($fromUserId, ['saldo' => $fromUser['saldo'] - $valor]);
        $this->userModel->update($toUserId, ['saldo' => $toUser['saldo'] + $valor]);

        $this->transactionRepository->inserirTransacao(
            $fromUserId,          
            $toUserId,          
            $valor,           
            'transferencia',      
            true            
        );
    }

   

   
}
