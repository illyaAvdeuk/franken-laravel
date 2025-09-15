<x-layout>
  <x-slot:heading>
    Jobs listing
  </x-slot:heading>
  <ul>
  @foreach ($jobs as $job)
    <li>
        <a href="/jobs/{{ $job->id }}">
          <strong>{{ $job->title }}</strong> 
        </a> - {{ $job->salary }}
    </li>
  @endforeach
  </ul>
</x-layout>