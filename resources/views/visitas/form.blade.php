@extends('layouts.app')
@section('title', $visita->exists ? 'Editar Visita' : 'Agendar Visita Técnica')
@section('page-title', $visita->exists ? 'Editar Visita Técnica' : 'Agendar Visita Técnica')

@section('content')
<div x-data="visitaForm()" class="max-w-2xl">

    @if($errors->any())
    <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-300">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          action="{{ $visita->exists ? route('visitas.update', $visita) : route('visitas.store') }}"
          id="formVisita">
        @csrf
        @if($visita->exists) @method('PUT') @endif

        <div class="card mb-5">
            <div class="card-header">
                <h2 class="font-semibold font-display text-gray-900 dark:text-white">Dados da Visita</h2>
            </div>
            <div class="card-body space-y-4">

                {{-- Cliente com autocomplete --}}
                <div>
                    <label class="input-label">Cliente *</label>
                    <div class="relative">
                        <input type="text" id="clienteNome" autocomplete="off"
                               class="input"
                               placeholder="Digite o nome do cliente..."
                               value="{{ old('cliente_nome', $visita->cliente?->nome) }}"
                               @input.debounce.300ms="buscarClientes($event.target.value)"
                               @blur="fecharSugestoes()"
                               required>
                        <input type="hidden" name="cliente_id" id="clienteId"
                               value="{{ old('cliente_id', $visita->cliente_id) }}" required>
                        <div x-show="sugestoes.length > 0" x-cloak
                             class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="c in sugestoes" :key="c.id">
                                <div @mousedown.prevent="selecionarCliente(c)"
                                     class="px-3 py-2 cursor-pointer hover:bg-yellow-50 dark:hover:bg-yellow-900/20 text-sm">
                                    <div class="font-medium text-gray-900 dark:text-white" x-text="c.nome"></div>
                                    <div class="text-xs text-gray-500" x-text="c.endereco"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Técnico --}}
                <div>
                    <label class="input-label">Técnico</label>
                    <select name="usuario_id" class="input">
                        <option value="">Selecione...</option>
                        @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" @selected(old('usuario_id', $visita->usuario_id) == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Data e Hora --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="input-label">Data da Visita *</label>
                        <input type="date" name="data_visita" required
                               value="{{ old('data_visita', $visita->data_visita?->format('Y-m-d')) }}"
                               class="input">
                    </div>
                    <div>
                        <label class="input-label">Horário</label>
                        <input type="time" name="hora_visita"
                               value="{{ old('hora_visita', $visita->hora_visita ? substr($visita->hora_visita,0,5) : '') }}"
                               class="input">
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="input-label">Status *</label>
                    <select name="status" id="status" x-model="status" required class="input max-w-xs">
                        @foreach($statusList as $k => $v)
                        <option value="{{ $k }}" @selected(old('status', $visita->status ?? 'Agendada') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tipo de Serviço --}}
                <div>
                    <label class="input-label">Tipo de Serviço</label>
                    <select name="tipo_servico" class="input max-w-xs">
                        <option value="">Selecione...</option>
                        @foreach($tipoServicoList as $t)
                        <option value="{{ $t }}" @selected(old('tipo_servico', $visita->tipo_servico) === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Endereço --}}
                <div>
                    <label class="input-label">Endereço</label>
                    <input type="text" name="endereco" id="enderecoVisita"
                           value="{{ old('endereco', $visita->endereco) }}"
                           class="input" placeholder="Endereço da visita">
                </div>

                {{-- Observações — bloqueado quando Realizada --}}
                <div>
                    <label class="input-label">Observações</label>
                    <textarea name="observacoes" id="observacoes" rows="3"
                              :readonly="status === 'Realizada'"
                              :class="status === 'Realizada' ? 'input bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : 'input'"
                              class="resize-none"
                              placeholder="Observações antes da visita...">{{ old('observacoes', $visita->observacoes) }}</textarea>
                    <p x-show="status === 'Realizada'" class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                        Campo bloqueado quando status é Realizada.
                    </p>
                </div>

                {{-- Resultado — bloqueado quando NÃO é Realizada --}}
                <div>
                    <label class="input-label">Resultado</label>
                    <textarea name="resultado" id="resultado" rows="3"
                              :readonly="status !== 'Realizada'"
                              :class="status !== 'Realizada' ? 'input bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : 'input'"
                              class="resize-none"
                              placeholder="Resultado após realização...">{{ old('resultado', $visita->resultado) }}</textarea>
                    <p x-show="status !== 'Realizada'" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Disponível somente quando status for Realizada.
                    </p>
                </div>

            </div>
        </div>

        {{-- Ações do formulário --}}
        <div class="flex justify-end gap-3 mb-6">
            <a href="{{ route('visitas.index') }}" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Voltar
            </a>
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ $visita->exists ? 'Atualizar' : 'Agendar' }}
            </button>
        </div>

    </form>

    {{-- ================================================================ --}}
    {{-- GED: Fotos e Vídeos — só aparece na edição --}}
    {{-- ================================================================ --}}
    @if($visita->exists)
    <div class="card mb-5">
        <div class="card-header">
            <h2 class="font-semibold font-display text-gray-900 dark:text-white">Fotos e Vídeos (GED)</h2>
        </div>
        <div class="card-body">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Máximo 6 fotos e 1 vídeo (até 1:30 cada) por visita.
            </p>

            <div class="mb-4 flex items-center gap-3">
                <label for="inputMidia"
                       class="btn btn-primary cursor-pointer inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Anexar Foto/Vídeo
                </label>
                <input type="file" id="inputMidia"
                       accept=".jpg,.jpeg,.png,.mp4,.mov,.webm"
                       multiple style="display:none">
                <span id="uploadStatus" class="text-sm text-gray-600 dark:text-gray-400"></span>
            </div>

            {{-- Contador --}}
            <div class="flex gap-4 text-xs text-gray-500 dark:text-gray-400 mb-4">
                <span id="contadorFotos">
                    Fotos: {{ $midias->where('tipo','foto')->count() }}/6
                </span>
                <span id="contadorVideos">
                    Vídeos: {{ $midias->where('tipo','video')->count() }}/1
                </span>
            </div>

            {{-- Galeria --}}
            <div id="galeriaMidia"
                 class="flex flex-wrap gap-3">
                @foreach($midias as $m)
                <div class="midia-item" data-id="{{ $m->id }}" data-tipo="{{ $m->tipo }}"
                     style="width:150px;">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        @if($m->tipo === 'foto')
                        <img src="{{ asset('storage/visita_midias/' . $m->arquivo) }}"
                             class="w-full h-24 object-cover">
                        @else
                        <video src="{{ asset('storage/visita_midias/' . $m->arquivo) }}"
                               class="w-full h-24 object-cover" controls></video>
                        @endif
                    </div>
                    <button type="button"
                            onclick="excluirMidia({{ $m->id }}, this)"
                            class="mt-1 w-full btn btn-danger text-xs py-1">
                        <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Excluir
                    </button>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
