<div>
    @foreach($set as $slug => $value)
        <input type="radio" id={{ $slug }} name={{ $setName }} value={{ $value }}>
        <label for={{ $slug }}>{{ $value }}</label><br>
    @endforeach
</div>
