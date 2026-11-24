<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\OrdemServicoController;
use App\Http\Controllers\ExecucaoController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\LancamentoController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\VisitaController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PermissaoController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\GarantiaController;
use App\Http\Controllers\CobrancaController;

// -------------------------------------------------------------------------
// Autenticação
// -------------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);
});

Route::post('/logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// -------------------------------------------------------------------------
// App — requer autenticação
// -------------------------------------------------------------------------
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Clientes
    Route::resource('clientes', ClienteController::class)->except(['show']);
    Route::get('clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');
    Route::get('clientes/{cliente}/historico', [ClienteController::class, 'historico'])->name('clientes.historico');

    // Ordens de Serviço
    Route::prefix('os')->name('os.')->group(function () {
        Route::get('/', [OrdemServicoController::class, 'index'])->name('index');
        Route::get('/nova', [OrdemServicoController::class, 'create'])->name('create');
        Route::post('/', [OrdemServicoController::class, 'store'])->name('store');
        Route::get('/{os}/editar', [OrdemServicoController::class, 'edit'])->name('editar');
        Route::put('/{os}', [OrdemServicoController::class, 'update'])->name('update');
        Route::delete('/{os}', [OrdemServicoController::class, 'destroy'])->name('destroy');
        Route::post('/{os}/finalizar', [OrdemServicoController::class, 'finalizar'])->name('finalizar');
        Route::get('/{os}/pdf', [OrdemServicoController::class, 'pdf'])->name('pdf');

        // AJAX — Produtos
        Route::post('/{os}/adicionar-produto', [OrdemServicoController::class, 'adicionarProduto'])->name('produto.add');
        Route::delete('/{os}/remover-produto', [OrdemServicoController::class, 'removerProduto'])->name('produto.remove');

        // AJAX — Serviços
        Route::post('/{os}/adicionar-servico', [OrdemServicoController::class, 'adicionarServico'])->name('servico.add');
        Route::delete('/{os}/remover-servico', [OrdemServicoController::class, 'removerServico'])->name('servico.remove');

        // AJAX — Anotações
        Route::post('/{os}/anotacao', [OrdemServicoController::class, 'adicionarAnotacao'])->name('anotacao.add');
        Route::delete('/{os}/anotacao/{anotacao}', [OrdemServicoController::class, 'removerAnotacao'])->name('anotacao.remove');

        // AJAX — Anexos
        Route::post('/{os}/anexo', [OrdemServicoController::class, 'uploadAnexo'])->name('anexo.upload');
        Route::delete('/{os}/anexo/{anexo}', [OrdemServicoController::class, 'removerAnexo'])->name('anexo.remove');
    });

    // Execuções
    Route::prefix('execucoes')->name('execucoes.')->group(function () {
        Route::get('/', [ExecucaoController::class, 'index'])->name('index');
        Route::get('/{execucao}/editar', [ExecucaoController::class, 'edit'])->name('editar');
        Route::put('/{execucao}', [ExecucaoController::class, 'update'])->name('update');
        Route::post('/{execucao}/checklist', [ExecucaoController::class, 'checklist'])->name('checklist');
        Route::post('/{execucao}/anexo', [ExecucaoController::class, 'anexo'])->name('anexo');
    });

    // Vendas
    Route::prefix('vendas')->name('vendas.')->group(function () {
        Route::get('/', [VendaController::class, 'index'])->name('index');
        Route::get('/nova', [VendaController::class, 'create'])->name('create');
        Route::post('/', [VendaController::class, 'store'])->name('store');
        Route::get('/{venda}/editar', [VendaController::class, 'edit'])->name('editar');
        Route::put('/{venda}', [VendaController::class, 'update'])->name('update');
        Route::delete('/{venda}', [VendaController::class, 'destroy'])->name('destroy');
        Route::post('/{venda}/faturar', [VendaController::class, 'faturar'])->name('faturar');
        Route::get('/{venda}/pdf', [VendaController::class, 'pdf'])->name('pdf');

        // Itens AJAX
        Route::post('/{venda}/adicionar-produto', [VendaController::class, 'adicionarProduto'])->name('produto.add');
        Route::post('/{venda}/adicionar-servico', [VendaController::class, 'adicionarServico'])->name('servico.add');
        Route::delete('/{venda}/remover-item', [VendaController::class, 'removerItem'])->name('item.remove');
        Route::post('/{venda}/desconto', [VendaController::class, 'atualizarDesconto'])->name('desconto');
    });

    // Financeiro
    Route::prefix('financeiro')->name('financeiro.')->group(function () {
        Route::get('/', [FinanceiroController::class, 'index'])->name('index');
        Route::get('/lancamentos', [LancamentoController::class, 'index'])->name('lancamentos');
        Route::post('/lancamentos', [LancamentoController::class, 'store'])->name('lancamentos.store');
        Route::put('/lancamentos/{lancamento}', [LancamentoController::class, 'update'])->name('lancamentos.update');
        Route::delete('/lancamentos/{lancamento}', [LancamentoController::class, 'destroy'])->name('lancamentos.destroy');
        Route::post('/lancamentos/{lancamento}/baixar', [LancamentoController::class, 'baixar'])->name('lancamentos.baixar');
        Route::get('/relatorio', [FinanceiroController::class, 'relatorio'])->name('relatorio');
    });

    // Produtos e Serviços
    Route::resource('produtos', ProdutoController::class)->except(['show']);
    Route::resource('servicos', ServicoController::class)->except(['show']);

    // Termos de Garantias
    Route::get('/garantias', [GarantiaController::class, 'index'])->name('garantias.index');

    // Cobranças
    Route::get('/cobrancas', [CobrancaController::class, 'index'])->name('cobrancas.index');

    // Visitas Técnicas
    Route::prefix('visitas')->name('visitas.')->group(function () {
        Route::get('/', [VisitaController::class, 'index'])->name('index');
        Route::get('/nova', [VisitaController::class, 'create'])->name('create');
        Route::post('/', [VisitaController::class, 'store'])->name('store');
        Route::get('/{visita}/editar', [VisitaController::class, 'edit'])->name('edit');
        Route::put('/{visita}', [VisitaController::class, 'update'])->name('update');
        Route::delete('/{visita}', [VisitaController::class, 'destroy'])->name('destroy');
        Route::post('/{visita}/midia', [VisitaController::class, 'uploadMidia'])->name('midia.upload');
        Route::delete('/{visita}/midia', [VisitaController::class, 'excluirMidia'])->name('midia.destroy');
        Route::get('/buscar-cliente', [VisitaController::class, 'buscarCliente'])->name('buscar-cliente');
    });

    // Relatórios
    Route::prefix('relatorios')->name('relatorios.')->group(function () {
        Route::get('/', [RelatorioController::class, 'index'])->name('index');
        Route::get('/os', [RelatorioController::class, 'os'])->name('os');
        Route::get('/financeiro', [RelatorioController::class, 'financeiro'])->name('financeiro');
        Route::get('/clientes', [RelatorioController::class, 'clientes'])->name('clientes');
        Route::get('/execucoes', [RelatorioController::class, 'execucoes'])->name('execucoes');
    });

    // Agenda / Calendário
    Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda');
    Route::get('/agenda/eventos', [AgendaController::class, 'eventos'])->name('agenda.eventos');

    // Administração
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
        Route::resource('permissoes', PermissaoController::class)->except(['show']);
    });

});

// -------------------------------------------------------------------------
// API interna (AJAX)
// -------------------------------------------------------------------------
Route::middleware(['auth'])->prefix('api/v1')->name('api.')->group(function () {
    Route::get('/buscar-clientes', [ApiController::class, 'buscarClientes'])->name('clientes.buscar');
    Route::get('/buscar-produtos', [ApiController::class, 'buscarProdutos'])->name('produtos.buscar');
    Route::get('/buscar-servicos', [ApiController::class, 'buscarServicos'])->name('servicos.buscar');
    Route::get('/cep/{cep}',      [ApiController::class, 'buscarCep'])->name('cep');
});