@vite('resources/js/app.js')
<script>
function visitaForm() {
    return {
        status: '{{ old('status', $visita->status ?? 'Agendada') }}',
        sugestoes: [],

        async buscarClientes(q) {
            if (q.length < 2) { this.sugestoes = []; return; }
            try {
                const r = await fetch(`{{ route('visitas.buscar-cliente') }}?q=${encodeURIComponent(q)}`);
                this.sugestoes = await r.json();
            } catch (e) { this.sugestoes = []; }
        },

        selecionarCliente(c) {
            document.getElementById('clienteNome').value = c.nome;
            document.getElementById('clienteId').value   = c.id;
            if (c.endereco) document.getElementById('enderecoVisita').value = c.endereco;
            this.sugestoes = [];
        },

        fecharSugestoes() {
            setTimeout(() => { this.sugestoes = []; }, 200);
        },
    };
}

@if($visita->exists)
// ---- GED ----
var visitaId = {{ $visita->id }};
var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

function atualizarContadores() {
    var fotos  = document.querySelectorAll('#galeriaMidia .midia-item[data-tipo="foto"]').length;
    var videos = document.querySelectorAll('#galeriaMidia .midia-item[data-tipo="video"]').length;
    document.getElementById('contadorFotos').textContent  = 'Fotos: ' + fotos + '/6';
    document.getElementById('contadorVideos').textContent = 'Vídeos: ' + videos + '/1';
}

