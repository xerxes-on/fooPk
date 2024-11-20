<table>
    <thead>
    <tr>
        <th>Title</th>
        <th>Ingredient 1</th>
        <th>Ingredient 2</th>
        <th>Ingredient 3</th>
        <th>Proteins</th>
        <th>Fats</th>
        <th>Carbs</th>
        <th>Calories</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $item)
        <tr>
            <td>{{ $item->title }}</td>
            @if($item->ingredients->count())
                @foreach($item->ingredients as $ingredient)
                    <td>{{ $ingredient->name }}</td>
                @endforeach
                <td>{{ $item->ingredients->sum('proteins') }}</td>
                <td>{{ $item->ingredients->sum('fats') }}</td>
                <td>{{ $item->ingredients->sum('carbohydrates') }}</td>
                <td>{{ $item->ingredients->sum('calories') }}</td>
            @else
                <td>RECORD DOES NOT EXIST</td>
                <td>RECORD DOES NOT EXIST</td>
                <td>RECORD DOES NOT EXIST</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
            @endif
        </tr>
    @endforeach
    </tbody>
</table>
