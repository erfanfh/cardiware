@php use App\Domain\Raffle\Enums\DrawCampaignStatusEnum; @endphp
@can('raffle-campaign.transition')
    <x-admin.metabox title="کنترل وضعیت" :collapsible="false">
        <div class="space-y-3">
            @if($drawCampaign->status === DrawCampaignStatusEnum::DRAFT)
                <x-admin.form action="{{ route('admin.draw-campaigns.transition',$drawCampaign) }}" method="post"><input type="hidden" name="status" value="scheduled"><x-admin.form.button type="submit" variant="secondary" class="w-full justify-center">زمان بندی انتشار</x-admin.form.button></x-admin.form>
                <x-admin.form action="{{ route('admin.draw-campaigns.transition',$drawCampaign) }}" method="post"><input type="hidden" name="status" value="open"><x-admin.form.button type="submit" variant="solid" class="w-full justify-center">باز کردن قرعه کشی</x-admin.form.button></x-admin.form>
            @elseif($drawCampaign->status === DrawCampaignStatusEnum::SCHEDULED)
                <x-admin.form action="{{ route('admin.draw-campaigns.transition',$drawCampaign) }}" method="post"><input type="hidden" name="status" value="open"><x-admin.form.button type="submit" variant="solid" class="w-full justify-center">باز کردن اکنون</x-admin.form.button></x-admin.form>
            @elseif($drawCampaign->status === DrawCampaignStatusEnum::OPEN)
                <x-admin.form action="{{ route('admin.draw-campaigns.transition',$drawCampaign) }}" method="post" class="js-confirm" data-confirm-title="بستن قرعه کشی" data-confirm-text="پس از بستن، بلیط جدید صادر نمی شود."><input type="hidden" name="status" value="closed"><x-admin.form.button type="submit" variant="solid" class="w-full justify-center">بستن دریافت بلیط</x-admin.form.button></x-admin.form>
            @elseif($drawCampaign->status === DrawCampaignStatusEnum::CLOSED)
                <div class="rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-xs leading-7 text-brand-800">کمپین برای اجرای Draw آماده است. دکمه شروع قرعه کشی بعد از پیاده سازی Draw Engine در مرحله بعد فعال می شود.</div>
            @endif

            @if(in_array($drawCampaign->status,[DrawCampaignStatusEnum::DRAFT,DrawCampaignStatusEnum::SCHEDULED,DrawCampaignStatusEnum::OPEN,DrawCampaignStatusEnum::CLOSED],true))
                <x-admin.form action="{{ route('admin.draw-campaigns.transition',$drawCampaign) }}" method="post" class="js-confirm" data-confirm-title="لغو قرعه کشی" data-confirm-text="تمام بلیط های فعال باطل خواهند شد. این عملیات برگشت مستقیم ندارد.">
                    <input type="hidden" name="status" value="cancelled">
                    <x-admin.field required label="دلیل لغو"><x-admin.form.textarea name="reason" rows="2" placeholder="دلیل لغو را ثبت کنید."/></x-admin.field>
                    <div class="mt-2"><x-admin.form.button type="submit" variant="danger" class="w-full justify-center">لغو قرعه کشی</x-admin.form.button></div>
                </x-admin.form>
            @endif
        </div>
    </x-admin.metabox>
@endcan