document.getElementById('inputMidia').addEventListener('change', function() {
    var files = Array.from(this.files);
    if (!files.length) return;

    var fila    = files.slice();
    var total   = fila.length;
    var enviados = 0, erros = 0;
    var statusEl = document.getElementById('uploadStatus');

    statusEl.textContent = 'Enviando 0/' + total + '...';

    function enviarProximo() {
        if (!fila.length) {
            document.getElementById('inputMidia').value = '';
            statusEl.textContent = erros > 0
                ? 'Concluído: ' + enviados + ' enviado(s), ' + erros + ' erro(s).'
                : 'Todos os ' + enviados + ' arquivo(s) enviados!';
            setTimeout(() => { statusEl.textContent = ''; }, 4000);
            return;
        }

        var file = fila.shift();
        var ext  = file.name.split('.').pop().toLowerCase();
        var isVideo = ['mp4','mov','webm'].includes(ext);

        function enviarUpload() {
            statusEl.textContent = 'Enviando ' + (enviados + erros + 1) + '/' + total + '...';

            var fd = new FormData();
            fd.append('midia', file);
            fd.append('_token', csrfToken);

            fetch(`/visitas/${visitaId}/midia`, {
                method: 'POST',
                body: fd,
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
            .then(r => r.json())
            .then(resp => {
                if (resp.success) {
                    enviados++;
                    var mediaTag = resp.tipo === 'foto'
                        ? `<img src="${resp.url}" class="w-full h-24 object-cover">`
                        : `<video src="${resp.url}" class="w-full h-24 object-cover" controls></video>`;
                    var div = document.createElement('div');
                    div.className = 'midia-item';
                    div.dataset.id   = resp.id;
                    div.dataset.tipo = resp.tipo;
                    div.style.width  = '150px';
                    div.innerHTML = `
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">${mediaTag}</div>
                        <button type="button" onclick="excluirMidia(${resp.id}, this)"
                                class="mt-1 w-full btn btn-danger text-xs py-1">
                            <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Excluir
                        </button>`;
                    document.getElementById('galeriaMidia').appendChild(div);
                    atualizarContadores();
                } else {
                    erros++;
                    statusEl.textContent = 'Erro: ' + resp.message;
                }
                enviarProximo();
            })
            .catch(() => {
                erros++;
                enviarProximo();
            });
        }

        if (isVideo) {
            var v = document.createElement('video');
            v.preload = 'metadata';
            v.onloadedmetadata = function() {
                URL.revokeObjectURL(v.src);
                if (v.duration > 90) {
                    alert('O vídeo "' + file.name + '" deve ter no máximo 1:30. Duração: ' + Math.round(v.duration) + 's');
                    erros++;
                    enviarProximo();
                } else {
                    enviarUpload();
                }
            };
            v.src = URL.createObjectURL(file);
        } else {
            enviarUpload();
        }
    }

    enviarProximo();
});

function excluirMidia(id, btn) {
    if (!confirm('Deseja realmente excluir este arquivo?')) return;
    var item = btn.closest('.midia-item');

    fetch(`/visitas/${visitaId}/midia`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ id: id }),
    })
    .then(r => r.json())
    .then(resp => {
        if (resp.success) {
            item.remove();
            atualizarContadores();
        } else {
            alert('Erro ao excluir arquivo.');
        }
    });
}
@endif
</script>
@endpush
