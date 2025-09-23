<x-layout>
  <x-slot:heading>
    Job - {{ $job->title }}
  </x-slot:heading>
    <p>Salary - {{ $job->salary }}</p>
    <p>Employer - {{ $job->employer->name }}</p>
</x-layout>