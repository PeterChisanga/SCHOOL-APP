@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Timetable Builder</h3>
    <p>Simple admin builder: drag/drop not implemented in this minimal version. Use the form to add entries.</p>

    <div class="row">
        <div class="col-md-6">
            <form id="entryForm" method="POST" action="{{ route('timetables.entry') }}">
                @csrf
                <input type="hidden" name="school_id" value="1" />
                <div class="mb-2">
                    <label>Class</label>
                    <select name="class_id" class="form-control">
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label>Subject</label>
                    <select name="subject_id" class="form-control">
                        @foreach($subjects as $s)
                            <option value="{{ $s->subject->id }}">{{ $s->subject->name }} ({{ $s->class->name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label>Teacher</label>
                    <select name="teacher_id" class="form-control">
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->first_name }} {{ $t->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label>Day</label>
                    <select name="day" class="form-control">
                        @foreach(['Mon','Tue','Wed','Thu','Fri'] as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label>Time Slot</label>
                    <select name="time_slot_id" class="form-control">
                        @foreach($timeSlots as $slot)
                            <option value="{{ $slot->id }}">{{ $slot->name }} ({{ substr($slot->start_time,0,5) }}-{{ substr($slot->end_time,0,5) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label>Room</label>
                    <input type="text" name="room" class="form-control" />
                </div>
                <div class="mb-2">
                    <label>Term</label>
                    <input type="text" name="term" class="form-control" value="Term 1" />
                </div>
                <div class="mb-2">
                    <label>Year</label>
                    <input type="text" name="year" class="form-control" value="{{ date('Y') }}" />
                </div>
                <button class="btn btn-primary">Save Entry</button>
            </form>
        </div>

        <div class="col-md-6">
            <h5>Existing Subjects (Class - Subject - Teacher)</h5>
            <ul>
                @foreach($subjects as $s)
                    <li>{{ $s->class->name }} - {{ $s->subject->name }} - {{ $s->teacher->first_name }} {{ $s->teacher->last_name }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection

