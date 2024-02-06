<div>
    @foreach($set as $slug => $value)
        <input type="radio" id={{ $slug }} name={{ $setName }} value={{ $value }} required>
        <label for={{ $slug }}>{{ $value }}</label>
    @endforeach
</div>
