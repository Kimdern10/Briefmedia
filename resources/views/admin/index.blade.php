@extends('layouts.admin')

@section('content')

<div class="content-inner container-fluid pb-0" id="page_layout">
    <div id="content-page" class="content-page">
        <div class="container-fluid">

            <div class="row">

                {{-- Success message --}}
                @if(session('success'))
                <div class="col-12 mb-3">
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                </div>
                @endif

                {{-- Send Newsletter Button --}}
                <div class="col-12 mb-4">
                    <a href="{{ route('admin.send-newsletter') }}" class="btn btn-primary">
                        Send Newsletter Now
                    </a>
                </div>

                {{-- Dashboard Stats --}}
<div class="row g-3 justify-content-center">

    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h5>Total Posts</h5>
                <h3>{{ $totalPosts }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h5>Draft Posts</h5>
                <h3>{{ $draftPosts }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h5>Subscribers</h5>
                <h3>{{ $subscribers }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h5>Total Likes</h5>
                <h3>{{ $likes }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h5>Total Views</h5>
                <h3>{{ $views }}</h3>
            </div>
        </div>
    </div>

</div> {{-- end row stats --}}

                {{-- Analytics Charts --}}
                <div class="row mt-4 g-4">

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Posts Per Month</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="postsChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Views Per Day</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="viewsChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Subscriber Growth</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="subscriberChart"></canvas>
                            </div>
                        </div>
                    </div>

                </div> {{-- end row charts --}}

                {{-- Latest Posts --}}
                <div class="col-12 mt-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>Latest Posts</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($latestPosts as $post)
                                    <tr>
                                        <td>{{ $post->title }}</td>
                                        <td>{{ $post->category->name ?? 'N/A' }}</td>
                                        <td>{{ $post->created_at->format('d M Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Most Viewed Posts --}}
                <div class="col-12 mt-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>Most Viewed Posts</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Views</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mostViewedPosts as $post)
                                    <tr>
                                        <td>{{ $post->title }}</td>
                                        <td>{{ $post->views }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div> {{-- end main row --}}

        </div>
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('postsChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($postsPerMonth->toArray())) !!},
        datasets: [{
            label: 'Posts Per Month',
            data: {!! json_encode(array_values($postsPerMonth->toArray())) !!},
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    }
});

new Chart(document.getElementById('viewsChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($viewsPerDay->pluck('date')) !!},
        datasets: [{
            label: 'Views Per Day',
            data: {!! json_encode($viewsPerDay->pluck('total')) !!},
            borderColor: 'rgba(255, 99, 132, 1)',
            fill: false,
            tension: 0.1
        }]
    }
});

new Chart(document.getElementById('subscriberChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($subscriberGrowth->pluck('date')) !!},
        datasets: [{
            label: 'Subscriber Growth',
            data: {!! json_encode($subscriberGrowth->pluck('total')) !!},
            borderColor: 'rgba(75, 192, 192, 1)',
            fill: false,
            tension: 0.1
        }]
    }
});
</script>

@endsection