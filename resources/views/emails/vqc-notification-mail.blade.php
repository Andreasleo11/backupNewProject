<x-mail::message> Here is the current status of reports: <br>
  <ul>
    <li>Approved Documents: {{ $data['approved'] }}</li>
    <li>Documents Waiting for Signature: {{ $data['waitingSignature'] }}</li>
    <li>Documents Waiting for Approval: {{ $data['waitingApproval'] }}</li>
    <li>Rejected Documents: {{ $data['rejected'] }}</li>
  </ul>
  <x-mail::button :url="$data['url']">Check Now</x-mail::button> Thanks, <br>{{ config('app.name') }}
</x-mail::message>
