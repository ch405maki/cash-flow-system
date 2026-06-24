<?php

namespace App\Services;

use App\Helpers\MacAddressHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActivityLogger
{
    protected ?Model $subject = null;
    protected mixed $causer = null;
    protected ?string $logName = null;
    protected array $properties = [];
    protected ?string $description = null;
    protected ?Request $httpRequest = null;

    public function __construct(?Request $request = null)
    {
        $this->httpRequest = $request ?? request();
    }

    public static function make(?Request $request = null): self
    {
        return new self($request);
    }

    public function on(?Model $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function by(mixed $causer = null): self
    {
        $this->causer = $causer ?? auth()->user();
        return $this;
    }

    public function logName(?string $name): self
    {
        $this->logName = $name;
        return $this;
    }

    public function with(array $properties): self
    {
        $this->properties = $properties;
        return $this;
    }

    public function merge(array $properties): self
    {
        $this->properties = array_merge($this->properties, $properties);
        return $this;
    }

    public function withMacAddress(): self
    {
        return $this;
    }

    public function log(string $description): void
    {
        $properties = $this->buildProperties();
        $causer = $this->causer ?? auth()->user();

        $activity = activity();

        if ($this->subject) {
            $activity->performedOn($this->subject);
        }

        $activity
            ->causedBy($causer)
            ->useLog($this->logName)
            ->withProperties($properties)
            ->log($description);
    }

    protected function buildProperties(): array
    {
        $properties = $this->properties;

        if ($this->httpRequest) {
            if (!isset($properties['ip_address'])) {
                $properties['ip_address'] = $this->httpRequest->ip();
            }
            if (!isset($properties['user_agent'])) {
                $properties['user_agent'] = $this->httpRequest->userAgent();
            }
            if (!isset($properties['mac_address'])) {
                $properties['mac_address'] = MacAddressHelper::getClientMac($this->httpRequest);
            }
        }

        return $properties;
    }

    public function statusChanged(
        Model $subject,
        string $oldStatus,
        string $newStatus,
        array $extra = [],
        ?string $description = null,
    ): void {
        $this->subject = $subject;
        $this->logName = $extra['log_name'] ?? 'Status Updated';
        $this->causer ??= auth()->user();

        $label = class_basename($subject);
        $identifier = $subject->getAttribute('request_no')
            ?? $subject->getAttribute('voucher_no')
            ?? $subject->getAttribute('po_no')
            ?? $subject->getAttribute('order_no')
            ?? "#{$subject->getKey()}";

        $description ??= "{$label} #{$identifier} status changed from {$oldStatus} to {$newStatus}";

        $this->properties = array_merge([
            'action' => 'status_update',
            'event' => 'Status Updated',
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ], $extra);

        $this->log($description);
    }

    public function created(
        Model $subject,
        array $extra = [],
        ?string $description = null,
    ): void {
        $this->subject = $subject;
        $this->logName = $extra['log_name'] ?? class_basename($subject) . ' Created';
        $this->causer ??= auth()->user();

        $label = class_basename($subject);
        $identifier = $subject->getAttribute('request_no')
            ?? $subject->getAttribute('voucher_no')
            ?? $subject->getAttribute('po_no')
            ?? $subject->getAttribute('order_no')
            ?? "#{$subject->getKey()}";

        $description ??= "{$label} #{$identifier} was created";

        unset($extra['log_name']);

        $this->properties = array_merge([
            'action' => 'create',
            'event' => "{$label} Created",
        ], $extra);

        $this->log($description);
    }

    public function released(
        Model $subject,
        string $identifier,
        int $quantity,
        array $extra = [],
        ?string $description = null,
    ): void {
        $this->subject = $subject;
        $this->logName = $extra['log_name'] ?? 'Released Items';
        $this->causer ??= auth()->user();

        $description ??= "Released {$quantity} items for {$identifier}";

        unset($extra['log_name']);

        $this->properties = array_merge([
            'action' => 'release',
            'event' => 'Items Released',
        ], $extra);

        $this->log($description);
    }

    public function uploaded(
        Model $subject,
        string $fileName,
        array $extra = [],
        ?string $description = null,
    ): void {
        $this->subject = $subject;
        $this->logName = $extra['log_name'] ?? 'File Uploaded';
        $this->causer ??= auth()->user();

        $label = class_basename($subject);

        $description ??= "File uploaded for {$label} #{$subject->getKey()}";

        unset($extra['log_name']);

        $this->properties = array_merge([
            'action' => 'upload',
            'event' => 'File Uploaded',
            'file_name' => $fileName,
        ], $extra);

        $this->log($description);
    }
}
