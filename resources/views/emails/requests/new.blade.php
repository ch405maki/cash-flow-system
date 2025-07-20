@component('mail::message')
# New Request Created

Hello,

A new request has been submitted with the following details:

**Request Number:** {{ $requestModel->request_no }}  
**Purpose:** {{ $requestModel->purpose }}  
**Status:** {{ ucfirst($requestModel->status) }}  
**Department:** {{ $requestModel->department->name }}  
**Requested by:** {{ $requestModel->user->name }}  

## Items Requested:
@component('mail::table')
| Quantity | Unit | Description |
|----------|------|-------------|
@foreach($requestModel->details as $item)
| {{ $item->quantity }} | {{ $item->unit }} | {{ $item->item_description }} |
@endforeach
@endcomponent

@component('mail::button', ['url' => url('/requests/' . $requestModel->id)])
View Request
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent