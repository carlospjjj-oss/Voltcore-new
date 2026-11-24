@extends('layouts.app')
@section('title', 'Agenda')
@section('page-title', 'Agenda')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/index.global.min.css">
<style>
    .fc { font-family: inherit; }
    .fc-toolbar-title { font-size: 1.1rem !important; font-weight: 600; }
    .fc .fc-button { background: #f59e0b; border-color: #f59e0b; font-size: 0.75rem; }
    .fc .fc-button:hover { background: #d97706; border-color: #d97706; }
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active { background: #d97706; border-color: #d97706; }
    .fc-event { cursor: pointer; border-radius: 4px; font-size: 0.75rem; }
    .dark .fc-theme-standard td, .dark .fc-theme-standard th { border-color: rgba(255,255,255,0.1); }
    .dark .fc-col-header-cell-cushion, .dark .fc-daygrid-day-number,
    .dark .fc-timegrid-axis-cushion, .dark .fc-timegrid-slot-label-cushion { color: #d1d5db; }
    .dark .fc-toolbar-title { color: #f9fafb; }
    .dark .fc-daygrid-day.fc-day-today { background: rgba(245,158,11,0.1) !important; }
    .fc-daygrid-day.fc-day-today { background: rgba(245,158,11,0.08) !important; }
</style>
@endpush

@section('content')
<div class="space-y-4">
    {{-- Legenda --}}
    <div class="flex gap-4 text-sm">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full inline-block" style="background:#f59e0b"></span> Visita Agendada</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full inline-block" style="background:#22c55e"></span> Visita Realizada</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full inline-block" style="background:#ef4444"></span> Visita Cancelada</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full inline-block" style="background:#6366f1"></span> OS Instalação</span>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <div id="calendar"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(el, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        buttonText: { today: 'Hoje', month: 'Mês', week: 'Semana', list: 'Lista' },
        height: 'auto',
        events: {
            url: '{{ route("agenda.eventos") }}',
            method: 'GET',
            extraParams: {},
            failure: function() { alert('Erro ao carregar eventos.'); },
        },
        eventClick: function(info) {
            if (info.event.url) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.url;
            }
        },
        eventDisplay: 'block',
        dayMaxEvents: 4,
    });
    calendar.render();
});
</script>
@endpush
