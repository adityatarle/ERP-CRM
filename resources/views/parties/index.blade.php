@include('layout.header')



<div class="container">
    <h1>Parties</h1>
    <!-- Import Form -->
    <form action="{{ route('parties.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" accept=".xlsx, .csv" required>
        <button type="submit">Import Parties</button>
    </form>

    <!-- Party List -->
    <table class="table">
        <thead>
            <tr>
                <th>Party Name</th>
                <th>GST/IN</th>
                <th>Email/EmailCC</th>
                <th>Phone/Mobile Number</th>
                <th>Address</th>
            </tr>
        </thead>
        <tbody>
            @foreach($parties as $party)
            <tr>
                <td>{{ $party->name }}</td>
                <td>{{ $party->gst_in }}</td>
                <td>{{ $party->email }}</td>
                <td>{{ $party->phone_number }}</td>
                <td>{{ $party->address }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@include('layout.footer')