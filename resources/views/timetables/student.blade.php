@extends('layouts.app')

@section('content')
<div class="container">
    <h3>{{ $class->name }} - Timetable ({{ $term }} {{ $year }})</h3>
    <a href="{{ route('timetables.download', ['type'=>'class','id'=>$class->id,'term'=>$term,'year'=>$year]) }}" class="btn btn-primary">Download PDF</a>
    <a href="{{ route('timetables.share', ['type'=>'class','id'=>$class->id,'term'=>$term,'year'=>$year]) }}" class="btn btn-success">Share via WhatsApp</a>

    <style>
        /* Visual cue when timetable updates */
        #timetable-table.flash-updated {
            box-shadow: 0 0 0 4px rgba(40,167,69,0.25);
            transition: box-shadow 0.4s ease-out;
        }
    </style>

    <div id="timetable-table">
        @include('timetables._table')
    </div>
</div>

@push('scripts')
<script>
(function(){
    const id = {{ $class->id }};
    const term = encodeURIComponent('{{ $term }}');
    const year = '{{ $year }}';
    let lastSeen = 0;

    async function fetchFragment(){
        try{
            const res = await fetch(`/timetables/fragment/class/${id}?term=${term}&year=${year}`, { credentials: 'same-origin' });
            if(!res.ok) return;
            const html = await res.text();
            const container = document.getElementById('timetable-table');
            if(container) {
                container.innerHTML = html;
                // visual cue
                container.classList.add('flash-updated');
                setTimeout(() => container.classList.remove('flash-updated'), 800);
            }
        }catch(e){
            console.error('Fragment fetch error', e);
        }
    }

    async function poll(){
        try{
            const res = await fetch(`/api/timetables/class/${id}/meta?term=${term}&year=${year}`, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            if(!res.ok) return;
            const data = await res.json();
            if(typeof data.last_updated_ts === 'undefined') return;

            const ts = data.last_updated_ts || 0;

            if(lastSeen === 0){
                // initialize seen timestamp but don't trigger a reload (page already has current HTML)
                lastSeen = ts;
                return;
            }

            if(ts !== lastSeen){
                lastSeen = ts;
                // automatically fetch updated HTML fragment and replace the table
                fetchFragment();
            }
        }catch(e){
            console.error('Timetable meta poll error', e);
        }
    }

    // initial poll and load
    poll();
    setInterval(poll, 15000); // poll every 15s
})();
</script>
@endpush

@endsection
