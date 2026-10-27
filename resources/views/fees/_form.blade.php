@php($fee = $fee ?? null)

<div class="form-group">
    <label for="class_id">Class</label>
    <select name="class_id" id="class_id" class="form-control">
        <option value="">All classes</option>
        @foreach($classes as $class)
            <option value="{{ $class->id }}" {{ old('class_id', $fee->class_id ?? '') == $class->id ? 'selected' : '' }}>
                {{ $class->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="type">Fee Type <span class="text-danger">*</span></label>
    <select name="type" id="type" class="form-control" required>
        <option value="">Select Fee Type</option>
        @foreach($types as $type)
            <option value="{{ $type }}" {{ old('type', $fee->type ?? '') == $type ? 'selected' : '' }}>{{ $type }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="term">Term <span class="text-danger">*</span></label>
    <select name="term" id="term" class="form-control" required>
        @foreach($terms as $term)
            <option value="{{ $term }}" {{ old('term', $fee->term ?? 'Term 1') == $term ? 'selected' : '' }}>{{ $term }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="amount">Amount (K) <span class="text-danger">*</span></label>
    <input type="number" step="0.01" min="0" name="amount" id="amount" class="form-control"
           value="{{ old('amount', $fee->amount ?? '') }}" required>
</div>

<div class="form-group form-check">
    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1"
           {{ old('is_active', $fee->is_active ?? true) ? 'checked' : '' }}>
    <label for="is_active" class="form-check-label">Active</label>
</div>
