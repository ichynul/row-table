<div {!! $attributes !!} >
    @foreach($rows as $row)
        @foreach($row as $item)
            {!! $item !!}
        @endforeach
    @endforeach
</div>