<?php

namespace App\Application\Audit\Support;

use App\Domain\Audit\Enums\AuditEventTypeEnum;
use App\Domain\Audit\Models\AuditEvent;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

class AuditEventPresenter
{
    public function typeLabel(AuditEventTypeEnum $type): string
    {
        return match ($type) {
            AuditEventTypeEnum::CREATED => 'ایجاد',
            AuditEventTypeEnum::UPDATED => 'ویرایش',
            AuditEventTypeEnum::DELETED => 'حذف',
            AuditEventTypeEnum::FORCE_DELETED => 'حذف دائمی',
            AuditEventTypeEnum::RESTORED => 'بازگردانی',
            AuditEventTypeEnum::ATTACHED => 'اتصال رابطه',
            AuditEventTypeEnum::DETACHED => 'جداسازی رابطه',
        };
    }

    public function subjectTypeLabel(string $type): string
    {
        $class = Relation::getMorphedModel($type) ?: $type;
        $short = Str::studly(class_basename($class));

        return config("audit-history.subjects.{$short}") ?: Str::headline($short);
    }

    public function subjectLabel(AuditEvent $event): string
    {
        foreach ([$event->subject_label, $event->new_data['title'] ?? null, $event->old_data['title'] ?? null,
            $event->new_data['name'] ?? null, $event->old_data['name'] ?? null] as $label) {
            if (is_scalar($label) && filled($label)) {
                return (string) $label;
            }
        }

        return '#'.$event->subject_id;
    }

    public function actorLabel(AuditEvent $event): string
    {
        if ($event->actor_name === 'system') {
            return 'سامانه';
        }
        if ($event->actor_name === 'guest') {
            return 'مهمان';
        }

        return $event->actor_name ?: ($event->user_id ? 'کاربر #'.$event->user_id : 'سامانه');
    }

    public function fieldLabel(string $field): string
    {
        return config("audit-history.fields.{$field}") ?: Str::headline($field);
    }

    public function summary(AuditEvent $event): string
    {
        $relation = $event->meta['relation'] ?? null;
        if (is_string($relation)) {
            $related = $this->subjectTypeLabel((string) ($event->meta['related_type'] ?? $relation));
            $id = $event->meta['related_id'] ?? null;

            return $related.($id !== null ? ' #'.$id : '');
        }

        if ($event->type === AuditEventTypeEnum::UPDATED) {
            $fields = array_keys($event->new_data ?? []);
            $names = array_map($this->fieldLabel(...), array_slice($fields, 0, 2));

            return implode('، ', $names).(count($fields) > 2 ? ' و '.(count($fields) - 2).' فیلد دیگر' : '');
        }

        return match ($event->type) {
            AuditEventTypeEnum::CREATED => 'ثبت اولیه اطلاعات',
            AuditEventTypeEnum::RESTORED => 'بازگشت از حذف',
            default => 'مشاهده جزئیات رویداد',
        };
    }

    /** @return list<array{field: string, old: mixed, new: mixed, has_old: bool, has_new: bool}> */
    public function changes(AuditEvent $event): array
    {
        $old = $event->old_data ?? [];
        $new = $event->new_data ?? [];
        $rows = [];

        foreach (array_unique(array_merge(array_keys($old), array_keys($new))) as $field) {
            $rows[] = [
                'field' => $field,
                'old' => $old[$field] ?? null,
                'new' => $new[$field] ?? null,
                'has_old' => array_key_exists($field, $old),
                'has_new' => array_key_exists($field, $new),
            ];
        }

        return $rows;
    }

    public function formatValue(mixed $value): string
    {
        if ($value === null) {
            return 'تهی';
        }
        if (is_bool($value)) {
            return $value ? 'بله' : 'خیر';
        }
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: '—';
        }

        $text = (string) $value;
        if ((str_starts_with($text, '{') || str_starts_with($text, '[')) && is_array(json_decode($text, true))) {
            return json_encode(json_decode($text, true), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: $text;
        }

        return $text;
    }
}
