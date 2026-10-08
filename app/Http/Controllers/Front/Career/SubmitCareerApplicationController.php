<?php

namespace App\Http\Controllers\Front\Career;

use App\Domain\Career\Models\CareerApplication;
use App\Domain\Career\Models\CareerPosition;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Career\SubmitCareerApplicationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SubmitCareerApplicationController extends Controller
{
    public function __invoke(SubmitCareerApplicationRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['resume', 'consent', 'website']);
        $position = isset($data['career_position_id'])
            ? CareerPosition::publiclyAvailable()->findOrFail($data['career_position_id'])
            : null;
        $data['career_position_id'] = $position?->id;
        $data['position_title'] = $position?->title;
        $data['status'] = 'new';

        $path = null;
        $resume = $request->file('resume');

        try {
            if ($resume) {
                $path = $resume->store('careers/resumes', 'local');
                if ($path === false) {
                    throw new \RuntimeException('Unable to store career resume.');
                }
                $data['resume_path'] = $path;
                $data['resume_original_name'] = Str::limit($resume->getClientOriginalName(), 255, '');
                $data['resume_mime'] = $resume->getMimeType();
            }

            CareerApplication::create($data);
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        return redirect()->to(route('careers.index').'#apply')
            ->with('career_application_success', 'درخواست همکاری شما با موفقیت ثبت شد. پس از بررسی، در صورت نیاز با شما تماس می‌گیریم.');
    }
}
