@component('mail::message')
# New Request Created

Hello,

A new request has been submitted with the following details:

**Request Number:** {{ $requestModel->request_no }}  
**Purpose:** {{ $requestModel->purpose }}  
**Status:** {{ ucfirst($requestModel->status) }}  

## Items Requested:
@component('mail::table')
| Description | Unit | Quantity |
|-------------|------|----------|
@foreach($requestModel->details as $item)
| {{ $item->item_description }} | {{ $item->unit }} | {{ $item->quantity }} |
@endforeach
@endcomponent

@component('mail::button', ['url' => url('/requests/' . $requestModel->id)])
View Request
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent