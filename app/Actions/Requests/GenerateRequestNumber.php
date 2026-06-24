<?php

namespace App\Actions\Requests;

use App\Models\Request;

class GenerateRequestNumber
{
    public function execute(): string
    {
        $prefix = 'REQ-' . now()->format('Ym') . '-';
        $lastRequest = Request::where('request_no', 'like', $prefix . '%')
            ->latest()
            ->first();

        $sequence = $lastRequest
            ? (int) str_replace($prefix, '', $lastRequest->request_no) + 1
            : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
